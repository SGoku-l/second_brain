<?php

namespace App\Services\Retrieval;
use GuzzleHttp\Client;

class Answerer
{
    /**
     * Create a new class instance.
     */

    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => "https://generativelanguage.googleapis.com/v1beta/",
        ]);
    }

    public function answer(string $question, array $chunks): string
    {
        $context = collect($chunks)
            ->map(fn ($c) => "File: {$c->file_path}\n{$c->content}")
            ->implode("\n\n---\n\n");

        $prompt = "Answer the question using ONLY the context below. If the context doesn't contain the answer, 
                    say so.\n\nContext:\n{$context}\n\nQuestion: {$question}";

        $attempts = 0;
        while ($attempts < 3) {
            try {
                $response = $this->client->post(
                    'models/gemini-2.0-flash:generateContent',
                    [
                        'query' => ['key' => config('services.gemini.api_key')],
                        'json' => [
                            'contents' => [
                                ['parts' => [['text' => $prompt]]],
                            ],
                        ],
                    ]
                );

                $data = json_decode($response->getBody(), true);
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No answer generated.';

            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $attempts++;
                if ($e->getResponse()->getStatusCode() === 429 && $attempts < 3) {
                    sleep(30);
                    continue;
                }
                return "Answer generation failed: " . $e->getMessage();
            }
        }

        return "Answer generation failed after retries.";
    }

}
