<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'location',
        'status',
        'description',
        'last_maintenance',
        'next_maintenance',
        'device_code',
        'category', 
        'brand',
        'model',
        'installation_date',
        'warranty_expiry',
        'specifications',
        'notes',
        'responsible_technician',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'responsible_technician');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
} 