<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function addUser(Request $request)
    {
        $access = auth()->user()->role->user_management;
        $existed = User::where('user_name', $request->user_name);
        if (!$access) return response()->json(['message' => 'unauthorized'], 401);
        if ($existed) return response()->json(['message' => 'username tidak tersedia'], 409);

        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|min:8',
            'branch_id' => 'required|integer',
            'department' => 'required|string',
        ]);

        $user = User::create([
            'user_name' => $request->user_name,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id ?? 3, //default value is 3, which is User
            'branch_id' => $request->branch_id,
            'department' => $request->department,
        ]);

        return response()->json(['message' => 'user created'], 201);
    }

    public function getCurrentUser(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        return new UserResource($user);
    }

    public function getUsers()
    {

        $currentUser = Auth::user();
        $users = User::with('role:id,role_name,asset_request,asset_approval,knowledge_base,user_management')->get();
        return ($currentUser->role->user_management) ? ['users' => UserResource::collection($users)] : response()->json(['message' => 'unauthorized'], 401);
    }
}
