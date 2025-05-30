<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'user_id'];
    protected $casts = [
        'due_date' => 'datetime',
    ];
    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsToMany(User::class);
    }
    /**
     * Get the task column that the task belongs to.
     */
    public function taskColumn()
    {
        return $this->belongsTo(TaskColumn::class);
    }
}
