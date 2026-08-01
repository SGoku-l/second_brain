<?php

namespace App\Console\Commands;

use App\Models\Chunks;
use App\Models\Source;
use App\Models\User;
use App\Services\Ingestion\Chunker;
use App\Services\Ingestion\Embedder;
use App\Services\Ingestion\GithubIngestor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestIngest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-ingest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest a repo, chunk it, embed it, store it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first();

        if (!$user || !$user->github_token) {
            $this->error('No user with a github_token found. Please authenticate with GitHub first.');
            return;
        }

        $ownerRepo = 'SGoku-l/Notes';

        // 1. Create or find a source for this repo
        $source = Source::firstOrCreate(
            ['identifier' => $ownerRepo, 'type' => 'github'],
            ['workspace_id' => '019fbeb5-eb70-71ff-81bd-04e681cb9363']
        );

        // 2. Fetch files from repo
        $this->info('Fetching files from GitHub repository...');
        $ingestor = new GithubIngestor(decrypt($user->github_token));
        $files = $ingestor->fetchRepoFiles($ownerRepo);
        $this->info(count($files) . " files fetched from the repository.");

        // 3. chunk + embed + store
        $chunker = new Chunker();
        $embedder = new Embedder();

        $bar = $this->output->createProgressBar(count($files));

        foreach ($files as $path => $content) {

            if (trim($content) === '') {
                $bar->advance();
                continue; // skip empty files
            }

            // Skip files already ingested for this source (dedupe on rerun)
            $alreadyIngested = Chunks::where('source_id', $source->id)
                ->where('file_path', $path)
                ->exists();

            if ($alreadyIngested) {
                $bar->advance();
                continue;
            }

            $pieces = $chunker->chunk($content);

            foreach ($pieces as $piece) {
                $attempts = 0;
                $embedding = null;

                while ($attempts < 3 && $embedding === null) {
                    try {
                        $embedding = $embedder->embed($piece);
                    } catch (\Exception $e) {
                        $attempts++;

                        if (str_contains($e->getMessage(), '429')) {
                            $this->warn("Rate limited, waiting 20s... (attempt {$attempts}/3) [{$path}]");
                            sleep(20);
                        } else {
                            $this->warn("Failed to embed from file {$path}: " . $e->getMessage());
                            break; // non-rate-limit error, don't retry
                        }
                    }
                }

                if ($embedding === null) {
                    continue; // gave up on this piece after retries or hard failure
                }

                $chunk = Chunks::create([
                    'source_id' => $source->id,
                    'file_path' => $path,
                    'content' => $piece,
                ]);

                // pgvector column needs a raw update — Eloquent doesn't cast to 'vector'
                DB::statement('UPDATE chunks SET embedding = ? WHERE id = ?', [
                    '[' . implode(',', $embedding) . ']',
                    $chunk->id,
                ]);

                // Pace requests to avoid hitting free-tier rate limits again
                usleep(500000); // 0.5s between successful calls
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info('Done. Total chunks: ' . Chunks::where('source_id', $source->id)->count());
    }
}