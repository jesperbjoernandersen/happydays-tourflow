<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();
            $table->foreignId('stay_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('hotel_id')->constrained()->onDelete('restrict');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])->default('pending');
            $table->json('hotel_age_policy_snapshot')->nullable();
            $table->json('rate_rule_snapshot')->nullable();
            $table->json('price_breakdown_json')->nullable();
            $table->unsignedTinyInteger('guest_count')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
