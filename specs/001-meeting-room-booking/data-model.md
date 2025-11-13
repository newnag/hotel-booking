# Data Model: Hotel Meeting Room Booking System

**Feature**: 001-meeting-room-booking  
**Date**: 2025-11-11  
**Phase**: 1 - Database Schema & Entity Design

## Overview

This document defines the database schema, entity relationships, and data validation rules for the hotel meeting room booking system.

## Entity-Relationship Diagram

```
┌─────────────┐       ┌──────────────────┐
│    Users    │       │  MeetingRooms    │
├─────────────┤       ├──────────────────┤
│ id          │       │ id               │
│ name        │       │ name             │
│ email       │◄──┐   │ description      │◄──┐
│ password    │   │   │ max_capacity     │   │
│ role        │   │   │ location         │   │
│ phone       │   │   │ is_active        │   │
│ line_id     │   │   │ created_at       │   │
│ language    │   │   │ updated_at       │   │
│ created_at  │   │   │ deleted_at       │   │
│ updated_at  │   │   └──────────────────┘   │
│ deleted_at  │   │                           │
└─────────────┘   │                           │
                  │                           │
                  │   ┌──────────────────┐    │
                  └───┤    Bookings      │────┘
                      ├──────────────────┤
                      │ id               │
                      │ user_id          │
                      │ room_id          │
                      │ booking_ref      │
                      │ start_datetime   │
                      │ end_datetime     │
                      │ attendee_count   │
                      │ decoration_theme │
                      │ status           │
                      │ notes            │
                      │ created_at       │
                      │ updated_at       │
                      │ deleted_at       │
                      └──────────────────┘
                               │
                               │
                      ┌────────┴───────────┐
                      │ LineNotifications  │
                      ├────────────────────┤
                      │ id                 │
                      │ booking_id         │
                      │ recipient_id       │
                      │ recipient_type     │
                      │ notification_type  │
                      │ message            │
                      │ status             │
                      │ sent_at            │
                      │ error_message      │
                      │ created_at         │
                      └────────────────────┘
```

## Database Tables

### 1. users

**Purpose**: Store all user accounts (guests and staff)

**Schema**:
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('guest', 'staff', 'admin') NOT NULL DEFAULT 'guest',
    phone VARCHAR(20) NULL,
    line_id VARCHAR(255) NULL COMMENT 'Line user ID for notifications',
    language VARCHAR(5) DEFAULT 'en' COMMENT 'User interface language preference',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete for audit trail',
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_line_id (line_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules**:
- `name`: Required, string, max 255 characters
- `email`: Required, valid email format, unique
- `password`: Required on creation, min 8 characters, hashed with bcrypt
- `role`: Required, one of: guest, staff, admin
- `phone`: Optional, valid phone format, max 20 characters
- `line_id`: Optional, string, max 255 characters
- `language`: Optional, valid locale code (en, th)

**Relationships**:
- Has many `bookings` (as guest)
- Has many `line_notifications` (as recipient)

**Business Rules**:
- Email must be unique across all users
- Default role is 'guest' on registration
- Soft deletes preserve user history in bookings
- Password must be hashed before storage

---

### 2. meeting_rooms

**Purpose**: Store meeting room information and configuration

**Schema**:
```sql
CREATE TABLE meeting_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    max_capacity INT UNSIGNED NOT NULL COMMENT 'Maximum number of people',
    location VARCHAR(255) NULL COMMENT 'Room location/building',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether room is available for booking',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_is_active (is_active),
    INDEX idx_max_capacity (max_capacity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules**:
- `name`: Required, string, max 255 characters, unique
- `description`: Optional, text
- `max_capacity`: Required, integer, min 1, max 1000
- `location`: Optional, string, max 255 characters
- `is_active`: Boolean, default true

**Relationships**:
- Has many `bookings`

**Business Rules**:
- Room name must be unique
- Max capacity must be positive integer
- Inactive rooms should not appear in guest searches
- Soft deletes preserve historical booking data

---

### 3. bookings

**Purpose**: Store meeting room bookings

**Schema**:
```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) UNIQUE NOT NULL COMMENT 'Unique booking reference number',
    user_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    attendee_count INT UNSIGNED NOT NULL COMMENT 'Number of attendees',
    decoration_theme VARCHAR(255) NULL COMMENT 'Decoration theme name (e.g., Corporate, Wedding, Conference)',
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    notes TEXT NULL COMMENT 'Additional booking notes from guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES meeting_rooms(id) ON DELETE RESTRICT,
    INDEX idx_booking_ref (booking_ref),
    INDEX idx_user_id (user_id),
    INDEX idx_room_id (room_id),
    INDEX idx_start_datetime (start_datetime),
    INDEX idx_end_datetime (end_datetime),
    INDEX idx_status (status),
    INDEX idx_room_date (room_id, start_datetime, end_datetime) COMMENT 'Composite index for availability queries'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules**:
