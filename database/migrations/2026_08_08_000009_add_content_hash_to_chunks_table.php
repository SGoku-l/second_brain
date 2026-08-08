<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chunks', function (Blueprint $table) {
            $table->string('content_hash', 64)->nullable()->after('content');
            $table->index(['source_id', 'file_path']);
        });
    }

    public function down(): void
    {
        Schema::table('chunks', function (Blueprint $table) {
            $table->dropIndex(['source_id', 'file_path']);
            $table->dropColumn('content_hash');
        });
    }
};
