<?php
session_start();
require '../config/koneksi.php';

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';
$query = "SELECT * FROM layanan";
if($keyword != ''){
    $query .= " WHERE nama_layanan LIKE '%$keyword%'";
}
$query .= " ORDER BY id_layanan DESC";
$layanan = mysqli_query($koneksi, $query);

$data_stats = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total, AVG(harga_perkg) as avg_harga FROM layanan"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Layanan | LaundryKu</title>
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* 1. Global Reset */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; }

        /* 2. Container Layout */
        .container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }

        /* 3. PENGATURAN RUANG AMAN (FIX JUDUL TENGGELAM) */
        .content { 
            padding: 20px 32px; 
            max-width: 1200px; 
            width: 100%; 
            margin: 0 auto; 
            /* Menambah margin-top untuk memberi ruang bagi Navbar */
            margin-top: 70px; 
        }

        /* 4. Page Header */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .title h1 { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
        .title p { color: #64748b; margin: 4px 0 0 0; }

        /* 5. Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card-stat { background: white; padding: 22px; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
        
        /* 6. Table Style */
        .table-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 22px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 14px; border-bottom: 2px solid #f1f5f9; color: #64748b; }
        td { padding: 14px; border-bottom: 1px solid #f1f5f9; }
        
        .btn-action { padding: 8px 12px; border-radius: 8px; text-decoration: none; margin-right: 5px; }
    </style>
</head>

<body>
<div class="container">
    <?php include '../includes/admin/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/admin/navbar.php'; ?>

        <div class="content">
            <div class="page-header">
                <div class="title">
                    <h1>Data Layanan</h1>
                    <p>Kelola daftar paket dan harga laundry secara efisien.</p>
                </div>
                <a href="tambah_layanan.php" style="background:#2563eb; color:white; padding:12px 20px; border-radius:10px; text-decoration:none; font-weight:600;">
                    <i class="fa-solid fa-plus"></i> Tambah Layanan
                </a>
            </div>

            <div class="stats-grid">
                <div class="card-stat">
                    <div style="background:#dbeafe; color:#2563eb; padding:15px; border-radius:12px;"><i class="fa-solid fa-box-open"></i></div>
                    <div>
                        <div style="font-size: 20px; font-weight: bold;"><?= $data_stats['total'] ?></div>
                        <div style="font-size: 12px; color: #64748b;">Total Layanan</div>
                    </div>
                </div>
                <div class="card-stat">
                    <div style="background:#fef3c7; color:#d97706; padding:15px; border-radius:12px;"><i class="fa-solid fa-wallet"></i></div>
                    <div>
                        <div style="font-size: 20px; font-weight: bold;">Rp <?= number_format($data_stats['avg_harga'], 0) ?></div>
                        <div style="font-size: 12px; color: #64748b;">Harga Rata-rata / Kg</div>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h2 style="font-size: 18px; margin: 0;">Daftar Katalog</h2>
                    <form method="GET" style="display:flex;">
                        <input type="text" name="keyword" placeholder="Cari layanan..." value="<?= $keyword ?>" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px 0 0 8px; outline:none;">
                        <button type="submit" style="padding: 10px 16px; background:#2563eb; border:none; color:white; border-radius:0 8px 8px 0; cursor:pointer;"><i class="fa-solid fa-search"></i></button>
                    </form>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Layanan</th>
                            <th>Harga per Kg</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($row = mysqli_fetch_assoc($layanan)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><span style="background:#eff6ff; color:#2563eb; padding:5px 10px; border-radius:6px; font-weight:600; font-size: 13px;"><?= $row['nama_layanan']; ?></span></td>
                            <td style="font-weight:600;">Rp <?= number_format($row['harga_perkg'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="detail_layanan.php?id_layanan=<?= $row['id_layanan']; ?>" class="btn-action" style="background:#f1f5f9; color:#475569;"><i class="fa-solid fa-eye"></i></a>
                                <a href="edit_layanan.php?id_layanan=<?= $row['id_layanan']; ?>" class="btn-action" style="background:#eff6ff; color:#2563eb;"><i class="fa-solid fa-pen"></i></a>
                                <a href="hapus_layanan.php?id_layanan=<?= $row['id_layanan']; ?>" class="btn-action" style="background:#fef2f2; color:#ef4444;" onclick="return confirm('Hapus data?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../includes/admin/footer.php'; ?>
    </div>
</div>
</body>
</html>