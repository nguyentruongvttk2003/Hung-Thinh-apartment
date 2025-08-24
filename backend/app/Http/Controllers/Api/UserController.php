<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();
            
            // Filter by role if provided
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by name or email if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $users = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('UserController store called', [
            'data' => $request->all(),
            'user' => auth()->user()?->id
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|in:resident,admin,accountant,technician',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            \Log::error('User validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        \Log::info('Creating user with validated data', $validator->validated());

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password, // Mutator will handle hashing
                'role' => $request->role,
                'status' => $request->status ?? 'active'
            ]);

            \Log::info('User created successfully', ['id' => $user->id]);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Người dùng đã được tạo thành công'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to create user', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|between:2,100',
                'email' => 'sometimes|string|email|max:100|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'password' => 'sometimes|string|min:6',
                'role' => 'sometimes|in:resident,admin,accountant,technician',
                'status' => 'sometimes|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $validator->validated();
            
            // Remove password if not provided
            if (!$request->has('password') || empty($request->password)) {
                unset($updateData['password']);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $user->fresh(),
                'message' => 'Người dùng đã được cập nhật thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting current user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa tài khoản của chính mình'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get technicians list.
     */
    public function technicians()
    {
        $technicians = User::where('role', 'technician')
                          ->where('status', 'active')
                          ->get(['id', 'name', 'email', 'phone']);
        
        return response()->json([
            'success' => true,
            'data' => $technicians,
            'message' => 'Technicians retrieved successfully'
        ]);
    }

    /**
     * Get accountants list.
     */
    public function accountants()
    {
        $accountants = User::where('role', 'accountant')
                          ->where('status', 'active')
                          ->get(['id', 'name', 'email', 'phone']);
        
        return response()->json([
            'success' => true,
            'data' => $accountants,
            'message' => 'Accountants retrieved successfully'
        ]);
    }
}
