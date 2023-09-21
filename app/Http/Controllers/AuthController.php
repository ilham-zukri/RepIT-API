<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserByDeptResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
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

        $user = User::where('user_name', strtolower($request->user_name))->first();

        //Check password hash
        if (!$user || !Hash::check($request->password, $user->password) || !$user->active) {
            return response()->json(['message' => 'Credentials does not match.'], 401);
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
        $existed = User::where('user_name', $request->user_name)->first();
        if (!$access) return response()->json(['message' => 'forbidden'], 403);
        if ($existed) return response()->json(['message' => 'username tidak tersedia'], 409);

        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|min:8',
            'branch_id' => 'required|integer',
            'department' => 'required|string',
        ]);

        $_request  = $request->all();
        $_request['password'] = Hash::make($request->password); 
        $_request['user_name'] = strtolower($request->user_name);

        User::create($_request);

        return response()->json(['message' => 'user created'], 201);
    }

    public function changeUname(Request $request) {
        $user = User::whereId(auth()->user()->id)->firstOrFail();

        $request->validate([
            'user_name' => 'required|string'
        ]);

        $existed = User::whereUserName($request->user_name)->first();
        if($existed) return response()->json(['message' => 'username sudah digunakan'], 409); 

        $user->update([
            'user_name' => strtolower($request->user_name)
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }

    public function changeEmail(Request $request){
        $user = User::whereId(auth()->user()->id)->firstOrFail();
        $request->validate([
            'email' => 'required|email'
        ]);

        $existed = User::whereEmail($request->email)->first();
        if($existed) return response()->json(['message' => 'email sudah digunakan'], 409);

        $user->update([
            'email' => strtolower($request->email)
        ]);

        return response()->json(['message' => 'berhasil'], 200);
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

    public function getUsersByDep() : JsonResponse | AnonymousResourceCollection{
        $access = (auth()->user()->role->asset_request);
        if(!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        $users = User::whereDepartment(auth()->user()->department)->get();

        return UserByDeptResource::collection($users);
    }
}
