<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = (int)$_SESSION['id'];

$pesanan = mysqli_query($koneksi,"
    SELECT
        p.id_pesanan,
        p.kode_pesanan,
        p.tanggal_masuk,
        p.tanggal_selesai,
        p.status,
        d.berat,
        l.nama_layanan,
        l.harga_perkg
    FROM pesanan p
    LEFT JOIN detail_pesanan d
        ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l
        ON d.id_layanan = l.id_layanan
    WHERE p.id_user='$id_user'
    ORDER BY p.id_pesanan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - LaundryKu</title>

    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           MAIN CONTENT & PRESET SYSTEM WRAPPER
           ========================================================================== */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }

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
            padding: 40px;
            margin-top: 75px;
            flex: 1;
            box-sizing: border-box;
        }

        /* PREMIUM CONTENT ACCENT HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .page-header p {
            margin-top: 6px;
            color: #64748b;
            font-size: 14px;
            font-weight: 400;
        }

        /* HIGH-CONTRAST GLOWING PRIMARY BUTTON */
        .btn-tambah {
            background: linear-gradient(135deg, #4f46e5, #2563eb);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.15);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(79, 70, 229, 0.25);
            filter: brightness(1.05);
        }

        /* ==========================================================================
           THE CASING: COMPONENT PREMIUM CARD LIST DATA
           ========================================================================== */
        .table-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 30px rgba(15, 23, 42, 0.015);
            overflow: hidden;
        }

        .table-header {
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .table-header p {
            font-size: 13px;
            color: #94a3b8;
            margin: 4px 0 0 0;
        }

        /* RE-DESIGN MINIMALIST SEARCH BOX */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 320px;
        }

        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            transition: color 0.2s;
        }

        .search-box input {
            width: 100%;
            padding: 12px 18px 12px 48px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            color: #0f172a;
            background-color: #f8fafc;
            box-sizing: border-box;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-box input:focus {
            outline: none;
            border-color: #4f46e5;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
        }

        .search-box input:focus + i {
            color: #4f46e5;
        }

        /* RESPONSIVE SCROLLBAR CONTEXT WRAPPER */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        /* CUSTOM MODERN SLENDER SCROLLBAR */
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* MODERN MINIMAL TABLE DESIGN STRUCTURE */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        thead th {
            background: #f8fafc;
            padding: 18px 24px;
            color: #64748b;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            border-bottom: 1px solid #edf2f7;
        }

        tbody td {
            padding: 20px 24px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            transition: background-color 0.2s ease;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr.data-row:hover td {
            background-color: rgba(248, 250, 252, 0.8);
        }

        /* MONOSPACE CODE DISPLAY HOVER DESIGN */
        .kode-order {
            font-family: 'SF Mono', SFMono-Regular, Consolas, monospace;
            font-weight: 600;
            color: #3b82f6;
            background: #f0fdf4;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            letter-spacing: -0.2px;
            display: inline-block;
            background-color: #eff6ff;
            color: #1e40af;
        }

        /* ==========================================================================
           ULTRA REFINED SOFT-PASTEL BADGES STATUS SYSTEM
           ========================================================================== */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: -0.1px;
            white-space: nowrap;
        }

        .badge.diterima { background: #eff6ff; color: #1d4ed8; }
        .badge.diproses { background: #fff7ed; color: #ea580c; }
        .badge.dicuci { background: #ecfeff; color: #0891b2; }
        .badge.disetrika { background: #f5f3ff; color: #6d28d9; }
        .badge.siap-diambil { background: #fdf2ff; color: #a21caf; }
        .badge.selesai { background: #f0fdf4; color: #15803d; }
        .badge.batal { background: #fef2f2; color: #b91c1c; }

        /* MINIMALIST BUTTON OUTLINE TRANSACTIONS */
        .btn-detail {
            background: #ffffff;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .btn-detail:hover {
            background: #ffffff;
            color: #4f46e5;
            border-color: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.05);
            transform: translateY(-1px);
        }

        /* EMPTY STATES AESTHETIC ELEMENT */
        .empty {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
        }

        .empty i {
            font-size: 48px;
            color: #e2e8f0;
            margin-bottom: 20px;
        }
        
        .empty p {
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }

        /* ==========================================================================
           RESPONSIVE INTERACTIVE MEDIA QUERY BREAKPOINTS
           ========================================================================== */
        @media(max-width: 992px) {
            .sidebar { width: 80px !important; min-width: 80px !important; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .topbar { left: 80px; height: 65px; }
            .content { margin-top: 65px; padding: 25px; }
        }

        @media(max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }
            .btn-tambah {
                width: 100%;
            }
            .table-header {
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

            <div class="page-header">
                <div>
                    <h1>Pesanan Saya</h1>
                    <p>Pantau dan cek status laundry Anda secara real-time.</p>
                </div>
                <a href="buat_pesanan.php" class="btn-tambah">
                    <i class="fa-solid fa-plus"></i>
                    Buat Pesanan Baru
                </a>
            </div>

            <div class="table-card">
                
                <div class="table-header">
                    <div>
                        <h2>Daftar Riwayat Pesanan</h2>
                        <p>Total rekapitulasi data pesanan pakaian Anda</p>
                    </div>
                    <div class="search-box">
                        <input type="text" id="searchTable" placeholder="Cari kode order atau layanan...">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Order</th>
                                <th>Jenis Layanan</th>
                                <th>Berat</th>
                                <th>Harga / Kg</th>
                                <th>Total Transaksi</th>
                                <th>Tanggal Masuk</th>
                                <th>Status</th>
                                <th style="text-align: center;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($pesanan) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($pesanan)): ?>
                                    <?php
                                        $total = ($row['berat'] ?? 0) * ($row['harga_perkg'] ?? 0);
                                        $statusClass = strtolower($row['status']);
                                        $statusClass = str_replace(' ', '-', $statusClass);
                                    ?>
                                    <tr class="data-row">
                                        <td>
                                            <span class="kode-order"><?= htmlspecialchars($row['kode_pesanan']); ?></span>
                                        </td>
                                        <td style="font-weight: 600; color: #0f172a;">
                                            <?= htmlspecialchars($row['nama_layanan']); ?>
                                        </td>
                                        <td style="font-weight: 500;">
                                            <?= htmlspecialchars($row['berat']); ?> <span style="color: #94a3b8; font-size: 12px; font-weight: 400;">Kg</span>
                                        </td>
                                        <td style="color: #64748b;">
                                            Rp <?= number_format($row['harga_perkg'], 0, ',', '.'); ?>
                                        </td>
                                        <td style="font-weight: 700; color: #4f46e5;">
                                            Rp <?= number_format($total, 0, ',', '.'); ?>
                                        </td>
                                        <td style="color: #64748b; font-size: 13px;">
                                            <?= date('d M Y', strtotime($row['tanggal_masuk'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $statusClass; ?>">
                                                <?= htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="detail_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn-detail">
                                                <i class="fa-solid fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty">
                                            <i class="fa-solid fa-box-open"></i>
                                            <p>Belum ada riwayat pesanan terdaftar.</p>
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