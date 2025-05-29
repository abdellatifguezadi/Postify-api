<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
        'status',
        'scheduled_date',
        'scheduled_time',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_date' => 'date'
    ];

    protected $with = ['medias', 'socialAccounts', 'tags'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medias()
    {
        return $this->hasMany(Media::class);
    }

    public function socialAccounts()
    {
        return $this->belongsToMany(SocialAccount::class)
            ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    // Scopes utiles pour filtrer les posts
    public function scopeDrafts($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeQueued($query)
    {
        return $query->where('status', 'queue');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeScheduledBetween($query, $start, $end)
    {
        return $query->whereBetween('scheduled_date', [$start, $end]);
    }

    public function scopeWithTag($query, $tagName)
    {
        return $query->whereHas('tags', function($q) use ($tagName) {
            $q->where('name', $tagName);
        });
    }
} 