<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // memastikan user login
    }

    public function index()
    {
        return view('dashboard.index'); // pastikan file resources/views/dashboard/index.blade.php ada
    }
}
