@php
    $appName = \App\Models\Setting::get('app_name', 'AI Chat Studio');
    $appLogo = \App\Models\Setting::get('app_logo', '');
@endphp
@extends('layouts.app')
@section('title', 'Team & User Admin — ' . $appName)

@section('content')
<div class="app-shell panel-collapsed">

    @include('layouts._sidebar')

    <main class="chat-main" style="padding:36px;overflow-y:auto;display:block">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <div>
            <h1 style="font-size:24px;font-weight:800;letter-spacing:-0.5px">👑 Team & User Admin</h1>
            <p style="font-size:13px;color:var(--text-secondary);margin-top:2px">Manage team members, roles, token quotas, and access permissions.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <a href="{{ route('chat.index') }}" class="btn btn-ghost" style="font-size:12px">
                ⬅️ Back to Studio
            </a>
            <a href="{{ route('admin.branding.index') }}" class="btn btn-ghost" style="font-size:12px">
                🎨 Branding Studio
            </a>
            <button class="btn btn-primary" onclick="openAddUserModal()" style="display:flex;align-items:center;gap:6px;padding:8px 16px;font-size:13px">
                <span>+</span> Add Team Member
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px">
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px">
            <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Total Team</div>
            <div style="font-size:24px;font-weight:800;color:var(--text-primary);margin-top:4px">{{ $stats['total'] }}</div>
        </div>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px">
            <div style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase">Super Admins</div>
            <div style="font-size:24px;font-weight:800;color:var(--accent-light);margin-top:4px">{{ $stats['admins'] }}</div>
        </div>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px">
            <div style="font-size:11px;font-weight:700;color:var(--success);text-transform:uppercase">Active Members</div>
            <div style="font-size:24px;font-weight:800;color:var(--success);margin-top:4px">{{ $stats['members'] }}</div>
        </div>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px">
            <div style="font-size:11px;font-weight:700;color:var(--danger);text-transform:uppercase">Deactivated</div>
            <div style="font-size:24px;font-weight:800;color:var(--danger);margin-top:4px">{{ $stats['deactivated'] }}</div>
        </div>
    </div>

    <!-- Users Table -->
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--bg-elevated)">
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600">User</th>
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600">Role</th>
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600">Status</th>
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600">Chats</th>
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600">Quota</th>
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600">Joined</th>
                    <th style="padding:14px 18px;color:var(--text-secondary);font-weight:600;text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr style="border-bottom:1px solid var(--border);transition:var(--transition)" onmouseover="this.style.background='var(--bg-elevated)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 18px">
                            <div style="display:flex;align-items:center;gap:12px">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#a855f7);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:14px">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700;color:var(--text-primary)">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span style="font-size:10px;padding:1px 6px;border-radius:99px;background:rgba(108,99,255,0.2);color:var(--accent-light);margin-left:4px">You</span>
                                        @endif
                                    </div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <td style="padding:14px 18px">
                            <form action="{{ route('admin.users.toggle-role', $user->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="border:none;background:none;cursor:pointer;padding:0" title="Click to toggle role">
                                    @if($user->role === 'super_admin')
                                        <span style="padding:4px 10px;border-radius:99px;font-size:11px;font-weight:700;background:rgba(168,85,247,0.2);color:#c084fc;border:1px solid rgba(168,85,247,0.4)">👑 Super Admin</span>
                                    @else
                                        <span style="padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--bg-elevated);color:var(--text-secondary);border:1px solid var(--border)">👤 Member</span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <td style="padding:14px 18px">
                            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="border:none;background:none;cursor:pointer;padding:0" title="Click to toggle active status">
                                    @if($user->is_active)
                                        <span style="padding:3px 8px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(34,197,94,0.15);color:var(--success);border:1px solid rgba(34,197,94,0.3)">🟢 Active</span>
                                    @else
                                        <span style="padding:3px 8px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(244,63,94,0.15);color:var(--danger);border:1px solid rgba(244,63,94,0.3)">🔴 Disabled</span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <td style="padding:14px 18px;color:var(--text-secondary)">
                            {{ $user->conversations_count }} chats
                        </td>

                        <td style="padding:14px 18px;color:var(--text-secondary)">
                            {{ $user->token_quota ? number_format($user->token_quota) . ' / mo' : 'Unlimited' }}
                        </td>

                        <td style="padding:14px 18px;color:var(--text-muted);font-size:12px">
                            {{ $user->created_at->diffForHumans() }}
                        </td>

                        <td style="padding:14px 18px;text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost" style="color:var(--danger);font-size:11px;padding:4px 8px">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>

<!-- Add User Modal -->
<div class="modal-overlay hidden" id="add-user-modal">
    <div class="modal" style="max-width:440px;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
            <div style="font-size:16px;font-weight:700">Add New Team Member</div>
            <button class="btn btn-ghost" onclick="closeAddUserModal()" style="font-size:14px;padding:2px 8px">✕</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:4px">Full Name</label>
                <input type="text" name="name" required style="width:100%;padding:8px 12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);font-size:13px;outline:none" placeholder="e.g. Jordan Smith">
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:4px">Email Address</label>
                <input type="email" name="email" required style="width:100%;padding:8px 12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);font-size:13px;outline:none" placeholder="jordan@company.com">
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:4px">Password</label>
                <input type="password" name="password" required style="width:100%;padding:8px 12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);font-size:13px;outline:none" placeholder="Minimum 8 characters">
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:4px">Role</label>
                <select name="role" style="width:100%;padding:8px 12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);font-size:13px;outline:none">
                    <option value="member" selected>👤 Member (Standard User)</option>
                    <option value="super_admin">👑 Super Admin (Full Control)</option>
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:4px">Monthly Token Quota (Optional)</label>
                <input type="number" name="token_quota" style="width:100%;padding:8px 12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);font-size:13px;outline:none" placeholder="Leave empty for unlimited">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <button type="button" class="btn btn-ghost" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 18px;font-size:13px">Create Account</button>
            </div>
        </form>
    </div>
</main>
</div>

@push('scripts')
<script>
function openAddUserModal() {
    document.getElementById('add-user-modal')?.classList.remove('hidden');
}
function closeAddUserModal() {
    document.getElementById('add-user-modal')?.classList.add('hidden');
}
</script>
@endpush
@endsection
