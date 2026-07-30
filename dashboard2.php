<?php
$conn = mysqli_connect("localhost", "root", "", "donasi");
$data = mysqli_query($conn, "SELECT * FROM donatur ORDER BY tanggal_donasi DESC");
$total = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - SiPeduli</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0a0f1e;
      --sidebar: #0d1426;
      --card: #111827;
      --card-hover: #161f35;
      --border: rgba(255,255,255,0.06);
      --accent: #3b82f6;
      --accent2: #6366f1;
      --accent3: #10b981;
      --red: #ef4444;
      --text: #f1f5f9;
      --muted: #64748b;
      --subtle: #1e293b;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

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
      top: 0; left: 0; bottom: 0;
      z-index: 100;
    }

    .sidebar-logo {
      padding: 28px 24px 20px;
      border-bottom: 1px solid var(--border);
    }

    .sidebar-logo .brand {
      font-family: 'Syne', sans-serif;
      font-size: 1.4rem;
      font-weight: 800;
      background: linear-gradient(135deg, #3b82f6, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.5px;
    }

    .sidebar-logo .brand-sub {
      font-size: 0.7rem;
      color: var(--muted);
      font-weight: 500;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-top: 2px;
    }

    .sidebar-nav {
      padding: 20px 12px;
      flex: 1;
    }

    .nav-label {
      font-size: 0.65rem;
      color: var(--muted);
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 0 12px;
      margin-bottom: 8px;
      margin-top: 16px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 10px;
      color: var(--muted);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s;
      margin-bottom: 2px;
    }

    .nav-item:hover, .nav-item.active {
      background: rgba(59, 130, 246, 0.12);
      color: #93c5fd;
    }

    .nav-item.active {
      color: var(--accent);
    }

    .nav-item svg {
      width: 16px; height: 16px;
      flex-shrink: 0;
    }

    .sidebar-footer {
      padding: 16px 12px;
      border-top: 1px solid var(--border);
    }

    /* ── Main ── */
    .main {
      margin-left: 240px;
      flex: 1;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── Topbar ── */
    .topbar {
      background: rgba(10,15,30,0.9);
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

    .topbar-title {
      font-family: 'Syne', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text);
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display: flex; align-items: center; justify-content: center;
      font-size: 0.8rem; font-weight: 700;
    }

    /* ── Content ── */
    .content {
      padding: 32px;
      flex: 1;
    }

    /* ── Stat Cards ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 22px 24px;
      position: relative;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
    }

    .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #818cf8); }
    .stat-card.green::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .stat-card.orange::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

    .stat-label {
      font-size: 0.75rem;
      color: var(--muted);
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    .stat-value {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: var(--text);
    }

    .stat-desc {
      font-size: 0.75rem;
      color: var(--muted);
      margin-top: 4px;
    }

    /* ── Table Section ── */
    .table-section {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
    }

    .table-header {
      padding: 20px 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--border);
    }

    .table-title {
      font-family: 'Syne', sans-serif;
      font-size: 1rem;
      font-weight: 700;
    }

    .table-count {
      background: rgba(59,130,246,0.15);
      color: #93c5fd;
      border: 1px solid rgba(59,130,246,0.3);
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead th {
      background: rgba(255,255,255,0.03);
      padding: 14px 20px;
      text-align: left;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--muted);
      border-bottom: 1px solid var(--border);
    }

    tbody td {
      padding: 16px 20px;
      font-size: 0.875rem;
      color: #cbd5e1;
      border-bottom: 1px solid rgba(255,255,255,0.04);
      vertical-align: middle;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    tbody tr {
      transition: background 0.15s;
    }

    tbody tr:hover td {
      background: rgba(59, 130, 246, 0.05);
    }

    .no-cell {
      width: 40px;
      color: var(--muted);
      font-size: 0.8rem;
      font-weight: 600;
    }

    .name-cell {
      font-weight: 600;
      color: var(--text);
    }

    .email-cell {
      color: var(--muted);
      font-size: 0.8rem;
    }

    /* Badge */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 0.72rem;
      font-weight: 600;
    }

    .badge-blue {
      background: rgba(59,130,246,0.15);
      color: #93c5fd;
      border: 1px solid rgba(59,130,246,0.25);
    }

    .badge-green {
      background: rgba(16,185,129,0.15);
      color: #6ee7b7;
      border: 1px solid rgba(16,185,129,0.25);
    }

    .badge-purple {
      background: rgba(139,92,246,0.15);
      color: #c4b5fd;
      border: 1px solid rgba(139,92,246,0.25);
    }

    /* Link Lihat */
    .lihat-link {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      color: var(--accent);
      font-size: 0.8rem;
      font-weight: 600;
      text-decoration: none;
      padding: 4px 10px;
      border-radius: 6px;
      background: rgba(59,130,246,0.1);
      border: 1px solid rgba(59,130,246,0.2);
      transition: all 0.2s;
    }

    .lihat-link:hover {
      background: rgba(59,130,246,0.2);
    }

    .dash-cell {
      color: var(--muted);
    }

    /* Date */
    .date-cell {
      font-size: 0.78rem;
      color: var(--muted);
      font-family: monospace;
      letter-spacing: 0.3px;
    }

    /* Action Buttons */
    .action-group {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.75rem;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: all 0.2s;
      letter-spacing: 0.3px;
    }

    .btn-edit {
      background: rgba(59,130,246,0.15);
      color: #93c5fd;
      border: 1px solid rgba(59,130,246,0.3);
    }

    .btn-edit:hover {
      background: rgba(59,130,246,0.3);
      transform: translateY(-1px);
    }

    .btn-delete {
      background: rgba(239,68,68,0.12);
      color: #fca5a5;
      border: 1px solid rgba(239,68,68,0.25);
    }

    .btn-delete:hover {
      background: rgba(239,68,68,0.25);
      transform: translateY(-1px);
    }

    .btn-email {
      background: rgba(16,185,129,0.12);
      color: #6ee7b7;
      border: 1px solid rgba(16,185,129,0.25);
    }

    .btn-email:hover {
      background: rgba(16,185,129,0.25);
      transform: translateY(-1px);
    }

    /* Fade-in rows */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    tbody tr {
      animation: fadeInUp 0.3s ease both;
    }

    <?php for($i=1;$i<=20;$i++): ?>
    tbody tr:nth-child(<?= $i ?>) { animation-delay: <?= $i * 0.04 ?>s; }
    <?php endfor; ?>
  </style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">SiPeduli</div>
    <div class="brand-sub">Admin Panel</div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Menu Utama</div>

    <a href="dashboard2.php" class="nav-item active">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      Data Donasi
    </a>

    <a href="biodata_user.php" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
      </svg>
      Data Login
    </a>

    <a href="laporan_pengaduan.php" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
        <rect x="9" y="3" width="6" height="4" rx="1"/>
        <path d="M9 12h6M9 16h4"/>
      </svg>
      Laporan Pengaduan
    </a>

    <div class="nav-label" style="margin-top:24px;">Sistem</div>

    <a href="logout.php" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
      </svg>
      Logout
    </a>
  </nav>

  <div class="sidebar-footer">
    <div style="font-size:0.7rem; color:var(--muted); text-align:center;">SiPeduli v1.0 &bull; 2025</div>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-title">Dashboard Admin</div>
    <div class="topbar-right">
      <div style="font-size:0.82rem; color:var(--muted);">Administrator</div>
      <div class="avatar">A</div>
    </div>
  </header>

  <!-- Content -->
  <div class="content">

    <!-- Stat Cards -->
    <div class="stats-row">
      <div class="stat-card blue">
        <div class="stat-label">Total Donasi</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-desc">Semua entri donasi</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Donasi Dana</div>
        <div class="stat-value">
          <?php
            $q = mysqli_query($conn, "SELECT COUNT(*) as c FROM donatur WHERE jenis_donasi='Dana'");
            $r = mysqli_fetch_assoc($q);
            echo $r['c'];
          ?>
        </div>
        <div class="stat-desc">Transfer bank</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-label">Donasi Barang</div>
        <div class="stat-value">
          <?php
            $q2 = mysqli_query($conn, "SELECT COUNT(*) as c FROM donatur WHERE jenis_donasi='Pakaian' OR jenis_donasi LIKE '%Barang%'");
            $r2 = mysqli_fetch_assoc($q2);
            echo $r2['c'];
          ?>
        </div>
        <div class="stat-desc">Pakaian & barang lainnya</div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-section">
      <div class="table-header">
        <div class="table-title">Daftar Donatur</div>
        <span class="table-count"><?= $total ?> data</span>
      </div>

      <?php
        // Reset pointer
        mysqli_data_seek($data, 0);
      ?>

      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Kategori</th>
            <th>Jenis Donasi</th>
            <th>Bukti Transfer</th>
            <th>Bukti Barang</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; while($row = mysqli_fetch_assoc($data)) : ?>
          <tr>
            <td class="no-cell"><?= $no++ ?></td>
            <td class="name-cell"><?= htmlspecialchars($row['nama']) ?></td>
            <td class="email-cell"><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <?php
                $kat = htmlspecialchars($row['kategori']);
                $badgeClass = 'badge-blue';
                if(stripos($kat,'bencana') !== false) $badgeClass = 'badge-blue';
                elseif(stripos($kat,'lainnya') !== false) $badgeClass = 'badge-purple';
                else $badgeClass = 'badge-green';
              ?>
              <span class="badge <?= $badgeClass ?>"><?= $kat ?></span>
            </td>
            <td>
              <?php
                $jenis = htmlspecialchars($row['jenis_donasi']);
                $jClass = ($jenis === 'Dana') ? 'badge-green' : 'badge-purple';
              ?>
              <span class="badge <?= $jClass ?>"><?= $jenis ?></span>
            </td>
            <td>
              <?= $row['bukti_transfer']
                ? "<a href='uploads/{$row['bukti_transfer']}' target='_blank' class='lihat-link'>
                    <svg width='12' height='12' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/><path d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/></svg>
                    Lihat</a>"
                : "<span class='dash-cell'>—</span>" ?>
            </td>
            <td>
              <?= $row['bukti_barang']
                ? "<a href='uploads/{$row['bukti_barang']}' target='_blank' class='lihat-link'>
                    <svg width='12' height='12' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/><path d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/></svg>
                    Lihat</a>"
                : "<span class='dash-cell'>—</span>" ?>
            </td>
            <td class="date-cell"><?= $row['tanggal_donasi'] ?></td>
            <td>
              <div class="action-group">
                <a href="edit_donatur.php?id=<?= $row['id'] ?>" class="btn btn-edit">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>
                <a href="hapus_donatur.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus data ini?')">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  Hapus
                </a>
                <a href="kirim_email.php?id=<?= $row['id'] ?>" class="btn btn-email">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4h16v16H4z" rx="2"/><polyline points="22,6 12,13 2,6"/></svg>
                  Email
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

</body>
</html>