<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - WANTRACE Internship System</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Quando (Sesuai Desain Figma) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Quando&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #eef4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 860px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 45px -10px rgba(1, 89, 135, 0.16), 0 10px 20px -8px rgba(0, 0, 0, 0.04);
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 520px;
        }

        /* ================= SISI KIRI (BIRU WANTRACE) ================= */
        .left-panel {
            background-color: #015987;
            padding: 3rem 2.25rem 2rem 2.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .brand-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .brand-logo-icon {
            width: 58px;
            height: auto;
            object-fit: contain;
            margin-bottom: 0.65rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
        }

        .brand-title {
            font-family: 'Quando', serif;
            font-size: 1.65rem;
            font-weight: 400;
            letter-spacing: 0.05em;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 0;
        }

        .brand-subtitle {
            font-family: 'Quando', serif;
            font-size: 0.95rem;
            font-weight: 400;
            color: #ffffff;
            letter-spacing: 0.02em;
            margin-top: 0.35rem;
            line-height: 1.2;
        }

        .illustration-section {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 1.75rem 0;
        }

        .illustration-img {
            width: 250px;
            max-width: 90%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.16));
        }

        .copyright-text {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.75);
            letter-spacing: 0.02em;
        }

        /* ================= SISI KANAN (FORM MASUK) ================= */
        .right-panel {
            background-color: #ffffff;
            padding: 3.5rem 3.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #015987;
            margin-bottom: 1.75rem;
            letter-spacing: -0.025em;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.825rem;
            margin-bottom: 1.25rem;
            line-height: 1.45;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.45rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            color: #1f2937;
            outline: none;
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }

        .form-input::placeholder {
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .form-input:focus {
            border-color: #015987;
            box-shadow: 0 0 0 3px rgba(1, 89, 135, 0.15);
        }

        .form-input.is-invalid {
            border-color: #ef4444;
            background-color: #fffafb;
        }

        .form-options {
            display: flex;
            align-items: center;
            margin: 0.25rem 0 1.65rem 0;
            font-size: 0.8rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: #4b5563;
            cursor: pointer;
            user-select: none;
        }

        .remember-me input {
            margin-right: 0.45rem;
            accent-color: #015987;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            background-color: #015987;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(1, 89, 135, 0.2);
        }

        .btn-submit:hover {
            background-color: #01466a;
            box-shadow: 0 6px 14px rgba(1, 89, 135, 0.3);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        /* ================= KOTAK BANTUAN ADMIN ================= */
        .help-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-top: 1.25rem;
            padding: 0.75rem 1rem;
            background-color: #f0f7fc;
            border: 1px solid #c8dff0;
            border-radius: 8px;
        }

        .help-notice-icon {
            flex-shrink: 0;
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.05rem;
            color: #015987;
            opacity: 0.75;
        }

        .help-notice-text {
            font-size: 0.775rem;
            color: #4a6a82;
            line-height: 1.5;
        }

        .help-notice-text strong {
            color: #015987;
            font-weight: 600;
        }

        /* ================= RESPONSIF LAYAR KECIL ================= */
        @media (max-width: 768px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                border-radius: 20px;
                max-width: 440px;
            }

            .left-panel {
                padding: 2.25rem 1.5rem;
            }

            .illustration-section {
                display: none;
            }

            .right-panel {
                padding: 2.5rem 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Panel Kiri: Identitas WANTRACE & Ilustrasi HD -->
        <div class="left-panel">
            <div class="brand-section">
                <!-- Logo Wi Kualitas Tinggi -->
                <img 
                    src="{{ asset('images/wi-logo.png') }}" 
                    alt="Logo Wi" 
                    class="brand-logo-icon"
                >
                <!-- Tipografi Elegan Vector: Tidak Pernah Blur -->
                <h1 class="brand-title">WANTRACE</h1>
                <p class="brand-subtitle">Internship System</p>
            </div>

            <!-- Ilustrasi Mahasiswa Kualitas Tinggi -->
            <div class="illustration-section">
                <img 
                    src="{{ asset('images/intern-illustration.png') }}" 
                    alt="Ilustrasi Peserta Magang PT WAN" 
                    class="illustration-img"
                >
            </div>

            <p class="copyright-text">Copyright &copy; 2026</p>
        </div>

        <!-- Panel Kanan: Form Masuk -->
        <div class="right-panel">
            <h1 class="form-title">Masuk</h1>

            {{-- Flash status berhasil logout --}}
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Alert error kredensial / akun nonaktif --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" novalidate>
                @csrf

                <!-- Input Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        placeholder="Masukkan email"
                        autocomplete="username"
                    >
                </div>

                <!-- Input Kata Sandi -->
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input @error('password') is-invalid @enderror" 
                            style="padding-right: 2.75rem;"
                            required 
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            id="togglePassword" 
                            style="position: absolute; right: 0.75rem; background: none; border: none; cursor: pointer; padding: 0.25rem; display: flex; align-items: center; color: #94a3b8; transition: color 0.2s;"
                            title="Tampilkan / Sembunyikan Kata Sandi"
                        >
                            <!-- Icon Eye Open -->
                            <svg id="eyeIconOpen" style="width: 1.25rem; height: 1.25rem; display: block;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Icon Eye Slash (Closed) -->
                            <svg id="eyeIconClosed" style="width: 1.25rem; height: 1.25rem; display: none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Opsi Ingat Saya -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="btn-submit">
                    Masuk
                </button>
            </form>

            <!-- Kotak Notifikasi Bantuan Admin/HR -->
            <div class="help-notice">
                <svg class="help-notice-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="help-notice-text">
                    Lupa password atau belum punya akun?<br>
                    Hubungi <strong>Admin / HR PT WAN</strong> untuk mendapatkan akses.
                </p>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIconOpen = document.getElementById('eyeIconOpen');
        const eyeIconClosed = document.getElementById('eyeIconClosed');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIconOpen.style.display = isPassword ? 'none' : 'block';
                eyeIconClosed.style.display = isPassword ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
