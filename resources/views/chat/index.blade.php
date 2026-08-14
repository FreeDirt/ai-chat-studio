@php
    $appName = \App\Models\Setting::get('app_name', 'AI Chat Studio');
    $appLogo = \App\Models\Setting::get('app_logo', '');
@endphp
@extends('layouts.app')
@section('title', ($conversation?->title ?? 'New Chat') . ' — ' . $appName)

@section('content')
<div class="app-shell">

    @include('layouts._sidebar')

    <!-- ===== MAIN CHAT ===== -->
    <main class="chat-main">
        <div class="chat-header">
            <button class="btn-sidebar-toggle" id="btn-toggle-sidebar" title="Toggle Sidebar (Ctrl+\)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
            </button>
            <div class="chat-header-title" id="chat-title">
                {{ $conversation?->title ?? 'Select or start a conversation' }}
                @if($conversation?->user)
                    <span style="font-size:11px;color:var(--text-secondary);font-weight:600;margin-left:6px;background:var(--bg-elevated);border:1px solid var(--border);padding:2px 8px;border-radius:99px" title="Chat owner">
                        👤 {{ $conversation->user->name }}
                    </span>
                @endif
                @if($conversation)
                    @if($conversation->visibility === 'team')
                        <span id="chat-visibility-badge" style="font-size:11px;color:#60a5fa;font-weight:700;margin-left:6px;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.3);padding:2px 8px;border-radius:99px" title="Shared with Workspace Team">
                            👥 Shared with Team
                        </span>
                    @elseif($conversation->visibility === 'link')
                        <span id="chat-visibility-badge" style="font-size:11px;color:#c084fc;font-weight:700;margin-left:6px;background:rgba(168,85,247,0.15);border:1px solid rgba(168,85,247,0.3);padding:2px 8px;border-radius:99px" title="Shared via Public Link">
                            🔗 Public Link
                        </span>
                    @elseif($conversation->visibility === 'custom')
                        <span id="chat-visibility-badge" style="font-size:11px;color:#34d399;font-weight:700;margin-left:6px;background:rgba(52,211,153,0.15);border:1px solid rgba(52,211,153,0.3);padding:2px 8px;border-radius:99px" title="Shared with Specific Team Members">
                            👤 Custom Members
                        </span>
                    @else
                        <span id="chat-visibility-badge" style="font-size:11px;color:var(--text-muted);font-weight:600;margin-left:6px;background:var(--bg-elevated);border:1px solid var(--border);padding:2px 8px;border-radius:99px" title="Only visible to you">
                            🔒 Private Chat
                        </span>
                    @endif
                @endif
            </div>

            <!-- Active Online Team Presence Bar -->
            <div id="presence-online-bar" style="display:flex;align-items:center;gap:6px;margin-right:8px"></div>

            <!-- Share Button -->
            @if($conversation)
            <button class="btn-hdr-action" id="btn-open-share-modal" title="Share Conversation">
                <span>🔗 Share</span>
            </button>
            @endif

            <a href="{{ route('settings.index') }}" class="provider-badge" id="provider-badge" title="{{ $activeKeyConfigured ? 'Active Provider: ' . strtoupper($provider) . ' (' . ($conversation?->model ?? $activeModel) . ') — Click to change in Settings' : '⚠️ API Key is missing for ' . strtoupper($provider) . ' — Click to configure in Settings' }}" style="text-decoration:none">
                <span class="dot" id="provider-dot" style="background: {{ $activeKeyConfigured ? 'var(--success)' : 'var(--warning)' }}"></span>
                <span id="provider-label">{{ strtoupper($provider) }}</span>
                <span style="color:var(--text-muted)">·</span>
                <span id="model-label" style="font-size:11px;color:var(--text-muted)">{{ $conversation?->model ?? $activeModel }}</span>
                @if(!$activeKeyConfigured)
                    <span style="font-size:10px;color:var(--warning);font-weight:700;margin-left:4px;background:rgba(245,158,11,0.15);padding:1px 6px;border-radius:99px">⚠️ Key Missing</span>
                @endif
            </a>

            <!-- Export Dropdown -->
            <div class="export-dropdown" id="export-dropdown-container">
                <button class="btn-hdr-action" id="btn-export-toggle" title="Export Conversation">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Export</span>
                </button>
                <div class="export-menu hidden" id="export-menu">
                    <button class="export-item" data-format="pdf">🖨️ PDF Report (Print / PDF)</button>
                    <button class="export-item" data-format="html">🌐 HTML Document (.html)</button>
                    <button class="export-item" data-format="md">📝 Markdown (.md)</button>
                    <button class="export-item" data-format="txt">📄 Plain Text (.txt)</button>
                    <button class="export-item" data-format="json">📦 JSON Backup (.json)</button>
                </div>
            </div>

            <!-- Compare Mode Toggle -->
            <button class="btn-hdr-action" id="btn-toggle-compare" title="Compare 2 AI Models Side-by-Side">
                <span>🔀 Compare</span>
            </button>

            <!-- Mode Toggle (Dark/Light/System) -->
            <button class="btn-hdr-action" id="btn-quick-mode-toggle" title="Toggle Light / Dark / System Mode">
                <span id="quick-mode-icon">🌓 Mode</span>
            </button>
        </div>

        @auth
        <div style="padding:10px 14px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg-surface);flex-shrink:0">
            <div style="display:flex;align-items:center;gap:8px;overflow:hidden">
                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#a855f7);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <div style="font-size:12px;font-weight:700;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name }}</div>
                    <div style="font-size:10px;color:var(--text-muted)">{{ auth()->user()->isSuperAdmin() ? '👑 Super Admin' : '👤 Member' }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-ghost" style="padding:4px 8px;font-size:11px;color:var(--text-muted)" title="Log Out">
                    🚪 Logout
                </button>
            </form>
        </div>
        @endauth
    </aside>

        <!-- Compare Mode Toolbar -->
        <div class="compare-toolbar hidden" id="compare-toolbar">
            <div class="compare-col-select">
                <span class="compare-label">Model A:</span>
                <select id="compare-provider-a" class="compare-select">
                    <option value="openrouter" selected>OpenRouter (Free)</option>
                    <option value="openai">OpenAI (GPT-4o)</option>
                    <option value="claude">Claude (Sonnet 3.5)</option>
                    <option value="gemini">Gemini (2.0 Flash)</option>
                    <option value="ollama">Ollama (Local)</option>
                </select>
            </div>
            <div class="compare-vs-badge">⚡ VS ⚡</div>
            <div class="compare-col-select">
                <span class="compare-label">Model B:</span>
                <select id="compare-provider-b" class="compare-select">
                    <option value="ollama" selected>Ollama (Local Llama 3)</option>
                    <option value="gemini">Gemini (2.0 Flash)</option>
                    <option value="openrouter">OpenRouter (Free)</option>
                    <option value="openai">OpenAI (GPT-4o)</option>
                    <option value="claude">Claude (Sonnet 3.5)</option>
                </select>
            </div>
        </div>

        <div class="messages-area" id="messages-area">
            @if(!$conversation || $messages->isEmpty())
                <div class="empty-state" id="empty-state">
                    <div class="empty-state-icon">✨</div>
                    <h2>{{ \App\Models\Setting::get('app_welcome_heading', 'What can I help with?') }}</h2>
                    <p>{{ \App\Models\Setting::get('app_welcome_subheading', 'Start a conversation with your AI assistant. Choose a persona from the right panel or just type your question.') }}</p>
                    <div class="quick-starters">
                        <button class="quick-starter" data-prompt="Explain the concept of Docker containers and why they're useful for development.">🐳 Explain Docker containers</button>
                        <button class="quick-starter" data-prompt="Write a SQL query to find the top 10 customers by total order value.">📊 Write a SQL query</button>
                        <button class="quick-starter" data-prompt="Give me ideas for a travel itinerary in Japan for 7 days.">🌍 Plan a Japan trip</button>
                        <button class="quick-starter" data-prompt="Review this code snippet and suggest improvements for best practices.">🔍 Code review tips</button>
                    </div>
                </div>
            @else
                @foreach($messages as $msg)
                    @include('chat._message', ['msg' => $msg])
                @endforeach
            @endif
        </div>

        <div class="input-area">
            <!-- Sleek Floating Team Typing Bar -->
            <div id="team-typing-indicator" class="team-typing-bar hidden" style="display:none">
                <span class="typing-pulse-dot"></span>
                <span id="typing-users-text">Someone is typing...</span>
            </div>

            <div class="input-wrapper">
                <!-- Prompt Library Popover Menu -->
                <div class="prompt-popover hidden" id="prompt-library-popover">
                    <div class="prompt-popover-header">
                        <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700">
                            <span>⚡ Prompt Library</span>
                        </div>
                        <button type="button" class="btn btn-ghost" id="btn-open-new-prompt" style="font-size:11px;padding:3px 8px">
                            + Custom Prompt
                        </button>
                    </div>
                    <div class="prompt-popover-search">
                        <input type="text" id="prompt-search-input" placeholder="🔍 Search templates or type /..." autocomplete="off">
                    </div>
                    <div class="prompt-popover-list" id="prompt-popover-list">
                        <div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">Loading prompts...</div>
                    </div>
                </div>

                <div class="chat-input-wrapper">
                @if($userPermission === 'view')
                    <div style="background:rgba(234,179,8,0.15);border:1px solid rgba(234,179,8,0.3);color:#eab308;padding:14px 18px;border-radius:var(--radius-md);font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:8px">
                        <span>👁️ Read-Only Shared Chat — You have view-only access to this conversation.</span>
                    </div>
                @else
                    <div id="drop-overlay" class="drop-overlay hidden">
                        <div class="drop-message">
                            <span class="drop-icon">📎</span>
                            <span class="drop-text">Drop file to attach for RAG analysis</span>
                        </div>
                    </div>

                    <div class="input-toolbar" id="input-toolbar" style="display:none">
                        <div class="persona-chip active" id="active-persona-chip">
                            <span id="active-persona-icon"></span>
                            <span id="active-persona-name"></span>
                            <span class="remove-persona" id="remove-persona">✕</span>
                        </div>
                    </div>
                    <textarea
                        id="chat-textarea"
                        data-auto-resize
                        placeholder="Message your AI assistant... (Shift+Enter for new line)"
                        rows="1"
                    ></textarea>
                    <div class="input-footer">
                        <div style="display:flex;align-items:center;gap:6px">
                            <button type="button" class="btn-attach" id="btn-prompt-library" title="Prompt Library (⚡)" style="font-size:12px;padding:5px 10px">
                                <span>⚡ Prompts</span>
                            </button>
                            <button type="button" class="btn-attach" id="btn-enhance-prompt" title="Enhance & polish prompt with AI (✨)" style="font-size:12px;padding:5px 10px">
                                <span id="enhance-icon">✨</span>
                                <span id="enhance-label">Polish</span>
                            </button>
                            <button type="button" class="btn-attach" id="btn-mic" title="Voice Input (Mic)" style="font-size:12px;padding:5px 10px">
                                🎙️ <span id="mic-label">Mic</span>
                            </button>
                            <button type="button" class="btn-attach" id="btn-attach" title="Attach document for RAG analysis" style="font-size:12px;padding:5px 10px">
                                📎 Attach Doc
                            </button>
                            <input type="file" id="file-input" style="display:none"
                                accept=".pdf,.docx,.txt,.md,.php,.js,.ts,.py,.java,.go,.rb,.cs,.cpp,.c,.h,.html,.css,.json,.yaml,.yml,.xml,.sh,.sql,.csv,.jsx,.tsx,.vue,.rs,.env">
                        </div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <span class="input-hints" style="font-size:11px;color:var(--text-muted)">
                                <kbd>Enter</kbd> to send
                            </span>
                            <button class="btn-send" id="btn-send" style="padding:7px 18px;font-weight:700">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Send
                            </button>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </main>

    <!-- ===== RIGHT PANEL ===== -->
    <aside class="right-panel">
        <div class="right-panel-tabs">
            <button class="panel-tab active" data-tab="personas" title="AI Personas">🎭 Personas</button>
            <button class="panel-tab" data-tab="docs" title="RAG Documents">📎 Docs</button>
            <button class="panel-tab" data-tab="bookmarks" title="Saved Bookmarks">📌 Saved</button>
        </div>

        <div class="panel-body">
            <!-- TAB 1: PERSONAS -->
            <div class="tab-content active" id="tab-content-personas">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <div class="panel-title">AI Personas</div>
                    <a href="{{ route('personas.index') }}" style="font-size:11px;color:var(--accent-light);text-decoration:none;font-weight:500" title="Open Persona Studio">Studio ⚙️</a>
                </div>
                <div style="margin-bottom:10px;position:relative">
                    <input type="text" id="persona-search-input" placeholder="🔍 Search personas..." 
                           style="width:100%;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);padding:6px 10px;font-size:12px;outline:none;transition:var(--transition)"
                           autocomplete="off">
                </div>

                <div id="personas-panel">
                    @foreach($personas as $persona)
                        <div class="persona-card"
                             data-id="{{ $persona->id }}"
                             data-name="{{ $persona->name }}"
                             data-icon="{{ $persona->icon }}"
                             data-prompt="{{ $persona->system_prompt }}"
                             style="border-color: transparent;">
                            <div class="persona-card-icon" style="background: {{ $persona->color }}22; color: {{ $persona->color }}">
                                {!! $persona->formatted_icon !!}
                            </div>
                            <div>
                                <div class="persona-card-name">{{ $persona->name }}</div>
                                <div class="persona-card-hint">Click to use</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 2: DOCUMENTS (RAG) -->
            <div class="tab-content hidden" id="tab-content-docs">
                <div class="panel-title" style="margin-bottom:10px">📎 Attached Documents</div>
                <div id="documents-panel">
                    <div id="doc-list" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px"></div>
                    <div id="doc-upload-zone" class="doc-upload-zone {{ !$conversation ? 'disabled' : '' }}"
                         onclick="{{ $conversation ? 'document.getElementById(\'file-input\').click()' : '' }}">
                        <div style="font-size:20px">📄</div>
                        <div style="font-size:12px;font-weight:600;color:var(--text-secondary)">Drop a file or click to upload</div>
                        <div style="font-size:10px;color:var(--text-muted)">PDF · DOCX · TXT · MD · Code · CSV</div>
                        <div style="font-size:10px;color:var(--text-muted)">Max 20 MB</div>
                    </div>
                    <div id="doc-processing" style="display:none;text-align:center;padding:12px;font-size:12px;color:var(--text-muted)">
                        <div style="margin-bottom:6px">⏳ Processing document…</div>
                        <div style="font-size:10px">Chunking &amp; embedding — may take a moment</div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: BOOKMARKS -->
            <div class="tab-content hidden" id="tab-content-bookmarks">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <div class="panel-title">📌 Saved Bookmarks</div>
                    <span id="bookmark-count" style="font-size:11px;color:var(--text-muted)">0 saved</span>
                </div>
                <div id="bookmarks-list" style="display:flex;flex-direction:column;gap:8px">
                    <div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">No bookmarks saved yet.<br>Click ⭐ on any AI reply to save it!</div>
                </div>
            </div>

            <!-- Stats -->
            <div class="panel-header" style="padding:12px 0 8px; border-bottom: 1px solid var(--border);">
                <div class="panel-title">Session Stats</div>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Messages</div>
                    <div class="stat-value" id="stat-messages">{{ $messages->count() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Tokens</div>
                    <div class="stat-value" id="stat-tokens">{{ $conversation ? $messages->sum('tokens_used') : 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Provider</div>
                    <div class="stat-value" style="font-size:13px;margin-top:5px" id="stat-provider">{{ strtoupper($provider) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Model</div>
                    <div class="stat-value" style="font-size:11px;margin-top:5px;word-break:break-all" id="stat-model">{{ $conversation?->model ?? '—' }}</div>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Rename Modal -->
<div class="modal-overlay hidden" id="rename-modal">
    <div class="modal">
        <h3>Rename Conversation</h3>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" class="form-input" id="rename-input" placeholder="Conversation title" maxlength="255">
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="rename-cancel">Cancel</button>
            <button class="btn btn-primary" id="rename-save">Save</button>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay hidden" id="delete-modal">
    <div class="modal">
        <h3>Delete Conversation?</h3>
        <p style="color:var(--text-secondary);font-size:14px;line-height:1.6">This will permanently delete this conversation and all its messages. This action cannot be undone.</p>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="delete-cancel">Cancel</button>
            <button class="btn btn-danger" id="delete-confirm">Delete</button>
        </div>
    </div>
</div>

<!-- Custom Prompt Modal -->
<div class="modal-overlay hidden" id="modal-new-prompt">
    <div class="modal" style="max-width:480px">
        <h3>Save Custom Prompt</h3>
        <div class="form-group" style="margin-top:12px">
            <label class="form-label">Title</label>
            <input type="text" class="form-input" id="np-title" placeholder="e.g. Code Review Assistant" maxlength="100">
        </div>
        <div class="form-group">
            <label class="form-label">Category</label>
            <input type="text" class="form-input" id="np-category" placeholder="e.g. Coding, Writing, Custom" maxlength="50">
        </div>
        <div class="form-group">
            <label class="form-label">Prompt Content</label>
            <textarea class="form-textarea" id="np-content" rows="5" placeholder="Enter your reusable prompt text..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="np-cancel">Cancel</button>
            <button class="btn btn-primary" id="np-save">💾 Save to Library</button>
        </div>
    </div>
</div>

<!-- Interactive Share Modal -->
<div class="modal-overlay hidden" id="share-modal">
    <div class="modal" style="max-width:500px;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700">
                <span>🔗 Share Conversation</span>
            </div>
            <button class="btn btn-ghost" id="share-modal-close" style="font-size:14px;padding:2px 8px">✕</button>
        </div>

        <div style="margin-bottom:20px">
            <label style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px">
                Privacy & Access Mode
            </label>
            <div style="display:flex;flex-direction:column;gap:8px">
                <label style="display:flex;align-items:center;gap:10px;background:var(--bg-elevated);border:1px solid var(--border);padding:10px 14px;border-radius:var(--radius-md);cursor:pointer">
                    <input type="radio" name="share_visibility" value="private">
                    <div>
                        <div style="font-size:13px;font-weight:700">🔒 Private (Only Me)</div>
                        <div style="font-size:11px;color:var(--text-muted)">Only you can see and edit this chat.</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:10px;background:var(--bg-elevated);border:1px solid var(--border);padding:10px 14px;border-radius:var(--radius-md);cursor:pointer">
                    <input type="radio" name="share_visibility" value="team">
                    <div>
                        <div style="font-size:13px;font-weight:700">👥 Workspace Team</div>
                        <div style="font-size:11px;color:var(--text-muted)">All registered team members can view this chat.</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:10px;background:var(--bg-elevated);border:1px solid var(--border);padding:10px 14px;border-radius:var(--radius-md);cursor:pointer">
                    <input type="radio" name="share_visibility" value="link">
                    <div>
                        <div style="font-size:13px;font-weight:700">🔗 Anyone with Link</div>
                        <div style="font-size:11px;color:var(--text-muted)">Anyone with the share link can view read-only.</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:10px;background:var(--bg-elevated);border:1px solid var(--border);padding:10px 14px;border-radius:var(--radius-md);cursor:pointer">
                    <input type="radio" name="share_visibility" value="custom">
                    <div>
                        <div style="font-size:13px;font-weight:700">👤 Specific Team Members</div>
                        <div style="font-size:11px;color:var(--text-muted)">Choose specific members & permissions.</div>
                    </div>
                </label>
            </div>

            <!-- Custom Members Selection Box -->
            <div id="custom-members-box" style="display:none;margin-top:12px;max-height:160px;overflow-y:auto;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px">
                <div style="font-size:11px;font-weight:700;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase">Select Members to Share With</div>
                <div id="custom-members-list" style="display:flex;flex-direction:column;gap:8px"></div>
            </div>
        </div>

        <!-- Shareable Link Box -->
        <div style="margin-bottom:20px">
            <label style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px">
                Share Link
            </label>
            <div style="display:flex;gap:8px">
                <input type="text" id="share-link-input" readonly style="flex:1;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);padding:8px 12px;font-size:12px;outline:none">
                <button type="button" class="btn btn-primary" id="btn-copy-share-link" style="padding:8px 14px;font-size:12px">📋 Copy Link</button>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px">
            <button type="button" class="btn btn-ghost" id="share-modal-cancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="btn-save-share-settings" style="padding:8px 18px;font-size:13px">Save Changes</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ========== CONFIG ==========
let currentConversationId = {{ $conversation?->id ?? 'null' }};
let currentPersonaId      = null;
let activeConvForModal    = null;
let isTyping              = false;
let totalTokens           = {{ $conversation ? $messages->sum('tokens_used') : 0 }};
let msgCount              = {{ $messages->count() }};

// ========== MARKED CONFIG ==========
marked.setOptions({
    gfm: true,
    breaks: true,
    highlight: (code, lang) => {
        if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(code, { language: lang }).value;
        }
        return hljs.highlightAuto(code).value;
    }
});

// ========== RENDER MESSAGE ==========
function renderMessage(role, content, meta = {}) {
    const isUser     = role === 'user';
    const authorName = meta.author_name || (isUser ? 'You' : 'AI Assistant');
    const avatar     = isUser ? authorName.charAt(0).toUpperCase() : '🤖';
    const tokenBadge = meta.tokens ? `<span class="token-badge">${meta.tokens} tokens</span>` : '';
    const timeBadge  = meta.time   ? `<span class="token-badge">${typeof meta.time === 'number' ? formatTime(meta.time) : meta.time}</span>` : '';
    const modelBadge = meta.model  ? `<span class="token-badge">${meta.model}</span>` : '';

    const actionsHtml = isUser
        ? `<div class="msg-actions"><button class="msg-act-btn btn-msg-edit" title="Edit message" data-id="${meta.id || ''}">✏️ Edit</button></div>`
        : `<div class="msg-actions"><button class="msg-act-btn btn-msg-speak" title="Read AI response aloud">🔊 Speak</button><button class="msg-act-btn btn-msg-bookmark" title="Bookmark response" data-id="${meta.id || ''}">⭐ Bookmark</button><button class="msg-act-btn btn-msg-regen" title="Regenerate AI response">🔄 Regenerate</button><button class="msg-act-btn btn-msg-copy" title="Copy response">📋 Copy</button></div>`;

    const renderedContent = isUser
        ? `<div class="user-text">${escapeHtml(content).replace(/\n/g, '<br>')}</div>`
        : `<div class="md-content">${marked.parse(content)}</div>`;

    const el = document.createElement('div');
    el.className = `message ${role}`;
    if (meta.id) el.dataset.id = meta.id;
    el.innerHTML = `
        <div class="msg-avatar">${avatar}</div>
        <div class="msg-body">
            <div class="msg-meta">
                <strong>${escapeHtml(authorName)}</strong>
                ${modelBadge} ${tokenBadge} ${timeBadge}
                ${actionsHtml}
            </div>
            <div class="msg-bubble">${renderedContent}</div>
        </div>
    `;

    // Add copy buttons to code blocks
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

    return el;
}

function formatTime(ms) {
    return ms < 1000 ? `${ms}ms` : `${(ms/1000).toFixed(1)}s`;
}

function showTypingIndicator() {
    const el = document.createElement('div');
    el.className = 'typing-indicator';
    el.id = 'typing-indicator';
    el.innerHTML = `
        <div class="msg-avatar" style="background: linear-gradient(135deg,#1e293b,#0f172a);border:1px solid var(--border-strong);font-size:16px;">🤖</div>
        <div class="typing-dots"><span></span><span></span><span></span></div>
    `;
    getMessagesArea().appendChild(el);
    scrollToBottom();
}

function removeTypingIndicator() {
    document.getElementById('typing-indicator')?.remove();
}

function getMessagesArea() { return document.getElementById('messages-area'); }
function scrollToBottom() {
    const area = getMessagesArea();
    area.scrollTop = area.scrollHeight;
}

// ========== SEND MESSAGE ==========
async function sendMessage() {
    if (isTyping) return;
    const textarea = document.getElementById('chat-textarea');
    const msg = textarea.value.trim();
    if (!msg) return;

    // Auto-create conversation if none
    if (!currentConversationId) {
        try {
            const res = await api('{{ route("conversations.new") }}', { method: 'POST' });
            currentConversationId = res.conversation.id;
            document.getElementById('btn-send').disabled = false;
            addConversationToSidebar(res.conversation);
        } catch (e) {
            toast('Failed to create conversation: ' + e.message, 'error');
            return;
        }
    }

    // Clear empty state
    document.getElementById('empty-state')?.remove();

    textarea.value = '';
    textarea.style.height = 'auto';

    // Render user message (mark pending until server confirms ID)
    const userEl = renderMessage('user', msg);
    userEl.dataset.pending = 'true';
    getMessagesArea().appendChild(userEl);
    scrollToBottom();
    msgCount++;
    updateStats();

    // Set typing state
    isTyping = true;
    document.getElementById('btn-send').disabled = true;

    if (isCompareMode) {
        showTypingIndicator();
        const providerA = document.getElementById('compare-provider-a').value;
        const providerB = document.getElementById('compare-provider-b').value;

        try {
            const res = await api('/conversations/compare', {
                method: 'POST',
                body: JSON.stringify({
                    message: msg,
                    provider_a: providerA,
                    provider_b: providerB,
                    persona_id: currentPersonaId,
                }),
            });

            removeTypingIndicator();
            renderCompareResult(res);
            scrollToBottom();
        } catch (e) {
            removeTypingIndicator();
            toast('Compare failed: ' + e.message, 'error');
        } finally {
            isTyping = false;
            document.getElementById('btn-send').disabled = false;
        }
        return;
    }

    const body = { conversation_id: currentConversationId, message: msg };
    if (currentPersonaId) body.persona_id = currentPersonaId;

    // Get Persona info
    const activePersona = (typeof personas !== 'undefined') ? personas.find(p => p.id == currentPersonaId) : null;
    const aiAvatar = activePersona?.icon || '🤖';
    const aiName = activePersona?.name || 'AI Assistant';

    // 🌟 Render Assistant Chat Bubble FIRST with thinking dots inside the bubble
    const aiEl = document.createElement('div');
    aiEl.className = 'message assistant';
    aiEl.dataset.pending = 'true';
    aiEl.innerHTML = `
        <div class="msg-avatar" style="${activePersona?.icon ? 'background:rgba(108,99,255,0.15);border:1px solid rgba(108,99,255,0.3)' : ''}">${aiAvatar}</div>
        <div class="msg-body">
            <div class="msg-meta">
                <strong>${escapeHtml(aiName)}</strong>
                <span class="token-badge streaming-badge" style="color:var(--accent-light);font-weight:700">● Thinking…</span>
            </div>
            <div class="msg-bubble">
                <div class="md-content">
                    <div class="ai-thinking-placeholder">
                        <span class="thinking-dot"></span>
                        <span class="thinking-dot"></span>
                        <span class="thinking-dot"></span>
                    </div>
                </div>
            </div>
        </div>
    `;
    getMessagesArea().appendChild(aiEl);
    scrollToBottom();

    const mdContainer = aiEl.querySelector('.md-content');
    const streamingBadge = aiEl.querySelector('.streaming-badge');

    let fullText = '';
    let hasStartedStreaming = false;

    try {
        const response = await fetch('{{ route("conversations.stream") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify(body),
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';

            for (const line of lines) {
                const trimmed = line.trim();
                if (!trimmed.startsWith('data: ')) continue;
                const jsonStr = trimmed.substring(6).trim();
                if (!jsonStr) continue;

                try {
                    const data = JSON.parse(jsonStr);

                    if (data.chunk) {
                        if (!hasStartedStreaming) {
                            hasStartedStreaming = true;
                            if (streamingBadge) streamingBadge.textContent = '● Streaming…';
                        }
                        fullText += data.chunk;
                        if (mdContainer) {
                            mdContainer.innerHTML = marked.parse(fullText) + '<span class="streaming-cursor">▌</span>';
                        }
                        scrollToBottom();
                    }

                    if (data.done) {
                        if (mdContainer) {
                            mdContainer.innerHTML = marked.parse(fullText);
                        }
                        // Update metadata badges
                        const metaDiv = aiEl.querySelector('.msg-meta');
                        if (metaDiv) {
                            metaDiv.innerHTML = `
                                <strong>AI</strong>
                                <span class="token-badge">${escapeHtml(data.model)}</span>
                                ${data.tokens ? `<span class="token-badge">${data.tokens} tokens</span>` : ''}
                                <span class="token-badge">${formatTime(data.time_ms)}</span>
                                <div class="msg-actions">
                                    <button class="msg-act-btn btn-msg-regen" title="Regenerate AI response">🔄 Regenerate</button>
                                    <button class="msg-act-btn btn-msg-copy" title="Copy response">📋 Copy</button>
                                </div>
                            `;
                        }

                        // Add copy buttons to pre code blocks and syntax highlight
                        aiEl.querySelectorAll('pre').forEach(pre => {
                            if (!pre.querySelector('.copy-code-btn')) {
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
                            }
                        });
                        aiEl.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));

                        // RAG Sources Badge
                        if (data.rag_used && data.rag_sources?.length) {
                            const srcDiv = document.createElement('div');
                            srcDiv.className = 'rag-sources';
                            srcDiv.innerHTML = `
                                <div class="rag-sources-label">📎 Sources used:</div>
                                ${data.rag_sources.map(s =>
                                    `<span class="rag-source-chip">${escapeHtml(s.document)} <span class="rag-score">${s.score}%</span></span>`
                                ).join('')}
                            `;
                            aiEl.querySelector('.msg-bubble')?.appendChild(srcDiv);
                        }

                        // Stamp both bubbles with real DB IDs so heartbeat won't duplicate them
                        if (data.user_message_id) {
                            userEl.dataset.id = data.user_message_id;
                            delete userEl.dataset.pending;
                        }
                        if (data.message_id) {
                            aiEl.dataset.id = data.message_id;
                        }

                        // Update stats
                        if (data.tokens) totalTokens += data.tokens;
                        msgCount++;
                        updateStats(data.model);

                        if (data.title) {
                            updateConvTitle(currentConversationId, data.title);
                        }
                    }
                } catch (err) {
                    console.error('SSE JSON parse error', err);
                }
            }
        }
    } catch (e) {
        removeTypingIndicator();
        toast('Streaming failed: ' + e.message, 'error');
    } finally {
        isTyping = false;
        document.getElementById('btn-send').disabled = false;
        textarea.focus();
    }
}

// ========== STATS ==========
function updateStats(model = null) {
    document.getElementById('stat-messages').textContent = msgCount;
    document.getElementById('stat-tokens').textContent   = totalTokens || 0;
    if (model) document.getElementById('stat-model').textContent = model;
}

// ========== CONVERSATION LOADING ==========
async function loadConversation(id) {
    if (id === currentConversationId) return;

    try {
        const res = await api(`/conversations/${id}/messages`);
        currentConversationId = id;
        msgCount    = res.messages.length;
        totalTokens = res.messages.reduce((s, m) => s + (m.tokens_used || 0), 0);

        // Update active state
        document.querySelectorAll('.conv-item').forEach(el => el.classList.toggle('active', el.dataset.id == id));

        // Update header
        document.getElementById('chat-title').textContent = res.conversation.title;
        document.getElementById('btn-attach').disabled = false;
        document.getElementById('doc-upload-zone').classList.remove('disabled');
        document.getElementById('doc-upload-zone').onclick = () => document.getElementById('file-input').click();

        // Load documents for this conversation
        loadDocuments(id);
        document.getElementById('provider-label').textContent = res.conversation.provider?.toUpperCase() || 'AI';
        document.getElementById('model-label') && (document.getElementById('model-label').textContent = res.conversation.model || '');
        document.getElementById('btn-send').disabled = false;

        // Render messages
        const area = getMessagesArea();
        area.innerHTML = '';

        if (res.messages.length === 0) {
            area.innerHTML = `<div class="empty-state" id="empty-state">
                <div class="empty-state-icon">✨</div>
                <h2>Empty conversation</h2>
                <p>Start typing to begin this conversation.</p>
            </div>`;
        } else {
            res.messages.forEach(m => {
                const el = renderMessage(m.role, m.content, {
                    model: m.model, tokens: m.tokens_used, time: null, id: m.id
                });
                area.appendChild(el);
            });
            area.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
        }

        scrollToBottom();
        updateStats(res.conversation.model);
    } catch (e) {
        toast('Failed to load conversation: ' + e.message, 'error');
    }
}

// ========== SIDEBAR HELPERS ==========
function addConversationToSidebar(conv) {
    const list = document.getElementById('conversations-list');
    const label = list.querySelector('.conv-section-label');
    const el = createConvItem(conv, true);
    if (label) label.after(el); else list.prepend(el);
}

function createConvItem(conv, isActive = false) {
    const el = document.createElement('a');
    el.href = `/?conversation=${conv.id}`;
    el.className = `conv-item${isActive ? ' active' : ''}`;
    el.dataset.id = conv.id;
    el.style.textDecoration = 'none';
    el.style.color = 'inherit';
    el.style.display = 'flex';
    el.innerHTML = `
        <div class="conv-icon">💬</div>
        <div class="conv-info">
            <div class="conv-title">${escapeHtml(conv.title)}</div>
            <div class="conv-meta">Just now</div>
        </div>
        <div class="conv-actions">
            <button type="button" class="conv-action-btn" data-action="rename" title="Rename">✏️</button>
            <button type="button" class="conv-action-btn" data-action="pin" title="Pin">📌</button>
            <button type="button" class="conv-action-btn" data-action="delete" title="Delete">🗑️</button>
        </div>
    `;
    attachConvItemHandlers(el);
    return el;
}

function updateConvTitle(id, title) {
    const el = document.querySelector(`.conv-item[data-id="${id}"] .conv-title`);
    if (el) el.textContent = title;
    document.getElementById('chat-title').textContent = title;
}

// ========== NEW CHAT ==========
document.getElementById('btn-new-chat')?.addEventListener('click', (e) => {
    e.preventDefault();
    history.pushState(null, '', '/');
    currentConversationId = null;
    totalTokens = 0;
    msgCount    = 0;
    currentPersonaId = null;

    // Reset UI
    document.getElementById('chat-title').textContent = 'New Conversation';
    document.getElementById('btn-send').disabled = false;
    getMessagesArea().innerHTML = `<div class="empty-state" id="empty-state">
        <div class="empty-state-icon">✨</div>
        <h2>What can I help with?</h2>
        <p>Start a conversation with your AI assistant.</p>
        <div class="quick-starters">
            <button class="quick-starter" data-prompt="Explain the concept of Docker containers and why they're useful for development.">🐳 Explain Docker containers</button>
            <button class="quick-starter" data-prompt="Write a SQL query to find the top 10 customers by total order value.">📊 Write a SQL query</button>
            <button class="quick-starter" data-prompt="Give me ideas for a travel itinerary in Japan for 7 days.">🌍 Plan a Japan trip</button>
            <button class="quick-starter" data-prompt="Review this code snippet and suggest improvements for best practices.">🔍 Code review tips</button>
        </div>
    </div>`;
    updateStats();
    clearPersona();
    document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
    document.getElementById('chat-textarea').value = '';
    document.getElementById('chat-textarea').focus();
});

// ========== SEND HANDLERS ==========
document.getElementById('btn-send').addEventListener('click', sendMessage);

document.getElementById('chat-textarea').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// QUICK STARTER CLICK HANDLER
document.getElementById('messages-area').addEventListener('click', e => {
    const starter = e.target.closest('.quick-starter');
    if (starter) {
        const text = starter.dataset.prompt;
        const textarea = document.getElementById('chat-textarea');
        if (textarea) {
            textarea.value = text;
            sendMessage();
        }
    }
});

// ========== MESSAGE ACTION DELEGATION (EDIT, REGENERATE, COPY) ==========
document.getElementById('messages-area').addEventListener('click', async e => {
    // 📋 COPY MESSAGE
    const copyBtn = e.target.closest('.btn-msg-copy');
    if (copyBtn) {
        const msgEl = copyBtn.closest('.message');
        const text = msgEl.querySelector('.md-content')?.innerText || msgEl.querySelector('.msg-bubble')?.innerText || '';
        navigator.clipboard.writeText(text);
        copyBtn.textContent = 'Copied!';
        setTimeout(() => copyBtn.textContent = '📋 Copy', 2000);
        return;
    }

    // 🔄 REGENERATE RESPONSE
    const regenBtn = e.target.closest('.btn-msg-regen');
    if (regenBtn) {
        if (isTyping || !currentConversationId) return;
        isTyping = true;
        showTypingIndicator();
        try {
            const body = { persona_id: currentPersonaId };
            const res = await api(`/conversations/${currentConversationId}/regenerate`, {
                method: 'POST',
                body: JSON.stringify(body),
            });
            removeTypingIndicator();
            if (res.success) {
                await loadConversation(currentConversationId);
                toast('Response regenerated!', 'success');
            }
        } catch (err) {
            removeTypingIndicator();
            toast('Failed to regenerate: ' + err.message, 'error');
        } finally {
            isTyping = false;
        }
        return;
    }

    // ✏️ EDIT USER MESSAGE
    const editBtn = e.target.closest('.btn-msg-edit');
    if (editBtn) {
        const msgEl = editBtn.closest('.message');
        const msgId = msgEl.dataset.id || editBtn.dataset.id;
        if (!msgId) return;

        const currentText = msgEl.querySelector('.user-text')?.innerText || msgEl.querySelector('.msg-bubble')?.innerText || '';

        // Render inline edit box
        const bubble = msgEl.querySelector('.msg-bubble');
        bubble.innerHTML = `
            <div class="edit-box">
                <textarea class="edit-textarea" rows="2">${escapeHtml(currentText.trim())}</textarea>
                <div class="edit-btn-row">
                    <button class="btn btn-ghost btn-cancel-edit" style="font-size:11px;padding:4px 10px">Cancel</button>
                    <button class="btn btn-primary btn-save-edit" style="font-size:11px;padding:4px 10px">Save &amp; Submit</button>
                </div>
            </div>
        `;

        const textarea = bubble.querySelector('.edit-textarea');
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = textarea.value.length;

        bubble.querySelector('.btn-cancel-edit').onclick = () => {
            bubble.innerHTML = `<div class="user-text">${escapeHtml(currentText).replace(/\n/g, '<br>')}</div>`;
        };

        bubble.querySelector('.btn-save-edit').onclick = async () => {
            const newText = textarea.value.trim();
            if (!newText || isTyping) return;

            isTyping = true;
            showTypingIndicator();
            try {
                const body = { content: newText, persona_id: currentPersonaId };
                const res = await api(`/conversations/messages/${msgId}`, {
                    method: 'PATCH',
                    body: JSON.stringify(body),
                });

                removeTypingIndicator();
                if (res.success) {
                    await loadConversation(currentConversationId);
                    toast('Message updated!', 'success');
                }
            } catch (err) {
                removeTypingIndicator();
                toast('Failed to edit message: ' + err.message, 'error');
            } finally {
                isTyping = false;
            }
        };
    }
});

// ========== QUICK STARTERS ==========
document.addEventListener('click', e => {
    const qs = e.target.closest('.quick-starter');
    if (qs) {
        document.getElementById('chat-textarea').value = qs.dataset.prompt;
        document.getElementById('chat-textarea').dispatchEvent(new Event('input'));
        document.getElementById('chat-textarea').focus();
    }
});

// ========== PERSONAS ==========
document.querySelectorAll('.persona-card').forEach(card => {
    card.addEventListener('click', () => {
        const isSelected = card.classList.contains('selected');
        document.querySelectorAll('.persona-card').forEach(c => {
            c.classList.remove('selected');
            c.style.borderColor = 'transparent';
        });

        if (!isSelected) {
            card.classList.add('selected');
            card.style.borderColor = 'var(--accent)';
            currentPersonaId = card.dataset.id;

            // Show active persona chip
            const iconContainer = document.getElementById('active-persona-icon');
            if (iconContainer) iconContainer.innerHTML = renderPersonaIcon(card.dataset.icon);
            document.getElementById('active-persona-name').textContent = card.dataset.name;
            document.getElementById('input-toolbar').style.display = 'flex';
        } else {
            clearPersona();
        }
    });
});

function renderPersonaIcon(icon) {
    icon = (icon || '🤖').trim();
    if (icon.startsWith('http://') || icon.startsWith('https://') || icon.startsWith('/') || icon.startsWith('data:image/')) {
        return `<img src="${escapeHtml(icon)}" alt="icon" style="width:16px;height:16px;object-fit:cover;border-radius:2px;vertical-align:middle">`;
    }
    return escapeHtml(icon);
}

document.getElementById('remove-persona')?.addEventListener('click', e => {
    e.stopPropagation();
    clearPersona();
});

function clearPersona() {
    currentPersonaId = null;
    document.getElementById('input-toolbar').style.display = 'none';
    document.querySelectorAll('.persona-card').forEach(c => {
        c.classList.remove('selected');
        c.style.borderColor = 'transparent';
    });
}

// ========== CONVERSATION ITEM HANDLERS ==========
function attachConvItemHandlers(el) {
    el.addEventListener('click', e => {
        if (!e.target.closest('.conv-action-btn')) {
            e.preventDefault();
            const id = parseInt(el.dataset.id);
            history.pushState(null, '', `/?conversation=${id}`);
            loadConversation(id);
        }
    });

    el.querySelectorAll('.conv-action-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            const action = btn.dataset.action;
            const id = parseInt(el.dataset.id);

            if (action === 'rename') openRenameModal(id, el.querySelector('.conv-title').textContent);
            if (action === 'delete') openDeleteModal(id, el);
            if (action === 'pin')    togglePin(id, el);
        });
    });
}

document.querySelectorAll('.conv-item').forEach(attachConvItemHandlers);

// ========== RENAME ==========
function openRenameModal(id, currentTitle) {
    activeConvForModal = id;
    document.getElementById('rename-input').value = currentTitle;
    document.getElementById('rename-modal').classList.remove('hidden');
    document.getElementById('rename-input').focus();
    document.getElementById('rename-input').select();
}

document.getElementById('rename-cancel').addEventListener('click', () => {
    document.getElementById('rename-modal').classList.add('hidden');
});

document.getElementById('rename-save').addEventListener('click', async () => {
    const title = document.getElementById('rename-input').value.trim();
    if (!title) return;

    try {
        await api(`/conversations/${activeConvForModal}/rename`, {
            method: 'PATCH',
            body: JSON.stringify({ title }),
        });
        updateConvTitle(activeConvForModal, title);
        document.getElementById('rename-modal').classList.add('hidden');
        toast('Renamed successfully', 'success');
    } catch (e) {
        toast('Failed to rename: ' + e.message, 'error');
    }
});

// ========== DELETE ==========
let deleteTarget = null;
function openDeleteModal(id, el) {
    deleteTarget = { id, el };
    document.getElementById('delete-modal').classList.remove('hidden');
}

document.getElementById('delete-cancel').addEventListener('click', () => {
    document.getElementById('delete-modal').classList.add('hidden');
    deleteTarget = null;
});

document.getElementById('delete-confirm').addEventListener('click', async () => {
    if (!deleteTarget) return;
    try {
        await api(`/conversations/${deleteTarget.id}`, { method: 'DELETE' });
        deleteTarget.el.remove();
        document.getElementById('delete-modal').classList.add('hidden');
        toast('Conversation deleted', 'success');

        if (currentConversationId === deleteTarget.id) {
            document.getElementById('btn-new-chat').click();
        }
        deleteTarget = null;
    } catch (e) {
        toast('Failed to delete: ' + e.message, 'error');
    }
});

// ========== PIN ==========
async function togglePin(id, el) {
    try {
        const res = await api(`/conversations/${id}/pin`, { method: 'PATCH' });
        toast(res.is_pinned ? 'Conversation pinned' : 'Conversation unpinned', 'success');
        // Refresh page to re-sort
        setTimeout(() => location.reload(), 800);
    } catch (e) {
        toast('Failed to pin: ' + e.message, 'error');
    }
}

// ========== CLOSE MODALS ON OVERLAY CLICK ==========
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.add('hidden');
    });
});

