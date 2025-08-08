<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'option_text',
        'description',
        'vote_count',
        'sort_order',
    ];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function responses()
    {
        return $this->hasMany(VoteResponse::class);
    }
} 