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

// Mulai transaksi untuk memastikan keamanan data
mysqli_begin_transaction($koneksi);

try {
    // 1. Hapus data di tabel anak (detail_pesanan) terlebih dahulu
    // Ini menyelesaikan masalah "foreign key constraint fails"
    $query1 = "DELETE FROM detail_pesanan WHERE id_layanan = $id";
    if (!mysqli_query($koneksi, $query1)) {
        throw new Exception("Gagal menghapus relasi detail pesanan.");
    }

    // 2. Hapus data di tabel utama (layanan)
    $query2 = "DELETE FROM layanan WHERE id_layanan = $id";
    if (!mysqli_query($koneksi, $query2)) {
        throw new Exception("Gagal menghapus layanan.");
    }

    // Jika semua berhasil, simpan perubahan
    mysqli_commit($koneksi);
    $_SESSION['success'] = "Data layanan dan riwayat pesanan terkait berhasil dihapus!";

} catch (Exception $e) {
    // Jika ada yang error, batalkan semua perubahan
    mysqli_rollback($koneksi);
    $_SESSION['error'] = $e->getMessage();
}

header("Location: layanan.php");
exit;
?>