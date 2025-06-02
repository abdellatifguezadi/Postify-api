<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'logo', 'slug'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withTimestamps();
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
