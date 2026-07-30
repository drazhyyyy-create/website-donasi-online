<?php
$conn = new mysqli("localhost", "root", "", "donasi");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$filterKategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filterJenis = isset($_GET['jenis_donasi']) ? $_GET['jenis_donasi'] : '';

$sql = "SELECT * FROM donatur WHERE 1=1";
if (!empty($filterKategori)) {
    $sql .= " AND kategori = '" . $conn->real_escape_string($filterKategori) . "'";
}
if (!empty($filterJenis)) {
    $sql .= " AND jenis_donasi = '" . $conn->real_escape_string($filterJenis) . "'";
}
$sql .= " ORDER BY tanggal_donasi DESC";
$result = $conn->query($sql);

$kategoriList = $conn->query("SELECT DISTINCT kategori FROM donatur");
$jenisList    = $conn->query("SELECT DISTINCT jenis_donasi FROM donatur");

// hitung total
$total_semua = 0;
$cekKolom = $conn->query("SHOW COLUMNS FROM donatur LIKE 'jumlah_donasi'");
if ($cekKolom && $cekKolom->num_rows > 0) {
    $total_sql = "SELECT SUM(jumlah_donasi) AS total_semua FROM donatur WHERE 1=1";
    if (!empty($filterKategori)) $total_sql .= " AND kategori = '" . $conn->real_escape_string($filterKategori) . "'";
    if (!empty($filterJenis))    $total_sql .= " AND jenis_donasi = '" . $conn->real_escape_string($filterJenis) . "'";
    $tr = $conn->query($total_sql);
    if ($tr) { $td = $tr->fetch_assoc(); $total_semua = $td['total_semua'] ?? 0; }
}

