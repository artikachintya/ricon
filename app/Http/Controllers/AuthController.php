<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Tampilkan form login (opsional kalau pakai Blade)
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Login via session
    public function login(Request $request)
    {
        $request->validate([
            'udomain' => 'required|string',
            'password' => 'required|string',
        ], [
            'udomain.required' => 'Udomain wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = $request->only('udomain', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // penting supaya session aman
            return redirect()->intended('/'); // redirect ke dashboard
        }

        // jika gagal login
        return back()->withErrors([
            'udomain' => 'Udomain atau password salah',
        ])->onlyInput('udomain');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // Ambil data user saat ini
    public function me()
    {
        return response()->json(Auth::user());
    }
}
