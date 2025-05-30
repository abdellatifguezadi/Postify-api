<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $taskColumn = TaskColumn::create($request->all());

        return response()->json($taskColumn, 201);
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
    public function update(Request $request, TaskColumn $taskColumn)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $taskColumn->update($request->all());

        return response()->json($taskColumn);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskColumn $taskColumn)
    {
        $taskColumn->delete();

        return response()->json(null, 204);
    }
}
