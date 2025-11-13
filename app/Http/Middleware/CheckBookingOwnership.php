<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBookingOwnership
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user is the owner of the booking
     * or has staff/admin privileges.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Staff and admin can access all bookings
        if ($user->isStaff() || $user->isAdmin()) {
            return $next($request);
        }

        // Get booking ID from route parameter
        $bookingId = $request->route('booking');

        if (! $bookingId) {
            abort(404, 'Booking not found.');
        }

        // Find the booking
        $booking = Booking::find($bookingId);

        if (! $booking) {
            abort(404, 'Booking not found.');
        }

        // Check if user owns the booking
        if ($booking->user_id !== $user->id) {
            abort(403, 'You do not have permission to access this booking.');
        }

        return $next($request);
    }
}
