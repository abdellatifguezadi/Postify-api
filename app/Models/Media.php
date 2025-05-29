<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'post_id',
        'type',
        'path'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Accesseur pour obtenir l'URL complète du média
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    // Mutateur pour le type de média
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtolower($value);
    }

    protected $appends = ['url'];
} 