<?php
$koneksi = new mysqli("localhost", "root", "", "donasi");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_telepon = $_POST['no_telepon'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = $koneksi->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $cek->bind_param("ss", $username, $email);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username atau Email sudah digunakan!'); window.location='register.php';</script>";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO users (nama, alamat, no_telepon, email, username, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nama, $alamat, $no_telepon, $email, $username, $password);
        if ($stmt->execute()) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat registrasi.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SiPeduli</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-deep:   #0A1628;
            --blue-mid:    #0D2149;
            --blue-bright: #2563EB;
            --blue-glow:   #3B82F6;
            --blue-sky:    #60A5FA;
            --accent:      #38BDF8;
            --white:       #FFFFFF;
            --white-80:    rgba(255,255,255,0.8);
            --white-40:    rgba(255,255,255,0.4);
            --white-10:    rgba(255,255,255,0.08);
            --white-06:    rgba(255,255,255,0.05);
            --border:      rgba(255,255,255,0.1);
            --teal-glow:   rgba(56,189,248,0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: var(--blue-deep);
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: stretch;
            overflow-x: hidden;
        }

        /* ── BACKGROUND ── */
        .bg-mesh {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 65% 55% at 15% 25%, rgba(37,99,235,0.28) 0%, transparent 65%),
                radial-gradient(ellipse 50% 60% at 85% 75%, rgba(56,189,248,0.14) 0%, transparent 60%),
                linear-gradient(155deg, #0D2149 0%, #0A1628 100%);
        }
        .grid-lines {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(56,189,248,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56,189,248,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            width: 44%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 52px;
            overflow: hidden;
            z-index: 1;
        }

        .orbit {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(56,189,248,0.1);
            animation: spin linear infinite;
            z-index: 0;
        }
        .orbit-1 { width: 500px; height: 500px; top: -120px; left: -180px; animation-duration: 42s; }
        .orbit-2 { width: 320px; height: 320px; top: 60px;  left: -50px;  animation-duration: 28s; animation-direction: reverse; }
        .orbit-3 { width: 160px; height: 160px; top: 220px; left: 70px;   animation-duration: 18s; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: floatUp linear infinite;
            z-index: 0;
        }
        @keyframes floatUp {
            0%   { transform: translateY(0) scale(1); opacity: 0; }
            10%  { opacity: 0.8; }
            90%  { opacity: 0.3; }
            100% { transform: translateY(-80vh) scale(0.4); opacity: 0; }
        }

        .left-content { position: relative; z-index: 2; }

        .brand-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--white-10);
            border: 1px solid rgba(56,189,248,0.22);
            border-radius: 100px;
            padding: 6px 16px 6px 8px;
            margin-bottom: 36px;
            backdrop-filter: blur(8px);
            text-decoration: none;
        }
        .brand-pill .dot {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, var(--blue-bright), var(--accent));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-pill .dot svg { width: 14px; height: 14px; fill: white; }
        .brand-pill span {
            font-family: 'Syne', sans-serif;
            font-size: 13px; font-weight: 700;
            color: var(--white-80); letter-spacing: 0.5px;
        }

        .hero-heading {
            font-family: 'Syne', sans-serif;
            font-size: 42px; font-weight: 800; line-height: 1.15;
            color: white; margin-bottom: 18px;
        }
        .hero-heading .highlight {
            background: linear-gradient(90deg, var(--blue-sky), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .hero-desc {
            font-size: 14.5px; font-weight: 300;
            color: var(--white-40); line-height: 1.75;
            max-width: 300px; margin-bottom: 40px;
        }

        .steps-list { display: flex; flex-direction: column; gap: 16px; }
        .step-item {
            display: flex; align-items: center; gap: 14px;
        }
        .step-num {
            width: 32px; height: 32px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--blue-bright), var(--accent));
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif; font-size: 13px; font-weight: 700;
            color: white;
            box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        }
        .step-text { font-size: 13.5px; color: var(--white-40); font-weight: 300; }
        .step-text strong { color: var(--white-80); font-weight: 500; }

        /* ── SEPARATOR ── */
        .sep {
            width: 1px; flex-shrink: 0; z-index: 1;
            background: linear-gradient(to bottom, transparent, rgba(56,189,248,0.2) 30%, rgba(56,189,248,0.2) 70%, transparent);
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 40px 40px;
            position: relative; z-index: 1; overflow-y: auto;
        }

        /* ── FORM CARD ── */
        .form-card {
            width: 100%; max-width: 400px;
            background: var(--white-06);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 38px;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(56,189,248,0.05),
                0 24px 64px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.08);
            position: relative;
            animation: cardIn 0.8s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateX(24px) scale(0.97); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }

        .glow-line {
            position: absolute; top: 0; left: 40px; right: 40px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            border-radius: 100px;
        }

        .form-header { margin-bottom: 28px; }
        .form-title {
            font-family: 'Syne', sans-serif;
            font-size: 22px; font-weight: 700; color: white; margin-bottom: 5px;
        }
        .form-sub { font-size: 13px; color: var(--white-40); font-weight: 300; }
        .form-sub strong { color: var(--accent); font-weight: 500; }

        /* 2-column grid for first 3 fields */
        .field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .field-grid .field-wrap { margin-bottom: 0; }
        .field-wrap { margin-bottom: 12px; }

        .field-label {
            display: flex; align-items: center; gap: 5px;
            font-size: 10.5px; font-weight: 500;
            letter-spacing: 0.9px; text-transform: uppercase;
            color: var(--white-40); margin-bottom: 7px;
        }

        .input-box { position: relative; }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 11px 40px 11px 14px;
            background: var(--white-10);
            border: 1px solid var(--border);
            border-radius: 9px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px; font-weight: 400; color: white;
            outline: none;
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
        }
        input::placeholder { color: var(--white-40); font-weight: 300; }
        input:focus {
            border-color: var(--accent);
            background: rgba(56,189,248,0.07);
            box-shadow: 0 0 0 3px var(--teal-glow);
        }

        .input-icon {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--white-40);
            display: flex; align-items: center; pointer-events: none;
        }
        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--white-40);
            display: flex; align-items: center;
            transition: color 0.2s; padding: 0;
        }
        .toggle-pw:hover { color: var(--accent); }

        /* Submit */
        .btn-daftar {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--blue-bright), var(--blue-glow));
            border: none; border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 14.5px; font-weight: 500; color: white;
            cursor: pointer; position: relative; overflow: hidden;
            box-shadow: 0 8px 28px rgba(37,99,235,0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.3px; margin-top: 8px;
        }
        .btn-daftar::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.13), transparent);
            pointer-events: none;
        }
        .btn-daftar::after {
            content: ''; position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.14), transparent);
            transition: left 0.5s;
        }
        .btn-daftar:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(37,99,235,0.5); }
        .btn-daftar:hover::after { left: 160%; }
        .btn-daftar:active { transform: translateY(0); }

        .login-row {
            text-align: center; margin-top: 20px;
            font-size: 13px; color: var(--white-40);
        }
        .login-row a {
            color: var(--blue-sky); text-decoration: none; font-weight: 500;
            transition: color 0.2s;
        }
        .login-row a:hover { color: var(--accent); }

        .bottom-brand {
            position: absolute; bottom: 24px; left: 0; right: 0;
            text-align: center; font-size: 11px;
            color: rgba(255,255,255,0.12); letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<div class="bg-mesh"></div>
<div class="grid-lines"></div>

<!-- LEFT PANEL -->
<div class="left-panel">
    <div class="orbit orbit-1"></div>
    <div class="orbit orbit-2"></div>
    <div class="orbit orbit-3"></div>

    <div class="left-content">
        <a href="index.php" class="brand-pill">
            <div class="dot">
                <svg viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402C1 3.543 4.068 2 6.935 2c2.02 0 4.11.943 5.065 3.338C12.955 2.943 15.045 2 17.065 2 19.932 2 23 3.543 23 7.191c0 4.105-5.37 8.863-11 14.402z"/></svg>
            </div>
            <span>SiPeduli Platform</span>
        </a>

        <h1 class="hero-heading">
            Bergabung &<br>
            <span class="highlight">Mulai</span><br>
            Berbagi
        </h1>

        <p class="hero-desc">
            Daftarkan diri Anda dan jadilah bagian dari gerakan kepedulian bersama SiPeduli.
        </p>

        <div class="steps-list">
            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-text"><strong>Isi data diri</strong> dengan lengkap dan benar</div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-text"><strong>Buat akun</strong> dengan username & password</div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-text"><strong>Login</strong> dan mulai berdonasi</div>
            </div>
        </div>
    </div>
</div>

<div class="sep"></div>

<!-- RIGHT PANEL -->
<div class="right-panel">
    <div class="form-card">
        <div class="glow-line"></div>

        <div class="form-header">
            <div class="form-title">Buat Akun Baru</div>
            <div class="form-sub">Daftar ke <strong>SiPeduli</strong> secara gratis</div>
        </div>

        <form method="POST" action="">

            <!-- Row 1: Nama + Alamat -->
            <div class="field-grid">
                <div class="field-wrap">
                    <div class="field-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Nama Lengkap
                    </div>
                    <div class="input-box">
                        <input type="text" name="nama" placeholder="Nama kamu" required>
                    </div>
                </div>
                <div class="field-wrap">
                    <div class="field-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Alamat
                    </div>
                    <div class="input-box">
                        <input type="text" name="alamat" placeholder="Alamat kamu" required>
                    </div>
                </div>
            </div>

            <!-- Row 2: No Telepon + Email -->
            <div class="field-grid">
                <div class="field-wrap">
                    <div class="field-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.86a16 16 0 0 0 6.06 6.06l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        No Telepon
                    </div>
                    <div class="input-box">
                        <input type="tel" name="no_telepon" placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>
                <div class="field-wrap">
                    <div class="field-label">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Email
                    </div>
                    <div class="input-box">
                        <input type="email" name="email" placeholder="email@kamu.com" required>
                    </div>
                </div>
            </div>

            <!-- Username -->
            <div class="field-wrap">
                <div class="field-label">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a4 4 0 0 1 8 0v2"/></svg>
                    Username
                </div>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Pilih username unik" required>
                    <span class="input-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                </div>
            </div>

            <!-- Password -->
            <div class="field-wrap">
                <div class="field-label">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Password
                </div>
                <div class="input-box">
                    <input type="password" id="pw" name="password" placeholder="Buat password kuat" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <svg id="eyeIco" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="submit" class="btn-daftar">Daftar Sekarang</button>
        </form>

        <div class="login-row">
            Sudah punya akun? <a href="index.php">Masuk di sini</a>
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

    // Floating particles
    const panel = document.querySelector('.left-panel');
    for (let i = 0; i < 16; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 4 + 1;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            background: rgba(56,189,248,${Math.random()*0.35+0.1});
            left: ${Math.random()*100}%;
            bottom: ${Math.random()*15}%;
            animation-duration: ${Math.random()*18+10}s;
            animation-delay: ${Math.random()*8}s;
        `;
        panel.appendChild(p);
    }
</script>
</body>
</html>