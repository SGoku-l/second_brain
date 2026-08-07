<?php

namespace App\Jobs;

use App\Exceptions\SubscriptionLimitExceeded;
use App\Models\Chunks;
use App\Models\Source;
use App\Services\ErrorLogger;
use App\Services\Ingestion\Chunker;
use App\Services\Ingestion\Embedder;
use App\Services\Ingestion\GithubIngestor;
use App\Services\Subscriptions\SubscriptionLimits;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngestRepoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(
        public string $sourceId,
        public string $repoFullName,
    ) {
        //
    }

    public function handle(ErrorLogger $errorLogger, SubscriptionLimits $limits): void
    {
        $source = Source::find($this->sourceId);

        if (! $source) {
            Log::warning('Repository ingestion skipped because its source no longer exists.', [
                'repository' => $this->repoFullName,
                'source_id' => $this->sourceId,
            ]);

            return;
        }

        $user = $source->workspace?->user;

        if (! $user || ! $user->github_token) {
            $this->markFailed($source, 'GitHub is not connected for this workspace.', null, $errorLogger);

            return;
        }

        try {
            $chunksIndexed = 0;

            Log::info('Repository ingestion started.', [
                'repository' => $this->repoFullName,
                'source_id' => $source->id,
            ]);

            $token = decrypt($user->github_token);
            $ingestor = new GithubIngestor($token);
            $files = $ingestor->fetchRepoFiles($this->repoFullName);
            $chunker = new Chunker;
            $embedder = new Embedder;

            Log::info('Repository files fetched for ingestion.', [
                'repository' => $this->repoFullName,
                'source_id' => $source->id,
                'files_found' => count($files),
            ]);

            foreach ($files as $path => $content) {
                if (trim($content) === '') {
                    continue;
                }

                $alreadyIngested = Chunks::query()
                    ->where('source_id', $source->id)
                    ->where('file_path', $path)
                    ->exists();

                if ($alreadyIngested) {
                    continue;
                }

                $pieces = $chunker->chunk($content);

                foreach ($pieces as $piece) {
                    $embedding = null;
                    $attempts = 0;

                    while ($attempts < 3 && $embedding === null) {
                        try {
                            $embedding = $embedder->embed($piece);
                        } catch (\Exception $e) {
                            $attempts++;

                            Log::warning('Repository embedding attempt failed.', [
                                'repository' => $this->repoFullName,
                                'source_id' => $source->id,
                                'path' => $path,
                                'attempt' => $attempts,
                                'reason' => $errorLogger->sanitize($e->getMessage()),
                            ]);
                            $errorLogger->log('warning', 'Repository embedding attempt failed.', [
                                'user_id' => $user->id,
                                'repository' => $this->repoFullName,
                                'source_id' => $source->id,
                                'path' => $path,
                                'attempt' => $attempts,
                                'exception' => $e,
                            ]);

                            if (str_contains($e->getMessage(), '429')) {
                                sleep(20);
                            } else {
                                break;
                            }
                        }
                    }

                    if ($embedding === null) {
                        continue;
                    }

                    try {
                        $limits->reserveStorage($user, strlen($piece));
                    } catch (SubscriptionLimitExceeded $e) {
                        $this->markLimitReached($source, $e->getMessage());

                        return;
                    }

                    $chunk = Chunks::create([
                        'source_id' => $source->id,
                        'file_path' => $path,
                        'content' => $piece,
                    ]);

                    DB::statement('UPDATE chunks SET embedding = ? WHERE id = ?', [
                        '['.implode(',', $embedding).']',
                        $chunk->id,
                    ]);

                    $chunksIndexed++;
                    $this->reportProgress($source, $chunksIndexed, 'files');
                    usleep(500000);
                }
            }

            $commits = $ingestor->fetchCommitHistory($this->repoFullName);

            Log::info('Repository commits fetched for ingestion.', [
                'repository' => $this->repoFullName,
                'source_id' => $source->id,
                'commits_found' => count($commits),
            ]);

            foreach ($commits as $sha => $commitData) {
                $path = "commit:{$sha}";

                $alreadyIngested = Chunks::query()
                    ->where('source_id', $source->id)
                    ->where('file_path', $path)
                    ->exists();

                if ($alreadyIngested) {
                    continue;
                }

                $content = "Commit by {$commitData['author']} on {$commitData['date']}\n\n"
                    ."Message: {$commitData['message']}\n\n"
                    ."Changes:\n{$commitData['diff']}";

                if (trim($content) === '') {
                    continue;
                }

                $pieces = $chunker->chunk($content);

                foreach ($pieces as $piece) {
                    $embedding = null;
                    $attempts = 0;

                    while ($attempts < 3 && $embedding === null) {
                        try {
                            $embedding = $embedder->embed($piece);
                        } catch (\Exception $e) {
                            $attempts++;

                            Log::warning('Repository embedding attempt failed.', [
                                'repository' => $this->repoFullName,
                                'source_id' => $source->id,
                                'path' => $path,
                                'attempt' => $attempts,
                                'reason' => $errorLogger->sanitize($e->getMessage()),
                            ]);
                            $errorLogger->log('warning', 'Repository embedding attempt failed.', [
                                'user_id' => $user->id,
                                'repository' => $this->repoFullName,
                                'source_id' => $source->id,
                                'path' => $path,
                                'attempt' => $attempts,
                                'exception' => $e,
                            ]);

                            if (str_contains($e->getMessage(), '429')) {
                                sleep(20);
                            } else {
                                break;
                            }
                        }
                    }

                    if ($embedding === null) {
                        continue;
                    }

                    try {
                        $limits->reserveStorage($user, strlen($piece));
                    } catch (SubscriptionLimitExceeded $e) {
                        $this->markLimitReached($source, $e->getMessage());

                        return;
                    }

                    $chunk = Chunks::create([
                        'source_id' => $source->id,
                        'file_path' => $path,
                        'content' => $piece,
                        'content_type' => 'commit',
                    ]);

                    DB::statement('UPDATE chunks SET embedding = ? WHERE id = ?', [
                        '['.implode(',', $embedding).']',
                        $chunk->id,
                    ]);

                    $chunksIndexed++;
                    $this->reportProgress($source, $chunksIndexed, 'commits');
                    usleep(500000);
                }
            }

            $source->update([
                'meta' => array_merge($source->meta ?? [], [
                    'source' => 'github',
                    'status' => 'indexed',
                    'last_error' => null,
                    'last_failed_at' => null,
                    'files_found' => count($files),
                    'commits_found' => count($commits),
                    'chunks_indexed' => $chunksIndexed,
                    'last_synced_at' => now()->toIso8601String(),
                ]),
                'last_synced_at' => now(),
            ]);

            Log::info('Repository ingestion completed.', [
                'repository' => $this->repoFullName,
                'source_id' => $source->id,
                'files_found' => count($files),
                'commits_found' => count($commits),
            ]);
        } catch (\Throwable $e) {
            $this->markFailed($source, $e->getMessage(), $e, $errorLogger);
        }
    }

    private function markFailed(Source $source, string $reason, ?\Throwable $exception, ErrorLogger $errorLogger): void
    {
        $reason = $errorLogger->sanitize($reason);

        $source->update([
            'meta' => array_merge($source->meta ?? [], [
                'source' => 'github',
                'status' => 'error',
                'last_error' => $reason,
                'last_failed_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::error('Repository ingestion failed.', [
            'repository' => $this->repoFullName,
            'source_id' => $source->id,
            'reason' => $reason,
            'exception' => $exception ? $exception::class : null,
        ]);
        $errorLogger->log('error', 'Repository ingestion failed.', [
            'user_id' => $source->workspace?->user_id,
            'repository' => $this->repoFullName,
            'source_id' => $source->id,
            'reason' => $reason,
            'exception' => $exception,
        ]);
    }

    private function markLimitReached(Source $source, string $reason): void
    {
        $source->update([
            'meta' => array_merge($source->meta ?? [], [
                'source' => 'github',
                'status' => 'limit_reached',
                'last_error' => $reason,
                'last_failed_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    private function reportProgress(Source $source, int $chunksIndexed, string $phase): void
    {
        if ($chunksIndexed % 10 !== 0) {
            return;
        }

        $source->update([
            'meta' => array_merge($source->meta ?? [], [
                'status' => 'indexing',
                'chunks_indexed' => $chunksIndexed,
                'phase' => $phase,
                'last_progress_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Repository ingestion progress.', [
            'repository' => $this->repoFullName,
            'source_id' => $source->id,
            'chunks_indexed' => $chunksIndexed,
            'phase' => $phase,
        ]);
    }
}
