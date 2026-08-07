<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('app_settings');

        Schema::table('subscription_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_plans', 'duration_days')) {
                $table->unsignedTinyInteger('duration_days')->nullable()->after('duration_months');
            }

            if (Schema::hasColumn('subscription_plans', 'trial_days')) {
                $table->dropColumn('trial_days');
            }
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscriptions', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
        });
    }
};
