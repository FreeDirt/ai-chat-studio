@php
    $appName = $appName ?? \App\Models\Setting::get('app_name', 'AI Chat Studio');
    $appLogo = $appLogo ?? \App\Models\Setting::get('app_logo', '');
    $myConversations = $myConversations ?? collect();
    $sharedConversations = $sharedConversations ?? collect();
    $conversation = $conversation ?? null;
    $currentRoute = request()->route()?->getName();
@endphp

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('chat.index') }}" class="app-logo">
            @if($appLogo)
                <img src="{{ $appLogo }}" alt="Logo" style="width:36px;height:36px;object-fit:contain;border-radius:var(--radius-md);flex-shrink:0">
            @else
                <div class="logo-icon">🤖</div>
            @endif
            <span class="logo-text">{{ $appName }}</span>
        </a>
        <a href="{{ route('chat.index') }}" class="btn-new-chat" id="btn-new-chat" style="text-decoration:none">
            + New Chat
        </a>
    </div>

    <!-- Sleek Top Navigation -->
    <nav class="sidebar-nav" style="padding:6px 8px;display:flex;gap:4px">
        <a href="{{ route('chat.index') }}" class="{{ $currentRoute === 'chat.index' ? 'active' : '' }}" title="Chats" style="flex:1;justify-content:center;padding:6px 10px;font-size:12px;font-weight:700">
            💬 Chats
        </a>
        <a href="{{ route('personas.index') }}" class="{{ $currentRoute === 'personas.index' ? 'active' : '' }}" title="Personas Studio" style="flex:1;justify-content:center;padding:6px 10px;font-size:12px">
            🎭 Personas
        </a>
        <button class="nav-search-btn" id="btn-search-trigger" onclick="document.getElementById('btn-search-trigger')?.click()" title="Search conversations (Ctrl+K)" style="padding:6px 10px;display:inline-flex;align-items:center;gap:4px">
            🔍 <kbd class="nav-kbd" style="font-size:9px;padding:1px 4px">⌘K</kbd>
        </button>
    </nav>

    <!-- Conversation List -->
    <div class="conversations-list" id="conversations-list">
        @if($myConversations->count() > 0 || $sharedConversations->count() > 0)
            <!-- Pinned Chats -->
            @if($myConversations->where('is_pinned', true)->count() > 0)
                <div class="conv-section-label">📌 Pinned</div>
                @foreach($myConversations->where('is_pinned', true) as $conv)
                    @include('chat._conv_item', ['conv' => $conv, 'active' => $conversation?->id === $conv->id])
                @endforeach
            @endif

            <!-- My Private Chats -->
            <div class="conv-section-label">🔒 My Private Chats</div>
            @foreach($myConversations->where('is_pinned', false) as $conv)
                @include('chat._conv_item', ['conv' => $conv, 'active' => $conversation?->id === $conv->id])
            @endforeach

            <!-- Shared Team Chats -->
            @if($sharedConversations->count() > 0)
                <div class="conv-section-label" style="margin-top:16px">👥 Shared with Me</div>
                @foreach($sharedConversations as $conv)
                    @include('chat._conv_item', ['conv' => $conv, 'active' => $conversation?->id === $conv->id])
                @endforeach
            @endif
        @else
            <div style="text-align:center;color:var(--text-muted);font-size:12px;padding:28px 12px;line-height:1.5">
                💬 <br>No conversations yet.<br><a href="{{ route('chat.index') }}" style="color:var(--accent-light);font-weight:600">Start a new chat!</a>
            </div>
        @endif
    </div>

    <!-- User Profile & Tools Footer Card -->
    @auth
    <div style="padding:10px 12px;border-top:1px solid var(--border);background:var(--bg-elevated);margin-top:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:8px;min-width:0">
                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#a855f7);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="min-width:0">
                    <div style="font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-primary)">
                        {{ auth()->user()->name }}
                    </div>
                    <div style="font-size:10px;color:var(--text-muted)">
                        {{ auth()->user()->isSuperAdmin() ? '👑 Super Admin' : '👤 Member' }}
                    </div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-ghost" title="Logout" style="font-size:12px;padding:4px 6px">
                    🚪
                </button>
            </form>
        </div>

        <!-- Quick Action Toolbar -->
        <div style="display:flex;gap:4px;justify-content:space-between;background:var(--bg-surface);border:1px solid var(--border);padding:4px;border-radius:var(--radius-md)">
            <a href="{{ route('settings.index') }}" class="btn btn-ghost {{ $currentRoute === 'settings.index' ? 'active' : '' }}" title="Settings" style="font-size:11px;padding:4px;flex:1;justify-content:center">
                ⚙️
            </a>
            <button class="btn btn-ghost" id="btn-analytics-trigger" title="Token Usage Analytics" style="font-size:11px;padding:4px;flex:1;justify-content:center">
                📊
            </button>
            <button class="btn btn-ghost" id="btn-shortcuts-trigger" title="Keyboard Shortcuts" style="font-size:11px;padding:4px;flex:1;justify-content:center">
                ❓
            </button>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost {{ $currentRoute === 'admin.users.index' ? 'active' : '' }}" title="User Management" style="font-size:11px;padding:4px;flex:1;justify-content:center">
                👥
            </a>
            <a href="{{ route('admin.branding.index') }}" class="btn btn-ghost {{ $currentRoute === 'admin.branding.index' ? 'active' : '' }}" title="Workspace Branding Studio" style="font-size:11px;padding:4px;flex:1;justify-content:center">
                🎨
            </a>
            @endif
        </div>
    </div>
    @endauth
</aside>
