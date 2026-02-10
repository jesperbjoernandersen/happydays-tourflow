<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rate_rules', function (Blueprint $table) {
            $table->string('rate_rule_type')->default('stay_type')->after('rate_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('rate_rules', function (Blueprint $table) {
            $table->dropColumn('rate_rule_type');
        });
    }
};
