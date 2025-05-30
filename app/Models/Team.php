<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name'];



    /**
     * Get the users for the team.
     */

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_users')
            ->withTimestamps();
    }
}
