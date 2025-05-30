<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable = ['name'];



    /**
     * Get the users for the team.
     */

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_users')
            ->withTimestamps();
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
