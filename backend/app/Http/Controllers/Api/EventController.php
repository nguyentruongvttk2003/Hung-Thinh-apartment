<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Event::with(['creator']);
            
            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }
            
            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $events = $query->latest()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to retrieve events: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:meeting,maintenance,power_outage,water_outage,social_event,emergency',
                'scope' => 'required|in:all,block,floor,apartment,specific',
                'target_scope' => 'nullable|array',
                'start_time' => 'required|date',
                'end_time' => 'nullable|date|after:start_time',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|in:scheduled,in_progress,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            $event = Event::create(array_merge($validatedData, [
                'created_by' => auth()->id()
            ]));
            
            $event->load('creator');
            
            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::with(['creator'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Event not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            
            $validatedData = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'type' => 'sometimes|required|in:meeting,maintenance,power_outage,water_outage,social_event,emergency',
                'scope' => 'sometimes|required|in:all,block,floor,apartment,specific',
                'target_scope' => 'nullable|array',
                'start_time' => 'sometimes|required|date',
                'end_time' => 'nullable|date|after:start_time',
                'location' => 'nullable|string|max:255',
                'status' => 'nullable|in:scheduled,in_progress,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            $event->update($validatedData);
            $event->load('creator');
            
            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to delete event'
            ], 500);
        }
    }

    public function upcoming()
    {
        $events = Event::where('start_time', '>=', now())->orderBy('start_time')->get();
        return response()->json([
            'success' => true,
            'data' => $events,
            'message' => 'Upcoming events retrieved successfully'
        ]);
    }
}
