<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'scope',
        'target_scope',
        'start_time',
        'end_time',
        'status',
        'require_quorum',
        'quorum_percentage',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'target_scope' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'require_quorum' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options()
    {
        return $this->hasMany(VoteOption::class);
    }

    public function responses()
    {
        return $this->hasMany(VoteResponse::class);
    }
} 