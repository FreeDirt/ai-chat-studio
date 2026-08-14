<a href="{{ route('chat.index', ['conversation' => $conv->id]) }}" class="conv-item {{ $active ? 'active' : '' }}" data-id="{{ $conv->id }}" style="text-decoration:none;color:inherit;display:flex">
    <div class="conv-icon">
        @if($conv->provider === 'ollama') 🦙 @else 🤖 @endif
    </div>
    <div class="conv-info">
        <div class="conv-title">{{ $conv->title }}</div>
        <div class="conv-meta">
            @if($conv->is_pinned) <span class="conv-pin-icon">📌</span> @endif
            @if($conv->visibility === 'team')
                <span style="color:#60a5fa;font-weight:700" title="Shared with Workspace Team">👥 Team</span> ·
            @elseif($conv->visibility === 'link')
                <span style="color:#c084fc;font-weight:700" title="Shared via Link">🔗 Link</span> ·
            @elseif($conv->visibility === 'custom')
                <span style="color:#a7f3d0;font-weight:700" title="Shared with Specific Members">👤 Shared</span> ·
            @endif
            {{ $conv->messages_count ?? 0 }} msgs
            @if($conv->last_active_at) · {{ $conv->last_active_at->diffForHumans() }} @endif
        </div>
    </div>
    <div class="conv-actions">
        <button type="button" class="conv-action-btn" data-action="rename" title="Rename">✏️</button>
        <button type="button" class="conv-action-btn" data-action="pin" title="{{ $conv->is_pinned ? 'Unpin' : 'Pin' }}">📌</button>
        <button type="button" class="conv-action-btn" data-action="delete" title="Delete">🗑️</button>
    </div>
</a>
