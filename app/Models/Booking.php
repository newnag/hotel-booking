<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_ref',
        'user_id',
        'room_id',
        'start_datetime',
        'end_datetime',
        'attendee_count',
        'decoration_theme',
        'status',
        'notes',
    ];

    /**
     * Bootstrap the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_ref)) {
                $booking->booking_ref = static::generateBookingRef();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'attendee_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the meeting room for the booking.
     */
    public function room()
    {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }

    /**
     * Get the line notifications for the booking.
     */
    public function lineNotifications()
    {
        return $this->hasMany(LineNotification::class);
    }

    /**
     * Scope a query to only include confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    /**
     * Scope a query to filter bookings by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where('start_datetime', '>=', $startDate)
            ->where('end_datetime', '<=', $endDate);
    }

    /**
     * Generate a unique booking reference.
     */
    public static function generateBookingRef(): string
    {
        do {
            $date = now()->format('Ymd');
            $lastBooking = static::whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastBooking ? intval(substr($lastBooking->booking_ref, -3)) + 1 : 1;
            $ref = 'BK'.$date.str_pad($sequence, 3, '0', STR_PAD_LEFT);

            // Check if ref already exists
            $exists = static::where('booking_ref', $ref)->exists();

            if ($exists) {
                // If exists, increment sequence
                $sequence++;
                $ref = 'BK'.$date.str_pad($sequence, 3, '0', STR_PAD_LEFT);
            }
        } while (static::where('booking_ref', $ref)->exists());

        return $ref;
    }
}
