<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "donasi";

$conn = mysqli_connect($host, $user, $pass, $db);

$id = $_GET['id'];

$query = "DELETE FROM donatur WHERE id = $id";
mysqli_query($conn, $query);

header("Location: dashboard2.php");
exit;
?>