// ========== DOCUMENT UPLOAD (RAG) ==========

async function loadDocuments(convId) {
    if (!convId) return;
    try {
        const res = await api(`/documents/conversation/${convId}`);
        const list = document.getElementById('doc-list');
        list.innerHTML = '';
        (res.documents || []).forEach(doc => {
            list.appendChild(renderDocCard(doc));
        });
    } catch (e) { /* silent */ }
}

function renderDocCard(doc) {
    const div = document.createElement('div');
    div.className = 'doc-card';
    div.dataset.docId = doc.id;
    div.innerHTML = `
        <div class="doc-card-icon">${doc.icon}</div>
        <div class="doc-card-body">
            <div class="doc-card-name" title="${doc.name}">${doc.name}</div>
            <div class="doc-card-meta">${doc.size} · ${doc.chunk_count} chunks</div>
        </div>
        <div class="doc-card-status" style="background:${doc.status_color}" title="${doc.status}"></div>
        <button class="doc-card-del" title="Remove document" data-id="${doc.id}">✕</button>
    `;
    div.querySelector('.doc-card-del').addEventListener('click', async (e) => {
        e.stopPropagation();
        if (!confirm(`Remove "${doc.name}" from this conversation?`)) return;
        try {
            await api(`/documents/${doc.id}`, { method: 'DELETE' });
            div.remove();
            toast('Document removed', 'success');
        } catch (err) {
            toast('Failed to remove: ' + err.message, 'error');
        }
    });
    return div;
}