- `booking_ref`: Required, unique, auto-generated (e.g., BK20251111001)
- `user_id`: Required, exists in users table
- `room_id`: Required, exists in meeting_rooms table, room must be active
- `start_datetime`: Required, datetime, must be in future
- `end_datetime`: Required, datetime, must be after start_datetime
- `attendee_count`: Required, integer, min 1, max room max_capacity
- `decoration_theme`: Optional, string, max 255 characters (e.g., "Corporate", "Wedding Reception", "Conference", "Seminar", "Workshop", "Cocktail Party")
- `status`: Required, one of: confirmed, cancelled, completed
- `notes`: Optional, text, max 1000 characters

**Relationships**:
- Belongs to `user` (guest who made booking)
- Belongs to `meeting_room`
- Has many `line_notifications`

**Business Rules**:
- Booking reference auto-generated: `BK` + `YYYYMMDD` + sequential number
- Start datetime must be in the future (at creation time)
- End datetime must be after start datetime
- Attendee count cannot exceed room's max_capacity
- No overlapping bookings for same room (enforced in application logic with locking)
- Decoration theme is free-text field allowing custom themes
- Status defaults to 'confirmed'
- Soft deletes preserve booking history
- Cannot delete users or rooms with active bookings (RESTRICT foreign key)

**Indexes**:
- Composite index on (room_id, start_datetime, end_datetime) for fast availability queries
- Individual indexes on frequently queried columns

---

### 4. line_notifications

**Purpose**: Track Line notification messages sent to users

**Schema**:
```sql
CREATE TABLE line_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NULL COMMENT 'Related booking (if applicable)',
    recipient_id BIGINT UNSIGNED NOT NULL COMMENT 'User ID receiving notification',
    recipient_type ENUM('staff', 'guest') NOT NULL,
    notification_type ENUM('booking_created', 'booking_confirmed', 'booking_cancelled', 'booking_reminder') NOT NULL,
    message TEXT NOT NULL COMMENT 'Notification message content',
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL COMMENT 'When notification was successfully sent',
    error_message TEXT NULL COMMENT 'Error details if sending failed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules**:
- `booking_id`: Optional, exists in bookings table
- `recipient_id`: Required, exists in users table, user must have line_id
- `recipient_type`: Required, one of: staff, guest
- `notification_type`: Required, one of: booking_created, booking_confirmed, booking_cancelled, booking_reminder
- `message`: Required, text
- `status`: Required, one of: pending, sent, failed
- `error_message`: Optional, text (only when status is failed)

**Relationships**:
- Belongs to `booking` (optional)
- Belongs to `user` (as recipient)

**Business Rules**:
- Recipient must have a Line ID configured
- Status defaults to 'pending'
- Sent_at timestamp set when status changes to 'sent'
- Error_message populated when status is 'failed'
- Notifications queued for async processing
- Failed notifications can be retried

---

## Sample Data Seeds

### Common Decoration Themes (for UI suggestions)
```php
// These can be stored as a config array for frontend dropdowns
// Not in database, just suggested values
[
    'ธีมธุรกิจ',
    'ธีมงานแต่งงาน',
    'ธีมการประชุม',
    'ธีมสัมมนา',
    'ธีมเวิร์กชอป',
    'ธีมค็อกเทล',
    'ธีมงานวันเกิด',
    'ธีมการอบรม',
    'กำหนดเอง' // Allow users to type their own
]
```

### Meeting Rooms
```php
[
    ['name' => 'ห้องแกรนด์บอลรูม', 'max_capacity' => 200, 'location' => 'ชั้น 2'],
    ['name' => 'ห้องประชุมผู้บริหาร', 'max_capacity' => 12, 'location' => 'ชั้น 5'],
    ['name' => 'ห้องแซฟไฟร์', 'max_capacity' => 50, 'location' => 'ชั้น 3'],
    ['name' => 'ห้องเอมเมอรัลด์', 'max_capacity' => 30, 'location' => 'ชั้น 3'],
    ['name' => 'ห้องรูบี้', 'max_capacity' => 25, 'location' => 'ชั้น 4'],
]
```

## Query Patterns

### 1. Find Available Rooms

```sql
-- Find rooms available for a specific time slot with minimum capacity
SELECT mr.*
FROM meeting_rooms mr
WHERE mr.is_active = 1
  AND mr.max_capacity >= ?  -- minimum required capacity
  AND mr.deleted_at IS NULL
  AND NOT EXISTS (
    SELECT 1 FROM bookings b
    WHERE b.room_id = mr.id
      AND b.status = 'confirmed'
      AND b.start_datetime < ?  -- requested end time
      AND b.end_datetime > ?    -- requested start time
      AND b.deleted_at IS NULL
  )
