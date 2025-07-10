<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    protected $fillable = [
        'profile_id',
        'platform',
        'account_name',
        'access_token',
        'refresh_token',
        'expires_at',
        'social_id',
        'avatar',
        'email'
    ];

    protected $hidden = [
        'access_token',
        'refresh_token'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }


    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
