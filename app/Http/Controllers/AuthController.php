<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show Login Form.
     */
    public function showLogin()
    {
        if (User::count() === 0) {
            return redirect()->route('setup.index');
        }

        if (Auth::check()) {
            return redirect()->route('chat.index');
        }

        return view('auth.login');
    }

    /**
     * Authenticate User.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact your Super Admin.',
                ]);
            }

            return redirect()->intended(route('chat.index'))->with('toast', [
                'type'    => 'success',
                'message' => '👋 Welcome back, ' . $user->name . '!',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log out User.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('toast', [
            'type'    => 'info',
            'message' => 'Logged out successfully.',
        ]);
    }
}
