<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Asset;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function createTicket(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'required|integer',
            'priority_id' => 'required|integer',
            'title' => 'required|string',
            'description' => 'required|string',
            'ticket_category_id' => 'required|integer',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $asset = Asset::find($request->asset_id);

        if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
        if ($asset->owner_id != $request->user()->id) return response()->json(['message' => 'Hanya pemilik asset yang dapat membuat tiket'], 403);

        $ticket = Ticket::create($request->except('images'));

        if ($request->hasFile('images')) {
            $imagesPath = public_path('tickets-images');
            foreach ($request->file('images') as $image) {
                $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $image->move($imagesPath, $imageName);
                $ticket->images()->create([
                    'path' => 'tickets-images/' . $imageName
                ]);
            }
        }

        return response()->json(['message' => 'Tiket berhasil dibuat'], 200);
    }

    public function getAllTickets(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        $spvAccess = auth()->user()->role->asset_approval;

        if (!$access) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ticketsQuery = Ticket::where('ticket_category_id', 1)
            ->orderBy('priority_id', 'asc')
            ->orderBy('created_at', 'desc');

        if ($spvAccess) {
            $ticketsQuery = Ticket::orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc');
        }

        $tickets = $ticketsQuery->paginate(10);

        return TicketResource::collection($tickets);
    }

    public function getMyTickets(Request $request)
    {
        $user = auth()->user();
        $assets = $user->assets->pluck('id')->flatten();
        $tickets = Ticket::whereIn('asset_id', $assets)
            ->orderBy('priority_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
        if ($tickets->isEmpty()) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }
    
        return TicketResource::collection($tickets);
    }
}

