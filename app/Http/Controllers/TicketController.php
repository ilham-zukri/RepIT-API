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

    public function handleTicket(Request $request)
    {
        $access = auth()->user()->role->asset_management || auth()->user()->role->asset_approval;
        if (!$access) return response()->json(['message' => 'Forbidden'], 403);

        $request->validate([
            'ticket_id' => 'required|integer',
        ]);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) return response()->json(['message' => 'Tiket tidak ditemukan'], 404);

        $ticket->update([
            'handler_id' => auth()->user()->id,
            'status_id' => 2,
            'responded_at' => now()
        ]);

        return response()->json(['message' => 'Berhasil ambil tiket'], 200);
    }

    public function progressTicket(Request $request)
    {
        $user = auth()->user();
        $access = $user->role->asset_management || $user->role->asset_approval;
        if (!$access) return response()->json(['message' => 'Forbidden'], 403);
        $request->validate([
            'ticket_id' => 'required|integer',
        ]);
        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        if ($ticket->handler_id != $user->id) return response()->json(['message' => 'Forbidden'], 403);

        $ticket->update([
            'status_id' => 3
        ]);

        return response()->json(['message' => 'Berhasil'], 200);
    }

    public function holdTicket(Request $request)
    {
        $access = auth()->user()->role->asset_management || auth()->user()->role->asset_approval;
        if (!$access) return response()->json(['message' => 'Forbidden'], 403);

        $request->validate([
            'ticket_id' => 'required|integer',
            'handler_note' => 'required|string',
        ]);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) return response()->json(['message' => 'Tiket tidak ditemukan'], 404);

        $ticket->update([
            'status_id' => 6,
        ]);

        $ticket->note()->create([
            'handler_note' => $request->handler_note
        ]);

        return response()->json(['message' => 'Berhasil mengundur penyelasaian tiket'], 200);
    }

    public function ToBeReviewedTicket(Request $request)
    {
        $access = auth()->user()->role->asset_management || auth()->user()->role->asset_approval;
        if (!$access) return response()->json(['message' => 'Forbidden'], 403);

        $request->validate([
            'ticket_id' => 'required|integer',
            'resolution_note' => 'required|string',
        ]);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) return response()->json(['message' => 'Tiket tidak ditemukan'], 404);

        $hasNote = $ticket->note()->exists();

        if ($hasNote) {
            $ticket->note()->update([
                'resolution_note' => $request->resolution_note
            ]);
        } else {
            $ticket->note()->create([
                'resolution_note' => $request->resolution_note
            ]);
        }
        
        $ticket->update([
            'status_id' => 4,
        ]);

        return response()->json(['message' => 'Tiket berhasil ditindak lanjut'], 200);
    }

    public function closeTicket(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ticket_id' => 'required|integer',
        ]);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) return response()->json(['message' => 'Tiket tidak ditemukan'], 404);

        $asset = $ticket->asset;
        if ($asset->owner_id != $user->id) return response()->json(['message' => 'Forbidden'], 403);

        $ticket->update([
            'status_id' => 5,
        ]);

        return response()->json(['message' => 'Tiket berhasil ditutup'], 200);
    }
}
