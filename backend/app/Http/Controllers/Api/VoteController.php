<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index()
    {
        $votes = Vote::latest()->paginate(15);
        return response()->json($votes);
    }

    public function store(Request $request)
    {
        $vote = Vote::create($request->all());
        return response()->json($vote, 201);
    }

    public function show($id)
    {
        $vote = Vote::findOrFail($id);
        return response()->json($vote);
    }

    public function update(Request $request, $id)
    {
        $vote = Vote::findOrFail($id);
        $vote->update($request->all());
        return response()->json($vote);
    }

    public function destroy($id)
    {
        $vote = Vote::findOrFail($id);
        $vote->delete();
        return response()->json(['message' => 'Vote deleted successfully']);
    }

    public function activate($id)
    {
        $vote = Vote::findOrFail($id);
        $vote->update(['status' => 'active']);
        return response()->json($vote);
    }

    public function close($id)
    {
        $vote = Vote::findOrFail($id);
        $vote->update(['status' => 'closed']);
        return response()->json($vote);
    }

    public function submitVote(Request $request, $id)
    {
        // Logic to submit vote would go here
        return response()->json(['message' => 'Vote submitted successfully']);
    }

    public function results($id)
    {
        $vote = Vote::findOrFail($id);
        // Logic to get vote results would go here
        return response()->json(['vote' => $vote, 'results' => []]);
    }

    public function active()
    {
        try {
            $votes = Vote::where('status', 'active')
                ->with(['creator', 'options'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($vote) {
                    return [
                        'id' => $vote->id,
                        'title' => $vote->title,
                        'description' => $vote->description,
                        'type' => $vote->type,
                        'scope' => $vote->scope,
                        'target_scope' => $vote->target_scope,
                        'start_date' => $vote->start_time,
                        'end_date' => $vote->end_time,
                        'status' => $vote->status,
                        'require_quorum' => $vote->require_quorum,
                        'quorum_percentage' => $vote->quorum_percentage,
                        'created_by' => $vote->created_by,
                        'notes' => $vote->notes,
                        'created_at' => $vote->created_at,
                        'updated_at' => $vote->updated_at,
                        'creator' => $vote->creator,
                        'options' => $vote->options,
                        'totalVotes' => $vote->responses ? $vote->responses->count() : 0,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $votes,
                'message' => 'Active votes retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching active votes: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
