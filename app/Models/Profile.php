<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'avatar',
        'description',
        'team_id',
        'color',
    ];


    public function team()
    {
        return $this->belongsTo(Team::class)
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
