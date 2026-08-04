<?php

namespace App\Http\Controllers;

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
                            ],
                            'required' => ['query'],
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

        if ($toolName !== 'search_context') {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32602, 'message' => 'Unknown tool'],
            ]);
        }

        $retriever = new Retriever();
        $results = $retriever->search($args['query'], $user->id);

        $text = collect($results)
            ->map(fn ($r) => "File: {$r->file_path}\n{$r->content}")
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

}
