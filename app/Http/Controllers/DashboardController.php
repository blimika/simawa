<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'superadmin') {
            return view('dashboard.superadmin');
        } elseif ($user->role === 'admin') {
            return view('dashboard.admin');
        } else {
            return view('dashboard.agent');
        }
    }
}
