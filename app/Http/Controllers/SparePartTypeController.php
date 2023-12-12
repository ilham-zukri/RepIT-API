<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SparePartType;

class SparePartTypeController extends Controller
{
    public function getTypes(){
        $types = SparePartType::all();
        return response()->json($types, 200);
    }


    public function getAvailableTypes(){
        $types = SparePartType::whereHas('spareParts', function ($query) {
            $query->where('status_id', 1);
        })->get();
        return response()->json($types, 200);
    }
}
