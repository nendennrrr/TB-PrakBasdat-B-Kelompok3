<?php
session_start();
require '../config/koneksi.php';

$id = isset($_GET['id_layanan']) ? (int) $_GET['id_layanan'] : 0;

if ($id <= 0) {
    header("Location: layanan.php");
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id_layanan=$id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: layanan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Layanan | LaundryKu</title>
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; width: 100%; }
        
        .page-container {
            flex-grow: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 20px; 
        }

        .detail-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 600px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        /* Aksen dekoratif di atas card */
        .detail-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 8px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
        }

        .detail-header { text-align: center; margin-bottom: 40px; }
        .icon-lg { font-size: 48px; color: #3b82f6; margin-bottom: 20px; }
        .badge { background: #dbeafe; color: #1e40af; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .detail-header h2 { font-size: 28px; color: #0f172a; margin: 15px 0 5px 0; }

        .info-grid { display: grid; gap: 20px; }
        .info-item { 
            background: #f8fafc; 
            padding: 20px; 
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            gap: 20px;
            border: 1px solid #f1f5f9;
            transition: 0.3s;
        }
        .info-item:hover { border-color: #cbd5e1; }
        .info-icon { width: 45px; height: 45px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .info-text label { display: block; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .info-text span { font-size: 16px; font-weight: 600; color: #1e293b; }

        .price-text { font-size: 22px !important; color: #166534 !important; }

        .btn-wrapper { margin-top: 30px; text-align: center; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 30px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-back:hover { background: #e2e8f0; color: #1e293b; }
    </style>
</head>

<body>

    <?php include '../includes/admin/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/admin/navbar.php'; ?>
        <br>
        <br>
        <br>
        <div class="page-container">
            <div class="detail-card">
                <div class="detail-header">
                    <div class="icon-lg"><i class="fa-solid fa-circle-info"></i></div>
                    <span class="badge">Layanan #<?= $data['id_layanan']; ?></span>
                    <h2>Detail Layanan</h2>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-tag"></i></div>
                        <div class="info-text">
                            <label>Nama Layanan</label>
                            <span><?= htmlspecialchars($data['nama_layanan']); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                        <div class="info-text">
                            <label>Harga per Kg</label>
                            <span class="price-text">Rp <?= number_format($data['harga_perkg'],0,',','.'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="btn-wrapper">
                    <a href="layanan.php" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <?php include '../includes/admin/footer.php'; ?>
    </div>

</body>
</html>