<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $images = ProjectImage::with('project')
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
            'project_id' => 'required|exists:projects,id',
            'images' => 'required|array|min:1',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project = Project::find($request->input('project_id'), ['*']);

        if (! $project || $project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $directory = 'projects/project-' . $project->id;

        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $created = [];

        foreach ($request->file('images') as $file) {
            $fileName = uniqid('project_', true) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $fileName, 'public');

            $created[] = ProjectImage::create([
                'project_id' => $project->id,
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
    public function show(Request $request, ProjectImage $projectImage)
    {
        if ($projectImage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($request->boolean('view')) {
            if (! Storage::disk('public')->exists($projectImage->image_path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $absolutePath = Storage::disk('public')->path($projectImage->image_path);

            return response()->file($absolutePath, [
                'Content-Type' => $projectImage->file_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . ($projectImage->file_name ?: basename($projectImage->image_path)) . '"',
            ]);
        }

        return response()->json($projectImage, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectImage $projectImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectImage $projectImage)
    {
        if ($projectImage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'sometimes|required|exists:projects,id',
            'image' => 'sometimes|required|file|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('project_id')) {
            $project = Project::find($request->input('project_id'), ['*']);

            if (! $project || $project->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $projectImage->project_id = $project->id;
        }

        if ($request->hasFile('image')) {
            $directory = 'projects/project-' . $projectImage->project_id;

            if (! Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            if (Storage::disk('public')->exists($projectImage->image_path)) {
                Storage::disk('public')->delete($projectImage->image_path);
            }

            $file = $request->file('image');
            $fileName = uniqid('project_', true) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $fileName, 'public');

            $projectImage->image_path = $path;
            $projectImage->file_name = $fileName;
            $projectImage->file_type = $file->getMimeType() ?? 'application/octet-stream';
        }

        $projectImage->save();

        return response()->json($projectImage, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ProjectImage $projectImage)
    {
        if ($projectImage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (Storage::disk('public')->exists($projectImage->image_path)) {
            Storage::disk('public')->delete($projectImage->image_path);
        }

        $projectImage->delete($projectImage->id);

        return response()->json(['message' => 'Project image deleted successfully'], 200);
    }
}
