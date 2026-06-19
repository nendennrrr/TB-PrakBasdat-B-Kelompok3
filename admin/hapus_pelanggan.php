<?php
session_start();
require '../config/koneksi.php';

$id = (int)$_GET['id'];

/* Cek apakah pelanggan punya pesanan */
$cek = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT COUNT(*) AS total
        FROM pesanan
        WHERE id_user = $id
    ")
);

if($cek['total'] > 0){

    $_SESSION['error'] =
    "Data pelanggan tidak dapat dihapus karena masih memiliki pesanan.";

    header("Location: pelanggan.php");
    exit;
}

/* Hapus pelanggan */
mysqli_query(
    $koneksi,
    "DELETE FROM users WHERE id = $id"
);

$_SESSION['success'] =
"Data pelanggan berhasil dihapus.";

header("Location: pelanggan.php");
exit;