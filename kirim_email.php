<?php
// Pastikan path koneksi benar
include 'koneksi.php'; // atau '../koneksi.php' jika file ada di folder lain

// Cek apakah parameter ID dikirim
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan di URL.");
}

$id = $_GET['id'];

// Pastikan koneksi berhasil
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Ambil data donatur berdasarkan ID
$query = mysqli_query($koneksi, "SELECT * FROM donatur WHERE id = '$id'");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data donatur tidak ditemukan.");
}

// Kirim email (contoh sederhana)
$to = $data['email'];
$subject = "Terima Kasih atas Donasi Anda di SiPeduli";
$message = "
Halo " . $data['nama'] . ",

Terima kasih atas donasi Anda melalui SiPeduli.
Berikut rincian donasi Anda:
";

// mail($to, $subject, $message); // aktifkan jika mail server sudah siap
echo "Email terkirim ke " . $to;
?>
