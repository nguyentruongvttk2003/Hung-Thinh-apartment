<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use Illuminate\Http\Request;

class AmenityBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = AmenityBooking::with(['amenity']);
        if ($request->filled('amenity_id')) {
            $query->where('amenity_id', $request->input('amenity_id'));
        }
        return response()->json($query->latest()->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amenity_id' => 'required|exists:amenities,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        $amenity = Amenity::findOrFail($validated['amenity_id']);
        $overlap = AmenityBooking::where('amenity_id', $amenity->id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_time', '<=', $validated['start_time'])
                         ->where('end_time', '>=', $validated['end_time']);
                  });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Khung giờ đã có người đặt'
            ], 422);
        }

        $booking = AmenityBooking::create([
            'amenity_id' => $amenity->id,
            'user_id' => auth()->id(),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'booked',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $booking->load('amenity'),
            'message' => 'Đặt tiện ích thành công'
        ], 201);
    }

    public function show($id)
    {
        return response()->json(AmenityBooking::with('amenity')->findOrFail($id));
    }

    public function destroy($id)
    {
        $booking = AmenityBooking::findOrFail($id);
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền hủy đặt chỗ này'
            ], 403);
        }
        $booking->delete();
        return response()->json(['success' => true, 'message' => 'Đã hủy đặt chỗ']);
    }

    public function myBookings()
    {
        $bookings = AmenityBooking::with('amenity')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
        return response()->json(['success' => true, 'data' => $bookings]);
    }
}


