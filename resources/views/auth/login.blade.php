<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POB Tracker</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #00ff66;
            --secondary: #00cc52;
            --accent: #00ff88;
            --bg-dark: #0a0f0a;
            --card-bg: rgba(15, 35, 15, 0.95);
            --card-border: rgba(0, 255, 100, 0.2);
            --text-primary: #e0ffe0;
            --text-secondary: #c0ffc0;
            --text-muted: #7ab37a;
            --success: #00ff66;
            --error: #ff4444;
            --warning: #ffcc00;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(0, 255, 100, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(0, 255, 136, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(0, 200, 80, 0.05) 0%, transparent 70%);
            z-index: -1;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            font-size: 4rem;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 0 20px rgba(0, 255, 100, 0.5));
        }

        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(0, 255, 100, 0.3);
        }

        .logo p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(0, 255, 100, 0.1);
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
            letter-spacing: 3px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(0, 255, 100, 0.2);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input {
            accent-color: var(--primary);
            width: 18px;
            height: 18px;
        }

        .checkbox-group span {
            color: var(--text-muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            width: 100%;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 255, 100, 0.4);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.15);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: var(--error);
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.15);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--success);
        }

        .alert-warning {
            background: rgba(255, 204, 0, 0.15);
            border: 1px solid rgba(255, 204, 0, 0.3);
            color: var(--warning);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
        }

        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .error-text {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">🚢</div>
            <h1>POB TRACKER</h1>
            <p>PERSON ON BOARD SYSTEM</p>
        </div>

        <div class="card">
            <h2 class="card-title">LOGIN</h2>

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">EMAIL</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">PASSWORD</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password"
                        required>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i>
                    LOGIN
                </button>
            </form>

            <div class="register-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar disini</a>
            </div>
        </div>

        <div class="copyright">
            © GS Ramba 2025. All rights reserved.
        </div>
    </div>
</body>

</html>