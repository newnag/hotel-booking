<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LineNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineNotificationService
{
    /**
     * Line Messaging API endpoint.
     *
     * @var string
     */
    protected $apiEndpoint = 'https://api.line.me/v2/bot/message/push';

    /**
     * Channel access token from config.
     *
     * @var string|null
     */
    protected $channelAccessToken;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->channelAccessToken = config('services.line.channel_access_token');
    }

    /**
     * Send booking created notification to staff.
     */
    public function sendBookingCreatedNotification(Booking $booking): void
    {
        // Get all staff users who should receive notifications
        $staffUsers = User::where('role', 'staff')
            ->orWhere('role', 'admin')
            ->whereNotNull('line_user_id')
            ->get();

        foreach ($staffUsers as $staff) {
            $message = $this->formatBookingCreatedMessage($booking);

            $this->createAndSendNotification(
                booking: $booking,
                recipient: $staff,
                recipientType: 'staff',
                notificationType: 'booking_created',
                message: $message
            );
        }
    }

    /**
     * Send booking confirmation to guest.
     */
    public function sendBookingConfirmation(Booking $booking): void
    {
        $guest = $booking->user;

        // Only send if guest has Line integration
        if (! $guest->line_user_id) {
            Log::info("Guest {$guest->id} does not have Line integration. Skipping notification.");

            return;
        }

        $message = $this->formatBookingConfirmationMessage($booking);

        $this->createAndSendNotification(
            booking: $booking,
            recipient: $guest,
            recipientType: 'guest',
            notificationType: 'booking_confirmed',
            message: $message
        );
    }

    /**
     * Send booking cancellation notification.
     */
    public function sendBookingCancellationNotification(Booking $booking): void
    {
        $guest = $booking->user;

        if (! $guest->line_user_id) {
            return;
        }

        $message = $this->formatBookingCancellationMessage($booking);

        $this->createAndSendNotification(
            booking: $booking,
            recipient: $guest,
            recipientType: 'guest',
            notificationType: 'booking_cancelled',
            message: $message
        );
    }

    /**
     * Send test notification.
     */
    public function sendTestNotification(User $user, string $message): LineNotification
    {
        return $this->createAndSendNotification(
            booking: null,
            recipient: $user,
            recipientType: $user->role,
            notificationType: 'booking_created', // Use default type for test
            message: $message
        );
    }

    /**
     * Send welcome message after LINE account is linked.
     */
    public function sendWelcomeMessage(User $user): ?LineNotification
    {
        if (!$user->line_user_id) {
            return null;
        }

        $message = sprintf(
            "🎉 ยินดีต้อนรับ!\n\n".
            "สวัสดีคุณ %s\n\n".
            "เชื่อมต่อบัญชี LINE สำเร็จแล้ว! 🎊\n\n".
            "ตั้งแต่นี้คุณจะได้รับการแจ้งเตือนเกี่ยวกับ:\n".
            "✅ การยืนยันการจอง\n".
            "⏰ การเตือนก่อนถึงเวลาประชุม\n".
            "📝 การเปลี่ยนแปลงหรือยกเลิกการจอง\n\n".
            "ขอบคุณที่ใช้บริการระบบจองห้องประชุม 🙏",
            $user->name
        );

        return $this->createAndSendNotification(
            booking: null,
            recipient: $user,
            recipientType: 'guest',
            notificationType: 'welcome',
            message: $message
        );
    }

    /**
     * Retry failed notification.
     */
    public function retryNotification(LineNotification $notification): bool
    {
        if ($notification->status !== 'failed') {
            return false;
        }

        // Check if recipient still has Line ID
        if (empty($notification->recipient->line_user_id)) {
            Log::warning('Cannot retry notification: recipient has no Line ID', [
                'notification_id' => $notification->id,
                'recipient_id' => $notification->recipient_id,
            ]);

            return false;
        }

        return $this->sendLineMessage(
            lineUserId: $notification->recipient->line_user_id,
            message: $notification->message,
            notification: $notification
        );
    }

    /**
     * Create notification record and send Line message.
     */
    protected function createAndSendNotification(
        ?Booking $booking,
        User $recipient,
        string $recipientType,
        string $notificationType,
        string $message
    ): LineNotification {
        // Create notification record
        $notification = LineNotification::create([
            'booking_id' => $booking?->id,
            'recipient_id' => $recipient->id,
            'recipient_type' => $recipientType,
            'notification_type' => $notificationType,
            'message' => $message,
            'status' => 'pending',
        ]);

        // Skip if user has no Line ID
        if (empty($recipient->line_user_id)) {
            Log::info('User has no Line ID. Notification logged only.', [
                'notification_id' => $notification->id,
                'user_id' => $recipient->id,
            ]);

            $notification->markAsFailed('User has no Line ID');

            return $notification;
        }

        // Send Line message
        $this->sendLineMessage(
            lineUserId: $recipient->line_user_id,
            message: $message,
            notification: $notification
        );

        return $notification;
    }

    /**
     * Send message via Line Messaging API.
     */
    protected function sendLineMessage(string $lineUserId, string $message, LineNotification $notification): bool
    {
        // If Line API is not configured, log and mark as failed gracefully
        if (! $this->channelAccessToken) {
            Log::warning('Line API not configured. Notification logged only.', [
                'notification_id' => $notification->id,
                'message' => $message,
            ]);

            $notification->markAsFailed('Line API not configured');

            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->channelAccessToken,
            ])->post($this->apiEndpoint, [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $notification->markAsSent();
                Log::info('Line notification sent successfully', [
                    'notification_id' => $notification->id,
                ]);

                return true;
            } else {
                $errorMessage = $response->json('message') ?? 'Unknown error';
                $notification->markAsFailed($errorMessage);
                Log::error('Line notification failed', [
                    'notification_id' => $notification->id,
                    'error' => $errorMessage,
                    'status' => $response->status(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error('Line notification exception', [
                'notification_id' => $notification->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format booking created message for staff.
     */
    protected function formatBookingCreatedMessage(Booking $booking): string
    {
        return sprintf(
            "🔔 การจองใหม่\n\n".
            "👤 ผู้จอง: %s\n".
            "🏢 ห้อง: %s\n".
            "📅 วันที่: %s\n".
            "⏰ เวลา: %s - %s\n".
            "👥 จำนวน: %d คน\n".
            "🎨 ธีม: %s\n".
            '📝 สถานะ: %s',
            $booking->user->name,
            $booking->room->name,
            $booking->start_datetime->format('d/m/Y'),
            $booking->start_datetime->format('H:i'),
            $booking->end_datetime->format('H:i'),
            $booking->attendee_count,
            $booking->decoration_theme ?? 'ไม่ระบุ',
            $this->getStatusText($booking->status)
        );
    }

    /**
     * Format booking confirmation message for guest.
     */
    protected function formatBookingConfirmationMessage(Booking $booking): string
    {
        return sprintf(
            "✅ ยืนยันการจองห้องประชุม\n\n".
            "สวัสดีคุณ %s\n\n".
            "การจองของคุณได้รับการยืนยันแล้ว\n\n".
            "📌 รายละเอียดการจอง:\n".
            "🏢 ห้อง: %s\n".
            "📅 วันที่: %s\n".
            "⏰ เวลา: %s - %s\n".
            "👥 จำนวน: %d คน\n".
            "🎨 ธีม: %s\n\n".
            'ขอบคุณที่ใช้บริการ',
            $booking->user->name,
            $booking->room->name,
            $booking->start_datetime->format('d/m/Y'),
            $booking->start_datetime->format('H:i'),
            $booking->end_datetime->format('H:i'),
            $booking->attendee_count,
            $booking->decoration_theme ?? 'ไม่ระบุ'
        );
    }

    /**
     * Format booking cancellation message.
     */
    protected function formatBookingCancellationMessage(Booking $booking): string
    {
        return sprintf(
            "❌ ยกเลิกการจองห้องประชุม\n\n".
            "สวัสดีคุณ %s\n\n".
            "การจองของคุณถูกยกเลิกแล้ว\n\n".
            "📌 รายละเอียด:\n".
            "🏢 ห้อง: %s\n".
            "📅 วันที่: %s\n".
            '⏰ เวลา: %s - %s',
            $booking->user->name,
            $booking->room->name,
            $booking->start_datetime->format('d/m/Y'),
            $booking->start_datetime->format('H:i'),
            $booking->end_datetime->format('H:i')
        );
    }

    /**
     * Get Thai status text.
     */
    protected function getStatusText(string $status): string
    {
        return match ($status) {
            'confirmed' => 'ยืนยันแล้ว',
            'cancelled' => 'ยกเลิก',
            'pending' => 'รอดำเนินการ',
            default => $status,
        };
    }
}
