<?php
session_start();
require '../config/koneksi.php';

/* =========================
   AMBIL DATA PESANAN
========================= */
$pesanan = mysqli_query($koneksi, "
    SELECT p.*, u.nama AS nama_pelanggan, d.berat, l.nama_layanan, l.harga_perkg
    FROM pesanan p
    LEFT JOIN users u ON p.id_user = u.id
    LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l ON d.id_layanan = l.id_layanan
    ORDER BY p.id_pesanan DESC
");

$total_pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pesanan | LaundryKu</title>
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .content { padding: 30px; }
        
        /* Stats Card */
        .info-card { 
            background: #ffffff; padding: 25px; border-radius: 20px; 
            display: flex; align-items: center; gap: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-bottom: 30px; border: 1px solid #e2e8f0;
        }
        .info-icon { width: 55px; height: 55px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; border-radius: 16px; font-size: 24px; }

        /* Table Styling */
        .table-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 16px; color: #64748b; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
        td { padding: 18px 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        tr:hover { background-color: #f8fafc; }

        /* Badges */
        .status-badge { padding: 6px 14px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-diterima { background: #dbeafe; color: #1e40af; }
        .status-proses { background: #fef3c7; color: #92400e; }
        .status-selesai { background: #dcfce7; color: #166534; }
        .status-diambil { background: #ede9fe; color: #5b21b6; }

        /* Actions */
        .action-group { display: flex; gap: 8px; }
        .btn-detail, .btn-delete { padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 14px; }
        .btn-detail { background: #eff6ff; color: #2563eb; }
        .btn-delete { background: #fef2f2; color: #dc2626; }
    </style>
</head>

<body>
<div class="container">
    <?php include '../includes/admin/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/admin/navbar.php'; ?>

        <div class="content">
            <div class="page-header" style="margin-bottom: 30px;">
                <h1>Data Pesanan</h1>
                <p style="color: #64748b;">Kelola seluruh transaksi laundry Anda dengan mudah.</p>
            </div>

            <div class="info-card">
                <div class="info-icon"><i class="fa-solid fa-box-open"></i></div>
                <div>
                    <h2 style="margin: 0; font-size: 24px;"><?= $total_pesanan['total']; ?></h2>
                    <span style="color: #64748b; font-size: 14px;">Total Pesanan Terdaftar</span>
                </div>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Berat</th>
                            <th>Total Harga</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($pesanan) > 0): 
                            while($row = mysqli_fetch_assoc($pesanan)): 
                                $total = ($row['berat'] ?? 0) * ($row['harga_perkg'] ?? 0);
                        ?>
                        <tr>
                            <td><strong>#<?= $row['kode_pesanan']; ?></strong></td>
                            <td><?= $row['nama_pelanggan']; ?></td>
                            <td><?= $row['nama_layanan']; ?></td>
                            <td><?= $row['berat']; ?> Kg</td>
                            <td>Rp <?= number_format($total,0,',','.'); ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_masuk'])); ?></td>
                            <td><span class="status-badge status-<?= strtolower($row['status']); ?>"><?= $row['status']; ?></span></td>
                            <td>
                                <div class="action-group">
                                    <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn-detail"><i class="fa-solid fa-eye"></i></a>
                                    <a href="hapus_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus?')"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada data pesanan</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../includes/admin/footer.php'; ?>
    </div>
</div>
</body>
</html>