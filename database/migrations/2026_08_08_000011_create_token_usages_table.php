<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tokens');
            $table->timestamp('recorded_at');
            $table->index(['user_id', 'recorded_at']);
            $table->index('recorded_at');
        });

        DB::table('user_subscriptions')
            ->where('tokens_used_current_period', '>', 0)
            ->orderBy('id')
            ->each(function (object $subscription): void {
                DB::table('token_usages')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $subscription->user_id,
                    'tokens' => $subscription->tokens_used_current_period,
                    'recorded_at' => $subscription->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_usages');
    }
};
