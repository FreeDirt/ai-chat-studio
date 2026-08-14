@php
    $totalBranches = 1;
    $currentBranch = 1;
    if ($msg->parent_id) {
        $siblings = $msg->siblings()->get();
        $totalBranches = $siblings->count();
        $currentBranch = $siblings->search(fn($s) => $s->id === $msg->id) + 1;
    }
@endphp
<div class="message {{ $msg->role }}" data-id="{{ $msg->id }}">
    <div class="msg-avatar">
        @if($msg->role === 'user')
            {{ strtoupper(substr($msg->user?->name ?? auth()->user()->name, 0, 1)) }}
        @else
            🤖
        @endif
    </div>
    <div class="msg-body">
        <div class="msg-meta">
            <strong>
                @if($msg->role === 'user')
                    {{ $msg->user?->name ?? 'You' }}
                    @if($msg->user && $msg->user->id === auth()->id())
                        <span style="font-size:10px;padding:1px 5px;border-radius:99px;background:rgba(108,99,255,0.2);color:var(--accent-light);margin-left:2px">You</span>
                    @elseif($msg->user)
                        <span style="font-size:10px;padding:1px 5px;border-radius:99px;background:var(--bg-elevated);color:var(--text-secondary);margin-left:2px">{{ $msg->user->isSuperAdmin() ? '👑 Admin' : '👤 Member' }}</span>
                    @endif
                @else
                    AI Assistant
                @endif
            </strong>
            @if($msg->model) <span class="token-badge">{{ $msg->model }}</span> @endif
            @if($msg->tokens_used) <span class="token-badge">{{ $msg->tokens_used }} tokens</span> @endif
            @if($msg->formatted_response_time) <span class="token-badge">{{ $msg->formatted_response_time }}</span> @endif
            
            @if($totalBranches > 1)
            <span class="branch-nav-pill" style="display:inline-flex;align-items:center;gap:4px;background:rgba(108,99,255,0.15);border:1px solid rgba(108,99,255,0.3);padding:1px 6px;border-radius:99px;font-size:10px;color:var(--accent-light);font-weight:700">
                <button type="button" class="btn-branch-prev" data-id="{{ $msg->id }}" style="background:none;border:none;color:inherit;cursor:pointer;padding:0 2px" title="Previous branch variant">←</button>
                <span>{{ $currentBranch }}/{{ $totalBranches }}</span>
                <button type="button" class="btn-branch-next" data-id="{{ $msg->id }}" style="background:none;border:none;color:inherit;cursor:pointer;padding:0 2px" title="Next branch variant">→</button>
            </span>
            @endif

            <span style="color:var(--text-muted);font-size:10px">{{ $msg->created_at->diffForHumans() }}</span>

            <!-- Message Action Toolbar -->
            <div class="msg-actions">
                <button class="msg-act-btn btn-msg-branch" title="Branch off this message to create an alternative thread" data-id="{{ $msg->id }}">🌿 Branch</button>
                @if($msg->role === 'user')
                    <button class="msg-act-btn btn-msg-edit" title="Edit message" data-id="{{ $msg->id }}">✏️ Edit</button>
                @else
                    <button class="msg-act-btn btn-msg-speak" title="Read AI response aloud">🔊 Speak</button>
                    <button class="msg-act-btn btn-msg-bookmark" title="Bookmark response" data-id="{{ $msg->id }}">⭐ Bookmark</button>
                    <button class="msg-act-btn btn-msg-regen" title="Regenerate AI response">🔄 Regenerate</button>
                    <button class="msg-act-btn btn-msg-copy" title="Copy full response" data-content="{{ $msg->content }}">📋 Copy</button>
                @endif
            </div>
        </div>
        <div class="msg-bubble msg-content">
            {!! $msg->role === 'user'
                ? '<div class="user-text">'.nl2br(e($msg->content)).'</div>'
                : '<div class="md-content">'.e($msg->content).'</div>'
            !!}
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
// Render all pre-loaded markdown messages
document.querySelectorAll('.md-content').forEach(el => {
    if (!el.dataset.parsed) {
        el.innerHTML = marked.parse(el.textContent);
        el.dataset.parsed = 'true';
        el.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
        el.querySelectorAll('pre').forEach(pre => {
            const btn = document.createElement('button');
            btn.className = 'copy-code-btn';
            btn.textContent = 'Copy';
            btn.onclick = () => {
                navigator.clipboard.writeText(pre.querySelector('code')?.textContent || '');
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy', 2000);
            };
            pre.style.position = 'relative';
            pre.appendChild(btn);
        });
    }
});
</script>
@endpush
@endonce
