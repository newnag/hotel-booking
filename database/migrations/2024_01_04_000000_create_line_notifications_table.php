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
        Schema::create('line_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->enum('recipient_type', ['staff', 'guest']);
            $table->enum('notification_type', ['booking_created', 'booking_confirmed', 'booking_cancelled', 'booking_reminder']);
            $table->text('message')->comment('Notification message content');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable()->comment('When notification was successfully sent');
            $table->text('error_message')->nullable()->comment('Error details if sending failed');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('booking_id');
            $table->index('recipient_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_notifications');
    }
};
