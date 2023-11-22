<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function getDepartmentsForList(){
        $access = auth()->user()->role->user_management;
        if (!$access) return response()->json(['message' => 'forbidden'], 403);

        $departments = Department::all();

        return response()->json($departments, 200);
    }
}
