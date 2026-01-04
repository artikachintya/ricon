<?php

namespace App\Http\Controllers;

use App\Models\Locker;
use App\Models\LockerItem;
use App\Models\LockerSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Promise\PromiseInterface;
//grs test
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\Clock\now;

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

        ini_set('max_execution_time', 120);

        $request->validate([
            'locker_id' => 'required|exists:lockers,id',
            'item_name' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            // Cari loker yang tersedia
            $locker = Locker::where('id', $request->locker_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->firstOrFail();

            // Buat Session
            $session = LockerSession::create([
                'locker_id' => $locker->id,
                'user_id'   => Auth::id(),
                'status'    => 'active',
            ]);

            // Buat Item
            /** @var LockerItem $item */
            $item = LockerItem::create([
                'locker_session_id' => $session->id,
                'item_name'         => $request->item_name,
                'item_detail'       => $request->item_detail,
                'key'               => Str::uuid()->toString(),
            ]);

            // Panggil Flask API untuk generate QR secara fisik
            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::post('http://127.0.0.1:5000/generate-qr', [
                    'locker_session_id' => $session->id,
                    'item_id'           => $item->id,
                    'item_detail'       => $item->item_detail ?? $item->item_name,
                    'key'               => $item->key
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Simpan path gambar ke tabel locker_items
                    if (isset($data['relative_path'])) {
                        $item->update([
                            'qr_path' => $data['relative_path']
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Flask Error saat generate QR: " . $e->getMessage());
            }

            // Update status loker menjadi terisi
            $locker->update([
                'status' => 'occupied',
            ]);

            // dd($session->id);
            return redirect()
                ->route('booking.show', $session->id)
                ->with('show_qr', true);
        });
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
            ->where('status', 'active')
            ->latest()
            ->first();
        // dd($booking);
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

        // gres test
        // Validasi input
        $request->validate([
            'item_name' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $id) {
            // Buat Item baru
            $item = LockerItem::create([
                'locker_session_id' => $id,
                'item_name'         => $request->item_name,
                'item_detail'       => $request->item_detail,
                'key'               => Str::uuid()->toString(),
            ]);

            // PANGGIL FLASK UNTUK GENERATE QR ITEM BARU INI
            try {
                $response = Http::post('http://127.0.0.1:5000/generate-qr', [
                    'locker_session_id' => $id, // <--- Mengirim ID Session yang sama dengan booking pertama
                    'item_id'           => $item->id,
                    'item_detail'       => $item->item_detail ?? $item->item_name,
                    'key'               => $item->key
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Simpan path ke database locker_items
                    if (isset($data['relative_path'])) {
                        $item->update([
                            'qr_path' => $data['relative_path']
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Flask Error saat Add Item: " . $e->getMessage());
            }

            return redirect()
                ->route('booking.show', $id)
                ->with('success', 'Item berhasil ditambahkan')
                ->with('show_qr', true);
        });
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

    public function getIotData()
    {
        try {
            $response = Http::get('http://127.0.0.1:2200/api/locker');

            //Kalau ternyata Promise → tunggu hasilnya
            if ($response instanceof PromiseInterface) {
                $response = $response->wait();
            }

            // Sekarang ini ResponseInterface (Guzzle)
            $status = $response->getStatusCode();
            $body   = json_decode((string) $response->getBody(), true);

            return response()->json([
                'success' => $status === 200,
                'status'  => $status,
                'data'    => $body,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function verifyQrCode(Request $request)
    {
        try {
            $response = Http::attach(
                'images',
                file_get_contents($request->file('images')),
                'frame.jpg'
            )->post('http://localhost:5000/recognize');

            if (!$response->successful()) {
                return response()->json(['error' => 'AI Server Error'], 500);
            }

            $data = $response->json();

            if (isset($data[0]['type']) && $data[0]['type'] === 'qr_raw') {
                $qrKey = $data[0]['key'];

                $item = LockerItem::where('key', $qrKey)
                    ->with(['session.user'])
                    ->first();
                if (!$item) {
                    return response()->json([['type' => 'qr_error', 'result' => 'QR Key Tidak Valid']]);
                }

                if ((int)$item->opened_by_sender !== 1) {
                    return response()->json([[
                        'type' => 'qr_used',
                        'result' => 'Kode QR sudah pernah digunakan'
                    ]]);
                }
                $item->update(['opened_by_sender' => 0]);

                return response()->json([[
                    'type' => 'qr_success',
                    'user_id' => $item->session->user_id,
                    'name' => $item->session->user->name ?? 'User',
                    'locker_id' => $item->session->locker_id
                ]]);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Connection to AI Server failed'], 500);
        }
    }
}
