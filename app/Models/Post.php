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
        'user_id',
        'social_account_id',
        'status',
        'scheduled_date',
        'scheduled_time',
        'published_at'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'published_at' => 'datetime'
    ];

    protected $with = ['medias', 'socialAccount', 'tags'];

    /**
     * Get the user that owns the post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the social account this post belongs to
     */
    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    /**
     * Get the media files for this post
     */
    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get the tags associated with the post
     */
    public function tags(): BelongsToMany
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
} 