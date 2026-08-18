<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TaskNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUserId = $request->user()->id;

        $taskNotes = TaskNote::with('task')
            ->whereHas('task', function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId);
            })
            ->when($request->filled('task_id'), function ($query) use ($request) {
                $query->where('task_id', $request->integer('task_id'));
            })
            ->get();
        return response()->json($taskNotes, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
   {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authUserId = $request->user()?->id;
        if (! $authUserId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,in_progress,on_hold,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(

                )], 400);
        }

        $validated = $validator->validated();
        $validated['note'] = $validated['note'] ?? $validated['notes'] ?? null;
        unset($validated['notes']);

        if ($validated['note'] === null || trim($validated['note']) === '') {
            return response()->json([
                'errors' => ['note' => ['The note field is required.']],
            ], 400);
        }

        $taskNote = TaskNote::create(array_merge($validated, ['user_id' => $authUserId]));

        return response()->json($taskNote, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskNote $tasknote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskNote $tasknote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskNote $tasknote)
    {
        $authUserId = $request->user()?->id;
        if (! $authUserId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'task_id' => 'sometimes|required|exists:tasks,id',
            'date' => 'sometimes|required|date',
            'note' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,in_progress,on_hold,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(

                )], 400);
        }

        $validated = $validator->validated();

        if (array_key_exists('note', $validated) || array_key_exists('notes', $validated)) {
            $validated['note'] = $validated['note'] ?? $validated['notes'];
        }

        unset($validated['notes']);

        if (!array_key_exists('task_id', $validated)) {
            $validated['task_id'] = $tasknote->task_id;
        }

        $validated['user_id'] = $authUserId;

        $tasknote->fill($validated);
        $tasknote->save();
        $tasknote->refresh();

        return response()->json($tasknote, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TaskNote $tasknote)
    {
        $noteId = $tasknote->id;
        $tasknote->delete($noteId);

        return response()->json([
            'message' => 'Task note deleted successfully',
            'deleted_id' => $noteId,
            'deleted_count' => 1,
        ], 200);
    }
}
