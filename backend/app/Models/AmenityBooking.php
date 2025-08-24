<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'amenity_id',
        'user_id',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


