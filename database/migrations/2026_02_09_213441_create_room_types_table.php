<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('code');
            $table->enum('room_type', ['hotel', 'house']); // hotel room or standalone house
            $table->unsignedTinyInteger('base_occupancy')->default(2);
            $table->unsignedTinyInteger('max_occupancy')->default(4);
            $table->unsignedTinyInteger('extra_bed_slots')->default(0);
            $table->decimal('single_use_supplement', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
