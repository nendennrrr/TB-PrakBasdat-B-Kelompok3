<?php
session_start();
require '../config/koneksi.php';

if(!isset($_GET['id'])){
    $_SESSION['error'] = "ID pesanan tidak ditemukan!";
    header("Location: pesanan.php");
    exit;
}

$id = (int)$_GET['id'];

/* =========================
   HAPUS DETAIL PESANAN DULU
========================= */

mysqli_query($koneksi,"
    DELETE FROM detail_pesanan
    WHERE id_pesanan='$id'
");

/* =========================
   HAPUS PESANAN
========================= */

$hapus = mysqli_query($koneksi,"
    DELETE FROM pesanan
    WHERE id_pesanan='$id'
");

if($hapus){

    $_SESSION['success'] =
    "Pesanan berhasil dihapus!";

}else{

    $_SESSION['error'] =
    "Pesanan gagal dihapus!";
}

header("Location: pesanan.php");
exit;
?>