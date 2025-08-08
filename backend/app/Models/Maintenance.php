<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'scheduled_date',
        'scheduled_time',
        'started_at',
        'completed_at',
        'assigned_technician',
        'work_performed',
        'parts_replaced',
        'cost',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'time',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_technician');
    }
} 