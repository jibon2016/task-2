<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use App\Traits\CommonQueryScopes;
use App\Traits\CommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    use CommonResponse;

    public function events(Request $request)
    {
        try {
//            $events = (new Event())->get_events($request);
            $events = Event::searchByTitle($request->input('title'))
                ->paginate(10);
            $this->status_message = 'Events list';
            $this->data = [
                'events' => EventResource::collection($events)->response()->getData(true),
            ];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            $this->status_message = 'Failed to fetch Events list';
            $this->data = [];
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }
    public function event(string $id)
    {
        try {
            $event = Event::query()->findOrFail($id)->with('tickets')->first();
            $this->status_message = 'Event details';
            $this->data = new EventResource($event);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $this->status_message = 'Failed to fetch event details';
            $this->data = [];
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }

    public function create(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
        ]);
        try {
            $event = Event::query()->create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'date' => $request->input('date'),
                'location' => $request->input('location'),
                'created_by' => auth()->id(),
            ]);
            $this->status_message = 'Event created successfully';
            $this->data = new EventResource($event);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->status_message = 'Failed to create event';
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }
    public function update(string $id, Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
        ]);
        try {
            $inputs = $request->all();
            $event = Event::query()->findOrFail($id);
            $event->update([
                'title' => $inputs['title'],
                'description' => $inputs['description'],
                'date' => $inputs['date'],
                'location' => $inputs['location'],
            ]);
            $this->status_message = 'Event updated successfully';
            $this->data = new EventResource($event);
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

    public function delete(string $id)
    {
        try {
            DB::beginTransaction();
            $event = Event::query()->find($id);
            if ($event) {
                $event->delete();
                $this->status_message = 'Event deleted successfully';
            }else{
                $this->status=false;
                $this->status_code = 404;
                $this->status_message = 'Event not found';
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
