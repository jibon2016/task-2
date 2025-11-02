<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use App\Traits\CommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    use CommonResponse;
    public function addTickets(Request $request, string $id)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
        ]);
        try {
            $event = Event::query()->find($id);
            $ticket = Ticket::query()->create([
                'event_id' => $event->id,
                'type' => $request->input('type'),
                'price' => $request->input('price'),
                'quantity' => $request->input('quantity'),
            ]);
            $this->status_message = 'Ticket created successfully';
            $this->data = new TicketResource($ticket);
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
    public function updateTicket(string $id, Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'type' => 'required|string|max:255',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
        ]);
        try {
            $ticket = Ticket::query()->findOrFail($id);
            $ticket->update([
                'event_id' => $request->input('event_id'),
                'type' => $request->input('type'),
                'price' => $request->input('price'),
                'quantity' => $request->input('quantity'),
            ]);
            $this->status_message = 'Event updated successfully';
            $this->data = new TicketResource($ticket);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->status_message = 'Failed to update event';
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }

    public function deleteTicket(string $id)
    {
        try {
            DB::beginTransaction();
            $ticket = Ticket::query()->find($id);
            if ($ticket) {
                $ticket->delete();
                $this->status_message = 'Ticket deleted successfully';
            }else{
                $this->status=false;
                $this->status_code = 404;
                $this->status_message = 'Ticket not found';
                $this->status_class = 'failed';
            }
            DB::commit();
        }catch (\Throwable $throwable){
            DB::rollBack();
            Log::error($throwable->getMessage());
            $this->status_message = 'Failed! ' . $throwable->getMessage();
            $this->status_code    = $this->status_code_failed;
            $this->status         = false;
        }
        return $this->commonApiResponse();
    }
}
