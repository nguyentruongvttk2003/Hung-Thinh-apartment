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
        $votes = Vote::where('status', 'active')->get();
        return response()->json($votes);
    }
}
