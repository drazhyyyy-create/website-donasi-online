<!DOCTYPE html>
<html>
<head>
    <title>Filter Laporan Donasi</title>
</head>
<body>
    <h2>Cetak Laporan Donasi</h2>
    <form action="laporan_donasi.php" method="get" target="_blank">
        <label for="jenis_donasi">Pilih Jenis Donasi:</label>
        <select name="jenis_donasi" id="jenis_donasi" required>
            <option value="Dana">Donasi Dana</option>
            <option value="Pakaian">Donasi Pakaian</option>
            <option value="Sembako">Donasi Sembako</option>
            <option value="Buku">Donasi Buku</option>
        </select>
        <br><br>
        <button type="submit">Download PDF</button>
    </form>
</body>
</html>