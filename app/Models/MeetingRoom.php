<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoom extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'max_capacity',
        'location',
        'hourly_rate',
        'is_active',
        'image_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_capacity' => 'integer',
            'hourly_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the bookings for the meeting room.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id');
    }

    /**
     * Scope a query to only include active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter rooms by minimum capacity.
     */
    public function scopeWithCapacity($query, int $minCapacity)
    {
        return $query->where('max_capacity', '>=', $minCapacity);
    }
}
