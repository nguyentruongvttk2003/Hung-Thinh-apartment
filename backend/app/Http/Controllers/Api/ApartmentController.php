<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Apartment::with(['owner', 'residents.user']);

        // Filter by block
        if ($request->has('block')) {
            $query->where('block', $request->block);
        }

        // Filter by floor
        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by apartment number
        if ($request->has('search')) {
            $query->where('apartment_number', 'like', '%' . $request->search . '%');
        }

        $apartments = $query->paginate($request->get('per_page', 15));

        return response()->json($apartments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apartment_number' => 'required|string|unique:apartments',
            'block' => 'nullable|string',
            'floor' => 'required|integer|min:1',
            'room_number' => 'required|integer|min:1',
            'area' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:1',
            'type' => 'required|in:studio,1BR,2BR,3BR,penthouse',
            'status' => 'required|in:occupied,vacant,maintenance,reserved',
            'owner_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $apartment = Apartment::create($validator->validated());

        return response()->json([
            'message' => 'Căn hộ đã được tạo thành công',
            'apartment' => $apartment->load(['owner', 'residents.user'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Apartment $apartment)
    {
        $apartment->load(['owner', 'residents.user', 'invoices' => function($query) {
            $query->latest()->limit(5);
        }, 'feedbacks' => function($query) {
            $query->latest()->limit(5);
        }]);

        return response()->json($apartment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apartment $apartment)
    {
        $validator = Validator::make($request->all(), [
            'apartment_number' => 'sometimes|string|unique:apartments,apartment_number,' . $apartment->id,
            'block' => 'nullable|string',
            'floor' => 'sometimes|integer|min:1',
            'room_number' => 'sometimes|integer|min:1',
            'area' => 'sometimes|numeric|min:0',
            'bedrooms' => 'sometimes|integer|min:1',
            'type' => 'sometimes|in:studio,1BR,2BR,3BR,penthouse',
            'status' => 'sometimes|in:occupied,vacant,maintenance,reserved',
            'owner_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $apartment->update($validator->validated());

        return response()->json([
            'message' => 'Căn hộ đã được cập nhật thành công',
            'apartment' => $apartment->load(['owner', 'residents.user'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Apartment $apartment)
    {
        // Check if apartment has residents
        if ($apartment->residents()->count() > 0) {
            return response()->json([
                'error' => 'Không thể xóa căn hộ đang có cư dân'
            ], 400);
        }

        // Check if apartment has invoices
        if ($apartment->invoices()->count() > 0) {
            return response()->json([
                'error' => 'Không thể xóa căn hộ đang có hóa đơn'
            ], 400);
        }

        $apartment->delete();

        return response()->json([
            'message' => 'Căn hộ đã được xóa thành công'
        ]);
    }

    /**
     * Get residents of an apartment.
     */
    public function residents(Apartment $apartment)
    {
        $residents = $apartment->residents()->with('user')->get();

        return response()->json($residents);
    }

    /**
     * Add a resident to an apartment.
     */
    public function addResident(Request $request, Apartment $apartment)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'relationship' => 'required|in:owner,tenant,family_member,domestic_worker',
            'move_in_date' => 'required|date',
            'is_primary_contact' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if user is already a resident of this apartment
        if ($apartment->residents()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'error' => 'Người dùng đã là cư dân của căn hộ này'
            ], 400);
        }

        $resident = $apartment->residents()->create($validator->validated());

        return response()->json([
            'message' => 'Cư dân đã được thêm thành công',
            'resident' => $resident->load('user')
        ], 201);
    }

    /**
     * Remove a resident from an apartment.
     */
    public function removeResident(Apartment $apartment, Resident $resident)
    {
        if ($resident->apartment_id !== $apartment->id) {
            return response()->json([
                'error' => 'Cư dân không thuộc căn hộ này'
            ], 400);
        }

        $resident->update([
            'status' => 'moved_out',
            'move_out_date' => now()
        ]);

        return response()->json([
            'message' => 'Cư dân đã được xóa khỏi căn hộ'
        ]);
    }

    /**
     * Get apartments owned by the authenticated user.
     */
    public function myApartments()
    {
        $user = auth()->user();
        
        $apartments = $user->residences()
            ->with(['apartment.owner', 'apartment.residents.user'])
            ->get()
            ->pluck('apartment');

        return response()->json($apartments);
    }

    /**
     * Get apartment statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => Apartment::count(),
            'occupied' => Apartment::where('status', 'occupied')->count(),
            'vacant' => Apartment::where('status', 'vacant')->count(),
            'maintenance' => Apartment::where('status', 'maintenance')->count(),
            'reserved' => Apartment::where('status', 'reserved')->count(),
            'by_type' => Apartment::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_block' => Apartment::selectRaw('block, count(*) as count')
                ->whereNotNull('block')
                ->groupBy('block')
                ->pluck('count', 'block'),
        ];

        return response()->json($stats);
    }
} 