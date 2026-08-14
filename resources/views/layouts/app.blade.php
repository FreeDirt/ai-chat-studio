@php
    $appPrimaryColor = \App\Models\Setting::get('app_primary_color', '#6c63ff');
    $appName        = \App\Models\Setting::get('app_name', 'AI Chat Studio');
    $appLogo        = \App\Models\Setting::get('app_logo', '');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $appName }} — A modern multi-provider AI workspace with RAG Document Q&A and Persona Studio.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function() {
            const mode = localStorage.getItem('app_mode') || 'dark';
            const theme = localStorage.getItem('app_theme') || 'purple';

            function applyMode(m) {
                let effective = m;
                if (m === 'system') {
                    effective = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-mode', effective);
                document.documentElement.setAttribute('data-mode-setting', m);
            }

            applyMode(mode);
            if (theme) document.documentElement.setAttribute('data-theme', theme);

            // Listen for OS system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (localStorage.getItem('app_mode') === 'system') {
                    applyMode('system');
                }
            });
        })();
    </script>
    <title>@yield('title', $appName)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Marked.js for markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- Highlight.js for code syntax -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <style>
        /* ===== CSS DESIGN SYSTEM ===== */
        :root {
            --bg-base:       #0a0c10;
            --bg-surface:    #11141c;
            --bg-elevated:   #171b26;
            --bg-hover:      #202536;
            --bg-active:     #292f45;
            --border:        rgba(255,255,255,0.08);
            --border-strong: rgba(255,255,255,0.16);

            --accent:        {{ $appPrimaryColor }};
            --accent-glow:   {{ $appPrimaryColor }}59;
            --accent-light:  {{ $appPrimaryColor }};
            --accent-dark:   {{ $appPrimaryColor }};

            --text-primary:  #f0f2f8;
            --text-secondary:#9aa0b4;
            --text-muted:    #63687e;

            --user-bubble:   linear-gradient(135deg, #1b263b, #151d2f);
            --user-border:   rgba(108,99,255,0.3);
            --ai-bubble:     #151923;
            --ai-border:     rgba(255,255,255,0.08);

            --success:       #4ade80;
            --warning:       #facc15;
            --danger:        #f87171;
            --info:          #60a5fa;

            --radius-sm:     6px;
            --radius-md:     10px;
            --radius-lg:     16px;
            --radius-xl:     24px;

            --sidebar-w:     280px;
            --panel-w:       270px;

            --font-sans:     'Inter', system-ui, -apple-system, sans-serif;
            --font-mono:     'Fira Code', monospace;

            --transition:    all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-lg:     0 20px 60px rgba(0,0,0,0.7);
            --shadow-glow:   0 0 30px var(--accent-glow);
        }

        /* ===== LIGHT MODE OVERRIDES ===== */
        [data-mode="light"] {
            --bg-base:       #f1f5f9;
            --bg-surface:    #ffffff;
            --bg-elevated:   #f8fafc;
            --bg-hover:      #e2e8f0;
            --bg-active:     #cbd5e1;

            --border:        #e2e8f0;
            --border-strong: #cbd5e1;

            --text-primary:  #0f172a;
            --text-secondary:#334155;
            --text-muted:    #64748b;

            --user-bubble:   linear-gradient(135deg, #4f46e5, #6366f1);
            --user-border:   rgba(79,70,229,0.3);
            --ai-bubble:     #ffffff;
            --ai-border:     #e2e8f0;

            --shadow-lg:     0 10px 30px rgba(0,0,0,0.06);
        }

        [data-mode="light"] html,
        [data-mode="light"] body {
            background: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99,102,241,0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168,85,247,0.05) 0px, transparent 50%);
            color: #0f172a;
        }

        [data-mode="light"] .sidebar,
        [data-mode="light"] .right-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-color: #e2e8f0;
        }

        [data-mode="light"] .chat-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border-color: #e2e8f0;
        }

        [data-mode="light"] .logo-text {
            background: linear-gradient(90deg, #0f172a, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        [data-mode="light"] .message.user .msg-bubble {
            color: #ffffff;
        }

        [data-mode="light"] .message.assistant .msg-bubble {
            color: #0f172a;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        [data-mode="light"] kbd {
            background: #e2e8f0;
            color: #334155;
            border-color: #cbd5e1;
        }

        [data-mode="light"] .input-wrapper {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        }

        [data-mode="light"] #chat-textarea {
            color: #0f172a;
        }

        [data-mode="light"] .compare-toolbar {
            background: rgba(255, 255, 255, 0.95);
            border-color: #e2e8f0;
        }

        [data-mode="light"] .compare-pane {
            background: #ffffff;
            border-color: #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        [data-mode="light"] pre {
            background: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        [data-mode="light"] pre code {
            color: #f8fafc !important;
        }

        /* ===== THEMES ===== */
        [data-theme="cyberpunk"] {
            --accent:        #f43f5e;
            --accent-hover:  #e11d48;
            --accent-light:  #fb7185;
            --accent-glow:   rgba(244,63,94,0.35);
        }
        [data-theme="emerald"] {
            --accent:        #10b981;
            --accent-hover:  #059669;
            --accent-light:  #34d399;
            --accent-glow:   rgba(16,185,129,0.35);
        }
        [data-theme="slate"] {
            --accent:        #3b82f6;
            --accent-hover:  #2563eb;
            --accent-light:  #60a5fa;
            --accent-glow:   rgba(59,130,246,0.35);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        .hidden { display: none !important; }

        html, body {
            height: 100%;
            font-family: var(--font-sans);
            background: var(--bg-base);
            background-image: 
                radial-gradient(at 0% 0%, rgba(108,99,255,0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168,85,247,0.08) 0px, transparent 50%);
            color: var(--text-primary);
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        /* ===== APP SHELL ===== */
        .app-shell {
            display: grid;
            grid-template-columns: var(--sidebar-w) 1fr var(--panel-w);
            height: 100vh;
            overflow: hidden;
        }

        .app-shell.sidebar-collapsed {
            grid-template-columns: 0px 1fr var(--panel-w);
        }

        .app-shell.panel-collapsed {
            grid-template-columns: var(--sidebar-w) 1fr 0px;
        }

        .app-shell.sidebar-collapsed.panel-collapsed {
            grid-template-columns: 0px 1fr 0px;
        }

        .app-shell.panel-collapsed .right-panel {
            display: none !important;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 20px 16px 16px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .app-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 25px var(--accent-glow);
            flex-shrink: 0;
            transition: var(--transition);
        }
        .app-logo:hover .logo-icon { transform: scale(1.05) rotate(-3deg); }

        .logo-text {
            font-size: 16px;
            font-weight: 700;
            background: linear-gradient(90deg, #fff, var(--accent-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.3px;
        }

        .btn-new-chat {
            width: 100%;
            padding: 10px 14px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            border: none;
            border-radius: var(--radius-md);
            color: #fff;
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 20px var(--accent-glow);
        }
        .btn-new-chat:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 6px 25px var(--accent-glow);
            filter: brightness(1.08);
        }
        .btn-new-chat:active { transform: translateY(0); }

        .sidebar-nav {
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-nav a, .sidebar-nav button {
            padding: 8px 12px;
            border-radius: var(--radius-md);
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: 1px solid transparent;
            width: 100%;
            cursor: pointer;
            font-family: var(--font-sans);
            text-align: left;
        }
        .sidebar-nav a:hover, .sidebar-nav button:hover { background: var(--bg-hover); color: var(--text-primary); }
        .sidebar-nav a.active, .sidebar-nav button.active { background: rgba(108,99,255,0.12); color: var(--accent-light); border-color: rgba(108,99,255,0.25); }

        .nav-kbd {
            margin-left: auto;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 10px;
            font-family: var(--font-mono);
            color: var(--text-muted);
        }



        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .conv-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            padding: 10px 8px 4px;
        }

        .conv-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 10px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            margin-bottom: 2px;
            border: 1px solid transparent;
        }
        .conv-item:hover { background: var(--bg-hover); border-color: var(--border); }
        .conv-item.active {
            background: var(--bg-active);
            border-color: rgba(108,99,255,0.3);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .conv-icon {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            background: var(--bg-elevated);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            border: 1px solid var(--border);
        }

        .conv-info { flex: 1; min-width: 0; }
        .conv-title {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .conv-meta {
            font-size: 10.5px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .conv-actions {
            display: none;
            gap: 2px;
            flex-shrink: 0;
        }
        .conv-item:hover .conv-actions { display: flex; }

        .conv-action-btn {
            width: 22px;
            height: 22px;
            border: none;
            background: none;
            color: var(--text-muted);
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: var(--transition);
        }
        .conv-action-btn:hover { background: var(--bg-base); color: var(--text-primary); }
        .conv-pin-icon { color: var(--accent); font-size: 10px; }

        /* ===== MAIN CHAT AREA ===== */
        .chat-main {
            display: flex;
            flex-direction: column;
            background: transparent;
            overflow: hidden;
            position: relative;
        }

        .chat-header {
            padding: 12px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            z-index: 10;
        }

        .btn-hdr-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }
        .btn-hdr-action:hover {
            background: var(--bg-hover);
            border-color: rgba(108,99,255,0.4);
            color: var(--text-primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        }

        .chat-header-title {
            flex: 1;
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.2px;
        }

        .provider-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 500;
            border: 1px solid var(--border-strong);
            background: var(--bg-elevated);
            color: var(--text-secondary);
            backdrop-filter: blur(8px);
        }
        .provider-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 10px var(--success);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        /* ===== MESSAGES ===== */
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            gap: 16px;
            padding: 40px;
        }
        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: var(--shadow-glow);
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        .empty-state h2 { font-size: 22px; font-weight: 700; letter-spacing: -0.4px; }
        .empty-state p { color: var(--text-secondary); font-size: 14px; max-width: 360px; line-height: 1.6; }

        .quick-starters {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 8px;
            max-width: 540px;
            width: 100%;
        }
        .quick-starter {
            padding: 14px 16px;
            background: rgba(23,27,38,0.8);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            cursor: pointer;
            text-align: left;
            transition: var(--transition);
            font-family: var(--font-sans);
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.45;
        }
        .quick-starter:hover {
            background: var(--bg-hover);
            border-color: rgba(108,99,255,0.4);
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        /* Message bubbles */
        .message {
            display: flex;
            gap: 14px;
            max-width: 860px;
            animation: msgIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes msgIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .message.user { flex-direction: row-reverse; align-self: flex-end; }
        .message.assistant { align-self: flex-start; }

        .msg-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .message.user .msg-avatar {
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            color: #fff;
        }
        .message.assistant .msg-avatar {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid var(--border-strong);
            font-size: 16px;
        }

        .msg-body { flex: 1; min-width: 0; }

        .msg-meta {
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .message.user .msg-meta { flex-direction: row-reverse; }

        .streaming-cursor {
            display: inline-block;
            color: var(--accent-light);
            font-weight: 800;
            margin-left: 2px;
            animation: blinkCursor 0.8s infinite;
        }
        @keyframes blinkCursor {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0; }
        }

        .msg-bubble {
            padding: 14px 18px;
            border-radius: var(--radius-lg);
            line-height: 1.65;
            font-size: 14px;
            word-break: break-word;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        }
        .message.user .msg-bubble {
            background: var(--user-bubble);
            border: 1px solid var(--user-border);
            border-top-right-radius: 4px;
            color: var(--text-primary);
        }
        .message.assistant .msg-bubble {
            background: var(--ai-bubble);
            border: 1px solid var(--ai-border);
            border-top-left-radius: 4px;
        }

        /* Markdown formatting */
        .msg-bubble p { margin-bottom: 12px; }
        .msg-bubble p:last-child { margin-bottom: 0; }
        .msg-bubble ul, .msg-bubble ol { margin: 8px 0 8px 20px; }
        .msg-bubble li { margin-bottom: 4px; }
        .msg-bubble blockquote {
            border-left: 3px solid var(--accent);
            padding-left: 12px;
            color: var(--text-secondary);
            margin: 8px 0;
            font-style: italic;
        }
        .msg-bubble table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 13px;
        }
        .msg-bubble th, .msg-bubble td {
            padding: 8px 12px;
            border: 1px solid var(--border);
            text-align: left;
        }
        .msg-bubble th { background: var(--bg-elevated); color: var(--accent-light); font-weight: 600; }
        .msg-bubble pre {
            margin: 12px 0;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-strong);
            overflow-x: auto;
            position: relative;
        }
        .msg-bubble code {
            font-family: var(--font-mono);
            font-size: 12.5px;
        }
        .msg-bubble :not(pre) > code {
            background: rgba(108,99,255,0.15);
            color: var(--accent-light);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .copy-code-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 3px 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text-secondary);
            font-size: 11px;
            cursor: pointer;
            transition: var(--transition);
            backdrop-filter: blur(4px);
        }
        .copy-code-btn:hover { background: var(--accent); color: #fff; }

        .token-badge {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 10px;
            color: var(--text-muted);
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 12px;
            align-self: flex-start;
            animation: msgIn 0.3s ease;
        }
        .typing-dots {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            background: var(--ai-bubble);
            border: 1px solid var(--ai-border);
            border-radius: var(--radius-lg);
            border-top-left-radius: 4px;
        }
        .typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: bounce 1.4s infinite ease-in-out;
        }
        .typing-dots span:nth-child(1) { animation-delay: 0s; }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40%           { transform: scale(1.0); opacity: 1; }
        }

        /* ===== INPUT AREA (FLOATING PILL DOCK) ===== */
        .input-area {
            padding: 16px 24px 20px;
            background: transparent;
            flex-shrink: 0;
            position: relative;
        }

        .input-wrapper {
            position: relative;
            background: rgba(17,20,28,0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-xl);
            padding: 12px 16px;
            transition: var(--transition);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            max-width: 880px;
            margin: 0 auto;
        }

        /* ===== PROMPT POPOVER LIBRARY ===== */
        .prompt-popover {
            position: absolute;
            bottom: calc(100% + 12px);
            left: 0;
            right: 0;
            background: var(--bg-surface);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-xl);
            padding: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 30px var(--accent-glow);
            z-index: 100;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .prompt-popover-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .prompt-popover-search input {
            width: 100%;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 8px 12px;
            color: var(--text-primary);
            font-size: 13px;
            outline: none;
            margin-bottom: 12px;
        }
        .prompt-popover-list {
            max-height: 280px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .prompt-item-card {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 14px;
            cursor: pointer;
            transition: var(--transition);
        }
        .prompt-item-card:hover {
            border-color: var(--accent);
            background: var(--bg-hover);
            transform: translateY(-1px);
        }
        .prompt-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .prompt-item-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
        .prompt-item-tag { font-size: 10px; padding: 2px 6px; border-radius: 99px; background: rgba(108,99,255,0.2); color: var(--accent-light); font-weight: 600; }
        .prompt-item-preview { font-size: 12px; color: var(--text-secondary); line-height: 1.4; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .btn-attach {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-attach:hover {
            background: var(--bg-hover);
            border-color: rgba(108,99,255,0.4);
            color: var(--text-primary);
            transform: translateY(-1px);
        }
        .btn-attach.recording {
            background: rgba(244,63,94,0.2);
            border-color: var(--danger);
            color: var(--danger);
            animation: pulse 1.5s infinite;
        }
        .input-wrapper:focus-within {
            border-color: rgba(108,99,255,0.6);
            box-shadow: 0 0 25px var(--accent-glow), 0 10px 40px rgba(0,0,0,0.5);
        }

        .input-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .persona-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            background: rgba(108,99,255,0.15);
            border: 1px solid rgba(108,99,255,0.3);
            border-radius: 99px;
            font-size: 11.5px;
            color: var(--accent-light);
            font-weight: 500;
        }
        .persona-chip .remove-persona {
            cursor: pointer;
            opacity: 0.7;
            margin-left: 2px;
        }
        .persona-chip .remove-persona:hover { opacity: 1; color: var(--danger); }

        textarea#chat-textarea {
            width: 100%;
            background: none;
            border: none;
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 14px;
            line-height: 1.55;
            resize: none;
            outline: none;
            max-height: 240px;
            min-height: 24px;
            overflow-y: hidden;
            box-sizing: border-box;
            display: block;
        }
        textarea#chat-textarea::placeholder { color: var(--text-muted); }

        .input-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }

        .input-hints {
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .input-hints kbd {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            padding: 1px 5px;
            border-radius: 3px;
            font-family: var(--font-mono);
            font-size: 10px;
        }

        .btn-send {
            padding: 7px 16px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            border: none;
            border-radius: var(--radius-md);
            color: #fff;
            font-family: var(--font-sans);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
            box-shadow: 0 2px 10px var(--accent-glow);
        }
        .btn-send:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px var(--accent-glow);
            filter: brightness(1.1);
        }
        .btn-send:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ===== RIGHT PANEL ===== */
        .right-panel {
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .panel-header {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .panel-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
        }

        .panel-body { padding: 16px; flex: 1; }

        /* Persona cards in right panel */
        .persona-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
            margin-bottom: 4px;
        }
        .persona-card:hover {
            background: var(--bg-hover);
            border-color: var(--border);
        }
        .persona-card.active {
            background: rgba(108,99,255,0.12);
            border-color: rgba(108,99,255,0.3);
        }
        .persona-card-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .persona-card-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .persona-card-hint {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
        }
        .stat-card {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px;
            text-align: center;
        }
        .stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); }
        .stat-value { font-size: 16px; font-weight: 700; color: var(--accent-light); margin-top: 2px; }

        /* ===== BUTTONS & FORMS ===== */
        .btn {
            padding: 9px 16px;
            border-radius: var(--radius-md);
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            color: #fff;
            box-shadow: 0 4px 15px var(--accent-glow);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px var(--accent-glow);
        }
        .btn-ghost {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            color: var(--text-secondary);
        }
        .btn-ghost:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-strong); }
        .btn-danger {
            background: rgba(248,113,113,0.15);
            border: 1px solid rgba(248,113,113,0.3);
            color: var(--danger);
        }
        .btn-danger:hover { background: rgba(248,113,113,0.25); }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            padding: 9px 12px;
            font-family: var(--font-sans);
            font-size: 13px;
            outline: none;
            transition: var(--transition);
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-select option { background: var(--bg-surface); color: var(--text-primary); }

        /* Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            animation: fadeIn 0.2s ease;
        }
        .modal-overlay.hidden { display: none; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .modal {
            background: var(--bg-surface);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-lg);
            padding: 24px;
            width: 90%;
            max-width: 480px;
            box-shadow: var(--shadow-lg);
            animation: scaleIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal.fullscreen {
            max-width: 98vw !important;
            height: 96vh !important;
        }
        @keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal h3 { font-size: 17px; font-weight: 700; margin-bottom: 16px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }

        /* ===== COMMAND PALETTE SEARCH MODAL ===== */
        .search-cmd-modal {
            max-width: 640px !important;
            width: 95% !important;
            padding: 0 !important;
            overflow: hidden !important;
            background: var(--bg-surface) !important;
            border: 1px solid var(--border-strong) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: 0 25px 70px rgba(0,0,0,0.8), 0 0 35px var(--accent-glow) !important;
        }

        .search-cmd-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            color: var(--text-muted);
            background: var(--bg-elevated);
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
            background: var(--bg-base);
            border: 1px solid var(--border);
            padding: 2px 6px;
            border-radius: 4px;
            color: var(--text-muted);
            font-family: var(--font-mono);
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
            padding: 12px 20px;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid rgba(255,255,255,0.03);
            text-decoration: none;
            color: inherit;
        }

        .search-cmd-item:hover, .search-cmd-item.selected {
            background: var(--bg-hover);
        }

        .search-cmd-item-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .search-cmd-item-body {
            flex: 1;
            min-width: 0;
        }

        .search-cmd-item-title {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-cmd-item-snippet {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .search-cmd-item-meta {
            font-size: 11px;
            color: var(--text-muted);
            flex-shrink: 0;
            text-align: right;
        }

        .search-cmd-badge {
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 99px;
            background: rgba(108,99,255,0.15);
            color: var(--accent-light);
            font-weight: 500;
            border: 1px solid rgba(108,99,255,0.3);
        }

        .search-cmd-footer {
            display: flex;
            gap: 16px;
            padding: 10px 20px;
            border-top: 1px solid var(--border);
            font-size: 11px;
            color: var(--text-muted);
            background: var(--bg-base);
        }

        .search-cmd-footer kbd {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            padding: 1px 5px;
            border-radius: 3px;
            font-family: var(--font-mono);
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 200;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .toast {
            padding: 10px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 500;
            color: #fff;
            box-shadow: var(--shadow-lg);
            animation: toastIn 0.3s ease;
            backdrop-filter: blur(8px);
        }
        @keyframes toastIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .toast.success { background: rgba(74,222,128,0.9); color: #052e16; }
        .toast.error   { background: rgba(248,113,113,0.9); color: #450a0a; }
        .toast.info    { background: rgba(108,99,255,0.9); color: #fff; }
    </style>
    @stack('head')
</head>
<body>
    @yield('content')

    <!-- Global Command Palette Search Modal -->
    <div class="modal-overlay hidden" id="search-modal">
        <div class="modal search-cmd-modal" style="max-width:640px;padding:0;overflow:hidden">
            <div class="search-cmd-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="search-cmd-input" placeholder="Search conversations &amp; messages... (Press Esc to close)" autocomplete="off" spellcheck="false">
                <span class="search-cmd-esc">Esc</span>
            </div>
            <div class="search-cmd-results" id="search-cmd-results">
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
                    Type to search across titles and message history...
                </div>
            </div>
            <div class="search-cmd-footer">
                <span><kbd>↑</kbd> <kbd>↓</kbd> Navigate</span>
                <span><kbd>↵</kbd> Select</span>
                <span><kbd>Esc</kbd> Close</span>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts Modal -->
    <div class="modal-overlay hidden" id="shortcuts-modal">
        <div class="modal" style="max-width:540px;padding:24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700">
                    <span>⌨️ Keyboard Shortcuts</span>
                </div>
                <button class="btn btn-ghost" id="shortcuts-close" style="font-size:14px;padding:2px 8px">✕</button>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <!-- Navigation -->
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">
                        🔍 Navigation & Search
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:12.5px">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Search Messages</span>
                            <span><kbd>Ctrl</kbd>+<kbd>K</kbd></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Toggle Navigation</span>
                            <span><kbd>Ctrl</kbd>+<kbd>\</kbd></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Toggle Personas</span>
                            <span><kbd>Ctrl</kbd>+<kbd>]</kbd></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Shortcuts Help</span>
                            <span><kbd>?</kbd> or <kbd>Ctrl</kbd>+<kbd>/</kbd></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Close Modal</span>
                            <span><kbd>Esc</kbd></span>
                        </div>
                    </div>
                </div>

                <!-- Messaging & Actions -->
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">
                        💬 Messaging & Modes
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:12.5px">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Send Prompt</span>
                            <span><kbd>Enter</kbd></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">New Line</span>
                            <span><kbd>Shift</kbd>+<kbd>Enter</kbd></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Prompt Library</span>
                            <span><kbd>/</kbd> in input</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="color:var(--text-secondary)">Dual AI Compare</span>
                            <span style="font-size:11px;color:var(--accent-light);font-weight:600">🔀 Toggle</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:20px;padding-top:12px;border-top:1px solid var(--border);text-align:center;font-size:11px;color:var(--text-muted)">
                Press <kbd>?</kbd> anywhere on the page to open or close this menu.
            </div>
        </div>
    </div>

    <!-- Token Usage & Cost Analytics Modal -->
    <div class="modal-overlay hidden" id="analytics-modal">
        <div class="modal" style="max-width:600px;padding:24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;gap:8px;font-size:16px;font-weight:700">
                    <span>📊 Token Usage & Cost Analytics</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <select id="analytics-provider-select" style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);padding:4px 10px;font-size:12px;outline:none;cursor:pointer">
                        <option value="all" selected>🌐 All Providers</option>
                        <option value="openrouter">🪐 OpenRouter</option>
                        <option value="openai">🟢 OpenAI</option>
                        <option value="claude">🔴 Claude</option>
                        <option value="gemini">🔵 Gemini</option>
                        <option value="ollama">🦙 Ollama (Local)</option>
                    </select>
                    <button class="btn btn-ghost" id="analytics-close" style="font-size:14px;padding:2px 8px">✕</button>
                </div>
            </div>

            @php
                $appTotalTokens   = \App\Models\Message::sum('tokens_used') ?? 0;
                $appTotalMessages = \App\Models\Message::count();
                $appAvgLatency    = (int) \App\Models\Message::whereNotNull('response_time_ms')->avg('response_time_ms');
                $appEstCost       = round(($appTotalTokens / 1000) * 0.00015, 4);
            @endphp
            <!-- Stats Grid -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px">
                <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px;text-align:center">
                    <div style="font-size:9.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Total Tokens</div>
                    <div id="modal-stat-tokens" style="font-size:17px;font-weight:800;color:var(--accent-light);margin-top:4px">{{ number_format($appTotalTokens) }}</div>
                </div>
                <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px;text-align:center">
                    <div style="font-size:9.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Est. Cost</div>
                    <div id="modal-stat-cost" style="font-size:17px;font-weight:800;color:var(--success);margin-top:4px">${{ number_format($appEstCost, 4) }}</div>
                </div>
                <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px;text-align:center">
                    <div style="font-size:9.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Avg Latency</div>
                    <div id="modal-stat-latency" style="font-size:17px;font-weight:800;color:var(--warning);margin-top:4px">{{ $appAvgLatency ? $appAvgLatency . 'ms' : 'N/A' }}</div>
                </div>
                <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px;text-align:center">
                    <div style="font-size:9.5px;font-weight:700;color:var(--text-muted);text-transform:uppercase">Messages</div>
                    <div id="modal-stat-messages" style="font-size:17px;font-weight:800;color:var(--text-primary);margin-top:4px">{{ number_format($appTotalMessages) }}</div>
                </div>
            </div>

            <!-- Model Usage Breakdown -->
            <div style="margin-bottom:16px">
                <div style="font-size:11px;font-weight:700;color:var(--accent-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
                    🧠 AI Model Distribution
                </div>
                <div id="analytics-models-list" style="display:flex;flex-direction:column;gap:8px">
                    <div style="text-align:center;color:var(--text-muted);font-size:12px">Loading model stats...</div>
                </div>
            </div>

            <div style="text-align:right">
                <button class="btn btn-primary" id="analytics-ok-btn" style="padding:6px 16px;font-size:12px">Done</button>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    {{-- child scripts are injected AFTER the layout script block below --}}
    <script>
        // Global HTML Escape Utility
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Global Toast Utility
        function toast(message, type = 'info', duration = 3000) {
            const container = document.getElementById('toast-container');
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.textContent = message;
            container.appendChild(el);
            setTimeout(() => el.remove(), duration);
        }

        // Global API Helper
        async function api(url, options = {}) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                    ...(options.headers || {}),
                },
                ...options,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || data.message || `HTTP ${res.status}`);
            return data;
        }

        // ========== GLOBAL COMMAND PALETTE SEARCH & SHORTCUTS MODAL ==========
        let searchDebounceTimer = null;
        let selectedSearchIndex = -1;

        function openSearchModal() {
            const modal = document.getElementById('search-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            const input = document.getElementById('search-cmd-input');
            if (input) {
                input.value = '';
                input.focus();
            }
            renderSearchResults([]);
        }

        function closeSearchModal() {
            document.getElementById('search-modal')?.classList.add('hidden');
        }

        function toggleShortcutsModal() {
            const modal = document.getElementById('shortcuts-modal');
            if (modal) modal.classList.toggle('hidden');
        }

        document.getElementById('shortcuts-close')?.addEventListener('click', () => {
            document.getElementById('shortcuts-modal')?.classList.add('hidden');
        });

        async function fetchAnalyticsData(provider = 'all') {
            const tokensEl  = document.getElementById('modal-stat-tokens');
            const costEl    = document.getElementById('modal-stat-cost');
            const latencyEl = document.getElementById('modal-stat-latency');
            const msgsEl    = document.getElementById('modal-stat-messages');

            try {
                const res = await api(`/analytics?provider=${encodeURIComponent(provider)}`);
                if (tokensEl)  tokensEl.textContent  = String(res.total_tokens || '0');
                if (costEl)    costEl.textContent    = String(res.estimated_cost || '$0.00');
                if (latencyEl) latencyEl.textContent = String(res.avg_latency_ms || 'N/A');
                if (msgsEl)    msgsEl.textContent    = String(res.total_messages || '0');

                const list = document.getElementById('analytics-models-list');
                if (list) {
                    const models = res.models || [];
                    if (!models.length) {
                        list.innerHTML = `<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12px">No model usage recorded for this provider</div>`;
                    } else {
                        const counts = models.map(m => parseInt(m.count) || 1);
                        const maxCount = Math.max(...counts, 1);
                        list.innerHTML = models.map(m => {
                            const count = parseInt(m.count) || 0;
                            const tokens = parseInt(m.tokens) || 0;
                            const pct = Math.round((count / maxCount) * 100);
                            const modelName = escapeHtml(m.model || 'Unknown');
                            return `
                                <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);padding:8px 12px">
                                    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;margin-bottom:4px">
                                        <span>${modelName}</span>
                                        <span style="color:var(--text-muted)">${count} msgs · ${tokens.toLocaleString()} tokens</span>
                                    </div>
                                    <div style="height:4px;background:var(--border);border-radius:99px;overflow:hidden">
                                        <div style="width:${pct}%;height:100%;background:var(--accent);border-radius:99px"></div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }
            } catch (e) {
                console.error('Analytics load error:', e);
                toast('Analytics error: ' + e.message, 'error');
            }
        }

        function openAnalyticsModal() {
            const modal = document.getElementById('analytics-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            const providerSelect = document.getElementById('analytics-provider-select');
            fetchAnalyticsData(providerSelect?.value || 'all');
        }

        document.getElementById('analytics-provider-select')?.addEventListener('change', (e) => {
            fetchAnalyticsData(e.target.value);
        });

        document.getElementById('analytics-close')?.addEventListener('click', () => {
            document.getElementById('analytics-modal')?.classList.add('hidden');
        });
        document.getElementById('analytics-ok-btn')?.addEventListener('click', () => {
            document.getElementById('analytics-modal')?.classList.add('hidden');
        });

        function toggleSidebar() {
            const shell = document.querySelector('.app-shell');
            if (shell) {
                shell.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', shell.classList.contains('sidebar-collapsed'));
            }
        }

        function togglePanel() {
            const shell = document.querySelector('.app-shell');
            if (shell) {
                shell.classList.toggle('panel-collapsed');
                localStorage.setItem('panel_collapsed', shell.classList.contains('panel-collapsed'));
            }
        }

        document.addEventListener('click', e => {
            if (e.target.closest('#btn-search-trigger')) {
                openSearchModal();
            }
            if (e.target.closest('#btn-shortcuts-trigger')) {
                toggleShortcutsModal();
            }
            if (e.target.closest('#btn-analytics-trigger')) {
                openAnalyticsModal();
            }
            if (e.target.closest('#btn-toggle-sidebar')) {
                toggleSidebar();
            }
            if (e.target.closest('#btn-toggle-panel')) {
                togglePanel();
            }
        });

        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.querySelector('.app-shell')?.classList.add('sidebar-collapsed');
        }
        if (localStorage.getItem('panel_collapsed') === 'true') {
            document.querySelector('.app-shell')?.classList.add('panel-collapsed');
        }

        // Global Keybinds (Ctrl+K, ?, Ctrl+/, Ctrl+], Ctrl+\)
        document.addEventListener('keydown', e => {
            const tag = e.target.tagName.toLowerCase();
            const isInput = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;

            // Ctrl+K or Cmd+K: Search
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const modal = document.getElementById('search-modal');
                if (modal && modal.classList.contains('hidden')) {
                    openSearchModal();
                } else {
                    closeSearchModal();
                }
            }

            // Ctrl+] or Cmd+]: Toggle AI Personas Panel
            if ((e.ctrlKey || e.metaKey) && (e.code === 'BracketRight' || e.keyCode === 221 || e.key === ']')) {
                e.preventDefault();
                togglePanel();
            }

            // Ctrl+\ or Cmd+\: Toggle Left Navigation Sidebar
            if ((e.ctrlKey || e.metaKey) && (e.code === 'Backslash' || e.keyCode === 220 || e.key === '\\' || e.key === '|')) {
                e.preventDefault();
                toggleSidebar();
            }

            // Ctrl+/ or Cmd+/: Shortcuts
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                toggleShortcutsModal();
            }

            // '?' key when not typing in input
            if (e.key === '?' && !isInput) {
                e.preventDefault();
                toggleShortcutsModal();
            }

            // Esc: Close all overlays
            if (e.key === 'Escape') {
                closeSearchModal();
                document.getElementById('shortcuts-modal')?.classList.add('hidden');
            }
        });

        // Search Input Debouncing & Arrow Key Navigation
        const searchInput = document.getElementById('search-cmd-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchDebounceTimer);
                const q = searchInput.value.trim();
                if (!q) {
                    renderSearchResults([]);
                    return;
                }

                searchDebounceTimer = setTimeout(async () => {
                    try {
                        const res = await api(`/conversations/search?q=${encodeURIComponent(q)}`);
                        renderSearchResults(res.results || [], q);
                    } catch (e) {
                        console.error(e);
                    }
                }, 200);
            });

            searchInput.addEventListener('keydown', e => {
                const items = document.querySelectorAll('.search-cmd-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedSearchIndex = (selectedSearchIndex + 1) % items.length;
                    updateSearchSelection(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedSearchIndex = (selectedSearchIndex - 1 + items.length) % items.length;
                    updateSearchSelection(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedSearchIndex >= 0 && items[selectedSearchIndex]) {
                        items[selectedSearchIndex].click();
                    }
                }
            });
        }

        function updateSearchSelection(items) {
            items.forEach((item, idx) => {
                item.classList.toggle('selected', idx === selectedSearchIndex);
                if (idx === selectedSearchIndex) item.scrollIntoView({ block: 'nearest' });
            });
        }

        function renderSearchResults(results, query = '') {
            const container = document.getElementById('search-cmd-results');
            if (!container) return;
            selectedSearchIndex = -1;

            if (!results.length) {
                container.innerHTML = query
                    ? `<div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No conversations found for "${escapeHtml(query)}"</div>`
                    : `<div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">Type to search across titles and message history...</div>`;
                return;
            }

            container.innerHTML = results.map((r, idx) => `
                <div class="search-cmd-item ${idx === 0 ? 'selected' : ''}" data-id="${r.id}">
                    <div class="search-cmd-item-icon">💬</div>
                    <div class="search-cmd-item-body">
                        <div class="search-cmd-item-title">
                            ${escapeHtml(r.title)}
                            <span class="search-cmd-badge">${escapeHtml(r.match_type)}</span>
                        </div>
                        <div class="search-cmd-item-snippet">${escapeHtml(r.snippet)}</div>
                    </div>
                    <div class="search-cmd-item-meta">
                        ${r.model ? `<div>${escapeHtml(r.model)}</div>` : ''}
                        <div style="font-size:10px">${escapeHtml(r.updated_at)}</div>
                    </div>
                </div>
            `).join('');

            if (results.length > 0) selectedSearchIndex = 0;

            container.querySelectorAll('.search-cmd-item').forEach(item => {
                item.addEventListener('click', () => {
                    const id = item.dataset.id;
                    closeSearchModal();
                    if (typeof loadConversation === 'function') {
                        loadConversation(id);
                    } else {
                        window.location.href = '/?conversation=' + id;
                    }
                });
            });
        }

    </script>

    @stack('scripts')

</body>
</html>
