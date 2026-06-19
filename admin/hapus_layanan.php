<?php
session_start();
require '../config/koneksi.php';

/* =========================
   CEK ID
========================= */
$id = isset($_GET['id_layanan']) ? (int) $_GET['id_layanan'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID layanan tidak valid!";
    header("Location: layanan.php");
    exit;
}

/* =========================
   CEK DATA
========================= */
$cek = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id_layanan=$id");

if (mysqli_num_rows($cek) == 0) {
    $_SESSION['error'] = "Data layanan tidak ditemukan!";
    header("Location: layanan.php");
    exit;
}

/* =========================
   HAPUS DATA
========================= */
$hapus = mysqli_query($koneksi, "DELETE FROM layanan WHERE id_layanan=$id");

if ($hapus) {
    $_SESSION['success'] = "Data layanan berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus data layanan!";
}

header("Location: layanan.php");
exit;
?>