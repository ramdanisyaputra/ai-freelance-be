<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'user_id',
        'brief',
        'user_brief',
        'language',
        'summary',
        'scope',
        'duration_days',
        'price',
        'currency',
        'content',
        'status',
    ];

    protected $casts = [
        'scope' => 'array',
        'price' => 'integer',
        'duration_days' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
