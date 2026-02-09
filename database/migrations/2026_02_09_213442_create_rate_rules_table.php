<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('stay_type_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('base_price', 10, 2);
            $table->decimal('price_per_adult', 10, 2)->default(0);
            $table->decimal('price_per_child', 10, 2)->default(0);
            $table->decimal('price_per_infant', 10, 2)->default(0);
            $table->decimal('price_per_extra_bed', 10, 2)->default(0);
            $table->decimal('single_use_supplement', 10, 2)->default(0);
            $table->unsignedTinyInteger('included_occupancy')->nullable(); // For UNIT_INCLUDED model
            $table->decimal('price_per_extra_person', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_rules');
    }
};
