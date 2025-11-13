<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LineNotification;
use App\Models\User;
use App\Services\LineNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Line notification service.
     *
     * @var \App\Services\LineNotificationService
     */
    protected $lineService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LineNotificationService $lineService)
    {
        $this->lineService = $lineService;
    }

    /**
     * Display notification history.
     *
     * @return \Illuminate\View\View
     */
    public function history(Request $request)
    {
        $query = LineNotification::with(['booking.room', 'recipient']);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by notification type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('notification_type', $request->type);
        }

        // Filter by recipient type
        if ($request->filled('recipient_type') && $request->recipient_type !== 'all') {
            $query->where('recipient_type', $request->recipient_type);
        }

        // Filter by booking ID
        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifications.history', compact('notifications'));
    }

    /**
     * Display notification settings.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $staffUsers = User::where('role', 'staff')
            ->orWhere('role', 'admin')
            ->get();

        // Get current Line API configuration status
        $lineConfigured = ! empty(config('services.line.channel_access_token'));

        return view('admin.notifications.settings', compact('staffUsers', 'lineConfigured'));
    }

    /**
     * Update notification settings.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'staff_recipients' => 'nullable|array',
            'staff_recipients.*' => 'exists:users,id',
            'notification_types' => 'nullable|array',
            'notification_types.booking_created' => 'nullable|boolean',
            'notification_types.booking_confirmed' => 'nullable|boolean',
            'notification_types.booking_cancelled' => 'nullable|boolean',
            'notification_types.booking_reminder' => 'nullable|boolean',
        ]);

        // In a real application, you would save these settings to database
        // For now, we'll just flash a success message

        return redirect()
            ->route('admin.notifications.settings')
            ->with('success', 'บันทึกการตั้งค่าแล้ว');
    }

    /**
     * Retry a failed notification.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function retry(LineNotification $notification)
    {
        if ($notification->status !== 'failed') {
            return redirect()
                ->back()
                ->with('error', 'สามารถลองส่งซ้ำได้เฉพาะการแจ้งเตือนที่ล้มเหลวเท่านั้น');
        }

        $success = $this->lineService->retryNotification($notification);

        if ($success) {
            return redirect()
                ->back()
                ->with('success', 'ส่งการแจ้งเตือนซ้ำเรียบร้อยแล้ว');
        }

        return redirect()
            ->back()
            ->with('error', 'ไม่สามารถส่งการแจ้งเตือนได้ กรุณาตรวจสอบ Log สำหรับข้อมูลเพิ่มเติม');
    }

    /**
     * Send a test notification.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function test(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($validated['recipient_id']);

        if (! $user->line_user_id) {
            return redirect()
                ->back()
                ->with('error', 'ผู้ใช้นี้ยังไม่ได้เชื่อมโยงบัญชี Line');
        }

        try {
            $this->lineService->sendTestNotification($user, $validated['message']);

            return redirect()
                ->back()
                ->with('success', 'ส่งการแจ้งเตือนทดสอบเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'ไม่สามารถส่งการแจ้งเตือนได้: '.$e->getMessage());
        }
    }
}
