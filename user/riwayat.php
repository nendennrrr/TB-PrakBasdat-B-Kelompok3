<?php
session_start();
require '../config/koneksi.php';

// 1. Proteksi Halaman
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];

// 2. Sanitasi Input Pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';

// 3. Membangun Kondisi Query (Koneksi Join)
$where = "WHERE p.id_user = '$id_user'";
if (!empty($search)) {
    $where .= " AND (
        p.kode_pesanan LIKE '%$search%' OR
        l.nama_layanan LIKE '%$search%' OR
        p.status LIKE '%$search%'
    )";
}

// 4. Pengambilan Data Riwayat Pesanan
$query_riwayat = "
    SELECT
        p.*,
        d.berat,
        l.nama_layanan,
        l.harga_perkg
    FROM pesanan p
    LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l ON d.id_layanan = l.id_layanan
    $where
    ORDER BY p.id_pesanan DESC
";
$riwayat = mysqli_query($koneksi, $query_riwayat);
$totalPesanan = mysqli_num_rows($riwayat);

// 5. Pengambilan Data Statistik Pesanan Selesai
$query_selesai = "SELECT COUNT(*) as total FROM pesanan WHERE id_user = '$id_user' AND status = 'Selesai'";
$sql_selesai = mysqli_query($koneksi, $query_selesai);
$selesai = mysqli_fetch_assoc($sql_selesai);
$totalSelesai = $selesai['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - LaundryKu</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">

    <style>
        /* ==========================================================================
           MAIN SYSTEM LAYOUT
           ========================================================================== */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 280px);
            box-sizing: border-box;
            background: #f8fafc;
        }

        .content {
            padding: 30px;
            margin-top: 75px;
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .page-wrapper {
            width: 100%;
            max-width: 1200px;
            box-sizing: border-box;
        }

        /* ==========================================================================
           HEADER HERO BANNER
           ========================================================================== */
        .header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            padding: 40px;
            border-radius: 24px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
        }

        .header-text h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .header-text p {
            opacity: 0.85;
            font-size: 14px;
            line-height: 1.5;
        }

        /* SEARCH BAR COMPONENT */
        .search-form {
            display: flex;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px;
            border-radius: 14px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            height: 44px;
            padding: 0 15px 0 45px;
            border: none;
            border-radius: 10px;
            background: transparent;
            font-size: 14px;
            color: #fff;
            outline: none;
            box-sizing: border-box;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
        }

        .search-form button {
            border: none;
            background: #ffffff;
            color: #1e3a8a;
            padding: 0 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-form button:hover {
            background: #f1f5f9;
        }

        /* ==========================================================================
           STATISTICS METRICS
           ========================================================================== */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
            border: 1px solid #e2e8f0;
        }

        .stat-details span {
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
            display: block;
            margin-bottom: 4px;
        }

        .stat-details h3 {
            font-size: 30px;
            margin: 0;
            color: #0f172a;
            font-weight: 700;
        }

        .stat-wrapper-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
        }

        .stat-wrapper-icon.blue { background: #eff6ff; color: #2563eb; }
        .stat-wrapper-icon.green { background: #f0fdf4; color: #16a34a; }

        /* ==========================================================================
           COMPACT CARD TILES SYSTEM
           ========================================================================== */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 25px;
        }

        .order-card {
            background: #fff;
            border-radius: 24px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.02);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .order-code {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .order-service {
            margin-top: 4px;
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        /* BADGES ARCHITECTURE */
        .status-badge {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: -0.1px;
        }

        .diterima      { background: #eff6ff; color: #2563eb; }
        .diproses      { background: #fef9c3; color: #a16207; }
        .dicuci        { background: #ecfeff; color: #0891b2; }
        .disetrika     { background: #f5f3ff; color: #7c3aed; }
        .siap-diambil  { background: #fdf2f8; color: #db2777; }
        .selesai       { background: #f0fdf4; color: #16a34a; }

        /* ITEM DETAILS GRID CONTAINER */
        .items-summary {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .summary-row span {
            color: #64748b;
            font-size: 13px;
        }

        .summary-row strong {
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        /* BILLING INFO BLOCK */
        .billing-block {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f1f5f9;
            padding-top: 18px;
        }

        .total-wrapper span {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .total-wrapper h3 {
            margin: 2px 0 0 0;
            font-size: 20px;
            color: #1e3a8a;
            font-weight: 700;
        }

        /* INTERACTIVE ROW BUTTONS */
        .action-flex {
            display: flex;
            gap: 8px;
        }

        .btn-card {
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-card.primary {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
        }

        .btn-card.primary:hover {
            background: #1d4ed8;
        }

        .btn-card.secondary {
            background: #f1f5f9;
            color: #334155;
        }

        .btn-card.secondary:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* ==========================================================================
           EMPTY FALLBACK SCREEN
           ========================================================================== */
        .empty-state {
            background: #fff;
            border-radius: 24px;
            padding: 70px 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.01);
        }

        .empty-state i {
            font-size: 55px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }

        /* ==========================================================================
           RESPONSIVE STRUCTURAL OVERRIDES
           ========================================================================== */
        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 30px;
            }
            .search-form {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; padding: 15px 10px; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .topbar { left: 80px; height: 65px; }
            .content { margin-top: 65px; padding: 20px; }
            .orders-grid { grid-template-columns: 1fr; }
            .billing-block { flex-direction: column; align-items: flex-start; gap: 15px; }
            .action-flex { width: 100%; }
            .btn-card { flex: 1; }
        }
    </style>
</head>

<body>

<div class="container">

    <?php include '../includes/user/sidebar.php'; ?>

    <div class="main-content">
        
        <?php include '../includes/user/navbar.php'; ?>

        <div class="content">
            <div class="page-wrapper">

                <div class="header">
                    <div class="header-text">
                        <h1>Riwayat Pesanan</h1>
                        <p>Monitor dan tracking seluruh riwayat transaksi laundry Anda secara realtime.</p>
                    </div>

                    <form class="search-form" method="GET">
                        <div class="search-box">
                            <i class="fa fa-search"></i>
                            <input
                                type="text"
                                name="search"
                                placeholder="Cari kode, layanan, status..."
                                value="<?= htmlspecialchars($search); ?>"
                            >
                        </div>
                        <button type="submit">Cari</button>
                    </form>
                </div>

                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-details">
                            <span>Total Transaksi</span>
                            <h3><?= $totalPesanan; ?></h3>
                        </div>
                        <div class="stat-wrapper-icon blue">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-details">
                            <span>Cucian Selesai</span>
                            <h3><?= $totalSelesai; ?></h3>
                        </div>
                        <div class="stat-wrapper-icon green">
                            <i class="fas fa-pills"></i>
                        </div>
                    </div>
                </div>

                <?php if ($totalPesanan > 0): ?>
                    <div class="orders-grid">
                        <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                            <?php
                            $totalHarga = ($row['berat'] ?? 0) * ($row['harga_perkg'] ?? 0);
                            $statusClass = strtolower(str_replace(' ', '-', trim($row['status'])));
                            ?>
                            <div class="order-card">
                                <div>
                                    <div class="card-header-row">
                                        <div>
                                            <div class="order-code">#<?= htmlspecialchars($row['kode_pesanan']); ?></div>
                                            <div class="order-service"><?= htmlspecialchars($row['nama_layanan'] ?? 'Layanan Tidak Diketahui'); ?></div>
                                        </div>
                                        <span class="status-badge <?= $statusClass; ?>">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </div>

                                    <div class="items-summary">
                                        <div class="summary-row">
                                            <span>Beban Berat</span>
                                            <strong><?= number_format($row['berat'] ?? 0, 1, ',', '.'); ?> Kg</strong>
                                        </div>

                                        <div class="summary-row">
                                            <span>Tanggal Masuk</span>
                                            <strong>
                                                <?= !empty($row['tanggal_masuk']) ? date('d M Y', strtotime($row['tanggal_masuk'])) : '-'; ?>
                                            </strong>
                                        </div>

                                        <?php if (!empty($row['tanggal_selesai']) && $row['tanggal_selesai'] != '0000-00-00'): ?>
                                            <div class="summary-row">
                                                <span>Tanggal Selesai</span>
                                                <strong><?= date('d M Y', strtotime($row['tanggal_selesai'])); ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="billing-block">
                                    <div class="total-wrapper">
                                        <span>Total Tagihan</span>
                                        <h3>Rp <?= number_format($totalHarga, 0, ',', '.'); ?></h3>
                                    </div>

                                    <div class="action-flex">
                                        <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn-card secondary">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>

                                        <?php if (strtolower(trim($row['status'])) === 'selesai'): ?>
                                            <a href="ulasan.php?id=<?= $row['id_pesanan']; ?>" class="btn-card primary">
                                                <i class="fas fa-star"></i> Ulas
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-folder-open"></i>
                        <p>Tidak ada transaksi laundry ditemukan.</p>
                    </div>
                <?php endif; ?>

            </div> 
        </div> 
    </div> 
</div> 

</body>
</html>