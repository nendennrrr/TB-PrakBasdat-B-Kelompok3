<?php
session_start();
require '../config/koneksi.php';

// Proteksi halaman admin
if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

/* ==========================================================================
   1. HITUNG STATISTIK UTAMA (WIDGETS)
   ========================================================================== */
$query_pemasukan = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total FROM detail_pesanan");
$data_pemasukan = mysqli_fetch_assoc($query_pemasukan);
$total_pemasukan = $data_pemasukan['total'] ?? 0;

$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan");
$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_transaksi = $data_transaksi['total'] ?? 0;

$query_ulasan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM ulasan");
$data_ulasan = mysqli_fetch_assoc($query_ulasan);
$total_ulasan = $data_ulasan['total'] ?? 0;


/* ==========================================================================
   2. DATA UNTUK DIAGRAM CHART PEMASUKAN (Per Bulan di Tahun Berjalan)
   ========================================================================== */
$tahun_ini = date('Y');

$query_chart = mysqli_query($koneksi, "
    SELECT 
        MONTH(p.tanggal_masuk) AS bulan, 
        SUM(d.total_harga) AS total 
    FROM pesanan p
    JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    WHERE YEAR(p.tanggal_masuk) = '$tahun_ini'
    GROUP BY MONTH(p.tanggal_masuk)
    ORDER BY MONTH(p.tanggal_masuk) ASC
");

$bulans = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];
$pemasukan_per_bulan = array_fill(1, 12, 0);

if ($query_chart) {
    while ($row = mysqli_fetch_assoc($query_chart)) {
        $pemasukan_per_bulan[(int)$row['bulan']] = (int)$row['total'];
    }
}

$json_labels = json_encode(array_values($bulans));
$json_data = json_encode(array_values($pemasukan_per_bulan));


/* ==========================================================================
   3. AMBIL DATA SELURUH AKTIVITAS TRANSAKSI (UNTUK TABEL)
   ========================================================================== */
