<?php

namespace App\Jobs;

use App\Models\Chunks;
use App\Models\Source;
use App\Services\Ingestion\Chunker;
use App\Services\Ingestion\Embedder;
use App\Services\Ingestion\GithubIngestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class IngestRepoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $sourceId,
        public string $repoFullName,
    ) {
        //
    }

    public function handle(): void
    {
        $source = Source::find($this->sourceId);

        if (! $source) {
            return;
        }

        $user = $source->workspace?->user;

        if (! $user || ! $user->github_token) {
            return;
        }

        $token = decrypt($user->github_token);
        $ingestor = new GithubIngestor($token);
        $files = $ingestor->fetchRepoFiles($this->repoFullName);
        $chunker = new Chunker();
        $embedder = new Embedder();

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

                $chunk = Chunks::create([
                    'source_id' => $source->id,
                    'file_path' => $path,
                    'content' => $piece,
                ]);

                DB::statement('UPDATE chunks SET embedding = ? WHERE id = ?', [
                    '[' . implode(',', $embedding) . ']',
                    $chunk->id,
                ]);

                usleep(500000);
            }
        }

        $source->update([
            'last_synced_at' => now(),
        ]);
    }
}
