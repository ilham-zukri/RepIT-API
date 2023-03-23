<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUsers()
    {
        $currentUser = Auth::user();
        $users = User::with('role:id,role_name,asset_request,asset_approval,knowledge_base,user_management')->get();
        return ($currentUser->role->user_management) ? ['users' => UserResource::collection($users)] : response()->json(['message' => 'unauthorized'], 401);
    }
}
