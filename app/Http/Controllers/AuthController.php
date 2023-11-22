<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserByDeptResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
            'department_id' => 'required|integer',
        ]);

        $_request  = $request->all();
        $_request['password'] = Hash::make($request->password);
        $_request['user_name'] = strtolower($request->user_name);

        User::create($_request);

        return response()->json(['message' => 'User Terbuat'], 201);
    }

    public function changeUname(Request $request)
    {
        $user = User::whereId(auth()->user()->id)->firstOrFail();

        $request->validate([
            'user_name' => 'required|string'
        ]);

        $existed = User::whereUserName($request->user_name)->first();
        if ($existed) return response()->json(['message' => 'username sudah digunakan'], 409);

        $user->update([
            'user_name' => strtolower($request->user_name)
        ]);

        return response()->json(['message' => 'berhasil'], 200);
    }

    public function changeEmail(Request $request)
    {
        $user = User::whereId(auth()->user()->id)->firstOrFail();
        $request->validate([
            'email' => 'required|email'
        ]);

        $existed = User::whereEmail($request->email)->first();
        if ($existed) return response()->json(['message' => 'email sudah digunakan'], 409);

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

    public function getUsers(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);
        
        $query = $request->query('user_name');
        if ($query) {
            $user = User::where('user_name', 'LIKE', "%{$query}%")->paginate(10);
        } else {
            $user = User::paginate(10);
        }
        return UserResource::collection($user);
    }

    public function getUsersByDep(): JsonResponse | AnonymousResourceCollection
    {
        $access = (auth()->user()->role->asset_request);
        if (!$access) return response()->json(['message' => 'tidak berwenang'], 403);

        $users = User::where('active', 1)
            ->where('branch_id', auth()->user()->branch_id)
            ->whereDepartmentId(auth()->user()->department_id)
            ->select('id', 'user_name')
            ->get();

        $loggedIn = User::whereId(auth()->user()->id)
            ->select('id', 'user_name')
            ->first();

        $users = $users->filter(function ($user) use ($loggedIn) {
            return $user->id !== $loggedIn->id;
        });

        $users->prepend($loggedIn);

        return response()->json(['data' => $users], 200);
    }

    public function getRole()
    {
        $role = auth()->user()->role;
        return response()->json(['data' => $role], 200);
    }

    public function getRolesForList(){
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $roles = Role::select('id', 'role_name')->get();

        return response()->json($roles, 200);
    }
}