async function uploadFile(file) {
    if (!currentConversationId) {
        try {
            const newRes = await api('{{ route("conversations.new") }}', { method: 'POST' });
            currentConversationId = newRes.conversation.id;
            addConversationToSidebar(newRes.conversation);
            history.pushState(null, '', `/?conversation=${newRes.conversation.id}`);
        } catch (e) {
            toast('Failed to initialize conversation for document attachment: ' + e.message, 'error');
            return;
        }
    }

    const zone = document.getElementById('doc-upload-zone');
    const proc = document.getElementById('doc-processing');

    zone.style.display = 'none';
    proc.style.display = 'block';

    const formData = new FormData();
    formData.append('file', file);
    formData.append('conversation_id', currentConversationId);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res = await fetch('/documents/upload', {
            method: 'POST',
            body: formData,
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Upload failed');

        const list = document.getElementById('doc-list');
        list.appendChild(renderDocCard(data.document));
        toast(`✅ "${data.document.name}" processed — ${data.document.chunk_count} chunks embedded!`, 'success', 5000);

    } catch (err) {
        toast('❌ ' + err.message, 'error', 6000);
    } finally {
        zone.style.display = '';
        proc.style.display = 'none';
    }
}

// File input trigger
document.getElementById('btn-attach').addEventListener('click', () => {
    document.getElementById('file-input').click();
});

document.getElementById('file-input').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) { uploadFile(file); e.target.value = ''; }
});