$query_aktivitas = mysqli_query($koneksi, "
    SELECT 
        p.*, 
        d.total_harga,
        u.nama AS nama_pelanggan
    FROM pesanan p
    LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN users u ON p.id_user = u.id
    ORDER BY p.tanggal_masuk DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Statistik | LaundryKu Admin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ==========================================================================
           TAMPILAN LAYAR DASHBOARD UTAMA (WEB UI PREMIUM)
           ========================================================================== */
        :root { 
            --primary: #0f172a; 
            --accent: #2563eb;
            --bg-main: #f8fafc;
            --card-bg: #ffffff; 
            --dark: #0f172a; 
            --border: #e2e8f0;
            --text-muted: #64748b;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-main); 
            margin: 0; 
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
        }

        .container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; display: flex; flex-direction: column; background-color: var(--bg-main); }
        .content-wrapper { flex: 1; padding: 32px 40px; box-sizing: border-box; }

        /* Header UI */
        .page-header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px; 
        }
        .page-title { margin: 0 0 4px 0; font-size: 24px; font-weight: 700; letter-spacing: -0.02em; }
        .page-subtitle { margin: 0; font-size: 14px; color: var(--text-muted); }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--accent);
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        /* ==========================================================================
           ✨ DESAIN BARU: STATS CARDS PREMIUM & ELEGAN DENGAN ANIMASI
           ========================================================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.015);
            /* Animasi transisi yang halus */
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        border-color 0.25s ease;
            user-select: none;
        }
        
        /* Efek Hover: Kartu sedikit naik, bayangan melembut */
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(37, 99, 235, 0.2);
            box-shadow: 0 12px 24px -8px rgba(15, 23, 42, 0.08), 
                        0 4px 12px -4px rgba(15, 23, 42, 0.03);
        }

        /* Efek Click/Active: Memantul mengecil memberikan feedback responsif */
        .stat-card:active {
            transform: translateY(-2px) scale(0.97);
            box-shadow: 0 6px 12px -6px rgba(15, 23, 42, 0.08);
            transition: transform 0.1s ease;
        }

        /* Decorative Glow Background Accent */
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 90px; height: 90px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(20px, -20px);
            transition: all 0.3s ease;
        }
        .stat-card:hover::before {
            transform: translate(15px, -15px) scale(1.2);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        /* Modifikasi warna box ikon agar tampak glow profesional */
        .icon-box.money { background: #f0fdf4; color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.1); }
        .icon-box.orders { background: #eff6ff; color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.1); }
        .icon-box.reviews { background: #fffbeb; color: #d97706; border: 1px solid rgba(217, 119, 6, 0.1); }

        /* Efek Ikon berputar tipis saat kartu di-hover */
        .stat-card:hover .icon-box {
            transform: scale(1.05) rotate(3deg);
        }

        .stat-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .stat-details span { 
            display: block; 
            font-size: 13px; 
            color: var(--text-muted); 
            font-weight: 600; 
            letter-spacing: 0.01em;
        }
        .stat-details h3 { 
            margin: 0; 
            font-size: 22px; 
            font-weight: 700; 
            color: #0f172a; 
            letter-spacing: -0.03em; 
        }

        /* Layout Main Sections */
        .report-layout { display: flex; flex-direction: column; gap: 24px; }

        .chart-section, .table-section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }
        .section-header { margin-bottom: 20px; }
        .section-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: var(--dark); }
        .chart-container { position: relative; width: 100%; height: 280px; }

        /* Tabel UI Desain Profesional & Bersih */
        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th { background-color: #f8fafc; color: #475569; padding: 12px 14px; font-weight: 600; border-bottom: 1px solid var(--border); }
        td { padding: 14px; border-bottom: 1px solid var(--border); color: #334155; }
        tr:hover td { background-color: #f8fafc; }

        /* Badges */
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-block; }
        .badge.selesai { background-color: #dcfce7; color: #15803d; }
        .badge.proses { background-color: #fef3c7; color: #b45309; }

        /* Aturan Hembunyikan Template Fisik Kertas di Browser */
        .print-letter-template, .print-footer-signature {
            display: none;
        }

        /* ==========================================================================
           🎨 MODUL CETAK TOTAL: MEMBERSIHKAN NAVBAR & ELEMEN WEB SECARA ABSOLUT
           ========================================================================== */
        @media print {
            aside, .sidebar, header, nav, .navbar, .btn-print, .chart-section, footer, 
            #sidebar-wrapper, .page-header, .icon-box, [class*="header-user"], .section-header,
            .no-print, [class*="nav"], [class*="topbar"], .main-header, .navbar-custom, .navbar-nav {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 11pt;
                line-height: 1.5;
                padding: 0 !important;
                margin: 0 !important;
            }

            .container, .main-content, .content-wrapper {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
            }

            .print-letter-template {
                display: block !important;
            }

            .kop-surat {
                text-align: center;
                border-bottom: 2px solid #000000;
                padding-bottom: 8px;
                margin-bottom: 20px;
            }
            .kop-surat h1 {
                font-size: 20pt;
                font-weight: bold;
                margin: 0 0 2px 0;
            }
            .kop-surat p {
                margin: 2px 0;
                font-size: 10pt;
                font-style: italic;
            }

            .meta-surat {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
                font-size: 11pt;
            }
            .meta-surat p { margin: 2px 0; }

            .isi-pengantar {
                text-align: justify;
                text-indent: 30px;
                margin-bottom: 20px;
            }

            .stats-grid {
                display: block !important;
                margin-bottom: 20px !important;
                padding-left: 15px;
            }
            .stat-card {
                border: none !important;
                padding: 2px 0 !important;
                margin-bottom: 4px !important;
                box-shadow: none !important;
                background: transparent !important;
                display: list-item !important; 
                list-style-type: circle;
                transform: none !important;
            }
            .stat-details span {
                display: inline !important;
                font-weight: bold;
            }
            .stat-details span::after {
                content: " : ";
            }
            .stat-details h3 {
                display: inline !important;
                font-size: 11pt !important;
                font-weight: normal !important;
            }

            .table-section {
                border: none !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #000000 !important;
                padding: 6px 8px !important;
                font-size: 10pt !important;
                color: #000000 !important;
            }
            th {
                background-color: #f2f2f2 !important;
                text-align: center !important;
                font-weight: bold !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr:hover td { background-color: transparent !important; }
            td { background: transparent !important; }
            
            td, td *, tr td a {
                color: #000000 !important;
                text-decoration: none !important;
            }
            .badge {
                padding: 0 !important;
                background: transparent !important;
                font-weight: normal !important;
            }

            .print-footer-signature {
                display: flex !important;
                justify-content: space-between !important;
                margin-top: 40px !important;
                page-break-inside: avoid;
            }
            .signature-box {
                text-align: center;
                width: 200px;
            }
            .signature-space {
                height: 60px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php require '../includes/admin/sidebar.php'; ?>
    
    <div class="main-content">
        
        <div class="no-print">
            <?php require '../includes/admin/navbar.php'; ?>
        </div>
        <br>
    <br>
    <br>
        <div class="content-wrapper">

            <div class="print-letter-template">
                <div class="kop-surat">
                    <h1>LAUNDRYKU MANAGEMENT SYSTEM</h1>
                    <p>Jl. Raya Garut - Bandung No. 42, Tarogong Kaler, Kabupaten Garut, Jawa Barat</p>
                    <p>Email: admin@laundryku.com | Telp: (0262) 234567 | Website: laundryku.com</p>
                </div>

                <div class="meta-surat">
                    <div>
                        <p><strong>Nomor :</strong> <?= date('Ymd') ?>/LP-FIN/ADM/<?= date('m') ?></p>
                        <p><strong>Perihal :</strong> Laporan Akumulasi Finansial & Dokumen Operasional</p>
                    </div>
                    <div>
                        <p>Garut, <?= date('d F Y') ?></p>
                    </div>
                </div>

                <p class="isi-pengantar">
                    Bersama dengan surat laporan ini, diserahkan lembar dokumen fisik hasil analisis data sistem 
                    yang memuat rekapan keuangan, total transaksi masuk, serta lampiran riwayat pesanan pelanggan dengan rincian sebagai berikut:
                </p>
            </div>

            <div class="page-header">
                <div>
                    <h1 class="page-title">Laporan & Analisis</h1>
                    <p class="page-subtitle">Pantau performa omset masuk, statistik, dan riwayat pesanan.</p>
                </div>
                <button onclick="window.print()" class="btn-print">
                    <i class="fa-solid fa-print"></i> Cetak Dokumen Resmi
                </button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon-box money"><i class="fa-solid fa-wallet"></i></div>
                    <div class="stat-details">
                        <span>Total Pemasukan</span>
                        <h3>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="icon-box orders"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <div class="stat-details">
                        <span>Total Pesanan</span>
                        <h3><?= $total_transaksi ?> Transaksi</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="icon-box reviews"><i class="fa-solid fa-star"></i></div>
                    <div class="stat-details">
                        <span>Ulasan Pelanggan</span>
                        <h3><?= $total_ulasan ?> Review</h3>
                    </div>
                </div>
            </div>

            <div class="report-layout">
                <div class="chart-section">
                    <div class="section-header">
                        <h2>Tren Grafik Pemasukan (Tahun <?= $tahun_ini ?>)</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="pemasukanChart"></canvas>
                    </div>
                </div>

                <div class="table-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%; text-align: center;">No</th>
                                    <th>Kode Order</th>
                                    <th>Nama Pelanggan</th>
                                    <th style="text-align: center;">Tanggal Masuk</th>
                                    <th style="text-align: right;">Total Biaya</th>
                                    <th style="text-align: center; width: 12%;">Status Kerja</th>
                                </tr>
                             Clyde_</thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if(!$query_aktivitas || mysqli_num_rows($query_aktivitas) == 0): 
                                ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum terdapat riwayat aktivitas transaksi.</td>
                                    </tr>
                                <?php 
                                else:
                                    while($row = mysqli_fetch_assoc($query_aktivitas)): 
                                ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted);"><?= $no++ ?></td>
                                    <td style="font-weight: 600; color: var(--accent);">#<?= htmlspecialchars($row['kode_pesanan'] ?? '-') ?></td>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($row['nama_pelanggan'] ?? 'Pelanggan Umum') ?></td>
                                    <td style="text-align: center; color: var(--text-muted);"><?= !empty($row['tanggal_masuk']) ? date('d M Y', strtotime($row['tanggal_masuk'])) : '-' ?></td>
                                    <td style="font-weight: 600; text-align: right;">Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.') ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= (isset($row['status']) && $row['status'] == 'Selesai') ? 'selesai' : 'proses' ?>">
                                            <?= htmlspecialchars($row['status'] ?? 'Proses') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="print-footer-signature">
                <div class="signature-box">
                    <p>Dibuat Oleh,</p>
                    <p style="font-weight: bold; margin-top: 2px;">Administrator Sistem</p>
                    <div class="signature-space"></div>
                    <p>( ____________________ )</p>
                </div>
                <div class="signature-box">
                    <p>Diperiksa & Disahkan,</p>
                    <p style="font-weight: bold; margin-top: 2px;">Pemilik / Pimpinan Toko</p>
                    <div class="signature-space"></div>
                    <p>( ____________________ )</p>
                </div>
            </div>

        </div>

        <?php require '../includes/admin/footer.php'; ?>
    </div>
</div>

<script>
    const ctx = document.getElementById('pemasukanChart').getContext('2d');
    const labelBulan = <?= $json_labels ?>;
    const dataPemasukan = <?= $json_data ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelBulan,
            datasets: [{
                label: 'Omset Pemasukan (Rp)',
                data: dataPemasukan,
                backgroundColor: 'rgba(37, 99, 235, 0.04)',
                borderColor: '#2563eb',
                borderWidth: 2,
                pointBackgroundColor: '#2563eb',
                pointHoverRadius: 6,
                tension: 0.2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); },
                        font: { family: 'Plus Jakarta Sans', size: 11 }
                    }
                },
                x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } } }
            }
        }
    });
</script>

</body>
</html>