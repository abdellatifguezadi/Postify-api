<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class)
            ->withTimestamps();
    }
} 