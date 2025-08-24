<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'apartment_id',
        'relationship',
        'move_in_date',
        'move_out_date',
        'status',
        'is_primary_contact',
        'notes',
    ];

    protected $casts = [
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'is_primary_contact' => 'boolean',
    ];

    /**
     * Get the user that is a resident.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the apartment where the user is a resident.
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Scope a query to only include active residents.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include primary contacts.
     */
    public function scopePrimaryContact($query)
    {
        return $query->where('is_primary_contact', true);
    }
} 