ORDER BY mr.max_capacity ASC, mr.name ASC;
```

### 2. Get Booking Calendar for a Date Range

```sql
-- Get all bookings within a date range for calendar display
SELECT 
    b.id,
    b.booking_ref,
    b.start_datetime,
    b.end_datetime,
    b.attendee_count,
    b.status,
    u.name AS guest_name,
    mr.name AS room_name,
    b.decoration_theme
FROM bookings b
INNER JOIN users u ON b.user_id = u.id
INNER JOIN meeting_rooms mr ON b.room_id = mr.id
WHERE b.start_datetime >= ?
  AND b.end_datetime <= ?
  AND b.status != 'cancelled'
  AND b.deleted_at IS NULL
ORDER BY b.start_datetime ASC;
```

### 3. Get Room Status Summary

```sql
-- Get current status of all rooms
SELECT 
    mr.id,
    mr.name,
    mr.max_capacity,
    mr.location,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM bookings b
            WHERE b.room_id = mr.id
              AND b.status = 'confirmed'
              AND NOW() BETWEEN b.start_datetime AND b.end_datetime
              AND b.deleted_at IS NULL
        ) THEN 'in-use'
        WHEN EXISTS (
            SELECT 1 FROM bookings b
            WHERE b.room_id = mr.id
              AND b.status = 'confirmed'
              AND b.start_datetime > NOW()
              AND b.start_datetime < DATE_ADD(NOW(), INTERVAL 2 HOUR)
              AND b.deleted_at IS NULL
        ) THEN 'booked-soon'
        ELSE 'available'
    END AS current_status,
    (SELECT COUNT(*) FROM bookings b 
     WHERE b.room_id = mr.id 
       AND b.start_datetime > NOW() 
       AND b.status = 'confirmed'
       AND b.deleted_at IS NULL
    ) AS upcoming_bookings_count
FROM meeting_rooms mr
WHERE mr.is_active = 1
  AND mr.deleted_at IS NULL
