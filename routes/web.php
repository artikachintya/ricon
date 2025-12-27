<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LockerBookingController;
use App\Http\Controllers\NotificationController;
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


Route::middleware('auth')->group(function() {
     Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::get('/locker-session/{session}/taken-notif', [NotificationController::class, 'itemTakenNotificationOnly']);
    Route::get('/notifications/booking', [NotificationController::class, 'indexBookingNotifications']);
});

Route::get('/kiosk', function () {
    return view('layouts.kiosk');
})->name('kiosk.scan');

Route::get('/users/{user}/active-lockers', function ($userId) {
    return LockerSession::where('user_id', $userId)
        ->where('status', 'active')
        ->get(['locker_id']);
});


Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


// HISTORY (SESSION-BASED)
Route::resource('/history', HistoryController::class)
    ->middleware('auth');
