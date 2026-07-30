<?php
include 'koneksi.php';
include 'send_email.php';

$id_donasi = $_POST['id_donasi'];
$status = $_POST['status']; // 'pending' atau 'diterima'

// Ambil data donatur
$query = mysqli_query($koneksi, "SELECT * FROM donasi WHERE id = '$id_donasi'");
$data = mysqli_fetch_assoc($query);

$email = $data['email'];
$nama = $data['nama'];

// Update status di database
mysqli_query($koneksi, "UPDATE donasi SET status='$status' WHERE id='$id_donasi'");

// Kirim notifikasi email
kirimEmailDonatur($email, $nama, $status);

header("Location: halaman_admin.php?pesan=update_sukses");
?>