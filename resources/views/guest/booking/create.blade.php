@extends('layouts.app')

@section('title', __('Create Booking'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">{{ __('Create Booking') }}</h2>
        </div>
    </div>

    <div class="row">
        <!-- Room Information -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ __('Selected Room') }}</h5>
                </div>
                <div class="card-body">
                    <h4>{{ $room->name }}</h4>
                    <p class="text-muted">{{ $room->description }}</p>
                    
                    <div class="mb-2">
                        <i class="fas fa-users text-primary"></i>
                        <strong>{{ __('Max Capacity') }}:</strong> {{ $room->max_capacity }}
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        <strong>{{ __('Location') }}:</strong> {{ $room->location }}
                    </div>
                    @if($room->hourly_rate > 0)
                    <div class="mb-2">
                        <i class="fas fa-money-bill-wave text-success"></i>
                        <strong>{{ __('Rate') }}:</strong> {{ number_format($room->hourly_rate, 2) }} {{ __('THB/hour') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">{{ __('Booking Details') }}</h6>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('Attendees') }}:</strong> {{ $attendeeCount }}</p>
                    <p><strong>{{ __('Start') }}:</strong><br>
                        {{ \Carbon\Carbon::parse($startDatetime)->format('d M Y H:i') }}</p>
                    <p class="mb-0"><strong>{{ __('End') }}:</strong><br>
                        {{ \Carbon\Carbon::parse($endDatetime)->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('guest.booking.store') }}">
                        @csrf

                        <!-- Hidden Fields -->
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        <input type="hidden" name="attendee_count" value="{{ $attendeeCount }}">
                        <input type="hidden" name="start_datetime" value="{{ $startDatetime }}">
                        <input type="hidden" name="end_datetime" value="{{ $endDatetime }}">

                        <!-- Decoration Theme -->
                        <div class="form-group mb-3">
                            <label for="decoration_theme">{{ __('Decoration Theme') }}</label>
                            <select class="form-control @error('decoration_theme') is-invalid @enderror" 
                                    id="decoration_theme" 
                                    name="decoration_theme">
                                <option value="">{{ __('-- Select Theme (Optional) --') }}</option>
                                @foreach($decorationThemes as $theme)
                                    <option value="{{ $theme }}" {{ old('decoration_theme') === $theme ? 'selected' : '' }}>
                                        {{ $theme }}
                                    </option>
                                @endforeach
                            </select>
                            @error('decoration_theme')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Choose a decoration theme for your event') }}
                            </small>
                        </div>

                        <!-- Notes -->
                        <div class="form-group mb-3">
                            <label for="notes">{{ __('Additional Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="4" 
                                      maxlength="1000"
                                      placeholder="{{ __('Enter any special requests or additional information...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Maximum 1000 characters') }}
                            </small>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="terms" required>
                                <label class="custom-control-label" for="terms">
                                    {{ __('I agree to the') }} 
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">
                                        {{ __('terms and conditions') }}
                                    </a>
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> {{ __('Confirm Booking') }}
                            </button>
                            <a href="{{ route('guest.booking.search') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> {{ __('Back to Search') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Booking Terms and Conditions') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>{{ __('Cancellation Policy') }}</h6>
                <ul>
                    <li>{{ __('สามารถยกเลิกการจองได้ก่อนเวลาเริ่มต้น 24 ชั่วโมง') }}</li>
                    <li>{{ __('Late cancellations may not be permitted') }}</li>
                </ul>

                <h6>{{ __('Room Usage') }}</h6>
                <ul>
                    <li>{{ __('Maximum capacity must not be exceeded') }}</li>
                    <li>{{ __('Room must be returned in original condition') }}</li>
                    <li>{{ __('Any damages will be charged to the booking account') }}</li>
                </ul>

                <h6>{{ __('Booking Confirmation') }}</h6>
                <ul>
                    <li>{{ __('You will receive a confirmation notification via Line (if registered)') }}</li>
                    <li>{{ __('Please arrive 15 minutes before your booking time') }}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection
