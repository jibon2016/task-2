<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Ticket;
use App\Notifications\BookingConfirm;
use App\Traits\CommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

class BookingController extends Controller
{
    use CommonResponse;

    public function store(Request $request, string $id)
    {
        $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'status' => ['required', new Enum(BookingStatus::class)],
            'quantity' => 'required|integer',
        ]);
        try {
            $ticket = Ticket::query()->find($id);
            $booking = Booking::query()->create([
                'user_id' => $request->input('user_id') ?? auth()->user()->id,
                'ticket_id' => $ticket->id,
                'status' => $request->input('status', BookingStatus::PENDING),
                'quantity' => $request->input('quantity'),
            ]);
            $this->status_message = 'Booking created successfully';
            $this->data = new BookingResource($booking);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->status_message = 'Failed to create Ticket';
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }

    public function userBookings()
    {
        try {
            $booking = Booking::query()->where('user_id', auth()->user()->id)->get();
            $this->status_message = 'User bookings list';
            $this->data = BookingResource::collection($booking);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $this->status_message = 'Failed to fetch Booking list';
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }

    public function updateBooking(string $id, Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'status' => ['required', new Enum(BookingStatus::class)],
            'quantity' => 'required|integer',
        ]);
        try {
            $booking = Booking::query()->findOrFail($id);
            $booking->update([
                'ticket_id' => $request->input('ticket_id'),
                'status' => $request->input('status'),
                'quantity' => $request->input('quantity'),
            ]);
            if ( $booking->status === BookingStatus::CONFIRMED->value ) {
                $booking->user->notify(new BookingConfirm($booking));
            }
            $this->status_message = 'Booking updated successfully';
            $this->data = new BookingResource($booking);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->status_message = 'Failed to update Booking';
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }
}
