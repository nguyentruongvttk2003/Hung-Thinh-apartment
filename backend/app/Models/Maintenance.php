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

    protected $appends = ['device_name', 'technician_name'];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    // Accessor cho scheduled_time để đảm bảo định dạng đúng
    public function getScheduledTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null;
    }

    // Accessor để lấy tên device
    public function getDeviceNameAttribute()
    {
        return $this->device ? $this->device->name : null;
    }

    // Accessor để lấy tên technician
    public function getTechnicianNameAttribute()
    {
        return $this->technician ? $this->technician->name : null;
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_technician');
    }
} 