<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contribution;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContributionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $contribution = Contribution::create([
            'user_id' => $request->user()->id, // Assuming authenticated users
            'amount' => $validated['amount'],
        ]);

        return response()->json([
            'message' => 'Contribution successful',
            'contribution' => $contribution,
        ], 201);
    }
}
