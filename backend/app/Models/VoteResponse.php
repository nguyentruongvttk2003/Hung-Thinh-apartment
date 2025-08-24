<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'vote_option_id',
        'user_id',
        'comment',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function option()
    {
        return $this->belongsTo(VoteOption::class, 'vote_option_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 