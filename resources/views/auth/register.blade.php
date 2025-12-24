<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - POB Tracker</title>
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

        .register-container {
            width: 100%;
            max-width: 450px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 0 20px rgba(0, 255, 100, 0.5));
        }

        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo p {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-top: 0.25rem;
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
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 0.4rem;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
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

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300ff66' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        select.form-control option {
            background: #0a1a0a;
            color: var(--text-primary);
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

        .alert-info {
            background: rgba(255, 204, 0, 0.15);
            border: 1px solid rgba(255, 204, 0, 0.3);
            color: var(--warning);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
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
            font-size: 0.8rem;
            margin-top: 0.2rem;
        }

        .role-options {
            display: flex;
            gap: 0.75rem;
        }

        .role-option {
            flex: 1;
            cursor: pointer;
        }

        .role-option input {
            display: none;
        }

        .role-card {
            padding: 1rem;
            border: 1px solid var(--card-border);
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .role-option input:checked+.role-card {
            border-color: var(--primary);
            background: rgba(0, 255, 100, 0.1);
            box-shadow: 0 0 15px rgba(0, 255, 100, 0.2);
        }

        .role-card:hover {
            border-color: var(--primary);
        }

        .role-card i {
            font-size: 1.5rem;
            color: var(--accent);
            display: block;
            margin-bottom: 0.5rem;
        }

        .role-card span {
            font-size: 0.85rem;
            font-family: 'Orbitron', sans-serif;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="logo">
            <div class="logo-icon">🚢</div>
            <h1>POB TRACKER</h1>
            <p>PERSON ON BOARD SYSTEM</p>
        </div>

        <div class="card">
            <h2 class="card-title">REGISTER</h2>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Akun baru memerlukan persetujuan Super Admin sebelum dapat digunakan.
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">NAMA LENGKAP</label>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">EMAIL</label>
                    <input type="email" name="email" class="form-control" placeholder="Masukkan email"
                        value="{{ old('email') }}" required>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">DEPARTMENT</label>
                    <select name="department" class="form-control" required>
                        <option value="">Pilih Department</option>
                        <option value="GS" {{ old('department') == 'GS' ? 'selected' : '' }}>GS</option>
                        <option value="ICT" {{ old('department') == 'ICT' ? 'selected' : '' }}>ICT</option>
                        <option value="SCM" {{ old('department') == 'SCM' ? 'selected' : '' }}>SCM</option>
                        <option value="HSSE" {{ old('department') == 'HSSE' ? 'selected' : '' }}>HSSE</option>
                        <option value="PO" {{ old('department') == 'PO' ? 'selected' : '' }}>PO</option>
                        <option value="RAM" {{ old('department') == 'RAM' ? 'selected' : '' }}>RAM</option>
                        <option value="WS" {{ old('department') == 'WS' ? 'selected' : '' }}>WS</option>
                        <option value="FM" {{ old('department') == 'FM' ? 'selected' : '' }}>FM</option>
                        <option value="RELATION" {{ old('department') == 'RELATION' ? 'selected' : '' }}>RELATION</option>
                        <option value="PE" {{ old('department') == 'PE' ? 'selected' : '' }}>PE</option>
                        <option value="Plan & Eval" {{ old('department') == 'Plan & Eval' ? 'selected' : '' }}>Plan & Eval
                        </option>
                        <option value="LMF" {{ old('department') == 'LMF' ? 'selected' : '' }}>LMF</option>
                    </select>
                    @error('department')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">PASSWORD</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 karakter" required>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">KONFIRMASI PASSWORD</label>
                    <input type="password" name="password_confirmation" class="form-control"
                        placeholder="Ulangi password" required>
                </div>

                <div class="form-group">
                    <label class="form-label">ROLE</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="department_user" {{ old('role', 'department_user') == 'department_user' ? 'checked' : '' }} required>
                            <div class="role-card">
                                <i class="bi bi-person"></i>
                                <span>User</span>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="gs" {{ old('role') == 'gs' ? 'checked' : '' }}>
                            <div class="role-card">
                                <i class="bi bi-building"></i>
                                <span>GS Staff</span>
                            </div>
                        </label>
                    </div>
                    @error('role')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i>
                    REGISTER
                </button>
            </form>

            <div class="login-link">
                Sudah punya akun? <a href="{{ route('login') }}">Login disini</a>
            </div>
        </div>

        <div class="copyright">
            © GS Ramba 2025. All rights reserved.
        </div>
    </div>
</body>

</html>