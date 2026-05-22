<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan ada user yang sedang login
        if (Auth::check()) {

            // Cek apakah flag is_active false (0)
            if (!Auth::user()->is_active) {

                // Paksa logout
                Auth::logout();

                // Hapus session keamanan
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect ke halaman login dengan pesan error
                return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.');
            }
        }

        return $next($request);
    }
}
