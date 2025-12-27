<?php

namespace App\Http\Controllers;

use App\Models\LockerItem;
use App\Models\LockerSession;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function index()
    {
        return Notification::where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function show($id)
    {
        $notification = Notification::with([
            'lockerItem.session.locker'
        ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'id' => $notification->id,
            'title' => $notification->title,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at,

            'item' => [
                'name' => $notification->lockerItem?->item_name,
                'detail' => $notification->lockerItem?->item_detail,
                'added_at' => $notification->lockerItem?->added_at,
            ],

            'session' => [
                'session_code' => $notification->lockerItem?->session?->session_code,
                'status' => $notification->lockerItem?->session?->status,
            ],

            'locker' => [
                'locker_code' => $notification->lockerItem?->session?->locker?->locker_code,
            ],
        ]);
    }

    public function markAsRead($id)
    {
        $notif = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notif->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    public function itemTakenNotificationOnly(LockerSession $session)
    {
        // Safety check jika session tidak ditemukan
        if (!$session) {
            return response()->json(['message' => 'Session not found'], 404);
        }

        // Ambil nama taker, default "Unknown" kalau null
        $takenByName = optional($session->assignedTaker)->name ?? 'Unknown';

        // Gabung semua nama item, default "Barang" kalau kosong
        $itemNames = $session->items?->pluck('item_name')->implode(', ') ?? 'Barang';

        // Locker code, default "Unknown"
        $lockerCode = optional($session->locker)->locker_code ?? 'Unknown';

        $firstItemId = $session->items->first()?->id ?? null;

        Notification::create([
            'user_id' => $session->user_id,
            'locker_item_id' => $firstItemId, // <- pastikan ini diisi atau kolom nullable
            'type' => 'item_taken',
            'title' => "Barang ({$itemNames}) di locker {$lockerCode} telah diambil oleh {$takenByName}",
            'data' => [
                'taken_by' => $takenByName,
                'taken_at' => $session->taken_at,
                'locker_session_id' => $session->id,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Taken notification sent for the session'
        ]);
    }

    public function indexBookingNotifications()
    {
        return \App\Models\Notification::where('user_id', Auth::id())
            ->where('title', 'like', '%berhasil dibooking%') // filter kata booking
            ->latest()
            ->get();
    }
}
