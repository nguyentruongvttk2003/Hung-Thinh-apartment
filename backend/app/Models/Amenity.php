<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'type',
        'status',
        'open_time',
        'close_time',
        'capacity',
        'booking_slot_minutes',
    ];

    protected $casts = [
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    public function bookings()
    {
        return $this->hasMany(AmenityBooking::class);
    }
}


