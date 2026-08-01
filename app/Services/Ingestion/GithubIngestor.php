<?php

namespace App\Services\Ingestion;

class GithubIngestor
{
    /**
     * Create a new class instance.
     */
    public function __construct(private string $token)
    {
        //
    }

    public function fetchRepoFiles(string $ownerRepo) {

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.github.com/',
            'headers'  => [
                'Authorization' => "Bearer {$this->token}",
                'Accept'        => 'application/vnd.github+json',
                'User-Agent'    => 'second-brain-app'
            ],
        ]);

        $tree = json_decode($client->get("repos/{$ownerRepo}/git/trees/main?recursive=1")->getBody() , true);

        $file = [];

        $exceptions = ['.md', '.php' , '.js', '.ts', '.py', '.java', '.rb', '.go', '.c', '.cpp', '.cs', '.html', '.css', 
         '.json', '.xml', '.yml', '.yaml', '.sh', '.bat', '.ps1', '.pl', '.r', '.swift', '.kt', '.dart'];

        $skipPatterns = ['package-lock.json', 'composer.lock', '.min.js', 'vendor/', 'node_modules/']; 

        foreach($tree['tree'] as $item){

            if($item['type'] !== 'blob') continue;
            if(!collect($exceptions)->contains(fn($ext) => str_ends_with($item['path'], $ext))) continue;
            if(collect($skipPatterns)->contains(fn($p) => str_contains($item['path'], $p))) continue;
            if(($item['size'] ?? 0) > 100000) continue; // Skip files larger than 100KB

            $content = $client->get("repos/{$ownerRepo}/contents/{$item['path']}")->getBody();
            $decoded =json_decode($content , true);
            $file[$item['path']] = base64_decode($decoded['content']);

        }

        return $file;

    }

}
