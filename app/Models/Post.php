<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content',
        'profile_id',
        'social_account_id',
        'status',
        'scheduled_time',
        'published_at'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'published_at' => 'datetime',
        'status' => 'string'
    ];

    protected $with = ['medias', 'socialAccount', 'tags'];


    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }


    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }


    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }


    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }


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
        return $query->whereBetween('scheduled_time', [$start, $end]);
    }
} 