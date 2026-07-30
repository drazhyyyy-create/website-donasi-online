<?php
session_start();
include 'Function/koneksi.php'; // koneksi ke DB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];

    // Cek apakah email sudah ada
    $check = mysqli_query($conn, "SELECT * FROM donatur WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $user = mysqli_fetch_assoc($check);
    } else {
        // Simpan data baru
        mysqli_query($conn, "INSERT INTO donatur (nama, email) VALUES ('$nama', '$email')");
        $user = ['nama' => $nama, 'email' => $email];
    }

    // Set session
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['email'] = $user['email'];

    // Redirect ke halaman utama
    header("Location: about.php");
    exit();
}
?>
