<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['name', 'bio', 'avatar'];


    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_profiles')
            ->withTimestamps();
    }

    public function columns()
    {
        return $this->hasMany(TaskColumn::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }
}