// Drag and drop on upload zone
const uploadZone = document.getElementById('doc-upload-zone');
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('drag-over');
});
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) uploadFile(file);
});

// Also allow drag-drop anywhere on the chat main area
document.querySelector('.chat-main').addEventListener('dragover', (e) => e.preventDefault());
document.querySelector('.chat-main').addEventListener('drop', (e) => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file) uploadFile(file);
});

// ========== COMPARE MODE HANDLERS ==========
let isCompareMode = false;
const btnToggleCompare = document.getElementById('btn-toggle-compare');
const compareToolbar   = document.getElementById('compare-toolbar');

btnToggleCompare?.addEventListener('click', () => {
    isCompareMode = !isCompareMode;
    btnToggleCompare.classList.toggle('active', isCompareMode);
    compareToolbar.classList.toggle('hidden', !isCompareMode);
    if (isCompareMode) {
        toast('🔀 Compare Mode active! Prompts will run on 2 models side-by-side.', 'info');
    }
});

function parseMarkdown(text) {
    if (!text) return '';
    return (typeof marked !== 'undefined') ? marked.parse(text) : escapeHtml(text).replace(/\n/g, '<br>');
}

function renderCompareResult(res) {
    const area = getMessagesArea();

    const speedA = res.a.time_ms || 0;
    const speedB = res.b.time_ms || 0;
    const isAFaster = speedA > 0 && (speedB === 0 || speedA <= speedB);
    const speedRatio = (speedB > 0 && speedA > 0) ? (Math.max(speedA, speedB) / Math.min(speedA, speedB)).toFixed(1) : 1;

    const grid = document.createElement('div');
    grid.className = 'compare-dual-grid';

    const textA = res.a.content || '';
    const textB = res.b.content || '';

    grid.innerHTML = `
        <div class="compare-pane">
            <div class="compare-pane-header">
                <div class="compare-pane-title">
                    <span>⚡ Model A (${escapeHtml(res.a.provider.toUpperCase())})</span>
                    ${isAFaster ? `<span class="compare-badge-fast">⚡ ${speedRatio}x Faster</span>` : ''}
                </div>
                <div style="font-size:11px;color:var(--text-muted)">${escapeHtml(res.a.model || '')}</div>
            </div>
            <div class="msg-content" style="flex:1">
                ${res.a.error 
                    ? `<div style="color:var(--danger);font-size:13px">❌ ${escapeHtml(res.a.error)}</div>`
                    : parseMarkdown(textA)
                }
            </div>
            <div class="compare-bench-bar">
                <span>⏱️ Latency: <strong>${formatTime(res.a.time_ms)}</strong></span>
                <span>📊 Tokens: <strong>${res.a.tokens || 'N/A'}</strong></span>
                <button class="btn btn-ghost btn-copy-a" style="font-size:11px;padding:2px 8px">📋 Copy A</button>
            </div>
        </div>

        <div class="compare-pane">
            <div class="compare-pane-header">
                <div class="compare-pane-title">
                    <span>⚡ Model B (${escapeHtml(res.b.provider.toUpperCase())})</span>
                    ${!isAFaster ? `<span class="compare-badge-fast">⚡ ${speedRatio}x Faster</span>` : ''}
                </div>
                <div style="font-size:11px;color:var(--text-muted)">${escapeHtml(res.b.model || '')}</div>
            </div>
            <div class="msg-content" style="flex:1">
                ${res.b.error 
                    ? `<div style="color:var(--danger);font-size:13px">❌ ${escapeHtml(res.b.error)}</div>`
                    : parseMarkdown(textB)
                }
            </div>
            <div class="compare-bench-bar">
                <span>⏱️ Latency: <strong>${formatTime(res.b.time_ms)}</strong></span>
                <span>📊 Tokens: <strong>${res.b.tokens || 'N/A'}</strong></span>
                <button class="btn btn-ghost btn-copy-b" style="font-size:11px;padding:2px 8px">📋 Copy B</button>
            </div>
        </div>
    `;

    grid.querySelector('.btn-copy-a')?.addEventListener('click', () => {
        navigator.clipboard.writeText(textA);
        toast('Copied Model A response!', 'success');
    });

    grid.querySelector('.btn-copy-b')?.addEventListener('click', () => {
        navigator.clipboard.writeText(textB);
        toast('Copied Model B response!', 'success');
    });

    area.appendChild(grid);
    grid.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
}

