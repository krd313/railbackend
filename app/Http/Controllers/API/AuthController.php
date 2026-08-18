<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registration API

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'User registered successfully', 'user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }



    public function changePassword(Request $request)
    {
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);
    if (!Hash::check($request->current_password, $request->user()->password)) {
        return response()->json(['message' => 'Current password is incorrect.'], 422);
    }
    $request->user()->update(['password' => Hash::make($request->password)]);
    return response()->json(['message' => 'Password changed successfully.']);
    }

    public function logout(Request $request)
    {

    $request->user()->currentAccessToken()->delete();


        return response()->json(['message' => 'User logged out successfully'], 200);
    }
}
