<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('users')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($tasks);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_column_id' => 'required|exists:task_columns,id',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return response()->json($task->load('users', 'taskColumn'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json('Deleted task successfully ', 204);
    }

    public function changeStatus(Request $request, Task $task)
    {
        $request->validate([
            'task_column_id' => 'required|exists:task_columns,id',
        ]);

        $task->task_column_id = $request->task_column_id;
        $task->save();

        return response()->json($task->load('taskColumn'));
    }

    public function assignToUsers(Request $request, Task $task)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'required|exists:users,id',
        ]);

        $users = (array)$request->users;

        $task->users()->syncWithoutDetaching($users);

        // $task->users()->attach($request->user_id);

        return response()->json($task->load('users'));
    }

    public function unassignFromUsers(Request $request, Task $task)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'required|exists:users,id',
        ]);

        $task->users()->detach($request->users);

        return response()->json($task->load('users'));
    }

    public function getUsers(Task $task)
    {
        return response()->json($task->users);
    }

    public function getTaskColumn(Task $task)
    {
        return response()->json($task->taskColumn);
    }
}
