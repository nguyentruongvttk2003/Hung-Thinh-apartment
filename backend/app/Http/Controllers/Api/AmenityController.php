<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        $query = Amenity::query();
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        return response()->json($query->latest()->paginate($request->get('per_page', 15)));
    }

    public function show($id)
    {
        return response()->json(Amenity::findOrFail($id));
    }

    public function availability(Request $request, $id)
    {
        $amenity = Amenity::findOrFail($id);
        $date = $request->input('date', now()->toDateString());
        $slotMinutes = $amenity->booking_slot_minutes ?: 60;

        $start = now()->parse($date . ' ' . ($amenity->open_time?->format('H:i') ?? '08:00'));
        $end = now()->parse($date . ' ' . ($amenity->close_time?->format('H:i') ?? '21:00'));

        $slots = [];
        for ($t = $start->copy(); $t->lt($end); $t->addMinutes($slotMinutes)) {
            $slotEnd = $t->copy()->addMinutes($slotMinutes);
            $hasBooking = $amenity->bookings()
                ->whereDate('start_time', $date)
                ->where(function ($q) use ($t, $slotEnd) {
                    $q->whereBetween('start_time', [$t, $slotEnd])
                      ->orWhereBetween('end_time', [$t, $slotEnd])
                      ->orWhere(function ($q2) use ($t, $slotEnd) {
                          $q2->where('start_time', '<=', $t)
                             ->where('end_time', '>=', $slotEnd);
                      });
                })
                ->exists();

            $slots[] = [
                'start' => $t->toDateTimeString(),
                'end' => $slotEnd->toDateTimeString(),
                'available' => !$hasBooking,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'amenity' => $amenity,
                'slots' => $slots,
            ]
        ]);
    }
}


