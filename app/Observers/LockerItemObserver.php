<?php

namespace App\Observers;

use App\Models\LockerItem;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LockerItemObserver
{

    /**
     * Handle the LockerItem "creating" event.
     * Generates a QR key from the Flask AI server.
     */
    // public function creating(LockerItem $item): void
    // {
    //     try {
    //         // Call Flask to generate and save the image
    //         $response = Http::get('http://localhost:5000generate');

    //         if ($response) {
    //             $data = $response->json();

    //             // Save both the key and the path to the database
    //             $item->key = $data['key'];
    //             $item->qr_path = $data['qr_path'];
    //             $item->opened_by_sender = 0;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("QR Generation failed: " . $e->getMessage());
    //     }
    // }

    /**
     * Trigger saat locker_item di-update
     */
    public function updated(LockerItem $item): void
    {
        $item->load('session');
        $userId = optional($item->session)->user_id;
        $user = optional($item->session)->user;

        if ($item->wasChanged('opened_by_sender') && (int)$item->opened_by_sender === 0) {
            Notification::create([

                'user_id' => $userId,
                'locker_item_id' => $item->id,
                'title' => "Barang {$item->item_name} telah masuk ke loker",
                'data' => [
                    'item_name' => $item->item_name,
                    'item_detail' => $item->item_detail,
                    'added_at' => $item->created_at,
                ],
                'is_read' => false
            ]);
            $waMessage = "Halo {$user->name}, barang '{$item->item_name}' telah berhasil dimasukkan ke loker. Silahkan buka website MyBox untuk mendownload receipt.";
            \App\Http\Controllers\NotificationController::sendWhatsApp($user->phone ?? "null", $waMessage);
        }
    }

}
