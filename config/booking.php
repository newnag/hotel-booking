<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Booking Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the meeting room booking system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Suggested Decoration Themes
    |--------------------------------------------------------------------------
    |
    | List of suggested decoration themes for meeting room bookings.
    | These will be displayed as options in the booking form.
    |
    */

    'decoration_themes' => [
        'ธรรมดา (ไม่มีการตกแต่ง)',
        'ธีมธุรกิจ (Business Theme)',
        'ธีมสีฟ้า - ขาว',
        'ธีมสีชมพู - ทอง',
        'ธีมดอกไม้สด',
        'ธีมโมเดิร์น (Modern)',
        'ธีมคลาสสิค (Classic)',
        'ธีมสีแดง - ทอง',
        'ธีมสีเขียว - ขาว',
        'ธีมวันเกิด (Birthday)',
        'ธีมแต่งงาน (Wedding)',
        'ธีมสัมมนา (Conference)',
        'ธีมงานเลี้ยง (Party)',
        'ธีมไทยโบราณ (Traditional Thai)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Time Restrictions
    |--------------------------------------------------------------------------
    |
    | Configure booking time restrictions and limits.
    |
    */

    'min_booking_duration' => 60, // Minimum booking duration in minutes
    'max_booking_duration' => 480, // Maximum booking duration in minutes (8 hours)
    'min_advance_booking' => 0, // Minimum hours in advance for booking (0 = can book immediately)
    'max_advance_booking' => 90, // Maximum days in advance for booking

    /*
    |--------------------------------------------------------------------------
    | Business Hours
    |--------------------------------------------------------------------------
    |
    | Configure business hours for meeting room bookings.
    |
    */

    'business_hours' => [
        'start' => '08:00',
        'end' => '20:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Status Options
    |--------------------------------------------------------------------------
    |
    | Valid booking status values.
    |
    */

    'status_options' => [
        'confirmed',
        'cancelled',
        'completed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure when Line notifications should be sent.
    |
    */

    'notifications' => [
        'send_on_create' => true,
        'send_on_confirm' => true,
        'send_on_cancel' => true,
        'send_reminder' => true,
        'reminder_hours_before' => 24, // Send reminder 24 hours before booking
    ],

];
