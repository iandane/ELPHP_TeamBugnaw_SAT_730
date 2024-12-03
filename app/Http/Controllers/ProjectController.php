<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    // Log incoming request data for debugging
    Log::info('Request Data:', $request->all());

    // Validate the input data
    try {
        $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'money_goal' => 'required|numeric',
        'deadline' => 'required|date_format:d/m/Y',
    ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation failed: ' . json_encode($e->errors()));
        return response()->json(['errors' => $e->errors()], 422);
    }

    // Date formatting and saving the project
    $project = new Project;
    $project->title = $validatedData['title'];
    $project->description = $validatedData['description'];
    $project->money_goal = $validatedData['money_goal'];
    $project->deadline = Carbon::createFromFormat('d/m/Y', $validatedData['deadline'])->format('Y-m-d');
    
    // Log project info before saving
    Log::info('Saving project', $project->toArray());

    // Handle image upload if exists
    $imagePath = null;
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        // Store image and log path
        $imagePath = $request->file('image')->store('images', 'public');
        Log::info('Image stored at: ' . $imagePath);
    }

    // Assign the image path and user_id
    $project->image = $imagePath;
    $project->user_id = Auth::id(); // Associate with the authenticated user

    // Wrap the saving operation in a transaction
    DB::beginTransaction();
    try {
        $project->save();
        DB::commit();  // Commit the transaction if save is successful
        Log::info('Project saved successfully', $project->toArray());
        
        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction on failure
        Log::error('Error saving project: ' . $e->getMessage());
        
        return response()->json([
            'message' => 'Error saving project',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Get all projects of the authenticated user
    public function index()
    {
        $projects = Auth::user()->projects; // Get all projects for the authenticated user

        return response()->json([
            'projects' => $projects
        ]);
    }

    public function getTitleAndImage()
	{
		// Get all projects for the authenticated user and only select the title and image fields
		$projects = Auth::user()->projects->map(function ($project) {
			// Only select the relevant fields (id, title, image)
			$projectData = $project->only(['id', 'title', 'image']);
			
			// Prepend the base URL to the image path if it exists
			if ($projectData['image']) {
				$projectData['image'] = asset('storage/' . $projectData['image']); // Generate the full image URL
			}

			return $projectData;
		});

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
        Log::info('Delete request received for project ID: ' . $id);
        Log::info('Authenticated user ID: ' . Auth::id());
        
        DB::beginTransaction();
        try {
            $project = Project::where('user_id', Auth::id())->find($id); 
            if (!$project) {
                Log::error('Project not found for user: ' . Auth::id() . ' with project ID: ' . $id);
                return response()->json([
                    'message' => 'Project not found'
                ], 404);
            } else {
                Log::info('Project found: ' . $project->id);
            }
    
            if ($project->image) {
                $imagePath = 'public/' . $project->image;
                if (Storage::exists($imagePath)) {
                    Storage::delete($imagePath);
                    Log::info('Deleted image at: ' . $imagePath);
                } else {
                    Log::warning('Image not found in storage at: ' . $imagePath);
                }
            }
    
            $project->delete();
            DB::commit();
    
            return response()->json([
                'message' => 'Project and associated image deleted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting project: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function formatDateExample()
    {
    $date = '02/12/2024';  // Example date in D/M/YYYY format
    $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
    
    // You can then use $formattedDate, for example, for saving to the database or displaying
    dd($formattedDate); // For debugging, will output '2024-12-02'
    }

}