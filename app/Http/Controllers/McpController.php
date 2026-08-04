<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Models\User;
use App\Services\Retrieval\Retriever;
use Illuminate\Http\Request;

class McpController extends Controller
{

    public function handle(Request $request){

        $token = $request->bearerToken();
        $user  = User::where('api_token' , $token)->first();

        if(!$user){

            return response()->json([
                'jsonrpc' => '2.0',
                'id'      => $request->input('id'),
                'error'   => [
                    'code'    => -32600,
                    'message' => 'Unauthorized'
                ],
            ],401);

        }

        $method = $request->input('method');
        $id     = $request->input('id');

        return match($method){

            'initialize' => $this->initialize($id),
            'tools/list' => $this->toolsList($id),
            'tools/call' => $this->toolsCall($id , $request->input('params') , $user),
            default => response()->json([
                'jsonrpc' => '2.0',
                'id'      => $id,
                'error'   => [
                    'code'    => -32601,
                    'message' => 'Method not found'
                ],
            ]),

        };

    }

    protected function initialize($id){

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => ['tools' => new \stdClass()],
                'serverInfo' => ['name' => 'second-brain', 'version' => '0.1.0'],
            ],
        ]);
        
    }

    protected function toolsList($id)
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'tools' => [
                    [
                        'name' => 'search_context',
                        'description' => 'Search the user\'s ingested codebase and docs for relevant context to answer a question about their project.',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'query' => [
                                    'type' => 'string', 'description' => 'The question or topic to search for'
                                ],
                                'repo' => [
                                    'type' => 'string',
                                    'description' => 'Optional: limit the search to one repo identifier (for example owner/repo). Omit to search all connected repos.'
                                ],
                            ],
                            'required' => ['query'],
                        ],
                    ],
                    [
                        'name' => 'list_repos',
                        'description' => 'List all repositories connected to the user\'s second brain.',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => new \stdClass(),
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function toolsCall($id, $params, User $user)
    {
        $toolName = $params['name'] ?? null;
        $args = $params['arguments'] ?? [];

        return match ($toolName) {
            'search_context' => $this->searchContext($id, $args, $user),
            'list_repos' => $this->listRepos($id, $user),
            default => response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32602, 'message' => 'Unknown tool'],
            ]),
        };
    }

    protected function searchContext($id, array $args, User $user)
    {
        $query = $args['query'] ?? null;
        $repo = $args['repo'] ?? null;

        if (! is_string($query) || trim($query) === '') {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32602, 'message' => 'Missing query'],
            ]);
        }

        $sourceIds = $this->resolveSourceIds($user, $repo);
        $retriever = new Retriever();
        $results = $retriever->search($query, $user->id, 5, $sourceIds);

        $text = collect($results)
            ->map(fn ($r) => "Repo: {$r->repo}\nFile: {$r->file_path}\n{$r->content}")
            ->implode("\n\n---\n\n");

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    ['type' => 'text', 'text' => $text ?: 'No relevant context found.'],
                ],
            ],
        ]);
    }

    protected function listRepos($id, User $user)
    {
        $repos = Source::query()
            ->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))
            ->select('id', 'identifier')
            ->orderBy('identifier')
            ->get();

        $text = $repos->map(fn ($repo) => "{$repo->identifier} | {$repo->id}")
            ->implode("\n");

        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    ['type' => 'text', 'text' => $text ?: 'No repositories connected.'],
                ],
            ],
        ]);
    }

    protected function resolveSourceIds(User $user, ?string $repoName): array
    {
        if (! is_string($repoName) || trim($repoName) === '') {
            return [];
        }

        return Source::query()
            ->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))
            ->where('identifier', trim($repoName))
            ->pluck('id')
            ->toArray();
    }

}
