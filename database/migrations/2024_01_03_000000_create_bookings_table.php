<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 20)->unique()->comment('Unique booking reference number');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->references('id')->on('meeting_rooms')->restrictOnDelete();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->unsignedInteger('attendee_count')->comment('Number of attendees');
            $table->string('decoration_theme')->nullable()->comment('Decoration theme name (e.g., Corporate, Wedding, Conference)');
            $table->enum('status', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->text('notes')->nullable()->comment('Additional booking notes from guest');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('booking_ref');
            $table->index('user_id');
            $table->index('room_id');
            $table->index('start_datetime');
            $table->index('end_datetime');
            $table->index('status');
            // Composite index for availability queries
            $table->index(['room_id', 'start_datetime', 'end_datetime'], 'idx_room_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
