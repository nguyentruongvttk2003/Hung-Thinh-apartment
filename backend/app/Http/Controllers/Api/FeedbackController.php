<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Feedback::with(['user', 'apartment', 'assignedTechnician']);
            
            // Filter by apartment if provided
            if ($request->has('apartment_id')) {
                $query->where('apartment_id', $request->apartment_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by type if provided
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by priority if provided
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            // Search by title if provided
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $feedbacks = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $feedbacks->items(),
                'current_page' => $feedbacks->currentPage(),
                'last_page' => $feedbacks->lastPage(),
                'per_page' => $feedbacks->perPage(),
                'total' => $feedbacks->total(),
                'message' => 'Feedbacks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách phản ánh: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Assign feedback to a technician
     */
    public function assign(Request $request, $id)
    {
        \Log::info('FeedbackController assign called', [
            'feedback_id' => $id,
            'data' => $request->all(),
            'user' => auth()->user()?->id
        ]);

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Feedback assign validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feedback = Feedback::findOrFail($id);
            
            // Check if technician role
            $technician = User::where('id', $request->assigned_to)
                             ->where('role', 'technician')
                             ->where('status', 'active')
                             ->first();
                             
            if (!$technician) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kỹ thuật viên không hợp lệ hoặc không hoạt động'
                ], 400);
            }

            $feedback->update([
                'assigned_to' => $request->assigned_to,
                'assigned_at' => now(),
                'status' => 'in_progress',
                'resolution_notes' => $request->notes
            ]);

            \Log::info('Feedback assigned successfully', [
                'feedback_id' => $feedback->id,
                'assigned_to' => $technician->name
            ]);

            return response()->json([
                'success' => true,
                'data' => $feedback->load(['user', 'apartment', 'assignedTechnician']),
                'message' => "Đã phân công phản ánh cho {$technician->name}"
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to assign feedback', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể phân công: ' . $e->getMessage()
            ], 500);
        }
    }
}
