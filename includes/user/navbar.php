<?php
// Memastikan session aktif sebelum mengambil data session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi jumlah notifikasi default
$jumlah_notif = 0;
$nama_user = $_SESSION['nama'] ?? 'Pelanggan';
$foto_user = '';

// Ambil data dari database jika user sudah login
if (isset($_SESSION['id'])) {
    include_once __DIR__ . '/../../config/koneksi.php'; // Hubungkan ke file koneksi database Anda
    
    $id_user = $_SESSION['id'];
    
    // 1. Ambil nama dan foto terbaru user untuk navbar
    $query_nav = mysqli_query($koneksi, "SELECT nama, foto FROM users WHERE id = '$id_user'");
    $data_nav = mysqli_fetch_assoc($query_nav);
    if ($data_nav) {
        $nama_user = $data_nav['nama'];
        $foto_user = $data_nav['foto'];
    }
    
    // 2. HITUNG NOTIFIKASI AKTIF
    // Hanya menghitung tanggapan admin yang BELUM DIBACA oleh user (status_baca_user = 0)
    $query_notif = mysqli_query($koneksi, "
        SELECT COUNT(*) as total 
        FROM ulasan 
        WHERE id_user = '$id_user' 
        AND tanggapan IS NOT NULL 
        AND tanggapan != '' 
        AND status_baca_user = 0
    ");
    
    if ($query_notif) {
        $data_notif = mysqli_fetch_assoc($query_notif);
        $jumlah_notif = $data_notif['total'] ?? 0;
    }
}

// Logika penentuan gambar profil (Foto asli vs Inisial Nama)
$path_foto_asli = "/TB_LAUNDRY/assets/img/" . $foto_user;
if (!empty($foto_user) && file_exists(__DIR__ . '/../../assets/img/' . $foto_user)) {
    $avatar_navbar = $path_foto_asli;
} else {
    $avatar_navbar = "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($nama_user) . "&backgroundColor=0f172a,2563eb,3b82f6&fontSize=45&bold=true";
}
?>

<style>
    /* Mengoptimalkan area topbar */
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
        background-color: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .topbar-right .notification {
        position: relative;
        cursor: pointer;
        color: #475569;
        transition: color 0.2s;
    }
    
    .topbar-right .notification:hover {
        color: #0f172a;
    }

    /* Badge Notifikasi Merah */
    .topbar-right .notif-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc2626;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 10px;
        line-height: 1;
    }

    .topbar-right .user-link {
        text-decoration: none; 
        color: inherit; 
        display: flex; 
        align-items: center; 
        gap: 12px;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }

    .topbar-right .user-link:hover {
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
        Halo,
        <b><?= htmlspecialchars($nama_user); ?></b>
    </div>

    <div class="topbar-right">

        <a href="/TB_LAUNDRY/user/ulasan.php" class="notification">
            <i class="fa-solid fa-bell" style="font-size: 18px;"></i>
            
            <?php if ($jumlah_notif > 0): ?>
                <span class="notif-badge">
                    <?= $jumlah_notif; ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="/TB_LAUNDRY/user/profil.php" class="user-link">
            
            <div class="user-text">
                <h4 style="margin: 0; font-size: 14px; font-weight: 600;"><?= htmlspecialchars($nama_user); ?></h4>
                <span style="font-size: 12px; color: #64748b;">Pelanggan</span>
            </div>

            <div class="profile">
                <img src="<?= $avatar_navbar; ?>" alt="profile">
            </div>

        </a>

    </div>

</div>