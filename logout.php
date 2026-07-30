<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke halaman login pengguna (index.php)
header("Location: index.php");
exit;
?>
