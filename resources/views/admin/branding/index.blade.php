@php
    $appName = \App\Models\Setting::get('app_name', 'AI Chat Studio');
    $appLogo = \App\Models\Setting::get('app_logo', '');
@endphp
@extends('layouts.app')
@section('title', 'Workspace Branding Studio — ' . $appName)

@section('content')
<div class="app-shell panel-collapsed">

    @include('layouts._sidebar')

    <!-- ===== MAIN CONTENT CONTAINER ===== -->
    <main class="chat-main" style="overflow-y:auto;padding:36px;display:block">
        <div style="max-width:960px;margin:0 auto">

            <!-- Hero Title Bar -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
                <div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:24px">🎨</span>
                        <h1 style="font-size:24px;font-weight:800;letter-spacing:-0.5px;color:var(--text-primary)">Workspace Branding Studio</h1>
                    </div>
                    <p style="font-size:13px;color:var(--text-secondary);margin-top:4px">
                        Customize your team workspace identity, primary theme color, logo, and home page welcome titles.
                    </p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <a href="{{ route('settings.index') }}" class="btn btn-ghost" style="font-size:12px;padding:8px 14px">
                        ⚙️ AI Settings
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="font-size:12px;padding:8px 14px">
                        👥 User Admin
                    </a>
                    <a href="{{ route('chat.index') }}" class="btn btn-primary" style="font-size:12px;padding:8px 16px">
                        💬 Open Chat Studio
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);padding:14px 18px;border-radius:var(--radius-lg);margin-bottom:24px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
                <span>✅</span> {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div style="background:rgba(244,63,94,0.15);border:1px solid rgba(244,63,94,0.3);color:var(--danger);padding:14px 18px;border-radius:var(--radius-lg);margin-bottom:24px;font-size:13px">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

                <!-- Left Form Section -->
                <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:24px">
                    @csrf

                    <!-- Card 1: Workspace Identity -->
                    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-xl);padding:24px">
                        <h3 style="font-size:15px;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px">
                            <span>🏷️</span> Workspace Identity & Titles
                        </h3>

                        <div style="display:flex;flex-direction:column;gap:16px">
                            <div>
                                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">App / Workspace Name</label>
                                <input type="text" id="input-app-name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" class="form-input" style="width:100%" required>
                                <span style="font-size:11px;color:var(--text-muted);margin-top:4px;display:block">Displayed in browser titles, headers, and sidebar navigation.</span>
                            </div>

                            <div>
                                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Home Welcome Heading</label>
                                <input type="text" id="input-welcome-heading" name="app_welcome_heading" value="{{ old('app_welcome_heading', $settings['app_welcome_heading']) }}" class="form-input" style="width:100%" required>
                            </div>

                            <div>
                                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Home Welcome Subheading</label>
                                <textarea id="input-welcome-subheading" name="app_welcome_subheading" rows="2" class="form-textarea" style="width:100%" required>{{ old('app_welcome_subheading', $settings['app_welcome_subheading']) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Primary Accent Color -->
                    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-xl);padding:24px">
                        <h3 style="font-size:15px;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px">
                            <span>🎨</span> Primary Accent Theme Color
                        </h3>

                        <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px">
                            <input type="color" id="primary-color-picker" name="app_primary_color" value="{{ old('app_primary_color', $settings['app_primary_color']) }}" style="width:48px;height:48px;border:none;border-radius:12px;cursor:pointer;background:none">
                            <div>
                                <input type="text" id="primary-color-text" value="{{ old('app_primary_color', $settings['app_primary_color']) }}" class="form-input" style="font-family:var(--font-mono);width:130px;font-weight:700;text-transform:uppercase" readonly>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Click the swatch to pick any custom Hex color.</div>
                            </div>
                        </div>

                        <label style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:10px">Curated Palette Presets</label>
                        <div style="display:flex;gap:12px;flex-wrap:wrap">
                            <button type="button" class="btn-color-preset" data-color="#6c63ff" style="background:#6c63ff;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Antigravity Violet"></button>
                            <button type="button" class="btn-color-preset" data-color="#3b82f6" style="background:#3b82f6;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Royal Blue"></button>
                            <button type="button" class="btn-color-preset" data-color="#10b981" style="background:#10b981;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Emerald Green"></button>
                            <button type="button" class="btn-color-preset" data-color="#f59e0b" style="background:#f59e0b;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Amber Gold"></button>
                            <button type="button" class="btn-color-preset" data-color="#ec4899" style="background:#ec4899;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Hot Pink"></button>
                            <button type="button" class="btn-color-preset" data-color="#8b5cf6" style="background:#8b5cf6;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Deep Purple"></button>
                            <button type="button" class="btn-color-preset" data-color="#06b6d4" style="background:#06b6d4;width:34px;height:34px;border-radius:50%;border:2px solid transparent;cursor:pointer" title="Cyan Spark"></button>
                        </div>
                    </div>

                    <!-- Card 3: Workspace Logo -->
                    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-xl);padding:24px">
                        <h3 style="font-size:15px;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px">
                            <span>🖼️</span> Workspace Logo
                        </h3>

                        <div style="display:flex;flex-direction:column;gap:16px">
                            <div>
                                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Option 1: Upload Logo Image (PNG, SVG, JPG, WEBP, max 10MB)</label>
                                <input type="file" id="logo-file-input" name="app_logo_file" accept="image/*,.svg" class="form-input" style="width:100%">
                            </div>

                            <div style="display:flex;align-items:center;gap:12px">
                                <div style="flex:1;height:1px;background:var(--border)"></div>
                                <span style="font-size:11px;color:var(--text-muted);font-weight:700">OR</span>
                                <div style="flex:1;height:1px;background:var(--border)"></div>
                            </div>

                            <div>
                                <label style="font-size:12px;font-weight:700;color:var(--text-secondary);display:block;margin-bottom:6px">Option 2: Logo Image URL</label>
                                <input type="text" id="logo-url-input" name="app_logo_url" value="{{ old('app_logo_url', $settings['app_logo']) }}" placeholder="https://example.com/logo.png" class="form-input" style="width:100%">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:8px">
                        <a href="{{ route('chat.index') }}" class="btn btn-ghost" style="padding:10px 20px">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="padding:10px 28px;font-size:14px;font-weight:700">
                            💾 Save Branding Changes
                        </button>
                    </div>
                </form>

                <!-- Right Live Preview Card -->
                <div style="position:sticky;top:0;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-xl);padding:24px;display:flex;flex-direction:column;gap:20px">
                    <div style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase;letter-spacing:0.5px">⚡ Live Workspace Preview</div>

                    <!-- Sidebar Logo Preview -->
                    <div style="background:var(--bg-base);border:1px solid var(--border);padding:14px;border-radius:var(--radius-lg);display:flex;align-items:center;gap:10px">
                        <div id="preview-logo-box" style="width:36px;height:36px;border-radius:var(--radius-md);background:linear-gradient(135deg, var(--accent), #a855f7);display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 0 20px var(--accent-glow);flex-shrink:0">
                            <span id="preview-logo-icon">🤖</span>
                            <img id="preview-logo-img" src="{{ $settings['app_logo'] }}" style="width:36px;height:36px;object-fit:contain;border-radius:var(--radius-md);{{ empty($settings['app_logo']) ? 'display:none' : '' }}">
                        </div>
                        <div id="preview-app-name" style="font-size:15px;font-weight:700;color:var(--text-primary)">
                            {{ $settings['app_name'] }}
                        </div>
                    </div>

                    <!-- Welcome Card Preview -->
                    <div style="background:var(--bg-elevated);border:1px solid var(--border);padding:20px;border-radius:var(--radius-lg);text-align:center">
                        <div id="preview-color-pill" style="width:48px;height:48px;border-radius:var(--radius-lg);background:linear-gradient(135deg, var(--accent), #a855f7);margin:0 auto 12px auto;display:flex;align-items:center;justify-content:center;font-size:24px">
                            ✨
                        </div>
                        <h3 id="preview-welcome-heading" style="font-size:16px;font-weight:700;margin-bottom:6px">
                            {{ $settings['app_welcome_heading'] }}
                        </h3>
                        <p id="preview-welcome-subheading" style="font-size:12px;color:var(--text-secondary);line-height:1.5">
                            {{ $settings['app_welcome_subheading'] }}
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </main>

