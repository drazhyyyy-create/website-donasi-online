<?php
$koneksi = new mysqli("localhost", "root", "", "donasi");

$filter = "";
$cari = "";
if (isset($_GET['cari']) && !empty($_GET['cari'])) {
    $cari = $koneksi->real_escape_string($_GET['cari']);
    $filter = "WHERE nama LIKE '%$cari%' OR email LIKE '%$cari%' OR isi LIKE '%$cari%'";
}

$query = "SELECT * FROM laporan_pengaduan $filter ORDER BY tanggal DESC";
$data = $koneksi->query($query);
$total = $data->num_rows;

$totalAll = $koneksi->query("SELECT COUNT(*) as c FROM laporan_pengaduan")->fetch_assoc()['c'];
$totalHari = $koneksi->query("SELECT COUNT(*) as c FROM laporan_pengaduan WHERE DATE(tanggal) = CURDATE()")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Pengaduan - SiPeduli</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0a0f1e;
      --sidebar: #0d1426;
      --card: #111827;
      --border: rgba(255,255,255,0.06);
      --accent: #3b82f6;
      --accent2: #6366f1;
      --text: #f1f5f9;
      --muted: #64748b;
    }

    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
    }

    /* ── Sidebar ── */
    .sidebar {
      width: 240px;
      min-height: 100vh;
      background: var(--sidebar);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      position: fixed;
      top:0; left:0; bottom:0;
      z-index: 100;
    }

    .sidebar-logo { padding:28px 24px 20px; border-bottom:1px solid var(--border); }
    .sidebar-logo .brand {
      font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:800;
      background:linear-gradient(135deg,#3b82f6,#818cf8);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      letter-spacing:-0.5px;
    }
    .sidebar-logo .brand-sub { font-size:0.7rem; color:var(--muted); font-weight:500; letter-spacing:2px; text-transform:uppercase; margin-top:2px; }

    .sidebar-nav { padding:20px 12px; flex:1; }

    .nav-label { font-size:0.65rem; color:var(--muted); font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:0 12px; margin-bottom:8px; margin-top:16px; }

    .nav-item {
      display:flex; align-items:center; gap:10px;
      padding:10px 14px; border-radius:10px;
      color:var(--muted); text-decoration:none;
      font-size:0.875rem; font-weight:500;
      transition:all 0.2s; margin-bottom:2px;
    }
    .nav-item:hover, .nav-item.active { background:rgba(59,130,246,0.12); color:#93c5fd; }
    .nav-item.active { color:var(--accent); }
    .nav-item svg { width:16px; height:16px; flex-shrink:0; }

    .sidebar-footer { padding:16px 12px; border-top:1px solid var(--border); }

    /* ── Main ── */
    .main { margin-left:240px; flex:1; min-height:100vh; display:flex; flex-direction:column; }

    .topbar {
      background:rgba(10,15,30,0.9); backdrop-filter:blur(20px);
      border-bottom:1px solid var(--border);
      padding:0 32px; height:64px;
      display:flex; align-items:center; justify-content:space-between;
      position:sticky; top:0; z-index:50;
    }

    .topbar-title { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:700; }

    .avatar {
      width:36px; height:36px; border-radius:50%;
      background:linear-gradient(135deg,var(--accent),var(--accent2));
      display:flex; align-items:center; justify-content:center;
      font-size:0.8rem; font-weight:700;
    }

    .content { padding:32px; flex:1; }

    /* ── Stats ── */
    .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:32px; }

    .stat-card {
      background:var(--card); border:1px solid var(--border);
      border-radius:16px; padding:22px 24px;
      position:relative; overflow:hidden;
      transition:transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,0,0,0.3); }
    .stat-card::before { content:''; position:absolute; top:0;left:0;right:0; height:3px; }
    .stat-card.blue::before { background:linear-gradient(90deg,#3b82f6,#818cf8); }
    .stat-card.orange::before { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
    .stat-card.red::before { background:linear-gradient(90deg,#ef4444,#f87171); }

    .stat-label { font-size:0.75rem; color:var(--muted); font-weight:600; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:8px; }
    .stat-value { font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; }
    .stat-desc { font-size:0.75rem; color:var(--muted); margin-top:4px; }

    /* ── Search Bar ── */
    .search-bar {
      display:flex; gap:10px;
      margin-bottom:20px;
    }

    .search-input {
      flex:1;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:10px;
      padding:10px 16px;
      font-size:0.875rem;
      color:var(--text);
      font-family:'Plus Jakarta Sans',sans-serif;
      outline:none;
      transition:border-color 0.2s;
    }
    .search-input::placeholder { color:var(--muted); }
    .search-input:focus { border-color:rgba(59,130,246,0.5); }

    .search-btn {
      background:var(--accent);
      color:white;
      border:none;
      border-radius:10px;
      padding:10px 20px;
      font-size:0.875rem;
      font-weight:600;
      cursor:pointer;
      font-family:'Plus Jakarta Sans',sans-serif;
      display:flex; align-items:center; gap:6px;
      transition:opacity 0.2s;
    }
    .search-btn:hover { opacity:0.85; }

    .reset-btn {
      background:rgba(255,255,255,0.06);
      color:var(--muted);
      border:1px solid var(--border);
      border-radius:10px;
      padding:10px 16px;
      font-size:0.875rem;
      font-weight:600;
      cursor:pointer;
      font-family:'Plus Jakarta Sans',sans-serif;
      text-decoration:none;
      display:flex; align-items:center;
      transition:all 0.2s;
    }
    .reset-btn:hover { color:var(--text); background:rgba(255,255,255,0.1); }

    /* ── Table ── */
    .table-section { background:var(--card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }

    .table-header {
      padding:20px 28px;
      display:flex; align-items:center; justify-content:space-between;
      border-bottom:1px solid var(--border);
    }

    .table-title { font-family:'Syne',sans-serif; font-size:1rem; font-weight:700; }

    .table-count {
      background:rgba(59,130,246,0.15); color:#93c5fd;
      border:1px solid rgba(59,130,246,0.3);
      padding:4px 12px; border-radius:999px;
      font-size:0.75rem; font-weight:600;
    }

    .search-tag {
      background:rgba(245,158,11,0.12); color:#fcd34d;
      border:1px solid rgba(245,158,11,0.25);
      padding:4px 10px; border-radius:999px;
      font-size:0.72rem; font-weight:600;
    }

    table { width:100%; border-collapse:collapse; }

    thead th {
      background:rgba(255,255,255,0.03);
      padding:14px 20px;
      text-align:left;
      font-size:0.7rem; font-weight:700;
      letter-spacing:1.5px; text-transform:uppercase;
      color:var(--muted);
      border-bottom:1px solid var(--border);
    }

    tbody td {
      padding:16px 20px;
      font-size:0.875rem; color:#cbd5e1;
      border-bottom:1px solid rgba(255,255,255,0.04);
      vertical-align:top;
    }

    tbody tr:last-child td { border-bottom:none; }
    tbody tr { transition:background 0.15s; }
    tbody tr:hover td { background:rgba(59,130,246,0.05); }

    .no-cell { color:var(--muted); font-size:0.8rem; font-weight:600; vertical-align:middle; }
    .name-cell { font-weight:600; color:var(--text); }
    .muted-cell { color:var(--muted); font-size:0.82rem; }

    .user-cell { display:flex; align-items:center; gap:10px; }
    .user-avatar {
      width:32px; height:32px; border-radius:50%;
      background:linear-gradient(135deg,#f59e0b,#fbbf24);
      display:flex; align-items:center; justify-content:center;
      font-size:0.72rem; font-weight:700; flex-shrink:0; color:white;
    }

    /* Isi pengaduan */
    .isi-cell {
      max-width: 320px;
      line-height: 1.6;
      color: #94a3b8;
      font-size: 0.83rem;
    }

    .date-cell { font-size:0.78rem; color:var(--muted); font-family:monospace; letter-spacing:0.3px; white-space:nowrap; vertical-align:middle; }

    /* Empty state */
    .empty-state {
      text-align:center; padding:60px 20px; color:var(--muted);
    }
    .empty-state svg { width:48px; height:48px; margin-bottom:12px; opacity:0.3; }
    .empty-state p { font-size:0.9rem; }

    @keyframes fadeInUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
    tbody tr { animation:fadeInUp 0.3s ease both; }
    <?php for($i=1;$i<=30;$i++): ?>
    tbody tr:nth-child(<?= $i ?>) { animation-delay:<?= $i*0.04 ?>s; }
    <?php endfor; ?>
  </style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">SiPeduli</div>
    <div class="brand-sub">Admin Panel</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Menu Utama</div>
    <a href="dashboard2.php" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Data Donasi
    </a>
    <a href="biodata_user.php" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Data Login
    </a>
    <a href="laporan_pengaduan.php" class="nav-item active">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
      Laporan Pengaduan
    </a>
    <div class="nav-label" style="margin-top:24px;">Sistem</div>
    <a href="logout.php" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
      Logout
    </a>
  </nav>
  <div class="sidebar-footer">
    <div style="font-size:0.7rem;color:var(--muted);text-align:center;">SiPeduli v1.0 &bull; 2025</div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="topbar-title">Laporan Pengaduan</div>
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="font-size:0.82rem;color:var(--muted);">Administrator</div>
      <div class="avatar">A</div>
    </div>
  </header>

  <div class="content">

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card blue">
        <div class="stat-label">Total Pengaduan</div>
        <div class="stat-value"><?= $totalAll ?></div>
        <div class="stat-desc">Semua laporan masuk</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-label">Hari Ini</div>
        <div class="stat-value"><?= $totalHari ?></div>
        <div class="stat-desc">Laporan hari ini</div>
      </div>
      <div class="stat-card red">
        <div class="stat-label">Hasil Pencarian</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-desc"><?= $cari ? "Filter: \"".htmlspecialchars($cari)."\"" : "Semua ditampilkan" ?></div>
      </div>
    </div>

    <!-- Search -->
    <form method="get" style="display:contents;">
      <div class="search-bar">
        <input
          type="text"
          name="cari"
          class="search-input"
          placeholder="Cari berdasarkan nama, email, atau isi pengaduan..."
          value="<?= htmlspecialchars($cari) ?>"
        >
        <button type="submit" class="search-btn">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          Cari
        </button>
        <?php if($cari): ?>
        <a href="laporan_pengaduan.php" class="reset-btn">Reset</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Table -->
    <div class="table-section">
      <div class="table-header">
        <div class="table-title">Daftar Pengaduan</div>
        <div style="display:flex;gap:8px;align-items:center;">
          <?php if($cari): ?>
          <span class="search-tag">Filter aktif: <?= htmlspecialchars($cari) ?></span>
          <?php endif; ?>
          <span class="table-count"><?= $total ?> laporan</span>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Pelapor</th>
            <th>Email</th>
            <th>Isi Pengaduan</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; if($total > 0): while($row = $data->fetch_assoc()): ?>
          <tr>
            <td class="no-cell"><?= $no++ ?></td>
            <td>
              <div class="user-cell">
                <div class="user-avatar"><?= strtoupper(substr($row['nama'],0,1)) ?></div>
                <span class="name-cell"><?= htmlspecialchars($row['nama']) ?></span>
              </div>
            </td>
            <td class="muted-cell"><?= htmlspecialchars($row['email']) ?></td>
            <td class="isi-cell"><?= htmlspecialchars($row['isi']) ?></td>
            <td class="date-cell"><?= $row['tanggal'] ?></td>
          </tr>
          <?php endwhile; else: ?>
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Tidak ada pengaduan<?= $cari ? " untuk \"".htmlspecialchars($cari)."\"" : "" ?>.</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

</body>
</html>