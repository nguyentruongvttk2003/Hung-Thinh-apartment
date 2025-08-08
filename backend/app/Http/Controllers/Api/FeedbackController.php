<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::latest()->paginate(15);
        return response()->json($feedbacks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'apartment_id' => 'required|exists:apartments,id',
        ]);

        $feedback = Feedback::create([
            'subject' => $request->subject,
            'description' => $request->description,
            'apartment_id' => $request->apartment_id,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json($feedback, 201);
    }

    public function show($id)
    {
        $feedback = Feedback::findOrFail($id);
        return response()->json($feedback);
    }

    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update($request->all());
        return response()->json($feedback);
    }

    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();
        return response()->json(['message' => 'Feedback deleted successfully']);
    }

    public function assign(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'assigned',
        ]);
        return response()->json($feedback);
    }

    public function resolve(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
        return response()->json($feedback);
    }

    public function rate(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'rating' => $request->rating,
        ]);
        return response()->json($feedback);
    }
}
