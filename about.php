<?php
require_once 'function/copyright.php';
?>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPeduli - Tentang Kami</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:   #1d4ed8;
            --accent:    #06b6d4;
            --warm:      #f97316;
            --soft-bg:   #f0f7ff;
            --card-bg:   #ffffff;
            --text-main: #0f172a;
            --text-muted:#64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--soft-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main { flex: 1; }

        /* ── NAV ─────────────────────────────── */
        nav {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid #e2eaf5;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 16px rgba(29,78,216,.06);
        }

        .nav-inner {
            max-width: 1200px;
            margin: auto;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-family: 'Fraunces', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-logo span { color: var(--accent); }

        .nav-links { display: flex; gap: 4px; align-items: center; }

        .nav-links a {
            padding: 7px 13px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-muted);
            transition: all .2s;
        }

        .nav-links a:hover { background: #e8f0fe; color: var(--primary); }
        .nav-links a.active { background: var(--primary); color: white; }
        .nav-links a.danger { color: #dc2626; }
        .nav-links a.danger:hover { background: #fee2e2; color: #991b1b; }

        .nav-links a.ai-link {
            background: linear-gradient(135deg, #6366f1, #06b6d4);
            color: white;
            font-weight: 600;
        }

        /* ── HERO ─────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1d4ed8 0%, #0891b2 60%, #0e7490 100%);
            padding: 72px 24px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: white;
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            backdrop-filter: blur(6px);
        }

        .hero h1 {
            font-family: 'Fraunces', serif;
            font-size: clamp(28px, 5vw, 48px);
            color: white;
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .hero h1 em {
            font-style: italic;
            color: #7dd3fc;
        }

        .hero p {
            color: rgba(255,255,255,.85);
            font-size: 16px;
            max-width: 560px;
            margin: 0 auto 32px;
            line-height: 1.7;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: var(--primary);
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            transition: transform .2s, box-shadow .2s;
        }

        .hero-btn:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.2); }

        /* ── STATS BAR ─────────────────────────── */
        .stats-bar {
            background: white;
            border-bottom: 1px solid #e2eaf5;
            padding: 0;
        }

        .stats-inner {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            divide-x: 1px solid #e2eaf5;
        }

        .stat-item {
            padding: 22px 20px;
            text-align: center;
            border-right: 1px solid #e2eaf5;
        }

        .stat-item:last-child { border-right: none; }

        .stat-item .num {
            font-family: 'Fraunces', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-item .lbl {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── QUICK ACTIONS ─────────────────────── */
        .section-wrap {
            max-width: 1200px;
            margin: auto;
            padding: 48px 24px;
        }

        .section-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .section-title {
            font-family: 'Fraunces', serif;
            font-size: clamp(22px, 3vw, 30px);
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .section-sub {
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 32px;
        }

        /* Quick Action Cards */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 48px;
        }

        .quick-card {
            background: white;
            border: 1.5px solid #e2eaf5;
            border-radius: 16px;
            padding: 22px 20px;
            text-decoration: none;
            text-align: center;
            transition: all .25s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .quick-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(29,78,216,.1);
        }

        .quick-card .qicon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .quick-card .qtitle {
            font-weight: 700;
            font-size: 14px;
            color: var(--text-main);
        }

        .quick-card .qdesc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* ── AI BANNER ─────────────────────────── */
        .ai-banner {
            background: linear-gradient(135deg, #1e1b4b, #312e81, #1d4ed8);
            border-radius: 20px;
            padding: 36px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .ai-banner .left h3 {
            font-family: 'Fraunces', serif;
            font-size: 22px;
            color: white;
            margin-bottom: 8px;
        }

        .ai-banner .left p {
            color: rgba(255,255,255,.75);
            font-size: 14px;
            line-height: 1.6;
            max-width: 480px;
        }

        .ai-banner-btns { display: flex; gap: 12px; flex-wrap: wrap; }

        .ai-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13.5px;
            text-decoration: none;
            transition: all .2s;
        }

        .ai-btn.primary { background: white; color: #1e1b4b; }
        .ai-btn.secondary { background: rgba(255,255,255,.15); color: white; border: 1px solid rgba(255,255,255,.3); }
        .ai-btn:hover { transform: translateY(-2px); }

        /* ── ACTIVITY CARDS ─────────────────────── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .act-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #e8f0fe;
            transition: all .28s;
            display: flex;
            flex-direction: column;
        }

        .act-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(29,78,216,.1);
            border-color: #bfdbfe;
        }

        .act-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .act-card-body { padding: 20px; flex: 1; }

        .act-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .act-card-body h3 {
            font-weight: 700;
            font-size: 16px;
            color: var(--text-main);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .act-card-body p {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.65;
        }

        /* ── PROGRESS BAR WIDGET ─────────────────── */
        .progress-section {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid #e2eaf5;
            margin-bottom: 48px;
        }

        .prog-item { margin-bottom: 20px; }
        .prog-item:last-child { margin-bottom: 0; }

        .prog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .prog-name { font-weight: 600; font-size: 14px; }
        .prog-pct  { font-size: 13px; font-weight: 700; color: var(--primary); }

        .prog-track {
            height: 8px;
            background: #e8f0fe;
            border-radius: 99px;
            overflow: hidden;
        }

        .prog-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 1.4s cubic-bezier(.4,0,.2,1);
            width: 0;
        }

        /* ── TESTIMONI ───────────────────────────── */
        .testi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 48px;
        }

        .testi-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2eaf5;
        }

        .testi-stars { color: #f59e0b; font-size: 13px; margin-bottom: 12px; }

        .testi-text {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 16px;
            font-style: italic;
        }

        .testi-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .testi-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .testi-name { font-weight: 700; font-size: 13px; }
        .testi-role { font-size: 12px; color: var(--text-muted); }

        /* ── FOOTER ──────────────────────────────── */
        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 28px 24px;
            margin-top: auto;
        }

        .footer-inner {
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-socials { display: flex; gap: 14px; }

        .footer-socials a {
            color: #64748b;
            font-size: 18px;
            transition: color .2s;
        }

        .footer-socials a:hover { color: white; }

        /* ── SCROLL TO TOP ───────────────────────── */
        #scrollTop {
            position: fixed;
            bottom: 28px;
            right: 28px;
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(29,78,216,.35);
            transition: transform .2s;
            z-index: 99;
        }

        #scrollTop:hover { transform: translateY(-3px); }

        /* ── ANIMATIONS ──────────────────────────── */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .6s ease, transform .6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: none;
        }

        /* ── MOBILE ──────────────────────────────── */
        @media (max-width: 768px) {
            .stats-inner { grid-template-columns: repeat(2, 1fr); }
            .ai-banner { flex-direction: column; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

<!-- ── NAV ─────────────────────────────────── -->
<nav>
    <div class="nav-inner">
        <a href="#" class="nav-logo">Si<span>Peduli</span></a>
        <div class="nav-links">
            <a href="form_pengaduan.php">Form Pengaduan</a>
            <a href="tambah_donasi.php">Donate</a>
            <a href="data_donasi.php">Data Donasi</a>
            <a href="profile.php">Profil</a>
            <a href="login.php">Admin</a>
            <a href="prediksi_ai.php" class="ai-link"><i class="fas fa-robot"></i> Prediksi AI</a>
            <a href="grafik_AI.php"><i class="fas fa-chart-line"></i> Analisis AI</a>
            <a href="logout.php" class="danger">Logout</a>
        </div>
        <!-- Mobile burger -->
        <button id="menu-button" class="md:hidden text-gray-600 text-xl focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden px-4 pb-4 flex flex-col gap-1 bg-white border-t border-gray-100">
        <a href="form_pengaduan.php" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 text-sm">Form Pengaduan</a>
        <a href="tambah_donasi.php" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 text-sm">Donate</a>
        <a href="data_donasi.php" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 text-sm">Data Donasi</a>
        <a href="profile.php" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 text-sm">Profil</a>
        <a href="login.php" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 text-sm">Admin</a>
        <a href="prediksi_ai.php" class="block px-3 py-2 rounded-lg text-indigo-600 font-semibold text-sm">Prediksi AI</a>
        <a href="grafik_AI.php" class="block px-3 py-2 rounded-lg text-blue-700 text-sm">Analisis AI</a>
        <a href="logout.php" class="block px-3 py-2 rounded-lg text-red-600 text-sm">Logout</a>
    </div>
</nav>

<main>

<!-- ── HERO ─────────────────────────────────── -->
<section class="hero">
    <div style="position:relative;z-index:1;">
        <div class="hero-badge">
            <i class="fas fa-heart"></i> Program Donasi SiPeduli
        </div>
        <h1>Bersama Kita Bisa Membuat<br><em>Perubahan Nyata</em></h1>
        <p>Bergabunglah dengan ribuan donatur yang telah mempercayakan donasinya kepada kami untuk menjangkau mereka yang paling membutuhkan.</p>
        <a href="tambah_donasi.php" class="hero-btn">
            <i class="fas fa-donate"></i> Donasi Sekarang
        </a>
    </div>
</section>

<!-- ── STATS BAR ──────────────────────────────── -->
<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat-item">
            <div class="num" data-target="1240">0</div>
            <div class="lbl">Total Donatur</div>
        </div>
        <div class="stat-item">
            <div class="num" data-target="86" data-prefix="Rp " data-suffix="Jt+">0</div>
            <div class="lbl">Dana Terkumpul</div>
        </div>
        <div class="stat-item">
            <div class="num" data-target="320">0</div>
            <div class="lbl">Penerima Manfaat</div>
        </div>
        <div class="stat-item">
            <div class="num" data-target="6">0</div>
            <div class="lbl">Program Aktif</div>
        </div>
    </div>
</div>

<!-- ── QUICK ACTIONS ──────────────────────────── -->
<div class="section-wrap">
    <div class="reveal">
        <div class="section-label">Aksi Cepat</div>
        <div class="section-title">Apa yang Ingin Kamu Lakukan?</div>
        <div class="section-sub">Pilih aksi di bawah ini untuk mulai berkontribusi.</div>
    </div>

    <div class="quick-grid reveal">
        <a href="tambah_donasi.php" class="quick-card">
            <div class="qicon" style="background:#eff6ff;">💙</div>
            <div class="qtitle">Donasi Sekarang</div>
            <div class="qdesc">Berikan donasi untuk program yang kamu pedulikan</div>
        </a>
        <a href="data_donasi.php" class="quick-card">
            <div class="qicon" style="background:#f0fdf4;">📋</div>
            <div class="qtitle">Lihat Data Donasi</div>
            <div class="qdesc">Pantau riwayat dan detail semua donasi</div>
        </a>
        <a href="prediksi_ai.php" class="quick-card">
            <div class="qicon" style="background:#eef2ff;">🤖</div>
            <div class="qtitle">Prediksi AI</div>
            <div class="qdesc">Gunakan AI untuk memprediksi potensi donasi</div>
        </a>
        <a href="grafik_AI.php" class="quick-card">
            <div class="qicon" style="background:#fff7ed;">📈</div>
            <div class="qtitle">Analisis Grafik</div>
            <div class="qdesc">Lihat tren donasi dalam grafik interaktif</div>
        </a>
        <a href="form_pengaduan.php" class="quick-card">
            <div class="qicon" style="background:#fdf4ff;">📝</div>
            <div class="qtitle">Form Pengaduan</div>
            <div class="qdesc">Sampaikan masukan atau laporan kamu</div>
        </a>
        <a href="profile.php" class="quick-card">
            <div class="qicon" style="background:#fff1f2;">👤</div>
            <div class="qtitle">Profil Saya</div>
            <div class="qdesc">Kelola data dan preferensi akunmu</div>
        </a>
    </div>

    <!-- ── AI BANNER ───────────────────────────── -->
    <div class="ai-banner reveal">
        <div class="left">
            <h3>✨ Fitur Prediksi AI Tersedia!</h3>
            <p>Manfaatkan kecerdasan buatan untuk menganalisis potensi donasi berdasarkan kategori dan jenis donasi. Dapatkan insight akurat untuk keputusan yang lebih baik.</p>
        </div>
        <div class="ai-banner-btns">
            <a href="prediksi_ai.php" class="ai-btn primary"><i class="fas fa-robot"></i> Coba Prediksi AI</a>
            <a href="grafik_AI.php" class="ai-btn secondary"><i class="fas fa-chart-area"></i> Lihat Analisis</a>
        </div>
    </div>

    <!-- ── PROGRESS DONASI ─────────────────────── -->
    <div class="reveal" style="margin-bottom:16px;">
        <div class="section-label">Progress Program</div>
        <div class="section-title">Capaian Program Donasi</div>
        <div class="section-sub">Seberapa jauh program kami telah berjalan.</div>
    </div>

    <div class="progress-section reveal">
        <div class="prog-item">
            <div class="prog-header">
                <span class="prog-name">🏫 Pendidikan Anak</span>
                <span class="prog-pct">78%</span>
            </div>
            <div class="prog-track"><div class="prog-fill" data-w="78"></div></div>
        </div>
        <div class="prog-item">
            <div class="prog-header">
                <span class="prog-name">🏥 Kesehatan Masyarakat</span>
                <span class="prog-pct">61%</span>
            </div>
            <div class="prog-track"><div class="prog-fill" data-w="61"></div></div>
        </div>
        <div class="prog-item">
            <div class="prog-header">
                <span class="prog-name">🌱 Lingkungan Hidup</span>
                <span class="prog-pct">45%</span>
            </div>
            <div class="prog-track"><div class="prog-fill" data-w="45"></div></div>
        </div>
        <div class="prog-item">
            <div class="prog-header">
                <span class="prog-name">🍱 Bantuan Pangan</span>
                <span class="prog-pct">90%</span>
            </div>
            <div class="prog-track"><div class="prog-fill" data-w="90"></div></div>
        </div>
    </div>

    <!-- ── KEGIATAN ────────────────────────────── -->
    <div class="reveal" style="margin-bottom:24px;">
        <div class="section-label">Kegiatan Kami</div>
        <div class="section-title">Aksi Nyata di Lapangan</div>
        <div class="section-sub">Lihat bagaimana donasi kamu berdampak langsung pada masyarakat.</div>
    </div>

    <div class="cards-grid reveal">
        <div class="act-card">
            <img src="https://storage.googleapis.com/a1aa/image/SnqY0nEIx9B9NPPFLi4PekS0PoErUiyxffWEHRS-S-8.jpg" alt="Distribusi Makanan">
            <div class="act-card-body">
                <span class="act-tag" style="background:#fef9c3;color:#92400e;">🍱 Pangan</span>
                <h3>Distribusi Makanan</h3>
                <p>Relawan kami mendistribusikan makanan kepada mereka yang membutuhkan. Inisiatif ini memastikan tidak ada yang kelaparan.</p>
            </div>
        </div>
        <div class="act-card">
            <img src="https://storage.googleapis.com/a1aa/image/_fPIRlBZ4xrnPFWCKnuXO5pVuUUXYh9KUWQ7uaBtpio.jpg" alt="Pembersihan Lingkungan">
            <div class="act-card-body">
                <span class="act-tag" style="background:#dcfce7;color:#166534;">🌿 Lingkungan</span>
                <h3>Pembersihan Lingkungan</h3>
                <p>Acara bersih-bersih komunitas untuk mempromosikan lingkungan yang lebih bersih dan sehat bagi semua orang.</p>
            </div>
        </div>
        <div class="act-card">
            <img src="https://storage.googleapis.com/a1aa/image/TGEFSH3pB-eIROdaLqOGA4Ixw8jZdMtSCOiAtyE33Fk.jpg" alt="Perlengkapan Sekolah">
            <div class="act-card-body">
                <span class="act-tag" style="background:#dbeafe;color:#1e40af;">📚 Pendidikan</span>
                <h3>Distribusi Perlengkapan Sekolah</h3>
                <p>Mendistribusikan perlengkapan sekolah kepada anak-anak untuk mendukung pendidikan dan masa depan mereka.</p>
            </div>
        </div>
        <div class="act-card">
            <img src="https://storage.googleapis.com/a1aa/image/xrJAA3V63oVbYGybUEW37Pj9mfoWjSkX70Nk1Gp5g38.jpg" alt="Kamp Medis">
            <div class="act-card-body">
                <span class="act-tag" style="background:#fce7f3;color:#9d174d;">❤️ Kesehatan</span>
                <h3>Kamp Medis Gratis</h3>
                <p>Kamp medis yang menyediakan pemeriksaan dan bantuan gratis bagi mereka yang tidak mampu membayar layanan kesehatan.</p>
            </div>
        </div>
        <div class="act-card">
            <img src="https://storage.googleapis.com/a1aa/image/MpGR2DaZfHSDhlyFFgAOO8DW494SGtEI5POsCjIzBvg.jpg" alt="Penanaman Pohon">
            <div class="act-card-body">
                <span class="act-tag" style="background:#d1fae5;color:#065f46;">🌳 Hijau</span>
                <h3>Penanaman Pohon</h3>
                <p>Acara penanaman pohon untuk mempromosikan kelestarian lingkungan dan memerangi perubahan iklim bersama-sama.</p>
            </div>
        </div>
        <div class="act-card">
            <img src="https://storage.googleapis.com/a1aa/image/PFVVmOvUF-IyZgbbZG3rXRFY_5I9d_yUW_4Hfxcf67U.jpg" alt="Fundraising">
            <div class="act-card-body">
                <span class="act-tag" style="background:#ede9fe;color:#5b21b6;">💜 Sosial</span>
                <h3>Fundraising Event</h3>
                <p>Acara penggalangan dana untuk mengumpulkan sumber daya bagi berbagai program komunitas yang sedang berjalan.</p>
            </div>
        </div>
    </div>

    <!-- ── TESTIMONI ───────────────────────────── -->
    <div class="reveal" style="margin-bottom:24px;">
        <div class="section-label">Testimoni</div>
        <div class="section-title">Kata Mereka yang Sudah Berdonasi</div>
        <div class="section-sub">Ribuan donatur telah mempercayai SiPeduli.</div>
    </div>

    <div class="testi-grid reveal">
        <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-text">"Prosesnya sangat mudah dan transparan. Saya bisa lihat langsung dampak donasi saya. Sangat memuaskan!"</p>
            <div class="testi-author">
                <div class="testi-avatar">AR</div>
                <div>
                    <div class="testi-name">Andi Rahmat</div>
                    <div class="testi-role">Donatur sejak 2024</div>
                </div>
            </div>
        </div>
        <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-text">"Fitur Prediksi AI sangat membantu saya memahami kategori donasi mana yang paling berdampak besar."</p>
            <div class="testi-author">
                <div class="testi-avatar">SM</div>
                <div>
                    <div class="testi-name">Siti Maharani</div>
                    <div class="testi-role">Donatur Rutin</div>
                </div>
            </div>
        </div>
        <div class="testi-card">
            <div class="testi-stars">★★★★☆</div>
            <p class="testi-text">"Platform yang sangat baik! Data donasi lengkap dan grafik analisisnya memudahkan pemantauan."</p>
            <div class="testi-author">
                <div class="testi-avatar">BW</div>
                <div>
                    <div class="testi-name">Budi Wicaksono</div>
                    <div class="testi-role">Relawan & Donatur</div>
                </div>
            </div>
        </div>
    </div>

</div><!-- end section-wrap -->
</main>

<!-- ── FOOTER ─────────────────────────────────── -->
<footer>
    <div class="footer-inner">
        <span style="font-size:13px;">&copy; <?= $copyright; ?></span>
        <div class="footer-socials">
            <a href="https://wa.me/6285810216030" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://www.instagram.com/mooneloz.id" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<!-- Scroll to top -->
<button id="scrollTop"><i class="fas fa-arrow-up"></i></button>

<script>
// Mobile menu toggle
document.getElementById('menu-button').addEventListener('click', function () {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});

// Scroll to top
const scrollBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    scrollBtn.style.display = window.scrollY > 300 ? 'flex' : 'none';
});
scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// Animated counter for stats
function animateCounter(el) {
    const target = parseInt(el.dataset.target);
    const prefix = el.dataset.prefix || '';
    const suffix = el.dataset.suffix || '';
    let current = 0;
    const step  = Math.ceil(target / 60);
    const timer = setInterval(() => {
        current += step;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = prefix + current.toLocaleString('id-ID') + suffix;
    }, 20);
}

// Intersection Observer for reveal + counters + progress bars
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// Counter observer (stats bar always visible on load)
const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.querySelectorAll('[data-target]').forEach(animateCounter);
            counterObs.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.stats-inner').forEach(el => counterObs.observe(el));

// Progress bar animation
const progObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.querySelectorAll('.prog-fill').forEach(bar => {
                bar.style.width = bar.dataset.w + '%';
            });
            progObs.unobserve(entry.target);
        }
    });
}, { threshold: 0.3 });

document.querySelectorAll('.progress-section').forEach(el => progObs.observe(el));
</script>
</body>
</html>