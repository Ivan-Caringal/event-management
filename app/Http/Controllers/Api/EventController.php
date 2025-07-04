<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\CanLoadRelationships;


class EventController extends Controller
{
     use CanLoadRelationships;
      private array $relations = ['user', 'attendees', 'attendees.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }


    public function index()
    {
       
        
        $query = $this->loadRelationships(Event::query());

        //load relationships 
        // foreach ($relations as $relation) {
        //     $query->when(
        //         $this->shouldIncludeRelation($relation),
        //         fn($q) => $q->with($relation)
        //     );
        // }
        //event resource para ma customize yung output ng api

        return EventResource::collection(
             $query->latest()->paginate());

    }

   

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);

         return new EventResource($this->loadRelationships($event));
    }   

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
         return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
         $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
            ])
        );
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
         $event->delete();
         return response(status: 204);
    }
}
