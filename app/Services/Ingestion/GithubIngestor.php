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

    public function fetchRepoFiles(string $ownerRepo, ?array $paths = null): array
    {

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

        $requestedPaths = $paths === null ? null : array_flip($paths);

        foreach($tree['tree'] as $item){

            if($item['type'] !== 'blob') continue;
            if ($requestedPaths !== null && ! isset($requestedPaths[$item['path']])) continue;
            if(!collect($exceptions)->contains(fn($ext) => str_ends_with($item['path'], $ext))) continue;
            if(collect($skipPatterns)->contains(fn($p) => str_contains($item['path'], $p))) continue;
            if(($item['size'] ?? 0) > 100000) continue; // Skip files larger than 100KB

            $content = $client->get("repos/{$ownerRepo}/contents/{$item['path']}")->getBody();
            $decoded =json_decode($content , true);
            $file[$item['path']] = base64_decode($decoded['content']);

        }

        return $file;

    }

    public function fetchCommitHistory(string $ownerRepo, int $limit = 50, ?string $since = null): array{

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.github.com/',
            'headers'  => [
                'Authorization' => "Bearer {$this->token}",
                'Accept'        => 'application/vnd.github+json',
                'User-Agent'    => 'second-brain-app'
            ],
        ]);

        // 1. Get the list of recent commits (message, author, sha)
        $commits = json_decode(
            $client->get("repos/{$ownerRepo}/commits",[
                'query' => array_filter([
                    'per_page' => $limit,
                    'since' => $since,
                ])
            ])->getBody() , true
        );

        $results = [];

        foreach($commits as $commit){

            $sha = $commit['sha'];
            $message = $commit['commit']['message'] ?? '';
            $author = $commit['commit']['author']['name'] ?? 'unknown';
            $date = $commit['commit']['author']['date'] ?? '';

            // 2. Fetch the individual commit to get its diff/patch.
            // Retry transient transport failures so one reset connection does not stop the job.
            $detail = null;
            $attempts = 0;

            while ($attempts < 3 && $detail === null) {
                try {
                    $detail = json_decode(
                        $client->get("repos/{$ownerRepo}/commits/{$sha}")->getBody(),
                        true
                    );
                } catch (\Exception $e) {
                    $attempts++;
                    sleep(3);
                }
            }

            if ($detail === null) {
                continue;
            }

            $changedFiles = collect($detail['files'] ?? [])
                    ->map(fn (array $file) => [
                        'path' => $file['filename'] ?? null,
                        'previous_path' => $file['previous_filename'] ?? null,
                        'status' => $file['status'] ?? null,
                    ])
                    ->filter(fn (array $file) => ! empty($file['path']))
                    ->values()
                    ->all();

            $diffSummary = collect($detail['files'] ?? [])
                    ->map(function ($file){
                        $patch = $file['patch'] ?? '';
                        // Cap each file's patch to avoid huge diffs blowing up the chunk
                        $patch = strlen($patch) > 1500 ? substr($patch, 0, 1500) . "\n...(truncated)" : $patch;
                        return "File: {$file['filename']} ({$file['status']})\n{$patch}";
                    })->implode("\n\n");

            $results[$sha] = [
                'message' => $message,
                'author'  => $author,
                'date'    => $date,
                'diff'    => $diffSummary,
                'files'   => $changedFiles,
            ];

            // Be gentle on GitHub's rate limits since this is one extra call per commit
            usleep(200000);

        }

        return $results;

    }

}
