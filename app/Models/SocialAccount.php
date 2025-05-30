<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'account_name',
        'access_token',
        'account_details'
    ];

    protected $casts = [
        'account_details' => 'json'
    ];

    protected $hidden = [
        'access_token'
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }


    /**
     * Get all posts for this social account
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
