<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];
$id_ulasan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id_ulasan <= 0){
    header("Location: lihat_ulasan.php");
    exit;
}

/* ==========================
   CEK DATA ULASAN
========================== */

$cek = mysqli_query($koneksi,"
    SELECT id_ulasan 
    FROM ulasan 
    WHERE id_ulasan='$id_ulasan' 
    AND id_user='$id_user'
");

if(mysqli_num_rows($cek) == 0){

    echo "
    <script>
        alert('Data ulasan tidak ditemukan atau bukan milik Anda');
        window.location='lihat_ulasan.php';
    </script>";
    exit;
}

/* ==========================
   HAPUS ULASAN
========================== */

$hapus = mysqli_query($koneksi,"
    DELETE FROM ulasan 
    WHERE id_ulasan='$id_ulasan' 
    AND id_user='$id_user'
");

if($hapus){

    echo "
    <script>
        alert('Ulasan berhasil dihapus');
        window.location='lihat_ulasan.php';
    </script>";

}else{

    die(mysqli_error($koneksi));
}
?>