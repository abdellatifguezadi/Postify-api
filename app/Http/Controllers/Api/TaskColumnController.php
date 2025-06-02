<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Task;
use App\Models\TaskColumn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;

class TaskColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taskColumns = TaskColumn::with('tasks')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($taskColumns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Profile $profile)
    {
        $validated = $request->validate(TaskColumn::rules($profile));
        $profile->columns()->create($validated);

        return response()->json($profile->load('columns'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskColumn $taskColumn)
    {
        return response()->json($taskColumn->load('tasks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile, TaskColumn $taskColumn)
    {
        $validated = $request->validate(TaskColumn::rules($profile, $taskColumn->id));

        $profile->columns()->where('id', $taskColumn->id)->update($validated);
        $taskColumn->refresh(); // Refresh the model to get the updated data ???

        return response()->json($profile->load('columns'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile, TaskColumn $taskColumn)
    {
        $profile->columns()->where('id', $taskColumn->id)->delete();
        return response()->json(null, 204);
    }
}
