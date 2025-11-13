<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minBookingDuration = config('booking.min_booking_duration', 60); // minutes
        $maxBookingDuration = config('booking.max_booking_duration', 480); // minutes
        $minAdvanceBooking = config('booking.min_advance_booking', 0); // hours
        $maxAdvanceBooking = config('booking.max_advance_booking', 90); // days

        $rules = [
            'room_id' => 'required|exists:meeting_rooms,id',
            'start_datetime' => [
                'required',
                'date',
                'before:'.now()->addDays($maxAdvanceBooking)->toDateTimeString(),
            ],
            'end_datetime' => [
                'required',
                'date',
                'after:start_datetime',
            ],
            'attendee_count' => 'required|integer|min:1',
            'decoration_theme' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        // Only add minimum advance booking rule if greater than 0
        if ($minAdvanceBooking > 0) {
            $rules['start_datetime'][] = 'after:'.now()->addHours($minAdvanceBooking)->toDateTimeString();
        } else {
            // Can book immediately, but must be after current time
            $rules['start_datetime'][] = 'after:'.now()->toDateTimeString();
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        $minAdvanceBooking = config('booking.min_advance_booking', 0);
        $maxAdvanceBooking = config('booking.max_advance_booking', 90);

        $messages = [
            'room_id.required' => __('Please select a meeting room.'),
            'room_id.exists' => __('Selected room is not available.'),
            'start_datetime.required' => __('Start date and time is required.'),
            'start_datetime.before' => __('Booking cannot be made more than :days days in advance.', [
                'days' => $maxAdvanceBooking,
            ]),
            'end_datetime.required' => __('End date and time is required.'),
            'end_datetime.after' => __('End time must be after start time.'),
            'attendee_count.required' => __('Number of attendees is required.'),
            'attendee_count.min' => __('At least one attendee is required.'),
            'notes.max' => __('Notes cannot exceed 1000 characters.'),
        ];

        // Only show minimum advance booking message if required
        if ($minAdvanceBooking > 0) {
            $messages['start_datetime.after'] = __('Booking must be made at least :hours hours in advance.', [
                'hours' => $minAdvanceBooking,
            ]);
        } else {
            $messages['start_datetime.after'] = __('Start time must be in the future.');
        }

        return $messages;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure datetime fields are properly formatted
        if ($this->has('start_datetime') && $this->has('end_datetime')) {
            $this->merge([
                'start_datetime' => Carbon::parse($this->start_datetime)->format('Y-m-d H:i:s'),
                'end_datetime' => Carbon::parse($this->end_datetime)->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $minDuration = config('booking.min_booking_duration', 60);
            $maxDuration = config('booking.max_booking_duration', 480);

            if ($this->has('start_datetime') && $this->has('end_datetime')) {
                $start = Carbon::parse($this->start_datetime);
                $end = Carbon::parse($this->end_datetime);
                $durationMinutes = $start->diffInMinutes($end);

                if ($durationMinutes < $minDuration) {
                    $validator->errors()->add(
                        'end_datetime',
                        __('Booking must be at least :minutes minutes long.', ['minutes' => $minDuration])
                    );
                }

                if ($durationMinutes > $maxDuration) {
                    $validator->errors()->add(
                        'end_datetime',
                        __('Booking cannot exceed :hours hours.', ['hours' => $maxDuration / 60])
                    );
                }
            }
        });
    }
}