$jumlah_data = ($result && $result !== false) ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Donasi - Transparansi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-deep:    #0A1628;
            --blue-mid:     #0D2149;
            --blue-rich:    #1A3A7C;
            --blue-bright:  #2563EB;
            --blue-glow:    #3B82F6;
            --blue-sky:     #60A5FA;
            --accent:       #38BDF8;
            --accent-soft:  rgba(56,189,248,0.12);
            --white:        #FFFFFF;
            --white-80:     rgba(255,255,255,0.8);
            --white-60:     rgba(255,255,255,0.6);
            --white-40:     rgba(255,255,255,0.4);
            --white-10:     rgba(255,255,255,0.08);
            --white-06:     rgba(255,255,255,0.05);
            --border:       rgba(255,255,255,0.08);
            --border-focus: rgba(56,189,248,0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--blue-deep);
            min-height: 100vh;
            color: white;
            overflow-x: hidden;
        }

        /* ── BACKGROUND ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 60% 50% at 10% 20%, rgba(37,99,235,0.22) 0%, transparent 70%),
                radial-gradient(ellipse 50% 60% at 90% 80%, rgba(56,189,248,0.12) 0%, transparent 60%),
                linear-gradient(160deg, #0D2149 0%, #0A1628 100%);
            pointer-events: none;
        }
        .grid-overlay {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(56,189,248,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56,189,248,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* ── LAYOUT ── */
        .page-wrap {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px 60px;
        }

        /* ── TOPBAR ── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
            animation: fadeDown 0.7s ease both;
        }
        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--white-10);
            border: 1px solid rgba(56,189,248,0.2);
            border-radius: 100px;
            padding: 6px 16px 6px 8px;
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
        .btn-print {
            display: flex; align-items: center; gap: 8px;
            padding: 9px 20px;
            background: rgba(37,99,235,0.2);
            border: 1px solid rgba(37,99,235,0.4);
            border-radius: 8px;
            color: var(--blue-sky);
            font-family: 'Outfit', sans-serif;
            font-size: 13.5px; font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-print:hover {
            background: rgba(37,99,235,0.35);
            border-color: var(--blue-sky);
        }

        /* ── PAGE HEADER ── */
        .page-header {
            margin-bottom: 32px;
            animation: fadeDown 0.7s 0.1s ease both;
        }
        .page-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: 32px; font-weight: 800;
            color: white;
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 6px;
        }
        .page-header h1 .icon-badge {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--blue-bright), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 6px 20px rgba(37,99,235,0.4);
        }
        .page-header p {
            font-size: 14.5px; font-weight: 300;
            color: var(--white-40);
        }

        /* ── STAT CARDS ── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
            animation: fadeDown 0.7s 0.2s ease both;
        }
        .stat-card {
            background: var(--white-06);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 20px; right: 20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(56,189,248,0.3), transparent);
        }
        .stat-label {
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.8px; text-transform: uppercase;
            color: var(--white-40);
            margin-bottom: 8px;
        }
        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 26px; font-weight: 700;
        }
        .stat-value.blue  { background: linear-gradient(90deg, var(--blue-sky), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .stat-value.green { background: linear-gradient(90deg, #34d399, #6ee7b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .stat-icon {
            position: absolute;
            right: 18px; top: 18px;
            width: 36px; height: 36px;
            background: var(--white-10);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }

        /* ── FILTER CARD ── */
        .filter-card {
            background: var(--white-06);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
            animation: fadeDown 0.7s 0.3s ease both;
        }
        .filter-card form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filter-label {
            font-size: 12px; font-weight: 500;
            letter-spacing: 0.6px; text-transform: uppercase;
            color: var(--white-40);
            white-space: nowrap;
        }
        select {
            padding: 9px 32px 9px 14px;
            background: var(--white-10);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            outline: none;
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            transition: border-color 0.2s;
            min-width: 180px;
        }
        select:focus { border-color: var(--accent); }
        select option { background: #0D2149; color: white; }

        .btn-filter {
            padding: 9px 22px;
            background: linear-gradient(135deg, var(--blue-bright), var(--blue-glow));
            border: none; border-radius: 8px;
            color: white; font-family: 'Outfit', sans-serif;
            font-size: 14px; font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(37,99,235,0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.5); }

        .btn-reset {
            padding: 9px 18px;
            background: var(--white-10);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--white-60); font-family: 'Outfit', sans-serif;
            font-size: 14px; font-weight: 400;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .btn-reset:hover { background: rgba(255,255,255,0.12); color: white; }

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--white-06);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            animation: fadeDown 0.7s 0.4s ease both;
        }

        .table-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
        }
        .table-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px; font-weight: 700;
            color: white;
        }
        .table-count {
            font-size: 12px; font-weight: 400;
            color: var(--white-40);
            background: var(--white-10);
            padding: 3px 10px; border-radius: 100px;
        }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        thead tr {
            background: rgba(37,99,235,0.12);
        }
        th {
            padding: 13px 16px;
            text-align: left;
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.8px; text-transform: uppercase;
            color: var(--blue-sky);
            white-space: nowrap;
            border-bottom: 1px solid rgba(37,99,235,0.2);
        }
        th:first-child, td:first-child { text-align: center; }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(56,189,248,0.04); }

        td {
            padding: 14px 16px;
            color: var(--white-80);
            font-weight: 300;
            vertical-align: middle;
        }

        .td-no {
            font-family: 'Syne', sans-serif;
            font-size: 13px; font-weight: 600;
            color: var(--white-40);
            text-align: center;
        }

        .td-name {
            font-weight: 500;
            color: white;
        }

        .td-email {
            color: var(--white-60);
            font-size: 13px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 100px;
            font-size: 12px; font-weight: 500;
            white-space: nowrap;
        }
        .badge-kategori {
            background: rgba(37,99,235,0.18);
            border: 1px solid rgba(37,99,235,0.3);
            color: var(--blue-sky);
        }
        .badge-jenis {
            background: rgba(56,189,248,0.12);
            border: 1px solid rgba(56,189,248,0.25);
            color: var(--accent);
        }

        .td-bukti img {
            width: 56px; height: 56px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
            display: block;
        }
        .td-empty {
            color: var(--white-40);
            font-style: italic;
            font-size: 13px;
        }

        .td-date {
            font-size: 13px;
            color: var(--white-60);
            white-space: nowrap;
        }

        .td-no-data {
            text-align: center;
            padding: 48px 16px;
            color: var(--white-40);
            font-style: italic;
        }

        /* ── TOTAL BOX ── */
        .total-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-top: 1px solid rgba(37,99,235,0.2);
            background: rgba(37,99,235,0.08);
        }
        .total-bar .lbl {
            font-size: 13px; color: var(--white-60); font-weight: 300;
            display: flex; align-items: center; gap: 6px;
        }
        .total-bar .lbl::before { content:''; width:6px; height:6px; background:var(--accent); border-radius:50%; display:inline-block; }
        .total-bar .val {
            font-family: 'Syne', sans-serif;
            font-size: 18px; font-weight: 700;
            background: linear-gradient(90deg, var(--blue-sky), var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── PRINT ── */
        @media print {
            body { background: white; color: black; }
            .bg-layer, .grid-overlay, .topbar, .filter-card, .btn-print { display: none; }
            .page-wrap { padding: 0; }
            .table-card { border: 1px solid #ccc; border-radius: 0; }
            th { background: #e0f2fe !important; color: #1e40af !important; }
            td { color: #111 !important; }
            .badge-kategori { background: #dbeafe !important; color: #1e40af !important; border: 1px solid #93c5fd !important; }
            .badge-jenis { background: #e0f9ff !important; color: #0284c7 !important; border: 1px solid #7dd3fc !important; }
        }
    </style>
</head>
<body>

<div class="bg-layer"></div>
<div class="grid-overlay"></div>

<div class="page-wrap">

    <!-- TOPBAR -->
    <div class="topbar">
        <a href="index.php" class="brand-pill">
            <div class="dot">
                <svg viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402C1 3.543 4.068 2 6.935 2c2.02 0 4.11.943 5.065 3.338C12.955 2.943 15.045 2 17.065 2 19.932 2 23 3.543 23 7.191c0 4.105-5.37 8.863-11 14.402z"/></svg>
            </div>
            <span>SiPeduli</span>
        </a>
        <button class="btn-print" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
            </svg>
            Cetak Laporan
        </button>
    </div>

    <!-- HEADER -->
    <div class="page-header">
        <h1>
            <span class="icon-badge">📊</span>
            Data Donasi Masuk
        </h1>
        <p>Daftar donasi yang telah masuk sebagai bentuk transparansi publik.</p>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-label">Total Data</div>
            <div class="stat-value blue"><?= $jumlah_data ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏷️</div>
            <div class="stat-label">Filter Aktif</div>
            <div class="stat-value blue"><?= (!empty($filterKategori) || !empty($filterJenis)) ? 'Ya' : 'Tidak' ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Total Akumulasi</div>
            <div class="stat-value green" style="font-size:18px">Rp <?= number_format($total_semua ?: 0, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="filter-card">
        <form method="GET">
            <span class="filter-label">Filter :</span>

            <select name="kategori">
                <option value="">Semua Kategori</option>
                <?php
                if ($kategoriList && $kategoriList->num_rows > 0) {
                    mysqli_data_seek($kategoriList, 0);
                    while ($row = mysqli_fetch_assoc($kategoriList)) {
                        $val = htmlspecialchars($row['kategori']);
                        $sel = ($filterKategori === $row['kategori']) ? 'selected' : '';
                        echo "<option value='{$val}' {$sel}>{$val}</option>";
                    }
                }
                ?>
            </select>

            <select name="jenis_donasi">
                <option value="">Semua Jenis Donasi</option>
                <?php
                if ($jenisList && $jenisList->num_rows > 0) {
                    mysqli_data_seek($jenisList, 0);
                    while ($row = mysqli_fetch_assoc($jenisList)) {
                        $val = htmlspecialchars($row['jenis_donasi']);
                        $sel = ($filterJenis === $row['jenis_donasi']) ? 'selected' : '';
                        echo "<option value='{$val}' {$sel}>{$val}</option>";
                    }
                }
                ?>
            </select>

            <button type="submit" class="btn-filter">Filter</button>
            <button type="button" class="btn-reset" onclick="window.location.href='data_donasi.php'">Reset</button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-title">Daftar Donatur</span>
            <span class="table-count"><?= $jumlah_data ?> entri</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Donatur</th>
                        <th>Email</th>
                        <th>Kategori</th>
                        <th>Jenis Donasi</th>
                        <th>Bukti</th>
                        <th>Tanggal Donasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result === false) {
                        echo "<tr><td colspan='7' class='td-no-data'>Terjadi kesalahan saat membaca data.</td></tr>";
                    } elseif ($result->num_rows > 0) {
                        $no = 1;
                        mysqli_data_seek($result, 0);
                        while ($row = $result->fetch_assoc()) {
                            $bukti = isset($row['bukti_transfer']) ? $row['bukti_transfer'] : (isset($row['bukti_barang']) ? $row['bukti_barang'] : '');
                            $gambar = $bukti
                                ? "<img src='uploads/" . htmlspecialchars($bukti) . "' alt='Bukti'>"
                                : "<span class='td-empty'>—</span>";
                            echo "<tr>
                                <td class='td-no'>{$no}</td>
                                <td class='td-name'>" . htmlspecialchars($row['nama']) . "</td>
                                <td class='td-email'>" . htmlspecialchars($row['email']) . "</td>
                                <td><span class='badge badge-kategori'>" . htmlspecialchars($row['kategori']) . "</span></td>
                                <td><span class='badge badge-jenis'>" . htmlspecialchars($row['jenis_donasi']) . "</span></td>
                                <td class='td-bukti'>{$gambar}</td>
                                <td class='td-date'>" . htmlspecialchars($row['tanggal_donasi']) . "</td>
                            </tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='7' class='td-no-data'>Belum ada data donasi.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="total-bar">
            <span class="lbl">Total Akumulasi Donasi</span>
            <span class="val">Rp <?= number_format($total_semua ?: 0, 0, ',', '.') ?></span>
        </div>
    </div>

</div>
</body>
</html>
<?php $conn->close(); ?>