</div>

<script>
// Preset Color Selector
document.querySelectorAll('.btn-color-preset').forEach(btn => {
    btn.addEventListener('click', () => {
        const color = btn.dataset.color;
        document.getElementById('primary-color-picker').value = color;
        document.getElementById('primary-color-text').value = color;
        document.documentElement.style.setProperty('--accent', color);
        document.documentElement.style.setProperty('--accent-glow', color + '59');
    });
});

document.getElementById('primary-color-picker')?.addEventListener('input', (e) => {
    const color = e.target.value;
    document.getElementById('primary-color-text').value = color;
    document.documentElement.style.setProperty('--accent', color);
    document.documentElement.style.setProperty('--accent-glow', color + '59');
});

// Live Text Updates
document.getElementById('input-app-name')?.addEventListener('input', e => {
    document.getElementById('preview-app-name').textContent = e.target.value || 'AI Chat Studio';
});

document.getElementById('input-welcome-heading')?.addEventListener('input', e => {
    document.getElementById('preview-welcome-heading').textContent = e.target.value || 'What can I help with?';
});

document.getElementById('input-welcome-subheading')?.addEventListener('input', e => {
    document.getElementById('preview-welcome-subheading').textContent = e.target.value || 'Start a conversation with your AI assistant.';
});

// Live Logo Preview from File Input
document.getElementById('logo-file-input')?.addEventListener('change', e => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
            const img = document.getElementById('preview-logo-img');
            const icon = document.getElementById('preview-logo-icon');
            if (img && icon) {
                img.src = ev.target.result;
                img.style.display = 'block';
                icon.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
});

// Live Logo Preview from URL Input
document.getElementById('logo-url-input')?.addEventListener('input', e => {
    const url = e.target.value.trim();
    const img = document.getElementById('preview-logo-img');
    const icon = document.getElementById('preview-logo-icon');
    if (url && img && icon) {
        img.src = url;
        img.style.display = 'block';
        icon.style.display = 'none';
    }
});
</script>
@endsection
