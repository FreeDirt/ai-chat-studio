<!DOCTYPE html>
<html lang="en" data-mode="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ \App\Models\Setting::get('app_name', 'AI Chat Studio') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
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
                radial-gradient(at 0% 0%, rgba(108, 99, 255, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.2) 0px, transparent 50%);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: cardAppear 0.5s ease-out;
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent), #a855f7);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 12px;
            box-shadow: 0 10px 25px rgba(108, 99, 255, 0.35);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .brand-sub {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 13px;
            font-family: var(--font-sans);
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(108, 99, 255, 0.3);
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .remember-row label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .error-alert {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #f43f5e;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            font-size: 12px;
            margin-bottom: 20px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border: none;
            border-radius: var(--radius-md);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            font-family: var(--font-sans);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon">✨</div>
            <h1 class="brand-title">Welcome Back</h1>
            <p class="brand-sub">Sign in to access your personal AI Chat Studio</p>
        </div>

        @if($errors->any())
            <div class="error-alert">
                <ul style="padding-left:16px">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
            </div>

            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn-submit">Sign In to Studio</button>
        </form>
    </div>
</body>
</html>
