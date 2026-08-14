<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    /**
     * Show First-Time Setup Wizard.
     */
    public function index()
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        return view('auth.setup');
    }

    /**
     * Store Super Admin Account & Complete Setup.
     */
    public function store(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        $request->validate([
            'app_name' => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Setting::set('app_name', $request->app_name);

        $admin = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        Auth::login($admin);

        return redirect()->route('chat.index')->with('toast', [
            'type' => 'success',
            'message' => '🎉 Setup completed! Welcome Super Admin ' . $admin->name . '!',
        ]);
    }
}
