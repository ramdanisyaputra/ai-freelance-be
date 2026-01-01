<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreelancerProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array\u003cint, string\u003e
     */
    protected $fillable = [
        'user_id',
        'stack',
        'rate_type',
        'min_price',
        'currency',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array\u003cstring, string\u003e
     */
    protected function casts(): array
    {
        return [
            'stack' => 'array',
            'min_price' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the freelancer profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
