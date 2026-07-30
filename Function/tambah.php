<?php

require_once 'koneksi.php';

function tambah($data){
    global $conn;
    $nama = htmlspecialchars($data["nama"]);
    $nominal = htmlspecialchars($data["nominal"]);
    $metode = htmlspecialchars($data["metode"]);
    $deskripsi = htmlspecialchars($data["deskripsi"]);

    

    $bukti = upload();
    
      //   if(!$bukti) {
      //   return false;
      //  }

    $result = mysqli_query($conn, "SELECT nama FROM donatur WHERE nama = '$nama'");
    if(mysqli_fetch_assoc($result)) {
      echo "<script>
      alert('nama sudah terdaftar!');
      </script>";
      return false;
    }
    

    //id sama timestap kosongin aja soalny auto
    $query = "INSERT INTO donatur (nama, nominal, metode, deskripsi, bukti) 
          VALUES ('$nama', '$nominal', '$metode', '$deskripsi', '$bukti')";

    // mysqli_query($conn, $query);

    if (!mysqli_query($conn, $query)) {
        die("Error: " . mysqli_error($conn));
    }
    

    return mysqli_affected_rows($conn);
}

function upload() {
  $namafile = $_FILES['bukti']['name'];
  $ukuranfile = $_FILES['bukti']['size'];
  $error = $_FILES['bukti']['error'];
  $tmpname = $_FILES['bukti']['tmp_name'];

  //cek apakah tidak ada gambar yang di upload

  if($error === 4) {
    // echo "<script>
    // alert('pilih gambar terlebih dahulu!');
    // </script>";
    return false;
  }

  //cek apakah yang diupload adalah gambar

  $ekstensigambarvalid = ['jpg', 'jpeg', 'png'];
  $ekstensigambar = explode('.', $namafile);
  $ekstensigambar = strtolower(end($ekstensigambar));

  if(!in_array($ekstensigambar, $ekstensigambarvalid)) {
    echo "<script>
    alert('yang anda upload bukan gambar!');
    </script>";
    return false;
  }

  //cek jika ukuranya terlalu besar
  if($ukuranfile > 10000000) {
    echo "<script>
    alert('ukuran gambar terlalu besar!');
    </script>";
    return false;
  }

  //lolos pengecekan gambar siap di upload
  //generate nama gambar baru
  $namafilebaru = uniqid();
  $namafilebaru .= '.';
  $namafilebaru .= $ekstensigambar;
  move_uploaded_file($tmpname, 'img/' . $namafilebaru);

  return $namafilebaru;
}

?>