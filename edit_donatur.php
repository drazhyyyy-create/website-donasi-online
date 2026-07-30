<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "donasi";

$conn = mysqli_connect($host, $user, $pass, $db);

$id = $_GET['id'];
$query = "SELECT * FROM donatur WHERE id = $id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $kategori = $_POST['kategori'];
    $jenis = $_POST['jenis_donasi'];

    $update = "UPDATE donatur SET 
        nama = '$nama',
        email = '$email',
        kategori = '$kategori',
        jenis_donasi = '$jenis'
        WHERE id = $id";

    mysqli_query($conn, $update);
    header("Location: dashboard2.php");
}
?>

<h2>Edit Donatur</h2>
<form method="post">
    Nama: <input type="text" name="nama" value="<?= $data['nama'] ?>"><br>
    Email: <input type="email" name="email" value="<?= $data['email'] ?>"><br>
    Kategori: <input type="text" name="kategori" value="<?= $data['kategori'] ?>"><br>
    Jenis Donasi: <input type="text" name="jenis_donasi" value="<?= $data['jenis_donasi'] ?>"><br>
    <button type="submit" name="submit">Simpan</button>
</form>
