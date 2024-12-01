<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;


class ProjectController extends Controller
{
    // Constructor to ensure the user is authenticated for project-related actions
    public function _construct()
    {
        $this->middleware('auth:sanctum'); // Ensure user is authenticated
    }

    // Store a new project (Create)
    public function store(Request $request)
    {
        // Validate the input data
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'money_goal' => 'required|numeric',
            'deadline' => 'required|date',
        ]);

        // Handle image upload if exists
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        // Create the project and associate it with the logged-in user
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'money_goal' => $request->money_goal,
            'deadline' => $request->deadline,
            'user_id' => Auth::id(), // Associate with the logged-in user
        ]);

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], 201);
    }

    // Get all projects of the authenticated user
    public function index()
    {
        $projects = Auth::user()->projects; // Get all projects for the authenticated user

        return response()->json([
            'projects' => $projects
        ]);
    }

    // Show a specific project
    public function show($id)
    {
        $project = Project::where('user_id', Auth::id())->findOrFail($id); // Ensure the project belongs to the authenticated user

        return response()->json([
            'project' => $project
        ]);
    }

    // Update a project
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'money_goal' => 'required|numeric',
            'deadline' => 'required|date',
        ]);

        $project = Project::where('user_id', Auth::id())->findOrFail($id); // Ensure project belongs to authenticated user

        // Handle image upload if exists
        if ($request->hasFile('image')) {
            // If a new image is uploaded, delete the old one
            if ($project->image) {
                Storage::delete('public/' . $project->image);
            }

            $imagePath = $request->file('image')->store('images', 'public');
            $project->image = $imagePath;
        }

        // Update project details
        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'money_goal' => $request->money_goal,
            'deadline' => $request->deadline,
        ]);

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ]);
    }

    // Delete a project
    public function destroy($id)
    {
        $project = Project::where('user_id', Auth::id())->findOrFail($id); // Ensure project belongs to authenticated user

        // Delete the project image if it exists
        if ($project->image) {
            Storage::delete('public/' . $project->image);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully;'
        ]);
    }
}