// ========== EXPORT CONVERSATION HANDLER ==========
const exportToggleBtn = document.getElementById('btn-export-toggle');
const exportMenu      = document.getElementById('export-menu');

exportToggleBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    exportMenu?.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('#export-dropdown-container')) {
        exportMenu?.classList.add('hidden');
    }
});

document.querySelectorAll('.export-item').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!currentConversationId) {
            toast('No active conversation to export', 'error');
            return;
        }
        const format = btn.dataset.format || 'md';
        exportMenu?.classList.add('hidden');
        if (format === 'pdf') {
            toast('Opening print view for PDF export...', 'info');
            window.open(`/conversations/${currentConversationId}/export?format=pdf`, '_blank');
        } else {
            toast(`Downloading chat export (.${format})...`, 'info');
            window.location.href = `/conversations/${currentConversationId}/export?format=${format}`;
        }
    });
});

// Quick Appearance Mode Toggle (Dark -> Light -> System -> Dark)
document.getElementById('btn-quick-mode-toggle')?.addEventListener('click', () => {
    const modes = ['dark', 'light', 'system'];
    const current = localStorage.getItem('app_mode') || 'dark';
    const nextIdx = (modes.indexOf(current) + 1) % modes.length;
    const nextMode = modes[nextIdx];

    localStorage.setItem('app_mode', nextMode);

    let effectiveMode = nextMode;
    if (nextMode === 'system') {
        effectiveMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-mode', effectiveMode);

    const icons = { dark: '🌙 Dark', light: '☀️ Light', system: '💻 System' };
    toast(`🌓 Appearance set to ${icons[nextMode]}!`, 'info');
});

// ========== PROMPT LIBRARY HANDLER ==========
let cachedPromptTemplates = [];

function getChatTextarea() {
    return document.getElementById('chat-textarea');
}

async function loadPromptTemplates() {
    try {
        const res = await api('/prompt-templates');
        cachedPromptTemplates = res.templates || [];
        renderPromptPopover(cachedPromptTemplates);
    } catch (e) {
        console.error(e);
    }
}

function renderPromptPopover(templates, filter = '') {
    const list = document.getElementById('prompt-popover-list');
    if (!list) return;

    const q = filter.toLowerCase().trim().replace(/^\//, '');
    const filtered = templates.filter(t => 
        !q || 
        t.title.toLowerCase().includes(q) || 
        (t.shortcut && t.shortcut.toLowerCase().includes(q)) || 
        t.content.toLowerCase().includes(q)
    );

    if (!filtered.length) {
        list.innerHTML = `<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">No templates found</div>`;
        return;
    }

    list.innerHTML = filtered.map(t => `
        <div class="prompt-item-card" data-id="${t.id}">
            <div class="prompt-item-header">
                <span class="prompt-item-title">${escapeHtml(t.title)}</span>
                ${t.shortcut ? `<span class="prompt-item-tag">/${escapeHtml(t.shortcut)}</span>` : ''}
            </div>
            <div class="prompt-item-preview">${escapeHtml(t.content)}</div>
        </div>
    `).join('');

    list.querySelectorAll('.prompt-item-card').forEach((card, idx) => {
        card.addEventListener('click', () => {
            const template = filtered[idx];
            insertPromptTemplate(template.content);
            closePromptPopover();
        });
    });
}

function autoResizeTextarea(el) {
    if (!el) return;
    el.style.height = 'auto';
    const maxH = 240;
    const newH = Math.max(24, Math.min(el.scrollHeight, maxH));
    el.style.height = newH + 'px';
    el.style.overflowY = el.scrollHeight > maxH ? 'auto' : 'hidden';
}

document.addEventListener('input', (e) => {
    if (e.target && e.target.id === 'chat-textarea') {
        autoResizeTextarea(e.target);
    }
});

document.addEventListener('keyup', (e) => {
    if (e.target && e.target.id === 'chat-textarea') {
        autoResizeTextarea(e.target);
    }
});

function insertPromptTemplate(content) {
    const area = getChatTextarea();
    if (!area) return;
    area.value = content;
    area.focus();
    autoResizeTextarea(area);
    const sendBtn = document.getElementById('btn-send');
    if (sendBtn) sendBtn.disabled = false;
}

function togglePromptPopover() {
    const popover = document.getElementById('prompt-library-popover');
    if (!popover) return;
    const isHidden = popover.classList.contains('hidden');
    if (isHidden) {
        popover.classList.remove('hidden');
        loadPromptTemplates();
        const searchInput = document.getElementById('prompt-search-input');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    } else {
        popover.classList.add('hidden');
    }
}

function closePromptPopover() {
    document.getElementById('prompt-library-popover')?.classList.add('hidden');
}

document.getElementById('btn-prompt-library')?.addEventListener('click', (e) => {
    e.stopPropagation();
    togglePromptPopover();
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('#prompt-library-popover') && !e.target.closest('#btn-prompt-library')) {
        closePromptPopover();
    }
});

document.getElementById('prompt-search-input')?.addEventListener('input', (e) => {
    renderPromptPopover(cachedPromptTemplates, e.target.value);
});

// Slash Command "/" trigger in chat textarea
const chatInput = getChatTextarea();
if (chatInput) {
    chatInput.addEventListener('keyup', (e) => {
        const val = chatInput.value;
        if (val.startsWith('/')) {
            const popover = document.getElementById('prompt-library-popover');
            if (popover && popover.classList.contains('hidden')) {
                popover.classList.remove('hidden');
                loadPromptTemplates();
            }
            renderPromptPopover(cachedPromptTemplates, val);
        } else if (val === '') {
            closePromptPopover();
        }
    });
}

// NEW CUSTOM PROMPT MODAL
document.getElementById('btn-open-new-prompt')?.addEventListener('click', () => {
    closePromptPopover();
    document.getElementById('np-title').value = '';
    document.getElementById('np-category').value = 'Custom';
    document.getElementById('np-content').value = getChatTextarea().value || '';
    document.getElementById('modal-new-prompt').classList.remove('hidden');
});

document.getElementById('np-cancel')?.addEventListener('click', () => {
    document.getElementById('modal-new-prompt').classList.add('hidden');
});

document.getElementById('np-save')?.addEventListener('click', async () => {
    const title    = document.getElementById('np-title').value.trim();
    const category = document.getElementById('np-category').value.trim() || 'Custom';
    const content  = document.getElementById('np-content').value.trim();

    if (!title || !content) {
        toast('Title and Content are required', 'error');
        return;
    }

    try {
        const res = await api('/prompt-templates', {
            method: 'POST',
            body: JSON.stringify({ title, category, content }),
        });
        toast('✅ Custom prompt saved to library!', 'success');
        document.getElementById('modal-new-prompt').classList.add('hidden');
        loadPromptTemplates();
    } catch (e) {
        toast('Failed: ' + e.message, 'error');
    }
});

// ========== PERSONA SIDEBAR SEARCH FILTER ==========
document.getElementById('persona-search-input')?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('#personas-panel .persona-card').forEach(card => {
        const name   = (card.dataset.name || '').toLowerCase();
        const prompt = (card.dataset.prompt || '').toLowerCase();
        const match  = !q || name.includes(q) || prompt.includes(q);
        card.style.display = match ? 'flex' : 'none';
    });
});


