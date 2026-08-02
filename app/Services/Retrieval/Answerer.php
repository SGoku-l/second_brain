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
            'timeout' => 15,
        ]);
    }

    public function answer(string $question, array $chunks): string
    {
        $context = collect($chunks)
            ->map(fn ($c) => "File: {$c->file_path}\n{$c->content}")
            ->implode("\n\n---\n\n");

        $prompt = "Answer the question using ONLY the context below. If the context doesn't contain the answer, say so.\n\nContext:\n{$context}\n\nQuestion: {$question}";

        try {
            $response = $this->client->post(
                'models/gemini-flash-latest:generateContent',
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
            $status = $e->getResponse()->getStatusCode();
            if ($status === 429) {
                throw new \Exception('Rate limit reached — try again in a bit.');
            }
            throw new \Exception('Answer generation failed: ' . $e->getMessage());
        }
    }

}
