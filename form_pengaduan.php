<?php
session_start();
require_once 'Function/copyright.php';
$koneksi = new mysqli("localhost", "root", "", "donasi");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

$sukses = '';
$error  = '';

if (isset($_POST['kirim'])) {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $isi   = trim($_POST['isi']);

    $stmt = $koneksi->prepare("INSERT INTO laporan_pengaduan (nama, email, isi) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $nama, $email, $isi);
        $stmt->execute();
        $sukses = "Pengaduan berhasil dikirim! Terima kasih.";
    } else {
        $error = "Gagal menyimpan: " . $koneksi->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan - SiPeduli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f0f7ff; min-height: 100vh; }
        nav  { background: rgba(255,255,255,0.9); backdrop-filter: blur(14px);
               border-bottom: 1px solid #e2eaf5; position: sticky; top: 0; z-index: 100;
               box-shadow: 0 2px 16px rgba(29,78,216,.06); }
        .nav-inner { max-width: 1200px; margin: auto; padding: 14px 24px;
                     display: flex; justify-content: space-between; align-items: center; }
        .nav-logo  { font-size: 20px; font-weight: 800; color: #1d4ed8; text-decoration: none; }
        .nav-logo span { color: #06b6d4; }
        .nav-links { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; }
        .nav-links a { padding: 7px 12px; border-radius: 8px; text-decoration: none;
                       font-size: 13px; font-weight: 500; color: #64748b; transition: all .2s; }
        .nav-links a:hover { background: #e8f0fe; color: #1d4ed8; }
        .nav-links a.active { background: #1d4ed8; color: white; }
        .nav-links a.danger { color: #dc2626; }
        .nav-links a.danger:hover { background: #fee2e2; }
        @media(max-width:768px){ .nav-links { display: none; } }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-inner">
        <a href="about.php" class="nav-logo">Si<span>Peduli</span></a>
        <div class="nav-links">
            <a href="about.php">Beranda</a>
            <a href="form_pengaduan.php" class="active">Form Pengaduan</a>
            <a href="tambah_donasi.php">Donate</a>
            <a href="data_donasi.php">Data Donasi</a>
            <a href="profile.php">Profil</a>
            <a href="login.php">Admin</a>
            <a href="prediksi_ai.php"><i class="fas fa-robot"></i> Prediksi AI</a>
            <a href="grafik_AI.php"><i class="fas fa-chart-line"></i> Analisis AI</a>
            <a href="Function/logout.php" class="danger">Logout</a>
        </div>
    </div>
</nav>

<!-- KONTEN -->
<div class="max-w-xl mx-auto mt-12 px-4 pb-16">

    <div class="bg-white rounded-2xl shadow-md border border-blue-100 p-8">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 text-2xl mb-3">📝</div>
            <h1 class="text-2xl font-bold text-gray-800">Form Pengaduan</h1>
            <p class="text-sm text-gray-500 mt-1">Sampaikan masukan, saran, atau laporan Anda</p>
        </div>

        <!-- Alert sukses -->
        <?php if ($sukses): ?>
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-6 text-sm font-medium">
            <i class="fas fa-check-circle text-green-500"></i> <?= htmlspecialchars($sukses) ?>
        </div>
        <?php endif; ?>

        <!-- Alert error -->
        <?php if ($error): ?>
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-6 text-sm font-medium">
            <i class="fas fa-exclamation-circle text-red-500"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama Anda"
                       value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" placeholder="contoh@email.com"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Pengaduan</label>
                <textarea name="isi" rows="5" placeholder="Tuliskan pengaduan atau masukan Anda di sini..."
                          required
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition resize-none"><?= isset($_POST['isi']) ? htmlspecialchars($_POST['isi']) : '' ?></textarea>
            </div>

            <button type="submit" name="kirim"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition text-sm shadow-md">
                <i class="fas fa-paper-plane mr-2"></i> Kirim Pengaduan
            </button>

        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">&copy; <?= $copyright ?></p>
</div>

</body>
</html>