// ========== AI PROMPT ENHANCER HANDLER ==========
document.getElementById('btn-enhance-prompt')?.addEventListener('click', async () => {
    const input = getChatTextarea();
    const btn   = document.getElementById('btn-enhance-prompt');
    const label = document.getElementById('enhance-label');
    const icon  = document.getElementById('enhance-icon');

    const promptText = input?.value.trim();
    if (!promptText) {
        toast('Please enter a draft prompt to enhance first!', 'warning');
        return;
    }

    if (btn) btn.disabled = true;
    if (label) label.textContent = 'Enhancing...';
    if (icon) icon.textContent = '⏳';

    try {
        const res = await api('/conversations/enhance-prompt', {
            method: 'POST',
            body: JSON.stringify({ prompt: promptText }),
        });

        if (res.success && res.enhanced_prompt) {
            input.value = res.enhanced_prompt;
            input.dispatchEvent(new Event('input'));
            toast('✨ Prompt enhanced into detailed instructions!', 'success');
        } else {
            toast('Failed: ' + (res.error || 'Unknown error'), 'error');
        }
    } catch (e) {
        toast('Enhance failed: ' + e.message, 'error');
    } finally {
        if (btn) btn.disabled = false;
        if (label) label.textContent = 'Polish';
        if (icon) icon.textContent = '✨';
    }
});


// ========== VOICE INPUT (SPEECH-TO-TEXT) ==========
const btnMic   = document.getElementById('btn-mic');
const micLabel = document.getElementById('mic-label');
let recognition = null;
let isRecording = false;

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;

    recognition.onstart = () => {
        isRecording = true;
        btnMic?.classList.add('recording');
        if (micLabel) micLabel.textContent = 'Listening...';
        toast('🎙️ Voice dictation active... Speak now!', 'info');
    };

    recognition.onresult = (e) => {
        let transcript = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            transcript += e.results[i][0].transcript;
        }
        const input = getChatTextarea();
        if (input && transcript) {
            input.value = transcript;
            input.dispatchEvent(new Event('input'));
        }
    };

    recognition.onerror = (e) => {
        isRecording = false;
        btnMic?.classList.remove('recording');
        if (micLabel) micLabel.textContent = 'Mic';
        if (e.error !== 'no-speech') {
            toast('Mic error: ' + e.error, 'error');
        }
    };

    recognition.onend = () => {
        isRecording = false;
        btnMic?.classList.remove('recording');
        if (micLabel) micLabel.textContent = 'Mic';
    };

    btnMic?.addEventListener('click', () => {
        if (!isRecording) {
            try {
                recognition.start();
            } catch (err) {
                toast('Mic start failed: ' + err.message, 'error');
            }
        } else {
            recognition.stop();
        }
    });
} else {
    btnMic?.addEventListener('click', () => {
        toast('Speech Recognition not supported in this browser. Try Chrome/Edge/Safari.', 'warning');
    });
}


// ========== TEXT-TO-SPEECH (TTS VOICE READER) ==========
let currentUtterance = null;
let speakingBtn = null;

document.addEventListener('click', (e) => {
    const speakBtn = e.target.closest('.btn-msg-speak');
    if (!speakBtn) return;

    const msgEl = speakBtn.closest('.message');
    if (!msgEl) return;

    const mdContent = msgEl.querySelector('.md-content');
    if (!mdContent) return;

    // If currently speaking this message -> STOP
    if (window.speechSynthesis && window.speechSynthesis.speaking && speakingBtn === speakBtn) {
        window.speechSynthesis.cancel();
        speakBtn.textContent = '🔊 Speak';
        speakingBtn = null;
        toast('Stopped audio playback', 'info');
        return;
    }

    // Stop any previous speech
    if (window.speechSynthesis) window.speechSynthesis.cancel();
    if (speakingBtn) {
        speakingBtn.textContent = '🔊 Speak';
    }

    // Extract text and strip code blocks / HTML / markdown formatting
    let textToRead = mdContent.innerText || mdContent.textContent || '';
    textToRead = textToRead
        .replace(/```[\s\S]*?```/g, 'Code snippet omitted.')
        .replace(/`[^`]+`/g, '')
        .replace(/[#*_~>]/g, '')
        .trim();

    if (!textToRead) {
        toast('No readable text in message', 'warning');
        return;
    }

    if (!window.speechSynthesis) {
        toast('Speech Synthesis not supported in this browser', 'warning');
        return;
    }

    const utterance = new SpeechSynthesisUtterance(textToRead);
    utterance.rate = 1.0;
    utterance.pitch = 1.0;

    utterance.onstart = () => {
        speakBtn.textContent = '⏹️ Stop';
        speakingBtn = speakBtn;
        toast('🔊 Reading response aloud...', 'info');
    };

    utterance.onend = () => {
        speakBtn.textContent = '🔊 Speak';
        speakingBtn = null;
    };

    utterance.onerror = () => {
        speakBtn.textContent = '🔊 Speak';
        speakingBtn = null;
    };

    currentUtterance = utterance;
    window.speechSynthesis.speak(utterance);
});


// ========== RIGHT PANEL TAB SWITCHING ==========
document.querySelectorAll('.panel-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        document.querySelectorAll('.panel-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById(`tab-content-${target}`)?.classList.remove('hidden');

        if (target === 'bookmarks') {
            loadBookmarks();
        }
    });
});

// ========== BOOKMARKS HANDLER ==========
async function loadBookmarks() {
    const list = document.getElementById('bookmarks-list');
    const countEl = document.getElementById('bookmark-count');
    if (!list) return;

    try {
        const res = await api('/bookmarks');
        const bookmarks = res.bookmarks || [];
        if (countEl) countEl.textContent = `${bookmarks.length} saved`;

        if (!bookmarks.length) {
            list.innerHTML = `<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">No bookmarks saved yet.<br>Click ⭐ on any AI reply to save it!</div>`;
            return;
        }

        list.innerHTML = bookmarks.map(b => `
            <div class="bookmark-card" data-id="${b.id}">
                <div class="bookmark-card-header">
                    <span class="bookmark-card-title">${escapeHtml(b.title)}</span>
                    <button class="bookmark-del-btn" title="Remove bookmark" data-id="${b.id}">✕</button>
                </div>
                <div class="bookmark-card-snippet">${escapeHtml(b.content.substring(0, 120))}${b.content.length > 120 ? '...' : ''}</div>
                <div class="bookmark-card-footer">
                    <span style="font-size:10px;color:var(--text-muted)">${b.created_at}</span>
                    <button class="bookmark-copy-btn" data-content="${escapeHtml(b.content)}">📋 Copy</button>
                </div>
            </div>
        `).join('');

        // 1-Click Navigate to Chat Message or Insert Text
        list.querySelectorAll('.bookmark-card').forEach((card, idx) => {
            const b = bookmarks[idx];
            card.style.cursor = 'pointer';

            card.addEventListener('click', (e) => {
                if (e.target.closest('.bookmark-del-btn') || e.target.closest('.bookmark-copy-btn')) return;

                if (b.conversation_id) {
                    const targetUrl = `/?conversation=${b.conversation_id}` + (b.message_id ? `#message-${b.message_id}` : '');
                    if (window.location.search.includes(`conversation=${b.conversation_id}`)) {
                        if (b.message_id) {
                            const msgEl = document.querySelector(`.message[data-id="${b.message_id}"]`);
                            if (msgEl) {
                                msgEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                msgEl.classList.add('highlight-message');
                                setTimeout(() => msgEl.classList.remove('highlight-message'), 3500);
                                toast('Scrolled to bookmarked message', 'info');
                            } else {
                                toast('Opened bookmarked conversation', 'info');
                            }
                        }
                    } else {
                        toast('Opening bookmarked conversation...', 'info');
                        window.location.href = targetUrl;
                    }
                } else {
                    insertPromptTemplate(b.content);
                    toast('Inserted bookmark text into prompt bar!', 'info');
                }
            });
        });

        // Copy bookmark snippet
        list.querySelectorAll('.bookmark-copy-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                navigator.clipboard.writeText(btn.dataset.content || '');
                toast('Copied bookmark to clipboard!', 'success');
            });
        });

        // Delete bookmark
        list.querySelectorAll('.bookmark-del-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const id = btn.dataset.id;
                try {
                    await api(`/bookmarks/${id}`, { method: 'DELETE' });
                    toast('Bookmark removed', 'info');
                    loadBookmarks();
                } catch (err) {
                    toast('Failed: ' + err.message, 'error');
                }
            });
        });

    } catch (e) {
        console.error('Load bookmarks failed:', e);
    }
}

