<!DOCTYPE html>
<html lang="en" data-mode="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied — {{ \App\Models\Setting::get('app_name', 'AI Chat Studio') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #07090e;
            --bg-surface: rgba(17, 20, 28, 0.85);
            --border: rgba(255, 255, 255, 0.1);
            --accent: #6c63ff;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --radius-xl: 20px;
            --radius-md: 10px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-sans);
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image: 
                radial-gradient(at 0% 0%, rgba(244, 63, 94, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(108, 99, 255, 0.15) 0px, transparent 50%);
        }

        .error-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .icon-badge {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #f43f5e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
        }

        h1 { font-size: 26px; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.5px; }
        p { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 24px; }

        .btn-home {
            display: inline-block;
            padding: 10px 24px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border-radius: var(--radius-md);
            transition: transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
        }

        .btn-home:hover { transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-badge">🚫</div>
        <h1>403 — Access Denied</h1>
        <p>This page requires Super Admin privileges. You do not have permission to view or manage user accounts.</p>
        <a href="{{ route('chat.index') }}" class="btn-home">⬅️ Return to AI Studio</a>
    </div>
</body>
</html>
