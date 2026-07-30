<?php
session_start();
$koneksi = new mysqli("localhost", "root", "", "donasi");

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $redirect = (isset($user['role']) && $user['role'] === 'admin') ? 'dashboard2.php' : 'about.php';
            echo "<script>alert('Login berhasil!'); window.location='{$redirect}';</script>";
        } else {
            echo "<script>alert('Password salah!'); window.location='register.php';</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SiPeduli</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-deep:   #0A1628;
            --blue-mid:    #0D2149;
            --blue-rich:   #1A3A7C;
            --blue-bright: #2563EB;
            --blue-glow:   #3B82F6;
            --blue-sky:    #60A5FA;
            --blue-pale:   #DBEAFE;
            --accent:      #38BDF8;
            --accent-soft: rgba(56,189,248,0.15);
            --white:       #FFFFFF;
            --white-80:    rgba(255,255,255,0.8);
            --white-40:    rgba(255,255,255,0.4);
            --white-10:    rgba(255,255,255,0.08);
            --white-06:    rgba(255,255,255,0.06);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            min-height: 100vh;
            background: var(--blue-deep);
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: stretch;
            overflow: hidden;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            width: 48%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 56px;
            overflow: hidden;
        }

        /* deep mesh background */
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 20% 30%, rgba(37,99,235,0.35) 0%, transparent 70%),
                radial-gradient(ellipse 50% 70% at 80% 80%, rgba(56,189,248,0.2) 0%, transparent 60%),
                linear-gradient(160deg, #0D2149 0%, #0A1628 100%);
            z-index: 0;
        }

        /* animated orbit rings */
        .orbit {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(56,189,248,0.12);
            animation: spin 30s linear infinite;
            z-index: 0;
        }
        .orbit-1 { width:480px; height:480px; top:-100px; left:-160px; animation-duration:40s; }
        .orbit-2 { width:320px; height:320px; top:80px; left:-40px; animation-direction:reverse; animation-duration:28s; }
        .orbit-3 { width:180px; height:180px; top:200px; left:80px; animation-duration:18s; }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: floatUp linear infinite;
            z-index: 0;
        }
        @keyframes floatUp {
            0%   { transform: translateY(0) scale(1); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.6; }
            100% { transform: translateY(-80vh) scale(0.5); opacity: 0; }
        }

        .left-content {
            position: relative;
            z-index: 2;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--white-10);
            border: 1px solid rgba(56,189,248,0.25);
            border-radius: 100px;
            padding: 6px 16px 6px 8px;
            margin-bottom: 36px;
            backdrop-filter: blur(8px);
        }
        .brand-pill .dot {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, var(--blue-bright), var(--accent));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-pill .dot svg { width:14px; height:14px; fill: white; }
        .brand-pill span {
            font-family: 'Syne', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: var(--white-80);
            letter-spacing: 0.5px;
        }

        .hero-heading {
            font-family: 'Syne', sans-serif;
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
            color: white;
            margin-bottom: 20px;
        }
        .hero-heading .highlight {
            background: linear-gradient(90deg, var(--blue-sky), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: 15px;
            font-weight: 300;
            color: var(--white-40);
            line-height: 1.7;
            max-width: 320px;
            margin-bottom: 48px;
        }



        /* vertical separator between panels */
        .sep {
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(56,189,248,0.2) 30%, rgba(56,189,248,0.2) 70%, transparent);
            flex-shrink: 0;
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 70% 50%, rgba(37,99,235,0.08) 0%, transparent 70%);
        }

        .form-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 380px;
            background: var(--white-06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 42px 40px;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(56,189,248,0.06),
                0 24px 64px rgba(0,0,0,0.4),
                inset 0 1px 0 rgba(255,255,255,0.1);
            animation: cardIn 0.8s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes cardIn {
            from { opacity:0; transform:translateX(24px) scale(0.97); }
            to   { opacity:1; transform:translateX(0) scale(1); }
        }

        /* top glow line */
        .card-glow-line {
            position: absolute;
            top: 0; left: 40px; right: 40px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            border-radius: 100px;
        }

        .form-header {
            margin-bottom: 32px;
        }
        .form-title {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 6px;
        }
        .form-sub {
            font-size: 13.5px;
            color: var(--white-40);
            font-weight: 300;
        }
        .form-sub strong {
            color: var(--accent);
            font-weight: 500;
        }

        /* fields */
        .field-wrap {
            margin-bottom: 16px;
        }
        .field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--white-40);
            margin-bottom: 8px;
        }
        .field-label svg { opacity: 0.6; }

        .input-box {
            position: relative;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 44px 13px 16px;
            background: var(--white-10);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 14.5px;
            font-weight: 400;
            color: white;
            outline: none;
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
        }
        input::placeholder { color: var(--white-40); font-weight: 300; }
        input:focus {
            border-color: var(--accent);
            background: rgba(56,189,248,0.08);
            box-shadow: 0 0 0 3px rgba(56,189,248,0.12);
        }

        .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--white-40);
            display: flex; align-items: center;
        }
        .toggle-pw {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--white-40);
            display: flex;
            transition: color 0.2s;
            padding: 0;
        }
        .toggle-pw:hover { color: var(--accent); }

        /* bottom row of form */
        .form-meta {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }
        .link-soft {
            font-size: 12px;
            color: var(--blue-sky);
            text-decoration: none;
            font-weight: 400;
            transition: color 0.2s;
        }
        .link-soft:hover { color: var(--accent); }

        /* CTA button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--blue-bright) 0%, var(--blue-glow) 100%);
            border: none;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 500;
            color: white;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(37,99,235,0.45);
            transition: transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.3px;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.14), transparent);
            pointer-events: none;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover { transform:translateY(-2px); box-shadow:0 12px 36px rgba(37,99,235,0.55); }
        .btn-login:hover::after { left: 160%; }
        .btn-login:active { transform:translateY(0); }

        /* divider */
        .or-row {
            display: flex; align-items: center; gap: 12px;
            margin: 22px 0;
        }
        .or-line { flex:1; height:1px; background: rgba(255,255,255,0.08); }
        .or-text { font-size: 11px; color: var(--white-40); font-weight: 300; }

        .register-link {
            text-align: center;
            font-size: 13px;
            color: var(--white-40);
        }
        .register-link a {
            color: var(--blue-sky);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .register-link a:hover { color: var(--accent); }

        /* bottom branding */
        .bottom-brand {
            position: absolute;
            bottom: 28px;
            left: 0; right: 0;
            text-align: center;
            font-size: 11px;
            color: rgba(255,255,255,0.15);
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
    <!-- orbits -->
    <div class="orbit orbit-1"></div>
    <div class="orbit orbit-2"></div>
    <div class="orbit orbit-3"></div>

    <div class="left-content">
        <div class="brand-pill">
            <div class="dot">
                <svg viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402C1 3.543 4.068 2 6.935 2c2.02 0 4.11.943 5.065 3.338C12.955 2.943 15.045 2 17.065 2 19.932 2 23 3.543 23 7.191c0 4.105-5.37 8.863-11 14.402z"/></svg>
            </div>
            <span>SiPeduli Platform</span>
        </div>

        <h1 class="hero-heading">
            Bersama <br>
            <span class="highlight">Berbagi</span><br>
            Lebih Mudah
        </h1>

        <p class="hero-desc">
            Platform donasi terpercaya untuk menghubungkan
            para dermawan dengan mereka yang membutuhkan.
        </p>


    </div>
</div>

<div class="sep"></div>

<!-- RIGHT PANEL -->
<div class="right-panel">
    <div class="form-card">
        <div class="card-glow-line"></div>

        <div class="form-header">
            <div class="form-title">Masuk Akun</div>
            <div class="form-sub">Selamat datang kembali di <strong>SiPeduli</strong></div>
        </div>

        <form method="POST">
            <div class="field-wrap">
                <div class="field-label">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Username
                </div>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="field-wrap">
                <div class="field-label">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Password
                </div>
                <div class="input-box">
                    <input type="password" id="pw" name="password" placeholder="Masukkan password" required>
                    <span class="input-icon">
                        <button type="button" class="toggle-pw" onclick="togglePw()">
                            <svg id="eyeIco" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </span>
                </div>
            </div>

            <div class="form-meta">
                <a href="#" class="link-soft">Lupa password?</a>
            </div>

            <button type="submit" name="login" class="btn-login">Masuk Sekarang</button>
        </form>

        <div class="or-row">
            <div class="or-line"></div>
            <span class="or-text">atau</span>
            <div class="or-line"></div>
        </div>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>

    <div class="bottom-brand">© 2024 SiPeduli · Berbagi itu Indah</div>
</div>

<script>
    function togglePw() {
        const pw = document.getElementById('pw');
        const ico = document.getElementById('eyeIco');
        if (pw.type === 'password') {
            pw.type = 'text';
            ico.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            pw.type = 'password';
            ico.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }

    // generate floating particles
    const panel = document.querySelector('.left-panel');
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 4 + 1;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            background: rgba(56,189,248,${Math.random()*0.4+0.1});
            left: ${Math.random()*100}%;
            bottom: ${Math.random()*20}%;
            animation-duration: ${Math.random()*20+12}s;
            animation-delay: ${Math.random()*10}s;
        `;
        panel.appendChild(p);
    }
</script>
</body>
</html>