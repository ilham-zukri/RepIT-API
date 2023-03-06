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
        dd($user);
        
        if (Hash::check($request->password, $user->password)) {
            dd('oke');
        }

        if (!$user || !Hash::check('rahasia', $user->password)) {
            throw ValidationException::withMessages(
                [
                    'message' => 'The provided credentials are incorrect'
                ]
            );
        }


        // return $user->createToken('user login')->plainTextToken;
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken->delete();

        return "Berhasil Logout";
    }

    public function getCurrentUser(Request $request)
    {
        return response()->json(['current_user' => auth()->user()]);
    }
}
