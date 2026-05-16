<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login &mdash; {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        }

        .login-header {
            background: linear-gradient(135deg, #4f8ef7 0%, #6c63ff 100%);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            color: #fff;
        }

        .login-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            backdrop-filter: blur(6px);
        }

        .login-body {
            padding: 2rem;
        }

        .form-control-lg {
            border-radius: 10px;
        }

        .btn-login {
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        [data-bs-theme="dark"] .login-card {
            background: #1e2235;
        }
    </style>
</head>
<body style="background: var(--bs-secondary-bg);">

<div class="login-wrapper p-3">

    {{-- Theme Toggle --}}
    <div class="text-end mb-3">
        <button id="themeToggle" class="icon-btn" type="button" title="Toggle Theme"
                style="background:var(--bs-body-bg);border-color:var(--bs-border-color);">
            <i id="themeIcon" class="fas fa-moon"></i>
        </button>
    </div>

    <div class="login-card card">

        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <h4 class="fw-bold mb-1">{{ config('app.name') }}</h4>
            <p class="mb-0 opacity-75" style="font-size:0.9rem;">Admin Control Panel</p>
        </div>

        <div class="login-body">
            <h5 class="fw-bold mb-1">Welcome back!</h5>
            <p class="text-muted mb-4" style="font-size: 0.875rem;">Sign in to your admin account</p>

            @if (session('status'))
                <div class="alert alert-success border-0 d-flex align-items-center gap-2 mb-3" style="border-radius:10px;">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="border-radius: 10px;">
                    <i class="fas fa-circle-exclamation me-2"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold" style="font-size:0.85rem;">
                        <i class="fas fa-envelope me-1 text-primary"></i> Email Address
                    </label>
                    <input type="email"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="admin@example.com"
                           autofocus
                           autocomplete="email"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label fw-semibold" style="font-size:0.85rem;">
                            <i class="fas fa-lock me-1 text-primary"></i> Password
                        </label>
                        <a href="{{ route('admin.password.request') }}" class="text-decoration-none" style="font-size:0.8rem;">
                            Forgot password?
                        </a>
                    </div>
                    <div class="input-group">
                        <input type="password"
                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                onclick="togglePwd()" title="Show/Hide Password">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember" style="font-size:0.85rem;">
                            Remember me
                        </label>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login btn-lg">
                        <i class="fas fa-right-to-bracket me-2"></i> Sign In to Admin
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="text-decoration-none text-muted" style="font-size:0.82rem;">
            <i class="fas fa-arrow-left me-1"></i> Back to User Login
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function togglePwd() {
    const pwd = document.getElementById('password');
    const eye = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        pwd.type = 'password';
        eye.className = 'fas fa-eye';
    }
}
</script>
</body>
</html>
