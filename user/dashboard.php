<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = (int)$_SESSION['id'];

/* 
   TOTAL PESANAN
  */
$totalOrder = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT COUNT(*) AS total
        FROM pesanan
        WHERE id_user = '$id_user'
    ")
);

/* 
   TOTAL SELESAI
   */
$totalSelesai = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT COUNT(*) AS total
        FROM pesanan
        WHERE id_user = '$id_user'
        AND status = 'Selesai'
    ")
);

/* 
   TOTAL PROSES
    */
$totalProses = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT COUNT(*) AS total
        FROM pesanan
        WHERE id_user = '$id_user'
        AND status = 'Diproses'
    ")
);

/* 
   TOTAL PEMBAYARAN
   */
$totalBayar = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT
            COALESCE(
                SUM(
                    COALESCE(d.berat,0)
                    *
                    COALESCE(l.harga_perkg,0)
                ),
                0
            ) AS total
        FROM pesanan p
        LEFT JOIN detail_pesanan d
            ON p.id_pesanan = d.id_pesanan
        LEFT JOIN layanan l
            ON d.id_layanan = l.id_layanan
        WHERE p.id_user = '$id_user'
    ")
);

/* ==========================================================================
   RIWAYAT TERBARU
   ========================================================================== */
$riwayat = mysqli_query($koneksi,"
    SELECT
        p.id_pesanan,
        p.kode_pesanan,
        p.status,
        p.tanggal_masuk,
        d.berat,
        l.nama_layanan,
        l.harga_perkg
    FROM pesanan p
    LEFT JOIN detail_pesanan d
        ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l
        ON d.id_layanan = l.id_layanan
    WHERE p.id_user = '$id_user'
    ORDER BY p.tanggal_masuk DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ringkasan - LaundryKu</title>

    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           MAIN CONTENT & WRAPPER SYSTEM
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
            box-sizing: border-box;
        }

        /* ==========================================================================
           HERO BANNER WELCOME
           ========================================================================== */
        .hero-banner {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            border-radius: 24px;
            padding: 40px;
            color: #ffffff;
            margin-bottom: 35px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
        }

        .hero-content h1 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff !important;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .hero-content h2 {
            font-size: 18px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9) !important;
            margin-bottom: 12px;
        }

        .hero-content p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8) !important;
            max-width: 600px;
            line-height: 1.6;
        }

        /* ==========================================================================
           GRID STATISTIK METRICS CARD
           ========================================================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 0;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            flex-shrink: 0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-content {
            min-width: 0;
        }

        .stat-content h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .stat-content p {
            margin: 4px 0 0 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
        }

        /* Varian Warna Pastel Profesional Masing-masing Card */
        .blue .stat-icon   { background: #eff6ff; color: #2563eb; }
        .orange .stat-icon { background: #fff7ed; color: #ea580c; }
        .green .stat-icon  { background: #f0fdf4; color: #16a34a; }
        .purple .stat-icon { background: #f5f3ff; color: #7c3aed; }

        /* ==========================================================================
           COMPONENTS DATA TABLE CARD
           ========================================================================== */
        .table-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            overflow: hidden;
        }

        .table-top {
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-top h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .table-top p {
            font-size: 13px;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        /* Search Box Component */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 300px;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            padding: 11px 16px 11px 44px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Responsive Wrapper Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        table th {
            background: #f8fafc;
            padding: 16px 30px;
            color: #475569;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f1f5f9;
        }

        table td {
            padding: 18px 30px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        /* Badge Kode Pesanan */
        .kode-pesanan {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #1e3a8a;
            background: #f0fdf4;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px dashed #bbf7d0;
        }

        /* Status Badge Management */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge.diproses { background: #fff7ed; color: #c2410c; }
        .badge.selesai { background: #f0fdf4; color: #15803d; }
        .badge.batal { background: #fef2f2; color: #b91c1c; }

        /* Empty State */
        .empty-data {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
            font-weight: 500;
        }

        .empty-data i {
            font-size: 40px;
            color: #cbd5e1;
            margin-bottom: 15px;
        }

        /* ==========================================================================
           MEDIA QUERY BREAKPOINTS RESPONSIVE
           ========================================================================== */
        @media(max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 992px) {
            .sidebar { width: 80px !important; min-width: 80px !important; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .topbar { left: 80px; height: 65px; }
            .content { margin-top: 65px; padding: 20px; }
        }

        @media(max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .table-top {
                flex-direction: column;
                align-items: flex-start;
            }
            .search-box {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <?php include '../includes/user/sidebar.php'; ?>

    <div class="main-content">

        <?php include '../includes/user/navbar.php'; ?>

        <div class="content">

            <div class="hero-banner">
                <div class="hero-content">
                    <h1>Halo, <?= htmlspecialchars($_SESSION['nama']); ?></h1>
                    <h2>Selamat Datang di LaundryKu</h2>
                    <p>Pantau status laundry, cek riwayat transaksi, dan lihat perkembangan pesanan Anda secara real-time melalui panel kendali utama Anda.</p>
                </div>
            </div>

            <div class="stats-grid">

                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h2><?= number_format($totalOrder['total'], 0, ',', '.'); ?></h2>
                        <p>Total Pesanan</p>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fa-solid fa-spinner fa-spin-pulse" style="--fa-animation-duration: 3s;"></i>
                    </div>
                    <div class="stat-content">
                        <h2><?= number_format($totalProses['total'], 0, ',', '.'); ?></h2>
                        <p>Sedang Diproses</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-content">
                        <h2><?= number_format($totalSelesai['total'], 0, ',', '.'); ?></h2>
                        <p>Pesanan Selesai</p>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <h2>Rp <?= number_format($totalBayar['total'], 0, ',', '.'); ?></h2>
                        <p>Total Pembayaran</p>
                    </div>
                </div>

            </div>

            <div class="table-card">
                
                <div class="table-top">
                    <div>
                        <h2>Riwayat Laundry Terbaru</h2>
                        <p>5 transaksi terakhir yang terdaftar pada sistem</p>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchTable" placeholder="Cari kode atau layanan...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Layanan</th>
                                <th>Berat</th>
                                <th>Harga / Kg</th>
                                <th>Tanggal Masuk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($riwayat) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($riwayat)): ?>
                                    <?php
                                        $statusClass = strtolower($row['status']);
                                        $statusClass = str_replace(' ', '-', $statusClass);
                                    ?>
                                    <tr class="data-row">
                                        <td>
                                            <span class="kode-pesanan"><?= htmlspecialchars($row['kode_pesanan']); ?></span>
                                        </td>
                                        <td style="font-weight: 600; color: #0f172a;">
                                            <?= htmlspecialchars($row['nama_layanan']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['berat']); ?> Kg
                                        </td>
                                        <td>
                                            Rp <?= number_format($row['harga_perkg'], 0, ',', '.'); ?>
                                        </td>
                                        <td style="color: #64748b;">
                                            <?= date('d M Y', strtotime($row['tanggal_masuk'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $statusClass; ?>">
                                                <?= htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-data">
                                            <i class="fa-solid fa-box-open"></i>
                                            <p>Belum ada riwayat pesanan laundry.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

        <?php include '../includes/user/footer.php'; ?>

    </div>
</div>

<script>
document.getElementById('searchTable').addEventListener('keyup', function(){
    let keyword = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr.data-row');

    rows.forEach(function(row){
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(keyword) ? '' : 'none';
    });
});
</script>
</body>
</html>