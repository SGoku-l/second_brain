<?php

namespace App\Services\Ingestion;
use GuzzleHttp\Client;

class Embedder
{
    /**
     * Create a new class instance.
     */

    protected Client $client;

    public function __construct()
    {
    
        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
        ]);

    }

    public function embed(string $text): array {

        $apikey = config('services.gemini.api_key');

        $response = $this->client->post(
            'models/gemini-embedding-001:embedContent',
            [
                'query' => ['key' => $apikey],
                'json'  => [
                    'content' => [
                        'parts' => [
                            ['text' => $text],
                        ],
                    ],
                    'outputDimensionality' => 768,
                ],
            ]
        );

        $data = json_decode($response->getBody(), true);

        return $data['embedding']['values'];

    }

}
