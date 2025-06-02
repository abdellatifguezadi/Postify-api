<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TaskColumn extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public static function rules(Profile $profile, $taskColumnId = null)
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('task_columns')->where(function ($query) use ($profile) {
                    return $query->where('profile_id', $profile->id);
                })->ignore($taskColumnId),
            ],
        ];
    }

    /**
     * Get the tasks for the task column.
     */

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function profiles()
    {
        return $this->belongsTo(Profile::class);
    }
}
