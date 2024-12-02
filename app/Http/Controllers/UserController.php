<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function update(Request $request)
        {
            // Validate input data
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            // Get authenticated user
            $user = Auth::user();

            // Update user details
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Save updates
            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        }


}
