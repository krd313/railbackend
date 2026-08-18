<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(
            Project::where('user_id', '=', $request->user()->id, 'and')
                ->orderBy('name', 'asc')
                ->get(),
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,on_hold,completed,cancelled',
            'priority' => 'nullable|numeric',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:51200',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:51200',
            'share_with' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(

                )], 422);
        }

        $validated = $validator->validated();
        unset($validated['image'], $validated['images']);

        $project = Project::create(array_merge(
            $validated,
            ['user_id' => $request->user()->id]
        ));

        $files = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        }

        if (!empty($files)) {
            $directory = 'projects/project-' . $project->id;

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            foreach ($files as $file) {
                $fileName = uniqid('project_', true) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($directory, $fileName, 'public');

                ProjectImage::create([
                    'project_id' => $project->id,
                    'user_id' => $request->user()->id,
                    'image_path' => $path,
                    'file_name' => $fileName,
                    'file_type' => $file->getMimeType() ?? 'application/octet-stream',
                ]);
            }
        }

        return response()->json($project, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $project = Project::find($id, ['*']);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json($project, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::find($id, ['*']);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,on_hold,completed,cancelled',
            'priority' => 'nullable|numeric',
            'image' => 'nullable|string',
            'share_with' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(

                )], 422);
        }
            $project->name = $request->input('name', $project->name);
            $project->description = $request->input('description', $project->description);
            $project->notes = $request->input('notes', $project->notes);
            $project->due_date = $request->input('due_date', $project->due_date);
            $project->status = $request->input('status', $project->status);
            $project->priority = $request->input('priority', $project->priority);
            
            $project->share_with = $request->input('share_with', $project->share_with);

        $project->save();

        return response()->json($project, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $project = Project::with('projectImages')->find($id, ['*']);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        foreach ($project->projectImages as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        if ($project->image && Storage::disk('public')->exists($project->image)) {
            Storage::disk('public')->delete($project->image);
        }

        $projectDirectory = 'projects/project-' . $project->id;
        if (Storage::disk('public')->exists($projectDirectory)) {
            Storage::disk('public')->deleteDirectory($projectDirectory);
        }

        $project->delete($id);
        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
}
