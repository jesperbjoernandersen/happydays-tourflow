<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allotments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedSmallInteger('quantity')->default(0); // Total rooms available
            $table->unsignedSmallInteger('allocated')->default(0); // Rooms allocated for tour operator
            $table->decimal('price_override', 10, 2)->nullable(); // Override rate for this date
            $table->boolean('cta')->default(true); // Close to arrival
            $table->boolean('ctd')->default(true); // Close to departure
            $table->unsignedTinyInteger('min_stay')->default(1);
            $table->unsignedTinyInteger('max_stay')->nullable();
            $table->unsignedTinyInteger('release_days')->nullable(); // Days before arrival for booking
            $table->boolean('stop_sell')->default(false);
            $table->timestamps();
            
            $table->unique(['room_type_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allotments');
    }
};
