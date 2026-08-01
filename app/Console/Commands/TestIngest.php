<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Ingestion\GithubIngestor;
use Illuminate\Console\Command;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first(); // or ::where('email', 'you@example.com')->first()

        if (!$user || !$user->github_token) {
            $this->error('No user with a github_token found. Log in via /auth/github first.');
            return;
        }

        $ingestor = new GithubIngestor(decrypt($user->github_token));
        $file = $ingestor->fetchRepoFiles('SGoku-l/Notes');
        dump(array_keys($file));
    }
}
