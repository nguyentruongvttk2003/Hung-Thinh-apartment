<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Vote::with(['creator']);
            
            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
            
            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $votes = $query->latest()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $votes,
                'message' => 'Votes retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to retrieve votes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:general_meeting,budget_approval,rule_change,facility_upgrade,other',
                'scope' => 'required|in:all,block,floor,apartment',
                'target_scope' => 'nullable|array',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'require_quorum' => 'boolean',
                'quorum_percentage' => 'integer|min:1|max:100',
                'notes' => 'nullable|string'
            ]);

            $vote = Vote::create(array_merge($validatedData, [
                'created_by' => auth()->id(),
                'status' => 'draft'
            ]));
            
            $vote->load('creator');
            
            return response()->json([
                'success' => true,
                'data' => $vote,
                'message' => 'Vote created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to create vote: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $vote = Vote::with(['creator', 'options'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $vote,
                'message' => 'Vote retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Vote not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $vote = Vote::findOrFail($id);
            
            $validatedData = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'type' => 'sometimes|required|in:general_meeting,budget_approval,rule_change,facility_upgrade,other',
                'scope' => 'sometimes|required|in:all,block,floor,apartment',
                'target_scope' => 'nullable|array',
                'start_time' => 'sometimes|required|date',
                'end_time' => 'sometimes|required|date|after:start_time',
                'status' => 'sometimes|in:draft,active,closed,cancelled',
                'require_quorum' => 'boolean',
                'quorum_percentage' => 'integer|min:1|max:100',
                'notes' => 'nullable|string'
            ]);

            $vote->update($validatedData);
            $vote->load('creator');
            
            return response()->json([
                'success' => true,
                'data' => $vote,
                'message' => 'Vote updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to update vote: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $vote = Vote::findOrFail($id);
            $vote->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Vote deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Failed to delete vote'
            ], 500);
        }
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
        $vote = Vote::with('options')->findOrFail($id);

        $validated = $request->validate([
            'vote_option_id' => 'required|exists:vote_options,id',
            'comment' => 'nullable|string',
        ]);

        // Ensure option belongs to this vote
        if (!$vote->options->pluck('id')->contains($validated['vote_option_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lựa chọn không thuộc cuộc bỏ phiếu này'
            ], 422);
        }

        // Prevent duplicate vote by same user
        $existing = $vote->responses()->where('user_id', auth()->id())->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã bỏ phiếu'
            ], 400);
        }

        $response = $vote->responses()->create([
            'vote_option_id' => $validated['vote_option_id'],
            'user_id' => auth()->id(),
            'comment' => $validated['comment'] ?? null,
            'voted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Vote submitted successfully'
        ]);
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
