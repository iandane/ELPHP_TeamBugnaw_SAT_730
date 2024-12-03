<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function update(Request $request, $token)
    {
        \Log::info('Update method called with token: ' . $token);

        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $user = $accessToken->tokenable;
        \Log::info('User found: ' . $user->email);

        if ($request->has('first_name')) {
            $user->first_name = $request->first_name;
        }
        if ($request->has('last_name')) {
            $user->last_name = $request->last_name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        \Log::info('User updated: ' . $user->email);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        // Log the user ID for debugging purposes
        \Log::info('Destroy method called with user ID: ' . $id);

        $user = User::find($id);
        if ($user) {
            $user->delete();
            \Log::info('User deleted: ' . $id);
            return response()->json(['message' => 'User deleted successfully'], 200);
        } else {
            \Log::error('User not found: ' . $id);
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
