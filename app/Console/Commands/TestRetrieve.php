<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Retrieval\Answerer;
use App\Services\Retrieval\Retriever;
use Illuminate\Console\Command;

class TestRetrieve extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-retrieve {question}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test retrieval against ingested chunks';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $user = User::first();
        $question = $this->argument('question');

        $retriever = new Retriever();
        $results = $retriever->search($question, $user->id);

        $this->info('=== TOP MATCHES ===');
        foreach ($results as $r) {

            $this->line("- {$r->file_path} (distance: " . round($r->distance, 4) . ")");

        }

        $this->newLine();

        $answerer = new Answerer();
        $answer = $answerer->answer($question, $results);

        $this->info('=== ANSWER ===');
        
        $this->line($answer);

    }

}
