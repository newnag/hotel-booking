<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\LineNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLineNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The booking instance.
     *
     * @var \App\Models\Booking
     */
    protected $booking;

    /**
     * The recipient type (staff or guest).
     *
     * @var string
     */
    protected $recipientType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Booking $booking, string $recipientType)
    {
        $this->booking = $booking;
        $this->recipientType = $recipientType;
    }

    /**
     * Execute the job.
     */
    public function handle(LineNotificationService $lineService): void
    {
        try {
            if ($this->recipientType === 'staff') {
                $lineService->sendBookingCreatedNotification($this->booking);
            } elseif ($this->recipientType === 'guest') {
                $lineService->sendBookingConfirmation($this->booking);
            }

            Log::info('Line notification job completed', [
                'booking_id' => $this->booking->id,
                'recipient_type' => $this->recipientType,
            ]);
        } catch (\Exception $e) {
            Log::error('Line notification job failed', [
                'booking_id' => $this->booking->id,
                'recipient_type' => $this->recipientType,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Line notification job permanently failed', [
            'booking_id' => $this->booking->id,
            'recipient_type' => $this->recipientType,
            'error' => $exception->getMessage(),
        ]);
    }
}
