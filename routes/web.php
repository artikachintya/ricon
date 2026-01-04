<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LockerBookingController;
use App\Http\Controllers\NotificationController;
use App\Models\Locker;
use App\Models\LockerSession;
use Illuminate\Support\Facades\Auth;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/login', function () {
    return view('login.login');
})->name('login');


// Login action (POST)
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->only('udomain', 'password');

    if (Auth::attempt(['udomain' => $credentials['udomain'], 'password' => $credentials['password']])) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->with('error', 'Udomain atau password salah')->withInput();
});


Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/locker-session/{session}/taken-notif', [NotificationController::class, 'itemTakenNotificationOnly']);
    Route::get('/locker-item/{item}/delivered', [NotificationController::class, 'itemDeliveredNotification']);
    Route::get('/notifications/booking', [NotificationController::class, 'indexBookingNotifications']);
    Route::resource('/booking', LockerBookingController::class);
    Route::put('/booking/{booking}/assign-user', [LockerBookingController::class, 'assignUser'])->name('booking.assignUser');
    Route::get('/booking/{booking}/assign-user', [LockerBookingController::class, 'showAssignUserForm'])->name('booking.showAssignUserForm');
    Route::post('/booking/{booking}/release', [LockerBookingController::class, 'releaseLocker'])->name('booking.release');
});

Route::get('/kiosk', function () {
    return view('layouts.kiosk');
})->name('kiosk.scan');


Route::get('/users/{userId}/active-lockers', function ($userId) {
    return LockerSession::where('status', 'active')
        ->where(function ($query) use ($userId) {
            $query->Where('assigned_taker_id', $userId);
        })
        ->get(['locker_id']);
});

Route::post('/lockers/update-statuses', function (\Illuminate\Http\Request $request) {
    $lockerIds = $request->input('locker_ids', []);
    $userId = $request->input('user_id');

    if (empty($lockerIds) || !$userId) {
        return response()->json(['message' => 'Missing locker IDs or user ID'], 400);
    }

    // 1. Update lockers ke 'available'
    Locker::whereIn('id', $lockerIds)->update(['status' => 'available']);

    // 2. Ambil session aktif dan update pakai each() biar observer jalan
    LockerSession::where('status', 'active')
        ->whereIn('locker_id', $lockerIds)        // harus locker yang discan
        ->whereNotNull('assigned_taker_id')        // harus sudah ada assigned taker ⚠️
        ->where(function ($query) use ($userId) {
            $query->where('assigned_taker_id', $userId);
        })
        ->get()
        ->each(function ($session) {
            $session->status = 'done';
            $session->taken_at = now();
            $session->save();
        });


    return response()->json(['message' => 'Statuses updated successfully']);
});


// HISTORY (SESSION-BASED)
Route::resource('/history', HistoryController::class)
    ->middleware('auth');

Route::post('/verify-qr', [LockerBookingController::class, 'verifyQrCode'])->name('qr.verify');
