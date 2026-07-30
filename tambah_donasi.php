<?php
require_once 'function/tambah.php';
require_once 'function/copyright.php';

$databerhasil = "";
$redirectUrl = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (tambah($_POST) > 0) {
        $databerhasil = "success";
        $redirectUrl = "index.php";
    } else {
        $databerhasil = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pilihan Donasi - SiPeduli</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0a0f1e;
      --card: #111827;
      --card-hover: #161f35;
      --border: rgba(255,255,255,0.06);
      --border-hover: rgba(59,130,246,0.35);
      --accent: #3b82f6;
      --accent2: #6366f1;
      --text: #f1f5f9;
      --muted: #64748b;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    /* Background glow */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 70% 50% at 10% 10%, rgba(59,130,246,0.07) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 90% 90%, rgba(99,102,241,0.06) 0%, transparent 60%);
      pointer-events: none;
    }

    body::after {
      content: '';
      position: fixed;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
      background-size: 32px 32px;
      pointer-events: none;
    }

    /* ── Topbar ── */
    .topbar {
      background: rgba(13,20,38,0.95);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      padding: 0 32px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .brand {
      font-family: 'Syne', sans-serif;
      font-size: 1.3rem;
      font-weight: 800;
      background: linear-gradient(135deg, #3b82f6, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.5px;
      text-decoration: none;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.82rem;
      color: var(--muted);
    }

    .topbar-right svg { width: 15px; height: 15px; }

    /* ── Page Content ── */
    .page {
      max-width: 900px;
      margin: 0 auto;
      padding: 40px 24px 60px;
      position: relative;
      z-index: 1;
    }

    /* Page heading */
    .page-heading {
      margin-bottom: 36px;
    }

    .page-heading h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--text);
      margin-bottom: 6px;
    }

    .page-heading p {
      font-size: 0.88rem;
      color: var(--muted);
    }

    .heading-line {
      display: inline-block;
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #3b82f6, #6366f1);
      border-radius: 2px;
      margin-top: 12px;
    }

    /* ── Grid ── */
    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-bottom: 16px;
    }

    /* ── Donation Card ── */
    .donation-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 28px 24px;
      cursor: pointer;
      transition: border-color 0.25s, transform 0.2s, box-shadow 0.25s, background 0.2s;
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      gap: 20px;
      animation: fadeInUp 0.4s ease both;
    }

    .donation-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      opacity: 0;
      transition: opacity 0.25s;
    }

    .donation-card:hover {
      border-color: var(--border-hover);
      background: var(--card-hover);
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(0,0,0,0.4), 0 0 0 1px rgba(59,130,246,0.1);
    }

    .donation-card:hover::before { opacity: 1; }

    /* Color accents per card */
    .card-bencana::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .card-kesehatan::before { background: linear-gradient(90deg, #ef4444, #f87171); }
    .card-panti::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .card-masjid::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .card-pendidikan::before { background: linear-gradient(90deg, #8b5cf6, #c084fc); }
    .card-perang::before { background: linear-gradient(90deg, #f97316, #fb923c); }
    .card-lainnya::before { background: linear-gradient(90deg, #64748b, #94a3b8); }

    /* Icon circle */
    .icon-wrap {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 1.8rem;
      transition: transform 0.2s;
    }

    .donation-card:hover .icon-wrap { transform: scale(1.08); }

    .icon-bencana { background: rgba(59,130,246,0.15); }
    .icon-kesehatan { background: rgba(239,68,68,0.15); }
    .icon-panti { background: rgba(245,158,11,0.15); }
    .icon-masjid { background: rgba(16,185,129,0.15); }
    .icon-pendidikan { background: rgba(139,92,246,0.15); }
    .icon-perang { background: rgba(249,115,22,0.15); }
    .icon-lainnya { background: rgba(100,116,139,0.15); }

    /* Card text */
    .card-body { flex: 1; }

    .card-title {
      font-family: 'Syne', sans-serif;
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 5px;
      letter-spacing: -0.2px;
    }

    .card-desc {
      font-size: 0.78rem;
      color: var(--muted);
      line-height: 1.5;
    }

    /* Arrow */
    .card-arrow {
      color: var(--muted);
      transition: color 0.2s, transform 0.2s;
      flex-shrink: 0;
    }

    .donation-card:hover .card-arrow {
      color: var(--accent);
      transform: translateX(3px);
    }

    /* ── Full-width "Lainnya" card ── */
    .card-lainnya-wrap {
      animation: fadeInUp 0.4s ease both;
      animation-delay: 0.28s;
    }

    .donation-card.full {
      grid-column: span 2;
    }

    /* Staggered animation */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .donation-card:nth-child(1) { animation-delay: 0.04s; }
    .donation-card:nth-child(2) { animation-delay: 0.08s; }
    .donation-card:nth-child(3) { animation-delay: 0.12s; }
    .donation-card:nth-child(4) { animation-delay: 0.16s; }
    .donation-card:nth-child(5) { animation-delay: 0.20s; }
    .donation-card:nth-child(6) { animation-delay: 0.24s; }
  </style>
</head>
<body>

<!-- Topbar -->
<header class="topbar">
  <a href="index.php" class="brand">SiPeduli</a>
  <div class="topbar-right">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>
    Pilih kategori donasi Anda
  </div>
</header>

<!-- Page -->
<div class="page">
  <div class="page-heading">
    <h1>Pilihan Donasi</h1>
    <p>Pilih kategori yang ingin Anda dukung hari ini</p>
    <span class="heading-line"></span>
  </div>

  <!-- Grid 2 kolom -->
  <div class="grid">

    <div class="donation-card card-bencana" onclick="submitDonasi('Bencana Alam')">
      <div class="icon-wrap icon-bencana">🌊</div>
      <div class="card-body">
        <div class="card-title">Donasi Bencana Alam</div>
        <div class="card-desc">Bantu korban bencana seperti gempa, banjir, dan tanah longsor.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>

    <div class="donation-card card-kesehatan" onclick="submitDonasi('Kesehatan')">
      <div class="icon-wrap icon-kesehatan">🏥</div>
      <div class="card-body">
        <div class="card-title">Donasi Kesehatan</div>
        <div class="card-desc">Berikan bantuan untuk pengobatan, operasi, dan alat kesehatan.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>

    <div class="donation-card card-panti" onclick="submitDonasi('Panti Asuhan')">
      <div class="icon-wrap icon-panti">🏠</div>
      <div class="card-body">
        <div class="card-title">Donasi Panti Asuhan</div>
        <div class="card-desc">Bantu kebutuhan hidup anak-anak di panti asuhan.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>

    <div class="donation-card card-masjid" onclick="submitDonasi('Masjid')">
      <div class="icon-wrap icon-masjid">🕌</div>
      <div class="card-body">
        <div class="card-title">Donasi Masjid</div>
        <div class="card-desc">Dukung pembangunan dan perawatan masjid di berbagai daerah.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>

    <div class="donation-card card-pendidikan" onclick="submitDonasi('Pendidikan')">
      <div class="icon-wrap icon-pendidikan">🎓</div>
      <div class="card-body">
        <div class="card-title">Donasi Pendidikan</div>
        <div class="card-desc">Bantu biaya sekolah anak-anak kurang mampu.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>

    <div class="donation-card card-perang" onclick="submitDonasi('Korban Perang')">
      <div class="icon-wrap icon-perang">🕊️</div>
      <div class="card-body">
        <div class="card-title">Donasi Korban Perang</div>
        <div class="card-desc">Ringankan penderitaan korban perang di berbagai wilayah konflik.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>

  </div>

  <!-- Donasi Lainnya — full width -->
  <div class="card-lainnya-wrap">
    <div class="donation-card card-lainnya" onclick="submitDonasi('Donasi Lainnya')">
      <div class="icon-wrap icon-lainnya">✨</div>
      <div class="card-body">
        <div class="card-title">Donasi Lainnya</div>
        <div class="card-desc">Masukkan jenis donasi sesuai keinginan Anda — kami siap menyalurkan ke tempat yang tepat.</div>
      </div>
      <svg class="card-arrow" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </div>
  </div>

</div>

<script>
function submitDonasi(jenis) {
  const jenisEncoded = encodeURIComponent(jenis);
  window.location.href = "Berdonasi.php?jenis=" + jenisEncoded;
}
</script>

</body>
</html>