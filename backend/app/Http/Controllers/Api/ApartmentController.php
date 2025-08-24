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
        \Log::info('ApartmentController index called', [
            'params' => $request->all(),
            'user' => auth()->user()?->id
        ]);

        $query = Apartment::query();
        
        \Log::info('Initial apartment count', ['count' => $query->count()]);

        // Filter by block - only if not empty
        if ($request->has('block') && !empty($request->block)) {
            $query->where('block', $request->block);
            \Log::info('Filtered by block', ['block' => $request->block, 'count' => $query->count()]);
        }

        // Filter by floor - only if not empty
        if ($request->has('floor') && !empty($request->floor)) {
            $query->where('floor', $request->floor);
            \Log::info('Filtered by floor', ['floor' => $request->floor, 'count' => $query->count()]);
        }

        // Filter by status - only if not empty
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
            \Log::info('Filtered by status', ['status' => $request->status, 'count' => $query->count()]);
        }

        // Filter by type - only if not empty
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
            \Log::info('Filtered by type', ['type' => $request->type, 'count' => $query->count()]);
        }

        // Search by apartment number - only if not empty
        if ($request->has('search') && !empty($request->search)) {
            $query->where('apartment_number', 'like', '%' . $request->search . '%');
            \Log::info('Filtered by search', ['search' => $request->search, 'count' => $query->count()]);
        }

        \Log::info('Final query count before pagination', ['count' => $query->count()]);

        // Add relationships
        $query->with(['owner', 'residents.user']);

        $apartments = $query->paginate($request->get('per_page', 15));

        \Log::info('Paginated result', [
            'items_count' => $apartments->count(),
            'total' => $apartments->total(),
            'current_page' => $apartments->currentPage()
        ]);

        return response()->json([
            'success' => true,
            'data' => $apartments->items(),
            'current_page' => $apartments->currentPage(),
            'last_page' => $apartments->lastPage(),
            'per_page' => $apartments->perPage(),
            'total' => $apartments->total(),
            'message' => 'Apartments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('ApartmentController store called', [
            'data' => $request->all(),
            'user' => auth()->user()?->id
        ]);

        $validator = Validator::make($request->all(), [
            'apartment_number' => 'required|string|unique:apartments',
            'block' => 'nullable|string',
            'floor' => 'required|integer|min:1',
            'area' => 'required|numeric|min:0',
            'type' => 'required|in:studio,1br,2br,3br,4br',
            'status' => 'required|in:occupied,vacant,maintenance,reserved',
            'owner_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Apartment validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = $validator->validated();
        
        // Auto-generate room_number from apartment_number (extract number part)
        $room_number = intval(preg_replace('/[^0-9]/', '', $data['apartment_number']));
        $data['room_number'] = $room_number ?: 101;
        
        // Auto-generate bedrooms based on type
        $bedroomMap = [
            'studio' => 0,
            '1br' => 1,
            '2br' => 2,
            '3br' => 3,
            '4br' => 4
        ];
        $data['bedrooms'] = $bedroomMap[$data['type']] ?? 1;
        
        // Convert type to match database enum
        $typeMap = [
            'studio' => 'studio',
            '1br' => '1BR',
            '2br' => '2BR', 
            '3br' => '3BR',
            '4br' => 'penthouse'
        ];
        $data['type'] = $typeMap[$data['type']] ?? '1BR';

        \Log::info('Creating apartment with processed data', $data);

        try {
            $apartment = Apartment::create($data);
            
            \Log::info('Apartment created successfully', ['id' => $apartment->id]);

            return response()->json([
                'success' => true,
                'message' => 'Căn hộ đã được tạo thành công',
                'data' => $apartment->load(['owner', 'residents.user'])
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to create apartment', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo căn hộ: ' . $e->getMessage()
            ], 500);
        }
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