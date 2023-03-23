<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('user_name', $request->user_name)->first();

        //Check password hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credentials does not match.'], 401);
        }

        return $user->createToken('user login')->plainTextToken;
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        // return response()->json(200);
    }

    public function getCurrentUser(Request $request)
    {
        return response()->json(['current_user' => auth()->user()]);
    }
}