ORDER BY mr.name;
```

## Laravel Eloquent Models

### User Model
```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'line_id', 'language'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];
    
    public function bookings() {
        return $this->hasMany(Booking::class);
    }
    
    public function lineNotifications() {
        return $this->hasMany(LineNotification::class, 'recipient_id');
    }
    
    public function isGuest() {
        return $this->role === 'guest';
    }
    
    public function isStaff() {
        return in_array($this->role, ['staff', 'admin']);
    }
}
```

### MeetingRoom Model
```php
class MeetingRoom extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'description', 'max_capacity', 'location', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'max_capacity' => 'integer'];
    
    public function bookings() {
        return $this->hasMany(Booking::class, 'room_id');
    }
    
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }
}
```

### Booking Model
```php
class Booking extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['booking_ref', 'user_id', 'room_id', 
                          'start_datetime', 'end_datetime', 'attendee_count', 
                          'decoration_theme', 'status', 'notes'];
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'attendee_count' => 'integer'
    ];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function room() {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }
    
    public function lineNotifications() {
        return $this->hasMany(LineNotification::class);
    }
    
    public function scopeConfirmed($query) {
        return $query->where('status', 'confirmed');
    }
    
    public function scopeUpcoming($query) {
        return $query->where('start_datetime', '>', now());
    }
}
```

## State Transitions

### Booking Status Flow

```
[Created] → confirmed (default)
           ↓
confirmed → cancelled (user cancellation or admin action)
           ↓
confirmed → completed (after end_datetime passes)
```

**Business Rules**:
- New bookings default to 'confirmed'
- Guests can cancel their own bookings (future feature)
- Staff can cancel any booking
- Status automatically changes to 'completed' after end_datetime (cron job or event)
- Cancelled bookings preserve historical data (soft delete)

## Performance Indexes

**Critical Indexes for Performance Requirements**:

1. **Availability Search**: `idx_room_date` on bookings(room_id, start_datetime, end_datetime)
2. **User Bookings**: `idx_user_id` on bookings(user_id)
3. **Calendar View**: `idx_start_datetime` and `idx_end_datetime` on bookings
4. **Status Dashboard**: `idx_room_id`, `idx_status` on bookings
5. **Room Lookup**: `idx_is_active` on meeting_rooms
6. **Notification Queue**: `idx_status`, `idx_created_at` on line_notifications

These indexes ensure query performance meets <100ms requirement for database operations.

## Data Integrity Constraints

1. **Foreign Keys**: Enforce referential integrity
   - RESTRICT on user_id, room_id in bookings (prevent deletion if active bookings exist)
   - CASCADE on pivot tables (delete relationships when parent deleted)
   - SET NULL on optional relationships (theme_id)

2. **Unique Constraints**: Prevent duplicates
   - email in users
   - booking_ref in bookings
   - name in meeting_rooms, decoration_themes

3. **Check Constraints** (Application Level):
   - end_datetime > start_datetime
   - attendee_count <= room.max_capacity
   - start_datetime >= now() (at creation time)

4. **Soft Deletes**: Preserve historical data
   - users, meeting_rooms, bookings use soft deletes
   - Enables audit trail and reporting

## Migration Order

1. users
2. meeting_rooms
3. bookings
4. line_notifications

This order respects foreign key dependencies.

---

## Notes on Decoration Themes

**Design Decision**: Decoration themes are stored as free-text in the `bookings.decoration_theme` field rather than in a separate table.

**Rationale**:
- **Flexibility**: Guests can specify custom themes without admin pre-configuration
- **Simplicity**: Reduces database complexity (2 fewer tables)
- **No constraints**: Themes don't need to be "available" per room - any text is valid
- **Lower maintenance**: No need to manage theme master data

**Implementation Approach**:
- Frontend can provide autocomplete suggestions from a predefined list
- Suggested themes stored in Laravel config file (`config/booking.php`)
- Guests can select from suggestions or type custom theme
- No validation constraints - any string up to 255 characters allowed
- Backend treats it as simple text field

**Example Config** (`config/booking.php`):
```php
return [
    'decoration_themes' => [
        'Corporate',
        'Wedding Reception',
        'Conference',
        'Seminar',
        'Workshop',
        'Cocktail Party',
        'Birthday Party',
        'Training Session',
    ],
];
```

**Frontend Implementation**:
- Use datalist HTML5 element for autocomplete
- Or use Select2/Choices.js for enhanced dropdown with custom input
- Store whatever user types directly to database
