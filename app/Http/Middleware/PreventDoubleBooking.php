<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ticketId = $request->route('id');

        if (!$request->user() || !$ticketId) {
            return $next($request);
        }

        $existing = Booking::where('ticket_id', $ticketId)
            ->where('user_id', $request->user()->id)
            ->where(function ($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a booking for this ticket.',
                'status_code' => 409,
            ], 409);
        }

        return $next($request);
    }
}
