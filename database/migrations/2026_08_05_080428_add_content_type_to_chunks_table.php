<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chunks', function (Blueprint $table) {
            $table->string('content_type')->default('code');
        });

        // Backfill: anything with file_path NOT starting with "commit:" is actually code
        DB::table('chunks')
            ->where('file_path', 'not like', 'commit:%')
            ->update(['content_type' => 'code']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chunks', function (Blueprint $table) {
            $$table->dropColumn('content_type');
        });
    }
};
