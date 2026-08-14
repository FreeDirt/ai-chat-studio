<!DOCTYPE html>
<html lang="en" data-mode="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $conversation->title }} — Shared AI Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/styles/github-dark.min.css">
    <script src="https://cdn.jsdelivr.net/npm/highlight.js@11.9.0/lib/highlight.min.js"></script>
    <style>
        :root {
            --bg-base: #07090e;
            --bg-surface: rgba(17, 20, 28, 0.85);
            --bg-elevated: rgba(28, 32, 45, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --accent: #6c63ff;
            --accent-light: #8b85ff;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --radius-lg: 16px;
            --radius-md: 10px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans); background: var(--bg-base); color: var(--text-primary); min-height: 100vh; display: flex; flex-direction: column; }
        
        header { background: var(--bg-surface); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10; }
        .header-title { font-size: 16px; font-weight: 800; }
        .header-sub { font-size: 11px; color: var(--text-secondary); margin-top: 2px; }

        main { flex: 1; max-width: 860px; width: 100%; margin: 0 auto; padding: 32px 24px; }
        
        .message { display: flex; gap: 16px; margin-bottom: 24px; }
        .msg-avatar { width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, var(--accent), #a855f7); display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff; flex-shrink: 0; }
        .msg-body { flex: 1; background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 16px 20px; line-height: 1.6; font-size: 14px; }
        .msg-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid var(--border); padding-bottom: 6px; font-size: 11px; color: var(--text-muted); }
        .token-badge { padding: 2px 6px; border-radius: 99px; background: var(--bg-elevated); border: 1px solid var(--border); color: var(--accent-light); }

        pre { background: #0d1117; padding: 14px; border-radius: var(--radius-md); overflow-x: auto; font-family: monospace; font-size: 13px; border: 1px solid var(--border); margin: 10px 0; }
        code { font-family: monospace; }
    </style>
</head>
<body>
    <header>
        <div>
            <div class="header-title">🔗 {{ $conversation->title }}</div>
            <div class="header-sub">Shared AI Conversation · Shared via {{ \App\Models\Setting::get('app_name', 'AI Chat Studio') }}</div>
        </div>
        <a href="/" style="padding:6px 14px;background:var(--accent);color:#fff;border-radius:var(--radius-md);text-decoration:none;font-weight:700;font-size:12px">Open Studio</a>
    </header>

    <main>
        @foreach($messages as $msg)
            <div class="message">
                <div class="msg-avatar">
                    {{ $msg->role === 'user' ? 'Y' : '🤖' }}
                </div>
                <div class="msg-body">
                    <div class="msg-meta">
                        <strong>{{ $msg->role === 'user' ? 'User' : 'AI Assistant' }}</strong>
                        @if($msg->model) <span class="token-badge">{{ $msg->model }}</span> @endif
                    </div>
                    <div class="md-content">{!! $msg->role === 'user' ? nl2br(e($msg->content)) : e($msg->content) !!}</div>
                </div>
            </div>
        @endforeach
    </main>

    <script>
        document.querySelectorAll('.md-content').forEach(el => {
            if (el.children.length === 0) {
                el.innerHTML = marked.parse(el.textContent);
                el.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
            }
        });
    </script>
</body>
</html>
