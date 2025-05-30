<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskColumn extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the tasks for the task column.
     */

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
