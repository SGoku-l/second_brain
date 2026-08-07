<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->unsignedTinyInteger('duration_months');
            $table->unsignedTinyInteger('duration_days')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('monthly_token_limit');
            $table->unsignedInteger('monthly_repo_limit');
            $table->unsignedInteger('storage_limit_mb');
            $table->boolean('is_free')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
