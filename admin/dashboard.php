<?php
session_start();
require '../config/koneksi.php';

// Proteksi halaman admin sederhana
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit;
}

/* ==========================================================================
   CARD STATISTIK
   ========================================================================== */
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='user'"));
$total_pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan"));
$total_ulasan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM ulasan"));
$rata_rating = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT ROUND(AVG(rating),1) AS rating FROM ulasan"));

/* ==========================================================================
   PESANAN TERBARU & PENCARIAN
   ========================================================================== */
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';
$queryPesanan = "SELECT p.kode_pesanan, u.nama, p.tanggal_masuk, p.status FROM pesanan p JOIN users u ON p.id_user = u.id";

if(!empty($keyword)){
    $queryPesanan .= " WHERE p.kode_pesanan LIKE '%$keyword%' OR u.nama LIKE '%$keyword%'";
}
$queryPesanan .= " ORDER BY p.id_pesanan DESC LIMIT 10";
$pesanan_terbaru = mysqli_query($koneksi, $queryPesanan);

/* ==========================================================================
   LAYANAN TERFAVORIT
   ========================================================================== */
$layanan_favorit = mysqli_query($koneksi, "
    SELECT l.nama_layanan, COUNT(*) AS total 
    FROM detail_pesanan d 
    JOIN layanan l ON d.id_layanan = l.id_layanan 
    GROUP BY l.id_layanan 
    ORDER BY total DESC LIMIT 5
");

/* ==========================================================================
   STATUS AKTIVITAS PESANAN (TAMPILAN LIST)
   ========================================================================== */
$pesanan_hari_ini = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE tanggal_masuk = CURDATE()"));
$diterima = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status='Diterima'"));
$diproses = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status='Diproses'"));
$selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status='Selesai'"));

/* ==========================================================================
   DATA STATISTIK MINGGUAN (7 HARI TERAKHIR) UNTUK LINE CHART
   ========================================================================== */
$labels_mingguan = [];
$data_mingguan = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('d M', strtotime($date));
    $labels_mingguan[] = $label;
    
    $q_hitung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE tanggal_masuk = '$date'"));
    $data_mingguan[] = (int)$q_hitung['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - LaundryKu</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ==========================================================================
           SISTEM TATA LETAK UTAMA (RESET & BOX SYSTEM)
           ========================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 280px; 
            width: calc(100% - 280px);
            display: flex;
            flex-direction: column;
        }

        .content {
            padding: 40px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* Title Area */
        .page-title h1 {
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .page-title p {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ==========================================================================
           HERO WELCOME BANNER
           ========================================================================== */
        .hero-banner {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            border-radius: 20px;
            padding: 32px;
            color: #ffffff;
            margin: 28px 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
        }

        .hero-content h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .hero-content h2 {
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 12px;
        }

        .hero-content p {
            font-size: 14px;
            max-width: 700px;
            opacity: 0.8;
            line-height: 1.6;
        }

        /* ==========================================================================
           GRID KARTU RINGKASAN DATA & HOVER ANIMATION
           ========================================================================== */
        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03), 0 2px 4px -2px rgba(15, 23, 42, 0.03);
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-6px);
            border-color: #cbd5e1;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
        }

        .card i {
            width: 48px;
            height: 48px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .card:hover i {
            transform: scale(1.1) rotate(3deg);
        }

        .card:nth-child(2) i { background: #f0fdf4; color: #16a34a; }
        .card:nth-child(3) i { background: #fef3c7; color: #d97706; }
        .card:nth-child(4) i { background: #faf5ff; color: #9333ea; }

        .card-info h2 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }

        .card-info p {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }

        /* ==========================================================================
           DASHBOARD MULTI-GRID STRUCTURE
           ========================================================================== */
        .dashboard-row-top {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .dashboard-row-sub {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* ==========================================================================
           TABEL KARTU (CARD PANEL)
           ========================================================================== */
        .table-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.02);
            transition: box-shadow 0.3s ease;
        }

        .table-card:hover {
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.04);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .table-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Form Pencarian Elegan */
        .search-form {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 4px 8px;
            width: 100%;
            max-width: 360px;
            border: 1px solid transparent;
            transition: all 0.25s ease;
        }
        
        .search-form:focus-within {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .search-form input {
            border: none;
            background: transparent;
            padding: 8px;
            width: 100%;
            font-size: 13px;
            outline: none;
            color: #0f172a;
        }

        .search-form button {
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
        }

        /* Desain Tabel Modern */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        table th {
            background: #f8fafc;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        table td {
            padding: 16px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        table tbody tr:hover {
            background: #f8fafc;
        }

        /* Desain Badge Status */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.selesai { background: #dcfce7; color: #15803d; }
        .badge.proses { background: #fef3c7; color: #b45309; }
        .badge.diterima { background: #e0f2fe; color: #0369a1; }

        /* ==========================================================================
           CHART & LIST DATA ITEM INTERACTIVE STYLE
           ========================================================================== */
        .chart-container {
            position: relative;
            width: 100%;
            height: 280px;
        }

        /* Baris List Interaktif */
        .list-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 15px 8px;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 8px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .list-row:last-child {
            border: none;
        }

        .list-row:hover {
            background: #f8fafc;
            padding-left: 14px;
        }

        .row-icon-box {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        /* Warna khusus untuk List Status */
        .status-hari-ini { background: #eff6ff; color: #2563eb; }
        .status-diterima { background: #e0f2fe; color: #0369a1; }
        .status-diproses { background: #fef3c7; color: #b45309; }
        .status-selesai { background: #dcfce7; color: #15803d; }

        .list-content {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }

        .list-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }

        .list-info span {
            font-size: 12px;
            color: #64748b;
        }

        .list-counter {
            background: #f1f5f9;
            color: #334155;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }
        
        .list-counter.highlight {
            background: #eff6ff;
            color: #2563eb;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .dashboard-row-top { grid-template-columns: 1fr; }
            .dashboard-row-sub { grid-template-columns: 1fr; }
        }

        @media (max-width: 1024px) {
            .cards { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; width: 100%; }
            .cards { grid-template-columns: 1fr; }
            .table-header { flex-direction: column; gap: 14px; align-items: flex-start; }
            .search-form { max-width: 100%; }
        }
    </style>
</head>

<body>

<div class="container">

    <?php include '../includes/admin/sidebar.php'; ?>

    <div class="main-content">
        
        <?php include '../includes/admin/navbar.php'; ?>

        <div class="content admin-dashboard">
            <br>
            <br>
            <br>
            <div class="page-title">
                <h1>Dashboard Admin</h1>
                <p>Ringkasan performa dan data sistem manajemen LaundryKu.</p>
            </div>

            <div class="hero-banner">
                <div class="hero-content">
                    <h1>Hai, <?= htmlspecialchars($_SESSION['nama']); ?>! </h1>
                    <h2>Selamat Datang di Panel Kendali Dashboard</h2>
                    <p>Kelola data pelanggan, pantau status pengerjaan transaksi laundry, ulasan performa outlet, dan validasi laporan keuangan dengan mudah dalam satu sistem terintegrasi.</p>
                </div>
            </div>

            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-users"></i>
                    <div class="card-info">
                        <h2><?= $total_pelanggan['total']; ?></h2>
                        <p>Total Pelanggan</p>
                    </div>
                </div>
                <div class="card">
                    <i class="fa-solid fa-file-invoice"></i>
                    <div class="card-info">
                        <h2><?= $total_pesanan['total']; ?></h2>
                        <p>Total Transaksi</p>
                    </div>
                </div>
                <div class="card">
                    <i class="fa-solid fa-comment-dots"></i>
                    <div class="card-info">
                        <h2><?= $total_ulasan['total']; ?></h2>
                        <p>Total Ulasan</p>
                    </div>
                </div>
                <div class="card">
                    <i class="fa-solid fa-star-half-stroke"></i>
                    <div class="card-info">
                        <h2><?= $rata_rating['rating'] ?? 0; ?> <span style="font-size:14px; color:#64748b;">/ 5</span></h2>
                        <p>Kepuasan Konsumen</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-row-top">
                
                <div class="table-card">
                    <div class="table-header">
                        <div>
                            <h2>Grafik Tren Pesanan Masuk</h2>
                            <p style="font-size:12px; color:#94a3b8; margin-top:2px;">Analisis volume pesanan 7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="weeklyTrendsChart"></canvas>
                    </div>
                </div>

                <div class="table-card">
                    <div style="margin-bottom: 20px;">
                        <h2>Status Aktivitas Pesanan</h2>
                        <p style="font-size:12px; color:#94a3b8; margin-top:2px;">Kondisi pengerjaan berkas nota saat ini</p>
                    </div>

                    <div class="list-row">
                        <div class="row-icon-box status-hari-ini"><i class="fa-solid fa-calendar-day"></i></div>
                        <div class="list-content">
                            <div class="list-info">
                                <h4>Masuk Hari Ini</h4>
                                <span>Pesanan baru tanggal sekarang</span>
                            </div>
                            <div class="list-counter highlight"><?= $pesanan_hari_ini['total']; ?></div>
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="row-icon-box status-diterima"><i class="fa-solid fa-file-import"></i></div>
                        <div class="list-content">
                            <div class="list-info">
                                <h4>Status Diterima</h4>
                                <span>Menunggu antrean pakaian</span>
                            </div>
                            <div class="list-counter"><?= $diterima['total']; ?></div>
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="row-icon-box status-diproses"><i class="fa-solid fa-spinner"></i></div>
                        <div class="list-content">
                            <div class="list-info">
                                <h4>Sedang Diproses</h4>
                                <span>Pakaian dicuci / disetrika</span>
                            </div>
                            <div class="list-counter"><?= $diproses['total']; ?></div>
                        </div>
                    </div>

                    <div class="list-row">
                        <div class="row-icon-box status-selesai"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="list-content">
                            <div class="list-info">
                                <h4>Sudah Selesai</h4>
                                <span>Siap diambil / dikirim ke user</span>
                            </div>
                            <div class="list-counter"><?= $selesai['total']; ?></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="dashboard-row-sub">
                
                <div class="table-card">
                    <div style="margin-bottom: 20px;">
                        <h2>Layanan Terfavorit</h2>
                        <p style="font-size:12px; color:#94a3b8; margin-top:2px;">Top 5 paket paling laris dipilih</p>
                    </div>

                    <?php if(mysqli_num_rows($layanan_favorit) > 0): ?>
                        <?php $rank = 1; while($row = mysqli_fetch_assoc($layanan_favorit)): ?>
                        <div class="list-row">
                            <div class="row-icon-box" style="font-size:12px; font-weight:700;"><?= $rank++; ?></div>
                            <div class="list-content">
                                <div class="list-info">
                                    <h4><?= htmlspecialchars($row['nama_layanan']); ?></h4>
                                    <span>Dipilih <?= $row['total']; ?>x oleh user</span>
                                </div>
                                <div class="list-counter highlight" style="font-size:12px;"><?= $row['total']; ?></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding: 40px 0; color:#94a3b8; font-size:13px;">
                            <i class="fa-solid fa-soap" style="font-size:24px; margin-bottom:10px; color:#cbd5e1;"></i>
                            <p>Belum ada rekaman data transaksi.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="table-card">
                    <div class="table-header">
                        <h2>Antrean Pesanan Terbaru</h2>
                        <form method="GET" class="search-form">
                            <input 
                                type="text" 
                                name="keyword" 
                                placeholder="Cari kode / nama..." 
                                value="<?= htmlspecialchars($keyword); ?>">
                            <button type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </form>
                    </div>

                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kode Nota</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($pesanan_terbaru) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                                    <tr>
                                        <td style="font-weight: 700; color:#2563eb;"><?= $row['kode_pesanan']; ?></td>
                                        <td style="font-weight: 500;"><?= htmlspecialchars($row['nama']); ?></td>
                                        <td>
                                            <?php
                                            $st = $row['status'];
                                            if($st == 'Selesai'){
                                                echo "<span class='badge selesai'>$st</span>";
                                            } elseif($st == 'Diproses'){
                                                echo "<span class='badge proses'>$st</span>";
                                            } else {
                                                echo "<span class='badge diterima'>$st</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align:center; color:#94a3b8; padding:30px;">Data tidak ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <?php include '../includes/admin/footer.php'; ?>

    </div>
</div>

<script>
    const ctx = document.getElementById('weeklyTrendsChart').getContext('2d');
    
    // Konfigurasi gradient warna untuk efek estetik premium di bawah garis grafik
    const chartGradient = ctx.createLinearGradient(0, 0, 0, 250);
    chartGradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
    chartGradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

    const weeklyTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels_mingguan); ?>,
            datasets: [{
                label: 'Jumlah Pesanan',
                data: <?= json_encode($data_mingguan); ?>,
                borderColor: '#2563eb',
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#2563eb',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
                tension: 0.35, // Membuat lekukan garis melengkung dinamis (smooth bezier)
                fill: true,
                backgroundColor: chartGradient
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 600 },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        stepSize: 1,
                        font: { family: 'Plus Jakarta Sans', size: 11, color: '#64748b' }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', size: 12, weight: 500, color: '#475569' } }
                }
            }
        }
    });
</script>

</body>
</html>