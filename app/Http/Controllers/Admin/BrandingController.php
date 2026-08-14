<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    /**
     * Display the workspace branding customization panel.
     */
    public function index()
    {
        $settings = [
            'app_name'               => Setting::get('app_name', 'AI Chat Studio'),
            'app_welcome_heading'    => Setting::get('app_welcome_heading', 'What can I help with?'),
            'app_welcome_subheading' => Setting::get('app_welcome_subheading', 'Start a conversation with your AI assistant.'),
            'app_primary_color'      => Setting::get('app_primary_color', '#6c63ff'),
            'app_logo'               => Setting::get('app_logo', ''),
        ];

        return view('admin.branding.index', compact('settings'));
    }

    /**
     * Update workspace branding settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name'               => 'required|string|max:100',
            'app_welcome_heading'    => 'required|string|max:200',
            'app_welcome_subheading' => 'required|string|max:500',
            'app_primary_color'      => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'app_logo_url'           => 'nullable|string|max:1000',
            'app_logo_file'          => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ]);

        Setting::set('app_name', $request->app_name);
        Setting::set('app_welcome_heading', $request->app_welcome_heading);
        Setting::set('app_welcome_subheading', $request->app_welcome_subheading);
        Setting::set('app_primary_color', $request->app_primary_color);

        if ($request->hasFile('app_logo_file')) {
            $file = $request->file('app_logo_file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            
            $destinationPath = public_path('uploads/branding');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);
            Setting::set('app_logo', '/uploads/branding/' . $filename);
        } elseif ($request->filled('app_logo_url')) {
            Setting::set('app_logo', $request->app_logo_url);
        }

        return redirect()->back()->with('success', '🎨 Workspace branding updated successfully!');
    }
}
