<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "donasi";

$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Alias $conn agar file lain yang pakai $conn tetap berfungsi
$conn = $koneksi;

function query($query): array {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
?>
