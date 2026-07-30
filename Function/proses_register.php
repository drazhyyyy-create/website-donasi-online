<?php
// proses_register.php

include '../Function/koneksi.php'; // Pastikan path ini benar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO user (username, password) VALUES ('$username', '$password')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo "Registrasi berhasil!";
            header("Location: login.php");
        } else {
            echo "Registrasi gagal: " . mysqli_error($conn);
        }
    } else {
        echo "Username atau password tidak dikirim!";
    }
}
?>
