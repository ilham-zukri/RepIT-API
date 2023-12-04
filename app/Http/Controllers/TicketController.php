<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TicketResource;
use App\Models\User;
use App\Notifications\SendNotification;

class TicketController extends Controller
{
    public function createTicket(Request $request): JsonResponse
    {
        $request->validate([
            'priority_id' => 'required|integer',
            'title' => 'required|string',
            'description' => 'required|string',
            'ticket_category_id' => 'required|integer',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:6144'
        ]);

        if ($request->ticket_category_id == 1) {
            $request->validate([
                'asset_id' => 'required|integer',
            ]);

            $asset = Asset::find($request->asset_id);
            if (!$asset) return response()->json(['message' => 'Asset tidak ditemukan'], 404);
            $user = auth()->user();
            if ($asset->owner_id != $user->id) {
                return response()->json(['message' => 'Hanya pemilik asset yang diizinkan untuk membuat tiket'], 403);
            }
        }
        $ticketData = $request->except('images');
        $ticketData['created_by_id'] = auth()->user()->id;
        $ticket = Ticket::create($ticketData);

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

        $itsQ =User::where('role_id', 4)->orWhere('role_id',5);

        if($ticket->ticket_category_id == 2){
            $itsQ = User::where('role_id', 5);
        }

        $its = $itsQ->get();
        
        foreach ($its as $it) {
            if($it->fcm_token != null){
                $it->notify(new SendNotification(
                    'Tiket Baru',
                    'Tiket baru dibuat oleh ' . auth()->user()->full_name . ' dengan prioritas ' . $ticket->priority->priority,
                    'ticket'
                ));
            }
        }
        return response()->json(['message' => 'Tiket berhasil dibuat'], 201);
    }

    public function getAllTickets(Request $request)
    {
        $access = auth()->user()->role->asset_management;
        $spvAccess = auth()->user()->role->asset_approval;

        if (!$access) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ticketsQuery = Ticket::where('ticket_category_id', 1)
            ->orderBy('status_id', 'asc')
            ->orderBy('priority_id', 'asc')
            ->orderBy('created_at', 'desc');

        if ($spvAccess) {
            $ticketsQuery = Ticket::orderBy('status_id', 'asc')->orderBy('priority_id', 'asc')
                ->orderBy('created_at', 'desc');
        }

        $tickets = $ticketsQuery
            ->orderByRaw('CASE WHEN flag_id = 1 THEN 0 ELSE 1 END')
            ->paginate(10);

        return TicketResource::collection($tickets);
    }

    public function getMyTickets(Request $request)
    {
        $user = auth()->user();

        $tickets = Ticket::whereCreatedById($user->id)
            ->where('status_id', '!=', 5)
            ->orderBy('priority_id', 'asc')
            ->orderBy('status_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($tickets->isEmpty()) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        return TicketResource::collection($tickets);
    }

    public function getHandledTickets(Request $request)
    {
        $user = auth()->user();
        if (!$user->role->asset_management) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $tickets = Ticket::whereHandlerId($user->id)
            ->where('status_id', '!=', 5)
            ->orderBy('priority_id', 'asc')
            ->orderBy('status_id', 'asc')
            ->orderBy('created_at', 'desc');
        $tickets = $tickets->paginate(10);

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

        $handler = $ticket->handler_id;
        if ($handler) return response()->json(['message' => 'Tiket sudah memiliki Handler'], 403);

        $ticket->update([
            'handler_id' => auth()->user()->id,
            'status_id' => 2,
            'responded_at' => now()
        ]);

        $user = User::where('id', $ticket->created_by_id)->first();

        if ($user->fcm_token != null) {
            $user->notify(new SendNotification(
                'Tiket Direspon',
                'Tiket anda telah direspon oleh' . auth()->user()->full_name,
                'ticket'
            ));
        }

        return response()->json([
            'message' => 'Berhasil ambil tiket',
            'data' => [
                'handler' => $ticket->handler->full_name ?? "#N/A",
                'responded_at' => $ticket->responded_at ? $ticket->responded_at->format('d/m/Y | H:i') : null,
                'status' => $ticket->status->status
            ]
        ], 200);

        
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

        if ($ticket->asset_id) {
            $asset = $ticket->asset;
            $asset->update([
                'status_id' => 3
            ]);
        }

        $user = User::where('id', $ticket->created_by_id)->first();

        if ($user->fcm_token != null) {
            $user->notify(new SendNotification(
                'Tiket Dikerjakan',
                'Tiket anda sedang dikerjakan oleh ' . auth()->user()->full_name,
                'ticket'
            ));
        }


        return response()->json([
            'message' => 'Berhasil',
            'data' => [
                'status' => $ticket->status->status
            ]
        ], 200);
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
        if ($ticket->handler_id != auth()->user()->id) return response()->json(['message' => 'Forbidden'], 403);

        $ticket->update([
            'status_id' => 6,
        ]);

        $ticket->note()->create([
            'handler_note' => $request->handler_note
        ]);

        $user = User::where('id', $ticket->created_by_id)->first();

        if ($user->fcm_token != null) {
            $user->notify(new SendNotification(
                'Tiket Ditunda',
                'Pengerjaan tiket anda ditunda',
                'ticket'
            ));
        }

        return response()->json([
            'message' => 'Berhasil mengundur penyelasaian tiket',
            'data' => [
                'status' => $ticket->status->status
            ]
        ], 200);
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
        if ($ticket->handler_id != auth()->user()->id) return response()->json(['message' => 'Forbidden'], 403);

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
            'resolved_at' => now(),
            'flag_id' => null
        ]);

        $user = User::where('id', $ticket->created_by_id)->first();

        if ($user->fcm_token != null) {
            $user->notify(new SendNotification(
                'Pengerjaan selesai',
                'Pengerjaan tiket anda sudah selesai, segera cek dan tutup tiket',
                'ticket'
            ));
        }

        return response()->json([
            'message' => 'Tiket berhasil ditindak lanjut',
            'data' => [
                'status' => $ticket->status->status
            ]
        ], 200);
    }

    public function closeTicket(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ticket_id' => 'required|integer',
        ]);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) return response()->json(['message' => 'Tiket tidak ditemukan'], 404);

        if ($ticket->created_by_id != $user->id) return response()->json(['message' => 'Forbidden'], 403);

        $ticket->update([
            'status_id' => 5,
            'closed_at' => now()
        ]);

        if ($ticket->asset_id) {
            $asset = $ticket->asset;
            $asset->update([
                'status_id' => 2
            ]);
        }

        $user = User::where('id', $ticket->handler_id)->first();

        if ($user->fcm_token != null) {
            $user->notify(new SendNotification(
                'Tiket ' . $ticket->id . ' ditutup',
                'Tiket sudah ditutup oleh user bersangkutan',
                'ticket'
            ));
        }

        return response()->json([
            'message' => 'Tiket berhasil ditutup',
            'data' => [
                'status' => $ticket->status->status
            ]
        ], 200);
    }
}
