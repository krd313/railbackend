<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['project', 'taskNotes'])
            ->where('user_id', $request->user()->id)
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($tasks, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
   {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'note' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,in_progress,on_hold,completed,cancelled',
            'priority' => 'nullable|numeric',
            'image' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(

                )], 400);
        }

        $validated = $validator->validated();
        $note = $validated['note'] ?? $validated['notes'] ?? null;
        unset($validated['note']);
        unset($validated['notes']);

        $task = Task::create(array_merge($validated, ['user_id' => $request->user()->id]));

        if ($note !== null && trim($note) !== '') {
            TaskNote::create([
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'date' => now()->toDateString(),
                'note' => $note,
                'status' => $task->status,
            ]);
        }

        return response()->json($task->load('taskNotes'), 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $task = Task::with(['project', 'taskNotes'])->find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json($task, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::with('project')->find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
            $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'reference' => 'nullable|string|max:255',
            'name'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'note' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,in_progress,on_hold,completed,cancelled',
            'priority' => 'nullable|numeric',
            'image' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(

                )], 400);
        }

        $validated = $validator->validated();
        $note = $validated['note'] ?? $validated['notes'] ?? null;
        unset($validated['note']);
        unset($validated['notes']);

        $task->fill($validated);
        $task->save();

        if ($note !== null && trim($note) !== '') {
            $taskNote = TaskNote::query()
                ->where('task_id', '=', $task->id, 'and')
                ->latest('id')
                ->first();

            if ($taskNote) {
                $taskNote->update([
                    'note' => $note,
                    'date' => now()->toDateString(),
                    'status' => $task->status,
                ]);
            } else {
                TaskNote::create([
                    'task_id' => $task->id,
                    'user_id' => $request->user()->id,
                    'date' => now()->toDateString(),
                    'note' => $note,
                    'status' => $task->status,
                ]);
            }
        }


        return response()->json($task->load('taskNotes'), 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $task = Task::with('images')->find($id);

        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        foreach ($task->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        if ($task->image && Storage::disk('public')->exists($task->image)) {
            Storage::disk('public')->delete($task->image);
        }

        $task->delete($task->id);

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
