<?php
$kategori = isset($_GET['jenis']) ? $_GET['jenis'] : 'Umum';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Donasi untuk: <?= htmlspecialchars($kategori) ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('https://source.unsplash.com/1600x900/?donation,charity') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }

        .form-container {
            max-width: 600px;
            margin: 60px auto;
            background: rgba(255, 255, 255, 0.97);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .info-box {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 8px;
        }

        .hidden {
            display: none;
        }

        button[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #219150;
        }
    </style>
    <script>
        function toggleInfo(value) {
            document.getElementById('info-dana').classList.add('hidden');
            document.getElementById('info-barang').classList.add('hidden');
            document.getElementById('upload-dana').classList.add('hidden');
            document.getElementById('upload-barang').classList.add('hidden');

            if (value === 'Dana') {
                document.getElementById('info-dana').classList.remove('hidden');
                document.getElementById('upload-dana').classList.remove('hidden');
            } else {
                document.getElementById('info-barang').classList.remove('hidden');
                document.getElementById('upload-barang').classList.remove('hidden');
            }
        }
    </script>
</head>
<body>

<div class="form-container">
    <h2>Donasi untuk: <?= htmlspecialchars($kategori) ?></h2>

    <form method="post" action="proses_donasi.php" enctype="multipart/form-data">
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">

        <div class="form-group">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Bentuk Donasi (pilih satu):</label><br>
            <label><input type="radio" name="donasi" value="Dana" onclick="toggleInfo(this.value)" required> Donasi Dana</label><br>
            <label><input type="radio" name="donasi" value="Pakaian" onclick="toggleInfo(this.value)"> Donasi Pakaian</label><br>
            <label><input type="radio" name="donasi" value="Sembako" onclick="toggleInfo(this.value)"> Donasi Sembako</label><br>
            <label><input type="radio" name="donasi" value="Buku" onclick="toggleInfo(this.value)"> Donasi Buku</label><br>
            <label><input type="radio" name="donasi" value="lainnya" onclick="toggleInfo(this.value)"> Donasi Lainnya</label>
        </div>

        <div id="info-dana" class="info-box hidden">
            <h4>Informasi Pembayaran Donasi Dana</h4>
            <p>Silakan transfer ke rekening berikut:</p>
            <ul>
                <li><strong>Bank:</strong> BCA</li>
                <li><strong>Nomor Rekening:</strong> 1234567890</li>
                <li><strong>Atas Nama:</strong> Yayasan Kebaikan</li>
            </ul>
            <p>Atau scan QRIS:</p>
            <img src="img/qris.jpeg" alt="QRIS" width="200">
        </div>

        <div id="upload-dana" class="form-group hidden">
            <label>Upload Bukti Transfer (JPG/PNG):</label>
            <input type="file" name="bukti_transfer" accept="image/*">
        </div>

        <div id="info-barang" class="info-box hidden">
            <h4>Alamat Pengiriman Donasi Barang</h4>
            <p>Silakan kirim donasi Anda ke:</p>
            <p><strong>Alamat:</strong> Jl. Contoh No. 123, Jakarta Selatan</p>
        </div>

        <div id="upload-barang" class="form-group hidden">
            <label>Upload Foto Barang (JPG/PNG):</label>
            <input type="file" name="bukti_barang" accept="image/*">
        </div>

        <button type="submit">Kirim Donasi</button>
    </form>
</div>

</body>
</html>
