<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $images = TaskImage::with('task')
            ->where('user_id', '=', $request->user()->id, 'and')
            ->get();

        return response()->json($images, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'images' => 'required|array|min:1',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::with('project')->find($request->input('task_id'), ['*']);

        if (! $task || $task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $directory = 'projects/project-' . $task->project_id . '/tasks/task-' . $task->id;

        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $created = [];

        foreach ($request->file('images') as $file) {
            $fileName = uniqid('task_', true) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $fileName, 'public');

            $created[] = TaskImage::create([
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'image_path' => $path,
                'file_name' => $fileName,
                'file_type' => $file->getMimeType() ?? 'application/octet-stream',
            ]);
        }

        return response()->json($created, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, TaskImage $taskImage)
    {
        if ($taskImage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($request->boolean('view')) {
            if (! Storage::disk('public')->exists($taskImage->image_path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $absolutePath = Storage::disk('public')->path($taskImage->image_path);

            return response()->file($absolutePath, [
                'Content-Type' => $taskImage->file_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . ($taskImage->file_name ?: basename($taskImage->image_path)) . '"',
            ]);
        }

        return response()->json($taskImage, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskImage $taskImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskImage $taskImage)
    {
        if ($taskImage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'task_id' => 'sometimes|required|exists:tasks,id',
            'image' => 'sometimes|required|file|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('task_id')) {
            $task = Task::find($request->input('task_id'), ['*']);

            if (! $task || $task->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $taskImage->task_id = $task->id;
        }

        if ($request->hasFile('image')) {
            $task = Task::find($taskImage->task_id, ['*']);

            if (! $task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $directory = 'projects/project-' . $task->project_id . '/tasks/task-' . $task->id;

            if (! Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            if (Storage::disk('public')->exists($taskImage->image_path)) {
                Storage::disk('public')->delete($taskImage->image_path);
            }

            $file = $request->file('image');
            $fileName = uniqid('task_', true) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $fileName, 'public');

            $taskImage->image_path = $path;
            $taskImage->file_name = $fileName;
            $taskImage->file_type = $file->getMimeType() ?? 'application/octet-stream';
        }

        $taskImage->save();

        return response()->json($taskImage, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TaskImage $taskImage)
    {
        if ($taskImage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (Storage::disk('public')->exists($taskImage->image_path)) {
            Storage::disk('public')->delete($taskImage->image_path);
        }

        $taskImage->delete($taskImage->id);

        return response()->json(['message' => 'Task image deleted successfully'], 200);
    }
}
