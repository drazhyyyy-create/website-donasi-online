<!DOCTYPE html>
<html>
<head>
    <title>Proses Donasi</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            text-align: center;
            background-color: #f4f6f9;
            margin: 0;
            padding: 50px;
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            display: inline-block;
            min-width: 400px;
        }
        h2 {
            color: #2c3e50;
        }
        a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        a:hover {
            background-color: #2980b9;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
<?php
// Koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "donasi");

// Cek koneksi
if ($koneksi->connect_error) {
    die("<p class='error'>Koneksi gagal: " . $koneksi->connect_error . "</p>");
}

// Buat folder upload jika belum ada
$folder_upload = "uploads/";
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0777, true);
}

// Tangkap data dari form
$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$kategori = $_POST['kategori'] ?? '';
$jenis_donasi = $_POST['donasi'] ?? '';
$bukti_transfer = null;
$bukti_barang = null;

// 🔹 Proses upload file (khusus untuk donasi dana)
if ($jenis_donasi === 'Dana' && isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === 0) {
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $allowed_mime = ['image/jpeg', 'image/png'];

    $ext = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['bukti_transfer']['tmp_name']);

    // Validasi tipe dan ekstensi file
    if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime)) {
        // Validasi ukuran file maksimal 2MB
        if ($_FILES['bukti_transfer']['size'] > 2 * 1024 * 1024) {
            die("<p class='error'>Ukuran file terlalu besar! Maksimal 2MB.</p>");
        }

        $filename = uniqid('transfer_') . '.' . $ext;
        if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $folder_upload . $filename)) {
            $bukti_transfer = $filename;
        } else {
            die("<p class='error'>Gagal mengunggah file bukti transfer.</p>");
        }
    } else {
        die("<p class='error'>Hanya file dengan format JPG atau PNG yang diperbolehkan.</p>");
    }
}

// 🔹 Proses upload file (jika donasi berupa barang)
if ($jenis_donasi !== 'Dana' && isset($_FILES['bukti_barang']) && $_FILES['bukti_barang']['error'] === 0) {
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $allowed_mime = ['image/jpeg', 'image/png'];

    $ext = strtolower(pathinfo($_FILES['bukti_barang']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['bukti_barang']['tmp_name']);

    if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime)) {
        if ($_FILES['bukti_barang']['size'] > 10 * 1024 * 1024) {
            die("<p class='error'>Ukuran file terlalu besar! Maksimal 2MB.</p>");
        }

        $filename = uniqid('barang_') . '.' . $ext;
        if (move_uploaded_file($_FILES['bukti_barang']['tmp_name'], $folder_upload . $filename)) {
            $bukti_barang = $filename;
        } else {
            die("<p class='error'>Gagal mengunggah file bukti barang.</p>");
        }
    } else {
        die("<p class='error'>Hanya file dengan format JPG atau PNG yang diperbolehkan.</p>");
    }
}

// 🔹 Simpan ke database
if ($nama && $email && $kategori && $jenis_donasi) {
    $stmt = $koneksi->prepare("INSERT INTO donatur (nama, email, kategori, jenis_donasi, bukti_transfer, bukti_barang) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nama, $email, $kategori, $jenis_donasi, $bukti_transfer, $bukti_barang);

    if ($stmt->execute()) {
        // 🔹 Generate PDF bukti donasi
        require('fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Bukti Donasi',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln(10);
        $pdf->Cell(0,10,"Nama: $nama",0,1);
        $pdf->Cell(0,10,"Email: $email",0,1);
        $pdf->Cell(0,10,"Kategori: $kategori",0,1);
        $pdf->Cell(0,10,"Jenis Donasi: $jenis_donasi",0,1);
        if ($bukti_transfer) {
            $pdf->Cell(0,10,"Bukti Transfer: $bukti_transfer",0,1);
        }
        if ($bukti_barang) {
            $pdf->Cell(0,10,"Bukti Barang: $bukti_barang",0,1);
        }

        $path_pdf = $folder_upload . 'bukti_donasi_' . uniqid() . '.pdf';
        $pdf->Output('F', $path_pdf);

        echo "<h2>Terima kasih, donasi Anda telah tercatat oleh admin.</h2>";
        echo "<a href='$path_pdf' target='_blank'>Download Bukti Donasi (PDF)</a><br>";
        echo "<a href='about.php'>Kembali ke Halaman Utama</a>";
    } else {
        echo "<p class='error'>Gagal menyimpan donasi: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    echo "<p class='error'>Semua data wajib diisi.</p>";
}

$koneksi->close();
?>
</div>

</body>
</html>
