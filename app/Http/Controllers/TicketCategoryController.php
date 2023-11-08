<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketCategory;

class TicketCategoryController extends Controller
{
    public function getCategories() {
        $categories = TicketCategory::all();

        return response()->json($categories, 200);
    }
}
