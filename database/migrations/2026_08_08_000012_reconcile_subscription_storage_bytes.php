<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_subscriptions')->orderBy('id')->each(function (object $subscription): void {
            $bytes = (int) DB::table('chunks')
                ->join('sources', 'sources.id', '=', 'chunks.source_id')
                ->join('workspaces', 'workspaces.id', '=', 'sources.workspace_id')
                ->where('workspaces.user_id', $subscription->user_id)
                ->sum(DB::raw('octet_length(chunks.content)'));

            DB::table('user_subscriptions')->where('id', $subscription->id)->update([
                'storage_used_bytes' => $bytes,
                'storage_used_mb' => (int) ceil($bytes / (1024 * 1024)),
            ]);
        });
    }

    public function down(): void
    {
        // This migration corrects derived accounting data.
    }
};
