<?php
// Memastikan session sudah aktif sebelum mengambil data session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil data foto terbaru dari database berdasarkan id session agar selalu sinkron
if (isset($_SESSION['id'])) {
    // Hubungkan koneksi jika belum terhubung di file utama
    include_once __DIR__ . '/../../config/koneksi.php';
    
    $id_user = $_SESSION['id'];
    $query_nav = mysqli_query($koneksi, "SELECT nama, foto FROM users WHERE id = '$id_user'");
    $data_nav = mysqli_fetch_assoc($query_nav);
    
    $nama_admin = $data_nav['nama'] ?? $_SESSION['nama'] ?? 'Admin';
    $foto_admin = $data_nav['foto'] ?? '';
} else {
    $nama_admin = $_SESSION['nama'] ?? 'Admin';
    $foto_admin = '';
}

// Logika penentuan gambar profil (Foto asli vs Inisial Nama)
$path_foto_asli = "/TB_LAUNDRY/assets/img/" . $foto_admin;
if (!empty($foto_admin) && file_exists(__DIR__ . '/../../assets/img/' . $foto_admin)) {
    $avatar_navbar = $path_foto_asli;
} else {
    // Jika tidak ada foto asli, gunakan inisial nama dinamis
    $avatar_navbar = "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($nama_admin) . "&backgroundColor=2563eb,0f172a,3b82f6&fontSize=45&bold=true";
}
?>

<style>
    /* Mengoptimalkan area klik dan interaksi visual pada topbar */
    .topbar-right .user-info {
        text-decoration: none; 
        color: inherit; 
        display: flex; 
        align-items: center; 
        gap: 12px;
        cursor: pointer; /* Mengubah kursor menjadi tangan saat diarahkan */
        transition: opacity 0.2s ease;
    }

    /* Efek feedback halus saat admin mengarahkan mouse ke profil mereka */
    .topbar-right .user-info:hover {
        opacity: 0.85;
    }

    .topbar-right .profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
</style>

<div class="topbar">

    <div class="welcome">
        Halo Admin,
        <b><?= htmlspecialchars($nama_admin); ?></b>
    </div>

    <div class="topbar-right">

        <!-- Komponen Notifikasi Telah Dihapus Untuk Menyederhanakan Tampilan -->

        <a href="/TB_LAUNDRY/admin/profil.php" class="user-info">

            <div class="user-text">
                <h4><?= htmlspecialchars($nama_admin); ?></h4>
                <span>Administrator</span>
            </div>

            <div class="profile">
                <img src="<?= $avatar_navbar; ?>" alt="profile">
            </div>

        </a>

    </div>

</div>