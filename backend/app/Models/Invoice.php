<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'apartment_id',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'management_fee',
        'electricity_fee',
        'water_fee',
        'parking_fee',
        'other_fees',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'due_date' => 'date',
        'management_fee' => 'decimal:2',
        'electricity_fee' => 'decimal:2',
        'water_fee' => 'decimal:2',
        'parking_fee' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getOutstandingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }
} 