// Global click delegation for ⭐ Bookmark button on AI messages
document.addEventListener('click', async (e) => {
    const bookmarkBtn = e.target.closest('.btn-msg-bookmark');
    if (!bookmarkBtn) return;

    const msgEl = bookmarkBtn.closest('.message');
    if (!msgEl) return;

    const mdContent = msgEl.querySelector('.md-content') || msgEl.querySelector('.user-text');
    const contentText = mdContent?.innerText || mdContent?.textContent || '';

    if (!contentText.trim()) {
        toast('Nothing to bookmark', 'warning');
        return;
    }

    const messageId = bookmarkBtn.dataset.id || null;

    try {
        bookmarkBtn.disabled = true;
        bookmarkBtn.textContent = '⭐ Saving...';

        await api('/bookmarks', {
            method: 'POST',
            body: JSON.stringify({
                content: contentText.trim(),
                conversation_id: currentConversationId || null,
                message_id: messageId ? parseInt(messageId) : null,
            }),
        });

        toast('📌 Message saved to Bookmarks!', 'success');
        bookmarkBtn.textContent = '⭐ Saved';
        loadBookmarks();
    } catch (err) {
        toast('Bookmark failed: ' + err.message, 'error');
        bookmarkBtn.textContent = '⭐ Bookmark';
        bookmarkBtn.disabled = false;
    }
});

// ========== SHARE MODAL HANDLER ==========
let availableShareUsers = [];

document.addEventListener('change', e => {
    if (e.target.name === 'share_visibility') {
        const customBox = document.getElementById('custom-members-box');
        if (customBox) {
            customBox.style.display = (e.target.value === 'custom') ? 'block' : 'none';
        }
    }
});

document.addEventListener('click', async (e) => {
    const shareBtn = e.target.closest('#btn-open-share-modal');
    if (!shareBtn) return;

    if (!currentConversationId) {
        toast('No active conversation to share', 'warning');
        return;
    }

    try {
        const res = await api(`/conversations/${currentConversationId}/share`);
        if (res.success) {
            const shareModal = document.getElementById('share-modal');
            if (shareModal) shareModal.classList.remove('hidden');

            availableShareUsers = res.users || [];

            document.querySelectorAll('input[name="share_visibility"]').forEach(r => {
                r.checked = r.value === res.visibility;
            });

            const customBox = document.getElementById('custom-members-box');
            if (customBox) {
                customBox.style.display = (res.visibility === 'custom') ? 'block' : 'none';
            }

            const inputLink = document.getElementById('share-link-input');
            if (inputLink) inputLink.value = res.share_url;

            // Render members list
            const membersList = document.getElementById('custom-members-list');
            if (membersList) {
                if (availableShareUsers.length === 0) {
                    membersList.innerHTML = '<div style="font-size:12px;color:var(--text-muted)">No other team members found.</div>';
                } else {
                    membersList.innerHTML = availableShareUsers.map(u => `
                        <div style="display:flex;align-items:center;justify-content:space-between;background:var(--bg-surface);border:1px solid var(--border);padding:8px 12px;border-radius:var(--radius-md)">
                            <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer">
                                <input type="checkbox" class="share-user-check" data-user-id="${u.id}" ${u.permission ? 'checked' : ''}>
                                <div>
                                    <div style="font-weight:700">${escapeHtml(u.name)}</div>
                                    <div style="font-size:10px;color:var(--text-muted)">${escapeHtml(u.email)}</div>
                                </div>
                            </label>
                            <select class="share-user-perm" data-user-id="${u.id}" style="background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-primary);padding:3px 6px;border-radius:4px;font-size:11px">
                                <option value="view" ${u.permission === 'view' || !u.permission ? 'selected' : ''}>👁️ View Only</option>
                                <option value="edit" ${u.permission === 'edit' ? 'selected' : ''}>✏️ Can Edit</option>
                            </select>
                        </div>
                    `).join('');
                }
            }
        }
    } catch (err) {
        toast('Share error: ' + err.message, 'error');
    }
});

document.addEventListener('click', e => {
    if (e.target.closest('#share-modal-close') || e.target.closest('#share-modal-cancel')) {
        document.getElementById('share-modal')?.classList.add('hidden');
    }
});

document.getElementById('btn-copy-share-link')?.addEventListener('click', () => {
    const input = document.getElementById('share-link-input');
    if (input) {
        navigator.clipboard.writeText(input.value);
        toast('📋 Share link copied to clipboard!', 'success');
    }
});

document.getElementById('btn-save-share-settings')?.addEventListener('click', async () => {
    const btnSaveShare = document.getElementById('btn-save-share-settings');
    const visibility = document.querySelector('input[name="share_visibility"]:checked')?.value || 'private';

    const shares = [];
    if (visibility === 'custom') {
        document.querySelectorAll('.share-user-check:checked').forEach(chk => {
            const userId = parseInt(chk.dataset.userId);
            const permSelect = document.querySelector(`.share-user-perm[data-user-id="${userId}"]`);
            shares.push({
                user_id: userId,
                permission: permSelect?.value || 'view',
            });
        });
    }

    try {
        if (btnSaveShare) {
            btnSaveShare.disabled = true;
            btnSaveShare.textContent = 'Saving...';
        }

        const res = await api(`/conversations/${currentConversationId}/share`, {
            method: 'POST',
            body: JSON.stringify({ visibility, shares }),
        });

        if (res.success) {
            toast('🔗 Share settings updated!', 'success');
            document.getElementById('share-modal')?.classList.add('hidden');
            location.reload();
        }
    } catch (err) {
        toast('Failed to save share settings: ' + err.message, 'error');
    } finally {
        if (btnSaveShare) {
            btnSaveShare.disabled = false;
            btnSaveShare.textContent = 'Save Changes';
        }
    }
});

// ========== REAL-TIME PRESENCE & COLLABORATION HEARTBEAT ==========
let isUserTyping = false;
let typingTimeout = null;

document.getElementById('chat-textarea')?.addEventListener('input', (e) => {
    if (e.target.value.trim().length > 0) {
        if (!isUserTyping) {
            isUserTyping = true;
            sendPresenceHeartbeat();
        }
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isUserTyping = false;
            sendPresenceHeartbeat();
        }, 5000);
    } else {
        isUserTyping = false;
        sendPresenceHeartbeat();
    }
});

async function sendPresenceHeartbeat() {
    if (!currentConversationId) return;

    try {
        await api('/presence/heartbeat', {
            method: 'POST',
            body: JSON.stringify({
                conversation_id: currentConversationId,
                is_typing: isUserTyping
            })
        });

        const res = await api(`/presence/${currentConversationId}`);
        if (res.success) {
            const bar = document.getElementById('presence-online-bar');
            if (bar && res.online_users) {
                bar.innerHTML = res.online_users.map(u => `
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:99px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:var(--success);font-size:11px;font-weight:600" title="${escapeHtml(u.email)} is viewing this chat">
                        <span style="width:6px;height:6px;border-radius:50%;background:var(--success);display:inline-block"></span>
                        ${escapeHtml(u.name)}
                    </span>
                `).join('');
            }

            const typingIndicator = document.getElementById('team-typing-indicator');
            const typingText = document.getElementById('typing-users-text');
            if (typingIndicator && typingText) {
                if (res.typing_users && Array.isArray(res.typing_users) && res.typing_users.length > 0) {
                    const names = res.typing_users;
                    let text = '';
                    if (names.length === 1) {
                        text = `✍️ ${names[0]} is typing...`;
                    } else if (names.length === 2) {
                        text = `✍️ ${names[0]} and ${names[1]} are typing...`;
                    } else {
                        text = `✍️ ${names[0]} and ${names.length - 1} others are typing...`;
                    }
                    typingText.textContent = text;
                    typingIndicator.style.display = 'inline-flex';
                    typingIndicator.classList.remove('hidden');
                } else {
                    typingIndicator.style.display = 'none';
                    typingIndicator.classList.add('hidden');
                }
            }

            // Real-Time Message Auto-Sync (Append new messages without page refresh!)
            if (res.messages && Array.isArray(res.messages)) {
                const messagesArea = document.getElementById('messages-area');
                let hasNewMessage = false;

                // Skip sync entirely while a stream is in-flight (pending bubbles present)
                const hasPending = messagesArea?.querySelector('.message[data-pending]');
                if (!hasPending) {
                    res.messages.forEach(msg => {
                        const existing = document.querySelector(`.message[data-id="${msg.id}"]`);
                        if (!existing && messagesArea) {
                            hasNewMessage = true;
                            const msgEl = renderMessage(msg.role, msg.content, {
                                id: msg.id,
                                model: msg.model,
                                tokens: msg.tokens_used,
                                time: msg.response_time_ms || msg.created_at,
                                author_name: msg.author_name
                            });

                            messagesArea.appendChild(msgEl);
                            document.getElementById('empty-state')?.remove();
                        }
                    });
                }

                if (hasNewMessage && messagesArea) {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                    document.querySelectorAll('.md-content').forEach(el => {
                        if (!el.dataset.parsed) {
                            el.innerHTML = marked.parse(el.textContent);
                            el.dataset.parsed = 'true';
                            el.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
                        }
                    });
                }
            }
        }
    } catch (err) {
        console.warn('Presence heartbeat:', err);
    }
}

setInterval(sendPresenceHeartbeat, 2500);
setTimeout(sendPresenceHeartbeat, 300);

// Auto-highlight message if URL hash is #message-ID
window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash && window.location.hash.startsWith('#message-')) {
        const msgId = window.location.hash.replace('#message-', '');
        setTimeout(() => {
            const msgEl = document.querySelector(`.message[data-id="${msgId}"]`);
            if (msgEl) {
                msgEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                msgEl.classList.add('highlight-message');
                setTimeout(() => msgEl.classList.remove('highlight-message'), 3500);
            }
        }, 400);
    }
});

</script>
@endpush
@endsection


@push('head')
<style>
/* Right Panel Tabs */
.right-panel-tabs {
    display: flex;
    align-items: center;
    border-bottom: 1px solid var(--border);
    background: var(--bg-surface);
    padding: 0 4px;
}
.panel-tab {
    flex: 1;
    padding: 12px 4px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
}
.panel-tab:hover {
    color: var(--text-primary);
}
.panel-tab.active {
    color: var(--accent-light);
    border-bottom-color: var(--accent);
    background: rgba(108,99,255,0.06);
}

.tab-content {
    display: block;
}
.tab-content.hidden {
    display: none !important;
}

/* Highlight pulse for navigated message */
@keyframes highlight-message-pulse {
    0% { box-shadow: 0 0 0 2px var(--accent), 0 0 25px var(--accent-glow); }
    50% { box-shadow: 0 0 0 4px var(--accent), 0 0 40px var(--accent-glow); }
    100% { box-shadow: none; }
}
.highlight-message {
    animation: highlight-message-pulse 2.5s ease-out;
    border-radius: var(--radius-lg);
}

