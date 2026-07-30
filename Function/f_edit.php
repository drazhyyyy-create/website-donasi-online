<?php


require_once 'koneksi.php';

function edit($data,$id) {
    global $conn;
    $nama = htmlspecialchars($data["nama"]);
    $nominal = htmlspecialchars($data["nominal"]);
    $metode = htmlspecialchars($data["metode"]);
    $deskripsi = htmlspecialchars($data["deskripsi"]);
  
    $query = "UPDATE donatur SET
              nama = '$nama',
              nominal = '$nominal',
              metode = '$metode',
              deskripsi = '$deskripsi'
              WHERE id = $id";
  mysqli_query($conn, $query);
  
  return mysqli_affected_rows($conn);
  
  }


?>