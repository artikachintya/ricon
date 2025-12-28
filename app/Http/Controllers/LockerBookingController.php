<?php

namespace App\Http\Controllers;

use App\Models\Locker;
use App\Models\LockerItem;
use App\Models\LockerSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

//grs test
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
//

class LockerBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lockers = Locker::all();
        return view('book_locker', compact('lockers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $request->vaildate([
        //     'locker_id'
        // ])
        // dd($request);
        //   DB::transaction(function () use ($request) {

        //     //Lock row avoid double booking
        //     $locker = Locker::where('id', $request->locker_id)
        //         ->where('status', 'available')
        //         ->lockForUpdate()
        //         ->firstOrFail();

        //     //Create locker session
        //     $session = LockerSession::create([
        //         'locker_id' => $locker->id,
        //         'user_id'   => Auth::id(),
        //         // 'user_id'   => 1,
        //         'status'    => 'active',
        //     ]);

        //     //Create locker item
        //     $lockerItem = LockerItem::create([
        //         'locker_session_id'   => $session->id, // FK ke locker_sessions
        //         'item_name'   => $request->item_name,
        //         'item_detail' => $request->item_detail,
        //     ]);

        //     //Update locker status
        //     $locker->update([
        //         'status' => 'occupied',
        //     ]);
        // });

        // return redirect()
        //     ->route('booking.index')
        //     ->with('success', 'Loker berhasil disewa');


        //gres test
        $request->validate([
            'locker_id' => 'required|exists:lockers,id',
            'item_name' => 'required|string',
        ]);

        $session = DB::transaction(function () use ($request) {

            // Lock locker
            $locker = Locker::where('id', $request->locker_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->firstOrFail();

            // Create session
            $session = LockerSession::create([
                'locker_id' => $locker->id,
                'user_id'   => Auth::id(),
                'status'    => 'active',
            ]);

            // CREATE ITEM PERTAMA + QR
            LockerItem::create([
                'locker_session_id' => $session->id,
                'item_name'         => $request->item_name,
                'item_detail'       => $request->item_detail,
                'key'               => Str::uuid()->toString(),
            ]);

            // Update locker
            $locker->update([
                'status' => 'occupied',
            ]);

            return $session;
        });

        return redirect()
            ->route('booking.show', $session->id)
            ->with('show_qr', true);
    }

    /**
     * Display the specified resource.
     */
    public function show(LockerSession $booking)
    {
        // Diambil dari kode baru
        $booking->load('items');
        return view('manage_locker', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $booking = LockerSession::where('user_id', Auth::id())
            ->where('status', 'active') // atau booked
            ->latest()
            ->first();
        return view('add_item_form', compact('booking'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Create locker item
        // LockerItem::create([
        //     'locker_session_id'   => $id, // FK ke locker_sessions
        //     'item_name'   => $request->item_name,
        //     'item_detail' => $request->item_detail,
        // ]);

        // return redirect()
        // ->route('dashboard')
        // ->with('success', 'Item berhasil ditambahkan');

        // gres test (Logika update baru dengan UUID)
        $request->validate([
            'item_name' => 'required|string',
        ]);

        LockerItem::create([
            'locker_session_id' => $id,
            'item_name'         => $request->item_name,
            'item_detail'       => $request->item_detail,
            'key'               => Str::uuid()->toString(),
        ]);

        return redirect()
            ->route('booking.show', $id)
            ->with('success', 'Item berhasil ditambahkan')
            ->with('show_qr', true);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showAssignUserForm(LockerSession $booking)
    {
        $users = User::orderBy('name')->get();
        return view('assign_taker', compact('booking', 'users'));
    }

    public function assignUser(Request $request, LockerSession $booking)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $booking->assigned_taker_id = $request->user_id;
        $booking->save();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Berhasil menambahkan user untuk mengambil barang');
    }

    public function releaseLocker(LockerSession $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($booking) {
            // 1. Expire locker session
            $booking->update([
                'status' => 'expired',
            ]);

            // 2. Kembalikan locker jadi available
            $booking->locker()->update([
                'status' => 'available',
            ]);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Loker berhasil dilepaskan. Anda dapat memesan loker kembali.');
    }
}
