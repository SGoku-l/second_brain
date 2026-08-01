<?php

namespace App\Services\Retrieval;
use App\Services\Ingestion\Embedder;
use Illuminate\Support\Facades\DB;

class Retriever
{
    /**
     * Create a new class instance.
     */

    protected Embedder $embedder;

    public function __construct()
    {
        $this->embedder = new Embedder();
    }

    public function search(string $question , string $userId , int $limit = 5): array{

        $queryEmbedding = $this->embedder->embed($question);
        $vectorString   = '[' . implode(',' , $queryEmbedding) . ']';

        return DB::select("

            SELECT chunks.file_path, chunks.content, 
                    chunks.embedding <=> ? AS distance
            FROM chunks
            JOIN sources ON chunks.source_id = sources.id
            JOIN workspaces ON sources.workspace_id = workspaces.id
            WHERE workspaces.user_id = ?
            ORDER BY distance ASC
            LIMIT ?

        ", [$vectorString, $userId, $limit]);

    }

}
