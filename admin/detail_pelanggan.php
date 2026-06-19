<?php
session_start();
require '../config/koneksi.php';

if(!isset($_GET['id'])){
    header("Location: pelanggan.php");
    exit;
}

$id = (int)$_GET['id'];

/* =========================
   DATA PELANGGAN
========================= */
$pelanggan = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT *
        FROM users
        WHERE id = $id
        AND role='user'
    ")
);

if(!$pelanggan){
    die("Data pelanggan tidak ditemukan");
}

/* =========================
   STATISTIK
========================= */
// Total Pesanan
$total_pesanan = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT COUNT(*) AS total
        FROM pesanan
        WHERE id_user = $id
    ")
);

// Total Transaksi
$total_transaksi = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT SUM(d.total_harga) AS total
        FROM detail_pesanan d
        JOIN pesanan p
            ON d.id_pesanan = p.id_pesanan
        WHERE p.id_user = $id
    ")
);

// Total Berat Laundry
$total_kg = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT SUM(d.berat) AS total
        FROM detail_pesanan d
        JOIN pesanan p
            ON d.id_pesanan = p.id_pesanan
        WHERE p.id_user = $id
    ")
);

// Rating
$rating = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT ROUND(AVG(rating),1) AS rating
        FROM ulasan
        WHERE id_user = $id
    ")
);

/* =========================
   RIWAYAT PESANAN
========================= */
$riwayat_pesanan = mysqli_query($koneksi,"
    SELECT *
    FROM pesanan
    WHERE id_user = $id
    ORDER BY id_pesanan DESC
");

/* =========================
   RIWAYAT ULASAN
========================= */
$riwayat_ulasan = mysqli_query($koneksi,"
    SELECT *
    FROM ulasan
    WHERE id_user = $id
    ORDER BY id_ulasan DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggan - Management System</title>
    
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        .container {
            display: flex;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        .main-content {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .content {
            padding: 30px !important;
            display: flex;
            flex-direction: column;
            gap: 24px;
            flex: 1;
        }

        .detail-header {
            display: flex;
            align-items: center;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 12px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        .back-btn:hover {
            color: #0f172a;
            border-color: #cbd5e1;
            background-color: #f1f5f9;
            transform: translateX(-3px);
        }

        /* LAYOUT BARU: BARIS ATAS SEJAJAR */
        .top-row-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
            width: 100%;
            align-items: stretch; /* Memastikan kartu kiri dan kanan sama tinggi */
        }

        /* Profile Card */
        .profile-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        }
        .profile-avatar {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 30px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
            margin-bottom: 14px;
        }
        .profile-info h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .profile-info p {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 14px 0;
        }
        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #2563eb;
            background: #eff6ff;
            padding: 6px 14px;
            border-radius: 30px;
            border: 1px solid #bfdbfe;
        }

        /* Grid Statistik (Kanan) - Berisi 4 kartu yang sejajar rapi */
        .stats-grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .stats-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.015);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            transition: all 0.2s ease;
        }
        .stats-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.04);
        }
        .stats-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }
        .icon-box-1 { background: #eff6ff; color: #2563eb; }
        .icon-box-2 { background: #f0fdf4; color: #16a34a; }
        .icon-box-3 { background: #faf5ff; color: #9333ea; }
        .icon-box-4 { background: #fefce8; color: #ca8a04; }

        .stats-data span {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }
        .stats-data h3 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        /* LAYOUT BAWAH: BARIS TABEL-TABEL */
        .bottom-tables-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 100%;
        }

        .table-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.01);
            overflow: hidden;
        }
        .table-card h2 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            padding: 18px 24px;
            margin: 0;
            border-bottom: 1px solid #f1f5f9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #f8fafc;
            padding: 12px 24px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        table td {
            padding: 14px 24px;
            font-size: 13.5px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .badge-secondary { background-color: #f1f5f9; color: #475569; }

        .rating-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 700;
            color: #0f172a;
        }
        .rating-star { color: #eab308; }

        .comment-text {
            color: #475569;
            font-style: italic;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
        }

        .empty-data {
            text-align: center;
            padding: 30px;
            color: #94a3b8;
        }

        /* Responsif untuk layar tablet/handphone */
        @media (max-width: 992px) {
            .top-row-layout, .bottom-tables-layout {
                grid-template-columns: 1fr;
            }
            .stats-grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .stats-grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <?php include '../includes/admin/sidebar.php'; ?>

    <div class="main-content">

        <?php include '../includes/admin/navbar.php'; ?>

        <div class="content">

            <div class="detail-header">
                <a href="pelanggan.php" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="top-row-layout">
                
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($pelanggan['nama'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($pelanggan['nama']); ?></h2>
                        <p><?= htmlspecialchars($pelanggan['email']); ?></p>
                        <span class="member-badge">
                            <i class="fa-solid fa-user-check"></i> Pelanggan Terverifikasi
                        </span>
                    </div>
                </div>

                <div class="stats-grid-container">
                    <div class="stats-box">
                        <div class="stats-icon icon-box-1">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div class="stats-data">
                            <span>Total Pesanan</span>
                            <h3><?= $total_pesanan['total'] ?? 0; ?></h3>
                        </div>
                    </div>

                    <div class="stats-box">
                        <div class="stats-icon icon-box-2">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div class="stats-data">
                            <span>Total Transaksi</span>
                            <h3>Rp <?= number_format($total_transaksi['total'] ?? 0, 0, ',', '.'); ?></h3>
                        </div>
                    </div>

                    <div class="stats-box">
                        <div class="stats-icon icon-box-3">
                            <i class="fa-solid fa-weight-scale"></i>
                        </div>
                        <div class="stats-data">
                            <span>Total Laundry</span>
                            <h3><?= number_format($total_kg['total'] ?? 0, 1, ',', '.'); ?> Kg</h3>
                        </div>
                    </div>

                    <div class="stats-box">
                        <div class="stats-icon icon-box-4">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="stats-data">
                            <span>Rata-rata Rating</span>
                            <h3><?= $rating['rating'] ?? '0.0'; ?></h3>
                        </div>
                    </div>
                </div>

            </div> <div class="bottom-tables-layout">

                <div class="table-card">
                    <h2>Riwayat Pesanan</h2>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(mysqli_num_rows($riwayat_pesanan) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($riwayat_pesanan)): 
                                    $status_text = strtolower($row['status']);
                                    $badge_class = 'badge-secondary';
                                    if($status_text == 'selesai') { $badge_class = 'badge-success'; }
                                    elseif(in_array($status_text, ['proses', 'sedang dicuci', 'dicuci'])) { $badge_class = 'badge-warning'; }
                                    elseif($status_text == 'diterima') { $badge_class = 'badge-info'; }
                                ?>
                                <tr>
                                    <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['kode_pesanan']); ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_masuk'])); ?></td>
                                    <td><span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-data">Belum ada riwayat pesanan.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <h2>Riwayat Ulasan</h2>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Rating</th>
                                    <th style="width: 70%;">Komentar</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(mysqli_num_rows($riwayat_ulasan) > 0): ?>
                                <?php while($ulasan = mysqli_fetch_assoc($riwayat_ulasan)): ?>
                                <tr>
                                    <td>
                                        <div class="rating-wrapper">
                                            <i class="fa-solid fa-star rating-star"></i>
                                            <span><?= $ulasan['rating']; ?>.0</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comment-text">"<?= htmlspecialchars($ulasan['komentar']); ?>"</div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">
                                        <div class="empty-data">Belum meninggalkan ulasan.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> </div> <?php include '../includes/admin/footer.php'; ?>

    </div>

</div>

</body>
</html>