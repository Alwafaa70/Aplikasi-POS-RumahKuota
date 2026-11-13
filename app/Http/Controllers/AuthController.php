<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user()->role);
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Owner.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();

            return $this->redirectToDashboard($user->role);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectToDashboard($role)
    {
        switch ($role) {
            case 'owner':
                return redirect()->route('owner.dashboard');
            case 'admin_gudang':
                return redirect()->route('admin.dashboard');
            case 'kasir':
                return redirect()->route('kasir.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login');
        }
    }
}
