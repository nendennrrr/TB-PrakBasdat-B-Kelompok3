<?php
session_start();
require '../config/koneksi.php';

$id_user = $_SESSION['id'];

if(!isset($_GET['id'])){
    header("Location: pesanan.php");
    exit;
}

$id_pesanan = $_GET['id'];

$query = mysqli_query($koneksi,"
    SELECT
        p.*,
        d.berat,
        l.nama_layanan,
        l.harga_perkg
    FROM pesanan p
    LEFT JOIN detail_pesanan d
        ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l
        ON d.id_layanan = l.id_layanan
    WHERE p.id_pesanan='$id_pesanan'
    AND p.id_user='$id_user'
");

$data = mysqli_fetch_assoc($query);

if(!$data){
    echo "
    <script>
        alert('Data tidak ditemukan');
        window.location='pesanan.php';
    </script>
    ";
    exit;
}

$total = $data['berat'] * $data['harga_perkg'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - LaundryKu</title>
    
    <!-- Google Fonts & Font Awesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">

    <style>
        /* ==========================================================================
           BASE STYLING & RESET (ALL BLUE THEME ACCENT)
           ========================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f0f4f8;
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
            min-height: 100vh;
            background: #f0f4f8;
        }

        .content {
            flex: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* ==========================================================================
           MODERN BLUE INVOICE CARD
           ========================================================================== */
        .invoice-card {
            background: #ffffff;
            width: 100%;
            max-width: 650px;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.05);
            border: 1px solid #dbeafe;
        }

        /* Card Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #bfdbfe;
        }

        .invoice-title h2 {
            color: #1e3a8a;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .invoice-title p {
            color: #60a5fa;
            font-size: 14px;
            margin-top: 2px;
        }

        .invoice-icon {
            background: #eff6ff;
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 20px;
        }

        /* ==========================================================================
           GRID LAYOUT INFO
           ========================================================================== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-item {
            background: #ffffff;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #eff6ff;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-item.full-width {
            grid-column: span 2;
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: #93c5fd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e3a8a;
        }

        .code-text {
            color: #2563eb;
            font-weight: 700;
        }

        /* ==========================================================================
           BLUE PALETTE BADGE STATUS
           ========================================================================== */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            width: fit-content;
        }

        .diterima { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .diproses { background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; }
        .selesai { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .diambil { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }

        /* ==========================================================================
           PRICE BREAKDOWN CARD
           ========================================================================== */
        .bill-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .bill-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #475569;
            margin-bottom: 12px;
        }

        .bill-row:last-of-type {
            margin-bottom: 0;
        }

        .bill-row.total-row {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px dashed #cbd5e1;
            color: #1e3a8a;
        }

        .total-amount {
            font-size: 22px;
            font-weight: 700;
            color: #2563eb;
        }

        /* ==========================================================================
           ACTION BUTTONS
           ========================================================================== */
        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 12px;
        }

        .btn-kembali, .btn-cetak {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-kembali {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-kembali:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .btn-cetak {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-cetak:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        /* ==========================================================================
           RESPONSIVE & PRINT SYSTEM (FIXED PRINT NAVBAR ISSUE)
           ========================================================================== */
        @media(max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .content {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .info-item.full-width {
                grid-column: span 1;
            }
            .button-group {
                flex-direction: column;
            }
        }

        /* CSS KHUSUS PRINT - Menjamin Navbar & Sidebar Benar-Benar Hilang */
        @media print {
            header, nav, .sidebar, .navbar, .topbar, #navbar, .button-group, footer, .footer {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            body {
                background: #ffffff;
                color: #000000;
            }
            .container, .main-content, .content {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                background: none !important;
            }
            .invoice-card {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 10px !important;
            }
            .bill-card {
                border: 1px dashed #000000 !important;
                background: transparent !important;
            }
            .info-value, .total-amount, .code-text, .invoice-title h2 {
                color: #000000 !important;
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
            <div class="invoice-card">
                
                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div class="invoice-title">
                        <h2>Nota Transaksi</h2>
                        <p>Ringkasan detail pengerjaan laundry Anda</p>
                    </div>
                    <div class="invoice-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>

                <!-- Informasi Grid Model -->
                <div class="info-grid">
                    
                    <div class="info-item">
                        <span class="info-label"><i class="fa-solid fa-hashtag"></i> Kode Pesanan</span>
                        <div class="info-value code-text"><?= $data['kode_pesanan']; ?></div>
                    </div>

                    <div class="info-item">
                        <span class="info-label"><i class="fa-solid fa-circle-info"></i> Status</span>
                        <div>
                            <?php
                            $status = strtolower($data['status']);
                            $class = 'diterima';
                            if($status == 'diproses') { $class = 'diproses'; }
                            elseif($status == 'selesai') { $class = 'selesai'; }
                            elseif($status == 'diambil') { $class = 'diambil'; }
                            ?>
                            <span class="badge-status <?= $class; ?>">
                                <i class="fa-solid fa-circle-dot" style="font-size: 8px;"></i>
                                <?= $data['status']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="info-label"><i class="fa-solid fa-calendar-plus"></i> Tanggal Masuk</span>
                        <div class="info-value"><?= date('d M Y', strtotime($data['tanggal_masuk'])); ?></div>
                    </div>

                    <div class="info-item">
                        <span class="info-label"><i class="fa-solid fa-calendar-check"></i> Tanggal Selesai</span>
                        <div class="info-value">
                            <?php if(!empty($data['tanggal_selesai']) && $data['tanggal_selesai'] != '0000-00-00'): ?>
                                <span style="color: #2563eb; font-weight: 600;">
                                    <?= date('d M Y', strtotime($data['tanggal_selesai'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-weight: 400; font-style: italic;">Sedang diproses</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-item full-width">
                        <span class="info-label"><i class="fa-solid fa-bell-concierge"></i> Jenis Layanan</span>
                        <div class="info-value"><?= $data['nama_layanan']; ?></div>
                    </div>

                </div>

                <!-- Rincian Biaya -->
                <div class="bill-card">
                    <div class="bill-row">
                        <span>Harga per Kilo</span>
                        <strong>Rp <?= number_format($data['harga_perkg'],0,',','.'); ?></strong>
                    </div>
                    <div class="bill-row">
                        <span>Berat Cucian</span>
                        <strong><?= $data['berat']; ?> Kg</strong>
                    </div>
                    <div class="bill-row total-row">
                        <span style="font-weight: 600;">Total Tagihan</span>
                        <span class="total-amount">Rp <?= number_format($total,0,',','.'); ?></span>
                    </div>
                </div>

                <!-- Tombol Action -->
                <div class="button-group">
                    <a href="pesanan.php" class="btn-kembali">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <button onclick="window.print()" class="btn-cetak">
                        <i class="fa-solid fa-print"></i> Cetak Nota
                    </button>
                </div>

            </div>
        </div>

        <?php include '../includes/user/footer.php'; ?>

    </div>
</div>

</body>
</html>