/* Bookmark Cards */
.bookmark-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 10px 12px;
    transition: var(--transition);
}
.bookmark-card:hover {
    border-color: var(--accent);
}
.bookmark-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.bookmark-card-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 170px;
}
.bookmark-del-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: var(--radius-sm);
}
.bookmark-del-btn:hover {
    color: var(--danger);
    background: rgba(248,113,113,0.15);
}
.bookmark-card-snippet {
    font-size: 11px;
    color: var(--text-secondary);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bookmark-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 6px;
    padding-top: 4px;
    border-top: 1px solid var(--border);
}
.bookmark-copy-btn {
    background: none;
    border: none;
    color: var(--accent-light);
    font-size: 10.5px;
    font-weight: 600;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: var(--radius-sm);
}
.bookmark-copy-btn:hover {
    background: var(--bg-hover);
}

/* Attach button */
.btn-attach {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    color: var(--text-secondary);
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    font-family: var(--font-sans);
    white-space: nowrap;
}
.btn-attach:hover {
    border-color: var(--accent);
    color: var(--text-primary);
}
.btn-attach.recording {
    background: rgba(244, 63, 94, 0.15) !important;
    border-color: #f43f5e !important;
    color: #f43f5e !important;
    animation: mic-pulse 1.2s infinite ease-in-out;
}
@keyframes mic-pulse {
    0%, 100% { box-shadow: 0 0 0px rgba(244,63,94,0); }
    50% { box-shadow: 0 0 12px rgba(244,63,94,0.6); }
}
.btn-attach:disabled { opacity: 0.4; cursor: not-allowed; }

/* Compare Mode */
.btn-compare {
    display: flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: var(--radius-md);
    background: var(--bg-elevated); border: 1px solid var(--border);
    color: var(--text-secondary); font-size: 12px; font-weight: 600;
    cursor: pointer; transition: var(--transition);
    font-family: var(--font-sans); margin-left: 6px;
}
.btn-compare:hover, .btn-compare.active {
    background: rgba(108,99,255,0.18);
    color: var(--accent-light);
    border-color: rgba(108,99,255,0.4);
    box-shadow: 0 0 15px var(--accent-glow);
}

.compare-toolbar {
    display: flex; align-items: center; justify-content: center; gap: 16px;
    padding: 10px 20px; background: rgba(15,23,42,0.8);
    border-bottom: 1px solid var(--border); backdrop-filter: blur(12px);
    animation: fadeIn 0.2s ease;
}
.compare-toolbar.hidden { display: none; }
.compare-col-select { display: flex; align-items: center; gap: 8px; }
.compare-label { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
.compare-select {
    background: var(--bg-elevated); border: 1px solid var(--border);
    color: var(--text-primary); border-radius: var(--radius-sm);
    padding: 4px 10px; font-size: 12px; font-weight: 500; outline: none;
    font-family: var(--font-sans); cursor: pointer;
}
.compare-vs-badge {
    font-size: 11px; font-weight: 800; color: #fbbf24;
    background: rgba(251,191,36,0.12); padding: 3px 10px;
    border-radius: 99px; border: 1px solid rgba(251,191,36,0.3);
    letter-spacing: 0.5px;
}

.compare-dual-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    width: 100%;
    margin-top: 10px;
}
.compare-pane {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: var(--transition);
}
.compare-pane:hover {
    border-color: var(--border-strong);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.compare-pane-header {
    display: flex; align-items: center; justify-content: space-between;
    padding-bottom: 10px; margin-bottom: 10px;
    border-bottom: 1px solid var(--border);
}
.compare-pane-title {
    font-size: 13px; font-weight: 700; color: var(--text-primary);
    display: flex; align-items: center; gap: 6px;
}
.compare-bench-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border);
    font-size: 11px; color: var(--text-muted);
}
.compare-badge-fast {
    background: rgba(74,222,128,0.15); color: #4ade80;
    padding: 2px 7px; border-radius: 99px; font-weight: 600;
    border: 1px solid rgba(74,222,128,0.3);
}

/* Upload zone */
.doc-upload-zone {
    border: 2px dashed var(--border-strong);
    border-radius: var(--radius-md);
    padding: 16px 10px;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.doc-upload-zone:hover { border-color: var(--accent); background: rgba(108,99,255,0.05); }
.doc-upload-zone.drag-over { border-color: var(--accent); background: rgba(108,99,255,0.1); }
.doc-upload-zone.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }

/* Prompt Library Popover */
.prompt-popover {
    position: absolute;
    bottom: calc(100% + 12px);
    left: 20px;
    width: 400px;
    max-width: calc(100vw - 40px);
    background: var(--bg-surface);
    border: 1px solid var(--border-strong);
    border-radius: var(--radius-lg);
    box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 30px var(--accent-glow);
    z-index: 120;
    overflow: hidden;
    animation: scaleIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.prompt-popover.hidden { display: none; }
.prompt-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-elevated);
}
.prompt-popover-search {
    padding: 8px 12px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-base);
}
.prompt-popover-search input {
    width: 100%;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    padding: 6px 10px;
    font-size: 12px;
    outline: none;
    font-family: var(--font-sans);
}
.prompt-popover-list {
    max-height: 280px;
    overflow-y: auto;
    padding: 6px;
}
.prompt-item-card {
    padding: 10px 12px;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid rgba(255,255,255,0.03);
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.prompt-item-card:hover {
    background: var(--bg-hover);
}
.prompt-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.prompt-item-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
.prompt-item-tag {
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 99px;
    background: rgba(108,99,255,0.15);
    color: var(--accent-light);
    font-family: var(--font-mono);
}
.prompt-item-preview {
    font-size: 11.5px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Document card */
.doc-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 12px;
    position: relative;
}
.doc-card-icon { font-size: 16px; flex-shrink: 0; }
.doc-card-body { flex: 1; min-width: 0; }
.doc-card-name { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; }
.doc-card-meta { font-size: 10px; color: var(--text-muted); margin-top: 1px; }
.doc-card-status { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.doc-card-del {
    background: none; border: none; color: var(--text-muted); cursor: pointer;
    font-size: 14px; padding: 0 2px; line-height: 1; flex-shrink: 0;
    transition: color 0.15s;
}
.doc-card-del:hover { color: var(--danger); }

/* Export Dropdown */
.export-dropdown { position: relative; margin-left: 8px; }
.btn-export {
    display: flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: var(--radius-md);
    background: var(--bg-elevated); border: 1px solid var(--border);
    color: var(--text-secondary); font-size: 12px; font-weight: 600;
    cursor: pointer; transition: var(--transition);
    font-family: var(--font-sans);
}
.btn-export:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-strong); }
.export-menu {
    position: absolute; right: 0; top: 100%; margin-top: 6px;
    background: var(--bg-surface); border: 1px solid var(--border-strong);
    border-radius: var(--radius-md); box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    z-index: 100; min-width: 175px; padding: 5px; display: flex; flex-direction: column; gap: 2px;
}
.export-menu.hidden { display: none; }
.export-item {
    background: none; border: none; padding: 7px 12px;
    font-size: 12px; font-weight: 500; color: var(--text-secondary);
    border-radius: var(--radius-sm); text-align: left; cursor: pointer; transition: var(--transition);
    font-family: var(--font-sans); width: 100%;
}
.export-item:hover { background: var(--bg-hover); color: var(--text-primary); }

/* RAG sources */
.rag-sources {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--border);
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.rag-sources-label { font-size: 11px; color: var(--text-muted); width: 100%; margin-bottom: 2px; }
.rag-source-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: rgba(108,99,255,0.12);
    border: 1px solid rgba(108,99,255,0.3);
    border-radius: 99px;
    font-size: 11px;
    color: var(--accent-light);
}
/* Command Palette Search Modal */
.search-cmd-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    color: var(--text-muted);
}
.search-cmd-header input {
    flex: 1;
    background: none;
    border: none;
    color: var(--text-primary);
    font-size: 15px;
    font-family: var(--font-sans);
    outline: none;
}
.search-cmd-esc {
    font-size: 11px;
    background: var(--bg-active);
    border: 1px solid var(--border);
    padding: 2px 6px;
    border-radius: 4px;
    color: var(--text-muted);
}
.search-cmd-results {
    max-height: 380px;
    overflow-y: auto;
    padding: 8px 0;
}
.search-cmd-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    color: inherit;
}
.search-cmd-item:hover, .search-cmd-item.selected {
    background: var(--bg-hover);
}
.search-cmd-item-icon { font-size: 18px; flex-shrink: 0; }
.search-cmd-item-body { flex: 1; min-width: 0; }
.search-cmd-item-title { font-size: 13.5px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
.search-cmd-item-snippet { font-size: 12px; color: var(--text-muted); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.search-cmd-item-meta { font-size: 11px; color: var(--text-muted); flex-shrink: 0; text-align: right; }
.search-cmd-badge { font-size: 10px; padding: 1px 6px; border-radius: 99px; background: rgba(108,99,255,0.15); color: var(--accent-light); font-weight: 500; }
.search-cmd-footer {
    display: flex;
    gap: 16px;
    padding: 10px 20px;
    border-top: 1px solid var(--border);
    font-size: 11px;
    color: var(--text-muted);
    background: var(--bg-base);
}
.search-cmd-footer kbd { background: var(--bg-elevated); border: 1px solid var(--border); padding: 1px 4px; border-radius: 3px; font-family: var(--font-mono); }

.msg-actions {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
    opacity: 0.4;
    transition: opacity 0.15s;
}
.message:hover .msg-actions { opacity: 1; }
.msg-act-btn {
    background: none;
    border: 1px solid transparent;
    color: var(--text-muted);
    font-size: 10px;
    padding: 1px 6px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition);
}
.msg-act-btn:hover {
    color: var(--text-primary);
    background: var(--bg-hover);
    border-color: var(--border);
}

/* User inline edit box */
.edit-box {
    margin-top: 6px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.edit-textarea {
    width: 100%;
    background: var(--bg-base);
    border: 1px solid var(--accent);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    padding: 8px 12px;
    font-family: var(--font-sans);
    font-size: 13.5px;
    outline: none;
}
.edit-btn-row {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
}

/* Sidebar Toggle Button */
.btn-sidebar-toggle {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    width: 32px;
    height: 32px;
    border-radius: var(--radius-md);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    flex-shrink: 0;
}
.btn-sidebar-toggle:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-strong); }

/* Floating Team Typing Bar */
.team-typing-bar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    margin: 0 auto 8px 16px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--accent-light);
    background: rgba(108,99,255,0.08);
    border: 1px solid rgba(108,99,255,0.22);
    border-radius: 99px;
    width: fit-content;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    animation: fadeIn 0.2s ease-in-out;
}
.team-typing-bar.hidden {
    display: none !important;
}
.typing-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--accent);
    animation: typingPulseAnim 1.2s infinite ease-in-out;
}
@keyframes typingPulseAnim {
    0%, 100% { opacity: 0.3; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1.2); }
}

/* In-Bubble AI Thinking Wave */
.ai-thinking-placeholder {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 2px;
    min-height: 24px;
}
.thinking-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--accent-light);
    display: inline-block;
    animation: thinkingDotBounce 1.4s infinite ease-in-out both;
}
.thinking-dot:nth-child(1) { animation-delay: -0.32s; }
.thinking-dot:nth-child(2) { animation-delay: -0.16s; }
.thinking-dot:nth-child(3) { animation-delay: 0s; }

@keyframes thinkingDotBounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.35; }
    40% { transform: scale(1.15); opacity: 1; }
}
</style>
@endpush
