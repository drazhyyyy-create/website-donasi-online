<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sipeduli_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$anggota = ['zidan', 'raihan', 'fadhil', 'jonathan', 'evan', 'heru'];
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = strtolower(trim($_POST['username']));
    $password = trim($_POST['password']);

    if (!preg_match('/^[a-z0-9]+$/', $username)) {
        $error = "Username hanya boleh huruf dan angka (tanpa simbol).";
    } elseif (in_array($username, $anggota)) {
        $_SESSION['admin'] = $username;

        $stmt = $conn->prepare("INSERT INTO admin_login (username) VALUES (?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        header("Location: dashboard2.php");
        exit;
    } else {
        $error = "Username tidak dikenali. Hanya anggota terdaftar yang boleh masuk.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin SiPeduli</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0a0f1e;
      --card: #111827;
      --border: rgba(255,255,255,0.06);
      --accent: #3b82f6;
      --accent2: #6366f1;
      --text: #f1f5f9;
      --muted: #64748b;
      --input-bg: #0d1426;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    /* Background grid glow */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 60% at 20% 20%, rgba(59,130,246,0.08) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 80% 80%, rgba(99,102,241,0.07) 0%, transparent 60%);
      pointer-events: none;
    }

    /* Subtle dot grid */
    body::after {
      content: '';
      position: fixed;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 32px 32px;
      pointer-events: none;
    }

    /* ── Login Card ── */
    .login-wrapper {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }

    /* Brand above card */
    .brand-header {
      text-align: center;
      margin-bottom: 28px;
    }

    .brand-name {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      background: linear-gradient(135deg, #3b82f6, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.5px;
    }

    .brand-sub {
      font-size: 0.72rem;
      color: var(--muted);
      font-weight: 500;
      letter-spacing: 3px;
      text-transform: uppercase;
      margin-top: 4px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 36px 32px;
      box-shadow: 0 24px 60px rgba(0,0,0,0.5);
      animation: fadeInUp 0.4s ease both;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .card-title {
      font-family: 'Syne', sans-serif;
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 4px;
    }

    .card-desc {
      font-size: 0.82rem;
      color: var(--muted);
      margin-bottom: 28px;
    }

    /* Divider line */
    .divider {
      height: 1px;
      background: var(--border);
      margin-bottom: 28px;
    }

    /* Error */
    .error-box {
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.25);
      border-radius: 10px;
      padding: 12px 14px;
      color: #fca5a5;
      font-size: 0.82rem;
      margin-bottom: 20px;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }

    .error-box svg { flex-shrink: 0; margin-top: 1px; }

    /* Form */
    .form-group { margin-bottom: 18px; }

    label {
      display: block;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--muted);
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    .input-wrap {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      width: 16px; height: 16px;
      pointer-events: none;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      background: var(--input-bg);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px 14px 12px 42px;
      font-size: 0.9rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: var(--text);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    input[type="text"]::placeholder,
    input[type="password"]::placeholder {
      color: #334155;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      border-color: rgba(59,130,246,0.5);
      box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    /* Submit button */
    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, #3b82f6, #6366f1);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 13px;
      font-size: 0.9rem;
      font-weight: 700;
      font-family: 'Plus Jakarta Sans', sans-serif;
      cursor: pointer;
      margin-top: 8px;
      transition: opacity 0.2s, transform 0.15s;
      letter-spacing: 0.3px;
    }

    .btn-submit:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    /* Footer note */
    .login-footer {
      text-align: center;
      margin-top: 20px;
      font-size: 0.72rem;
      color: var(--muted);
    }
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="brand-header">
    <div class="brand-name">SiPeduli</div>
    <div class="brand-sub">Admin Panel</div>
  </div>

  <div class="card">
    <div class="card-title">Selamat Datang</div>
    <div class="card-desc">Masuk ke panel admin SiPeduli</div>
    <div class="divider"></div>

    <?php if ($error): ?>
    <div class="error-box">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <div class="input-wrap">
          <svg class="input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
          <input
            type="text"
            name="username"
            placeholder="Masukkan username..."
            required
            pattern="[a-zA-Z0-9]+"
            title="Hanya huruf dan angka, tanpa simbol"
            autocomplete="off"
          >
        </div>
      </div>

      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <svg class="input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
          <input
            type="password"
            name="password"
            placeholder="Masukkan password..."
            required
          >
        </div>
      </div>

      <button type="submit" class="btn-submit">Masuk</button>
    </form>
  </div>

  <div class="login-footer">SiPeduli v1.0 &bull; 2025 &bull; Hanya untuk anggota terdaftar</div>
</div>

<script>
document.querySelector('input[name="username"]').addEventListener('input', function () {
  this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
});
</script>

</body>
</html>