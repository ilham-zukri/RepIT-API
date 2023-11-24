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
        $request->validate([
            "user_id" => 'required|string',
            'user_name' => 'required|string'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $existed = User::whereUserName($request->user_name)->first();
        if ($existed) return response()->json(['message' => 'username sudah digunakan'], 409);

        $user->update([
            'user_name' => strtolower($request->user_name)
        ]);

        return response()->json(['message' => 'Berhasil Mengganti Username'], 200);
    }

    public function changeEmail(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'email' => 'required|email'
        ]);
        $user = User::whereId($request->user_id)->firstOrFail();

        $existed = User::whereEmail($request->email)->first();
        if ($existed) return response()->json(['message' => 'email sudah digunakan'], 409);

        $user->update([
            'email' => strtolower($request->email)
        ]);

        return response()->json(['message' => 'Berhail Mengganti Email'], 200);
    }

    public function changePassword(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
            'old_password' => 'required|string|min:8',
            'password' => 'required|string|min:8'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        if (!Hash::check($request->old_password, $user->password)) return response()->json(['message' => 'password lama tidak sesuai'], 400);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Berhasil Mengganti Password'], 200);
    }

    public function resetPassword(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Berhasil Reset Password'], 200);
    }

    public function changeFullName(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'full_name' => 'required|string'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update([
            'full_name' => $request->full_name,
        ]);

        return response()->json(['message' => 'Berhasil Mengganti Nama Lengkap'], 200);
    }

    public function changeRole(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
            'role_id' => 'required|integer'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update([
            'role_id' => $request->role_id
        ]);

        return response()->json(['message' => 'Berhasil Mengganti Password'], 200);
    }


    public function changeDepartment(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
            'department_id' => 'required|integer'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update([
            'department_id' => $request->department_id
        ]);

        return response()->json(['message' => 'Berhasil Mengganti Department'], 200);
    }

    public function changeBranch(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
            'branch_id' => 'required|integer'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update([
            'branch_id' => $request->branch_id
        ]);

        return response()->json(['message' => 'Berhasil Pindah Cabang'], 200);
    }

    public function setActive(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
            'active' => 'required|boolean'
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update([
            'active' => $request->active
        ]);

        if ($user->active) return response()->json(['message' => 'User berhasil diaktifkan'], 200);

        $user->tokens()->delete();
        return response()->json(['message' => 'User berhasil dinonaktifkan'], 200);
    }

    public function editUser(Request $request)
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $request->validate([
            'user_id' => 'required|string',
        ]);

        $user = User::whereId($request->user_id)->firstOrFail();

        $user->update($request->all());

        return response()->json(['message' => 'Berhasil Mengedit User'], 200);
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
            $user = User::where('user_name', 'LIKE', "%{$query}%")
                ->orderBy('active', 'desc')
                ->paginate(10);
        } else {
            $user = User::orderBy('active', 'desc')->paginate(10);
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

    public function getRolesForList()
    {
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $roles = Role::select('id', 'role_name')->get();

        return response()->json($roles, 200);
    }
}
