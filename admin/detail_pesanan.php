<?php
session_start();
require '../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(isset($_POST['update_status'])){
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $tgl_selesai = ($status == 'Selesai') ? date('Y-m-d') : null;
    $query = "UPDATE pesanan SET status='$status'" . ($tgl_selesai ? ", tanggal_selesai='$tgl_selesai'" : "") . " WHERE id_pesanan='$id'";
    if(mysqli_query($koneksi, $query)){
        header("Location: detail_pesanan.php?id=".$id);
        exit;
    }
}

$data = mysqli_query($koneksi,"SELECT p.*, u.nama AS nama_pelanggan, d.berat, l.nama_layanan, l.harga_perkg FROM pesanan p LEFT JOIN users u ON p.id_user = u.id LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan LEFT JOIN layanan l ON d.id_layanan = l.id_layanan WHERE p.id_pesanan='$id'");
$row = mysqli_fetch_assoc($data);
$total = ($row['berat'] ?? 0) * ($row['harga_perkg'] ?? 0);

// Logika warna badge dinamis berdasarkan status - Tema Biru Gradasi & Pastel
$status_colors = [
    'Diterima' => ['bg' => '#f1f5f9', 'text' => '#475569', 'icon' => 'fa-clipboard-list'],
    'Diproses' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'icon' => 'fa-spinner'],
    'Dicuci' => ['bg' => '#e0f2fe', 'text' => '#2563eb', 'icon' => 'fa-soap'],
    'Disetrika' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'icon' => 'fa-shirt'],
    'Siap Diambil' => ['bg' => '#ffedd5', 'text' => '#c2410c', 'icon' => 'fa-box-tissue'], 
    'Selesai' => ['bg' => '#dcfce7', 'text' => '#15803d', 'icon' => 'fa-circle-check']    
];
$current_status = $row['status'] ?? 'Diterima';
$badge_bg = $status_colors[$current_status]['bg'] ?? '#f1f5f9';
$badge_text = $status_colors[$current_status]['text'] ?? '#475569';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan Admin | LaundryKu</title>
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root { 
            --primary: #2563eb; 
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-main: #f0f4f8;
            --card-bg: #ffffff; 
            --dark: #1e3a8a; 
            --border: #dbeafe;
            --text-muted: #60a5fa;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-main); 
            margin: 0; 
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-main);
        }
        
        .content-wrapper { 
            flex: 1;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 40px 24px;
            box-sizing: border-box;
        }
        
        .card-container { 
            width: 100%; 
            max-width: 850px; 
            background: var(--card-bg); 
            border-radius: 24px; 
            padding: 40px; 
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.04);
            border: 1px solid var(--border);
        }

        .btn-back {
            color: #475569; 
            text-decoration: none; 
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .btn-back:hover { 
            color: var(--primary); 
            transform: translateX(-4px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--border);
            flex-wrap: wrap;
            gap: 16px;
        }

        .card-title {
            margin: 0; 
            font-size: 24px; 
            font-weight: 700; 
            letter-spacing: -0.5px;
            color: var(--dark);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Grid System */
        .grid-stats { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 20px; 
            margin-bottom: 30px; 
        }

        .stat-item { 
            padding: 20px; 
            border-radius: 16px; 
            background: #ffffff; 
            border: 1px solid #eff6ff; 
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
        }
        
        .stat-item:hover {
            border-color: var(--border);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.02);
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-content {
            flex: 1;
        }

        .stat-item label { 
            font-size: 11px; 
            color: #93c5fd; 
            text-transform: uppercase; 
            font-weight: 700; 
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }
        
        .stat-item h3 { 
            font-size: 16px; 
            margin: 0; 
            color: #1e293b; 
            font-weight: 600;
        }

        .price-detail {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }

        /* Panel Kontrol Manajemen Status */
        .control-panel { 
            background: #f8fafc; 
            padding: 20px; 
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            gap: 16px; 
            border: 1px solid #e2e8f0;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .select-wrapper {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        select { 
            width: 100%;
            padding: 14px 18px; 
            border-radius: 12px; 
            border: 1px solid #cbd5e1; 
            font-family: inherit;
            font-weight: 600; 
            color: #1e293b; 
            background-color: white;
            cursor: pointer; 
            appearance: none;
            -webkit-appearance: none;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
            font-size: 12px;
        }

        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-save { 
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white; 
            padding: 14px 28px; 
            border-radius: 12px; 
            border: none; 
            font-family: inherit;
            font-weight: 600; 
            font-size: 14px;
            cursor: pointer; 
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-save:hover { 
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .grid-stats { grid-template-columns: 1fr; }
            .card-container { padding: 24px; }
            .control-panel { flex-direction: column; align-items: stretch; }
            .btn-save { justify-content: center; }
        }

        /* SYSTEM RESET SAAT DI PRINT */
        @media print {
            header, nav, sidebar, .sidebar, navbar, .navbar, .topbar, footer, .footer, .btn-back, .control-panel {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .container, .main-content, .content-wrapper {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                background: none !important;
            }
            .card-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .stat-item {
                border: 1px solid #000000 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php include '../includes/admin/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/admin/navbar.php'; ?>
        <br>
        <br>
        <br>
        <div class="content-wrapper">
            <div class="card-container">
                <a href="pesanan.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
                </a>
                
                <div class="card-header">
                    <h1 class="card-title">
                        Transaksi #<?= $row['kode_pesanan'] ?? '-'; ?>
                    </h1>
                    <span class="status-badge" style="background: <?= $badge_bg ?>; color: <?= $badge_text ?>;">
                        <i class="fa-solid <?= $status_colors[$current_status]['icon'] ?? 'fa-circle' ?>"></i>
                        <?= $current_status ?>
                    </span>
                </div>
                
                <div class="grid-stats">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fa-solid fa-user"></i></div>
                        <div class="stat-content">
                            <label>Pelanggan</label>
                            <h3><?= $row['nama_pelanggan'] ?? '-'; ?></h3>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon"><i class="fa-solid fa-bell-concierge"></i></div>
                        <div class="stat-content">
                            <label>Layanan</label>
                            <h3><?= $row['nama_layanan'] ?? '-'; ?></h3>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div>
                        <div class="stat-content">
                            <label>Waktu Masuk</label>
                            <h3><?= isset($row['tanggal_masuk']) ? date('d M Y', strtotime($row['tanggal_masuk'])) : '-'; ?></h3>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                        <div class="stat-content">
                            <label>Tanggal Selesai</label>
                            <h3>
                                <?php if(!empty($row['tanggal_selesai']) && $row['tanggal_selesai'] != '0000-00-00'): ?>
                                    <span style="color: #15803d; font-weight: 600;">
                                        <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-weight: 400; font-style: italic;">Belum Selesai</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>

                    <div class="stat-item" style="grid-column: span 2; border-color: rgba(37, 99, 235, 0.15); background: #f8fafc;">
                        <div class="stat-icon" style="background: var(--primary); color: #ffffff;"><i class="fa-solid fa-wallet"></i></div>
                        <div class="stat-content">
                            <label style="color: #2563eb;">Total Tagihan</label>
                            <h3 style="color: #1d4ed8; font-size: 20px; font-weight: 700;">Rp <?= number_format($total, 0, ',', '.'); ?></h3>
                            <span class="price-detail">
                                Rincian: <?= $row['berat'] ?? 0; ?> kg × Rp <?= number_format($row['harga_perkg'] ?? 0, 0, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <form method="POST" class="control-panel">
                    <div class="select-wrapper">
                        <select name="status">
                            <?php foreach(['Diterima', 'Diproses', 'Selesai'] as $s): ?>
                                <option value="<?= $s ?>" <?= $current_status == $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn-save">
                        <i class="fa-solid fa-arrow-rotate-right"></i> Perbarui Status
                    </button>
                </form>
            </div>
        </div>

        <?php include '../includes/admin/footer.php'; ?>
    </div>
</div>

</body>
</html>