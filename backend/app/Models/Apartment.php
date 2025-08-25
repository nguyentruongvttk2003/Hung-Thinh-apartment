<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartment_number',
        'block',
        'floor',
        'room_number',
        'area',
        'bedrooms',
        'type',
        'status',
        'owner_id',
        'description',
    ];

    protected $casts = [
        'area' => 'decimal:2',
    ];

    /**
     * Get the owner of the apartment.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the residents of the apartment.
     */
    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    /**
     * Get the active residents of the apartment.
     */
    public function activeResidents()
    {
        return $this->hasMany(Resident::class)->where('status', 'active');
    }

    /**
     * Get the invoices for the apartment.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the pending invoices for the apartment.
     */
    public function pendingInvoices()
    {
        return $this->hasMany(Invoice::class)->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    /**
     * Get the feedbacks related to the apartment.
     */
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the primary contact resident.
     */
    public function primaryContact()
    {
        return $this->hasOne(Resident::class)->where('is_primary_contact', true);
    }

    /**
     * Scope a query to only include occupied apartments.
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope a query to only include vacant apartments.
     */
    public function scopeVacant($query)
    {
        return $query->where('status', 'vacant');
    }

    /**
     * Scope a query to only include apartments in a specific block.
     */
    public function scopeInBlock($query, $block)
    {
        return $query->where('block', $block);
    }

    /**
     * Scope a query to only include apartments on a specific floor.
     */
    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Get the total outstanding amount for this apartment.
     */
    public function getOutstandingAmount()
    {
        return $this->invoices()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum(\DB::raw('total_amount - paid_amount'));
    }

    /**
     * Check if apartment has outstanding payments.
     */
    public function hasOutstandingPayments()
    {
        return $this->getOutstandingAmount() > 0;
    }

    /**
     * Get the full apartment display name.
     */
    public function getFullNameAttribute()
    {
        return "Căn hộ {$this->apartment_number}";
    }

    /**
     * Get the location display name.
     */
    public function getLocationAttribute()
    {
        $location = [];
        if ($this->block) {
            $location[] = "Block {$this->block}";
        }
        $location[] = "Tầng {$this->floor}";
        $location[] = "Phòng {$this->room_number}";
        
        return implode(' - ', $location);
    }
} 