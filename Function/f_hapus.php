<?php

require_once 'koneksi.php';

function hapus($id){
    global $conn;
    mysqli_query($conn, "DELETE FROM donatur WHERE id = $id");
    return mysqli_affected_rows($conn); 


}

?>