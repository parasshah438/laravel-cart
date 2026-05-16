<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password &mdash; {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    @vite(['resources/sass/admin.scss'])

    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-wrapper { width: 100%; max-width: 440px; }
        .login-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.12); }
        .login-header { background: linear-gradient(135deg, #4f8ef7 0%, #6c63ff 100%); padding: 2.5rem 2rem 2rem; text-align: center; color: #fff; }
        .login-icon { width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; backdrop-filter: blur(6px); }
        .login-body { padding: 2rem; }
        .form-control-lg { border-radius: 10px; }
        .btn-login { border-radius: 10px; font-weight: 600; letter-spacing: 0.02em; }

        .password-strength { height: 4px; border-radius: 2px; transition: width .3s, background-color .3s; }
        [data-bs-theme="dark"] .login-card { background: #1e2235; }
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
                <i class="fas fa-lock-open"></i>
            </div>
            <h4 class="fw-bold mb-1">{{ config('app.name') }}</h4>
            <p class="mb-0 opacity-75" style="font-size:0.9rem;">Admin Control Panel</p>
        </div>

        <div class="login-body">
            <h5 class="fw-bold mb-1">Set New Password</h5>
            <p class="text-muted mb-4" style="font-size: 0.875rem;">
                Choose a strong password (min. 8 characters).
            </p>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="border-radius:10px;">
                    <i class="fas fa-circle-exclamation me-2"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold" style="font-size:0.85rem;">
                        <i class="fas fa-envelope me-1 text-primary"></i> Email Address
                    </label>
                    <input type="email"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email', $email ?? '') }}"
                           placeholder="admin@example.com"
                           autofocus
                           autocomplete="email"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold" style="font-size:0.85rem;">
                        <i class="fas fa-lock me-1 text-primary"></i> New Password
                    </label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               minlength="8"
                               autocomplete="new-password"
                               required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('password','eye1')" title="Show/Hide">
                            <i class="fas fa-eye" id="eye1"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Strength bar --}}
                    <div class="mt-2 bg-secondary-subtle rounded" style="height:4px;">
                        <div id="strengthBar" class="password-strength" style="width:0%;background:#dc3545;"></div>
                    </div>
                    <div id="strengthLabel" class="text-muted mt-1" style="font-size:0.75rem;"></div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold" style="font-size:0.85rem;">
                        <i class="fas fa-lock me-1 text-primary"></i> Confirm New Password
                    </label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control form-control-lg"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="••••••••"
                               autocomplete="new-password"
                               required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('password_confirmation','eye2')" title="Show/Hide">
                            <i class="fas fa-eye" id="eye2"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login btn-lg">
                        <i class="fas fa-check me-2"></i> Reset Password
                    </button>
                </div>
            </form>

        </div>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('admin.login') }}" class="text-decoration-none text-muted" style="font-size:0.82rem;">
            <i class="fas fa-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Theme toggle
    const html  = document.documentElement;
    const btn   = document.getElementById('themeToggle');
    const icon  = document.getElementById('themeIcon');
    const saved = localStorage.getItem('adminTheme') || 'light';
    html.setAttribute('data-bs-theme', saved);
    icon.className = saved === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    btn.addEventListener('click', () => {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        localStorage.setItem('adminTheme', next);
    });

    // Show/hide password
    function togglePwd(id, eyeId) {
        const input = document.getElementById(id);
        const eye   = document.getElementById(eyeId);
        if (input.type === 'password') {
            input.type = 'text';
            eye.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            eye.className = 'fas fa-eye';
        }
    }

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function () {
        const val   = this.value;
        const bar   = document.getElementById('strengthBar');
        const label = document.getElementById('strengthLabel');
        let score   = 0;
        if (val.length >= 8)                    score++;
        if (/[A-Z]/.test(val))                  score++;
        if (/[0-9]/.test(val))                  score++;
        if (/[^A-Za-z0-9]/.test(val))           score++;

        const levels = [
            { pct: '25%', color: '#dc3545', text: 'Weak' },
            { pct: '50%', color: '#fd7e14', text: 'Fair' },
            { pct: '75%', color: '#ffc107', text: 'Good' },
            { pct: '100%', color: '#198754', text: 'Strong' },
        ];
        if (val.length === 0) {
            bar.style.width = '0%'; label.textContent = ''; return;
        }
        const lvl = levels[Math.min(score - 1, 3)] || levels[0];
        bar.style.width           = lvl.pct;
        bar.style.backgroundColor = lvl.color;
        label.textContent         = lvl.text;
        label.style.color         = lvl.color;
    });
</script>
</body>
</html>
