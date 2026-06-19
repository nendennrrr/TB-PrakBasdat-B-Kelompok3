<?php
session_start();
require '../config/koneksi.php';

$keyword = isset($_GET['keyword'])
    ? mysqli_real_escape_string($koneksi,$_GET['keyword'])
    : '';

$query = "
    SELECT *
    FROM users
    WHERE role='user'
";

if($keyword != ''){
    $query .= "
        AND (
            nama LIKE '%$keyword%'
            OR email LIKE '%$keyword%'
        )
    ";
}

$query .= "
    ORDER BY id DESC
";

$pelanggan = mysqli_query($koneksi,$query);

$total_pelanggan = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
        SELECT COUNT(*) total
        FROM users
        WHERE role='user'
    ")
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Management System</title>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        /* FIX: Memastikan main-content menjadi flex container utama */
        .main-content {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: 100%;
            overflow-x: hidden; /* Mencegah halaman meluber horizontal */
        }

        /* FIX: Mengubah .content menjadi penengah horizontal berbasis flex */
        .content {
            padding: 40px !important;
            display: flex !important;
            flex-direction: column;
            align-items: center; /* MEMBUAT KONTEN DI DALAMNYA PASTI DI TENGAH */
            width: 100% !important;
            max-width: none !important; /* Reset max-width luar */
            margin: 0 !important;       /* Reset margin luar */
            box-sizing: border-box;
            flex: 1;
        }

        /* PERBAIKAN UTAMA: Wrapper dalam yang membatasi lebar komponen agar seimbang di tengah */
        .content-inner-container {
            width: 100%;
            max-width: 1200px; /* Batas lebar konten Anda */
            display: flex;
            flex-direction: column;
            gap: 32px;
            box-sizing: border-box;
        }

        /* Alert Styling */
        .alert-success, .alert-error {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .alert-success { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .alert-error { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* Page Header Layout */
        .page-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
            letter-spacing: -0.5px;
        }
        .page-header p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        /* Enhanced Info Card Dashboard Style */
        .info-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 20px;
            width: fit-content;
            min-width: 320px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #2563eb;
            transition: width 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.05), 0 8px 12px -6px rgba(0, 0, 0, 0.03);
            border-color: #cbd5e1;
        }
        .info-card:hover::before {
            width: 6px;
        }
        .info-icon {
            width: 56px;
            height: 56px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .info-card h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.1;
        }
        .info-card span {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            display: block;
            margin-bottom: 4px;
        }
        .card-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            color: #16a34a;
            background: #f0fdf4;
            padding: 2px 8px;
            border-radius: 20px;
            margin-top: 6px;
        }

        /* Modern Table Container */
        .table-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            overflow: hidden;
            width: 100%;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 30px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
        }
        .table-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        /* Search Form Controls */
        .search-form {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 4px 10px;
            width: 320px;
            transition: all 0.2s ease;
        }
        .search-form:focus-within {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .search-form input {
            border: none;
            background: transparent;
            padding: 6px 8px;
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
            padding: 6px;
            display: flex;
            align-items: center;
        }

        /* Table Architecture */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        table th {
            background: #f8fafc;
            padding: 16px 30px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            border-bottom: 1px solid #e2e8f0;
        }
        table td {
            padding: 20px 30px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        table tbody tr {
            transition: all 0.2s ease;
        }
        table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* User Identity Cells */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .user-name-wrapper {
            display: flex;
            flex-direction: column;
        }
        .user-name {
            font-weight: 600;
            color: #0f172a;
        }
        .user-role-badge {
            font-size: 11px;
            color: #2563eb;
            background: #eff6ff;
            padding: 1px 6px;
            border-radius: 4px;
            width: fit-content;
            margin-top: 2px;
            font-weight: 500;
        }
        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            border: 1px solid #bfdbfe;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #16a34a;
            background-color: #f0fdf4;
            padding: 4px 10px;
            border-radius: 8px;
        }
        .status-dot {
            width: 6px;
            height: 6px;
            background-color: #16a34a;
            border-radius: 50%;
        }

        /* Action Group Utilities */
        .action-group {
            display: flex;
            gap: 8px;
        }
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }
        .btn-detail {
            background-color: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }
        .btn-detail:hover {
            background-color: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
        }
        .btn-delete {
            background-color: #fef2f2;
            color: #ef4444;
            border-color: #fee2e2;
        }
        .btn-delete:hover {
            background-color: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);
        }

        /* Custom Table Footer Meta */
        .table-footer-meta {
            padding: 20px 30px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #64748b;
        }

        /* Empty State */
        .empty-data {
            text-align: center;
            padding: 60px 0;
            color: #94a3b8;
        }
    </style>
    
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<div class="container">

    <?php include '../includes/admin/sidebar.php'; ?>

    <div class="main-content">

        <?php include '../includes/admin/navbar.php'; ?>
        <br>
        <br>
        <br>
        <div class="content">
            
            <div class="content-inner-container">

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <?= $_SESSION['success']; ?>
                    </div>
                <?php unset($_SESSION['success']); endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= $_SESSION['error']; ?>
                    </div>
                <?php unset($_SESSION['error']); endif; ?>

                <div class="page-header-container">
                    <div class="page-header">
                        <h1>Data Pelanggan</h1>
                        <p>Kelola seluruh hak akses, preferensi profil, dan riwayat pelanggan laundry.</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <span>Total Pelanggan Terdaftar</span>
                        <h2><?= $total_pelanggan['total']; ?> <span style="display:inline; font-size:16px; color:#64748b; font-weight:400;">User</span></h2>
                        <div class="card-badge">
                            <i class="fa-solid fa-arrow-trend-up" style="margin-right: 4px;"></i> Database Active
                        </div>
                    </div>
                </div>

                <div class="table-card">

                    <div class="table-header">
                        <h2>Daftar Basis Data</h2>
                        
                        <form method="GET" class="search-form">
                            <input
                                type="text"
                                name="keyword"
                                placeholder="Cari berdasarkan nama / email..."
                                value="<?= htmlspecialchars($keyword) ?>">
                            <button type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 12%;">ID Register</th>
                                    <th style="width: 28%;">Nama Lengkap</th>
                                    <th style="width: 26%;">Alamat Email</th>
                                    <th style="width: 18%;">Status Akun</th>
                                    <th style="width: 16%; text-align: center;">Manajemen Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(mysqli_num_rows($pelanggan) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($pelanggan)): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #64748b; font-size: 13px;">
                                        USR-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($row['nama'], 0, 1)); ?>
                                            </div>
                                            <div class="user-name-wrapper">
                                                <span class="user-name"><?= htmlspecialchars($row['nama']); ?></span>
                                                <span class="user-role-badge">Pelanggan</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #475569; font-weight: 500;">
                                        <?= htmlspecialchars($row['email']); ?>
                                    </td>
                                    <td>
                                        <div class="status-badge">
                                            <div class="status-dot"></div>
                                            Terverifikasi
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-group" style="justify-content: center;">
                                            <a href="detail_pelanggan.php?id=<?= $row['id']; ?>"
                                               class="btn-icon btn-detail"
                                               title="Lihat Detail">
                                                <i class="fa-solid fa-user-gear"></i>
                                            </a>
                                            <a href="hapus_pelanggan.php?id=<?= $row['id']; ?>"
                                               class="btn-icon btn-delete"
                                               title="Hapus Pelanggan"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini dari sistem?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-data">
                                            <i class="fa-solid fa-inbox" style="font-size: 36px; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                                            Tidak ada rekaman data pelanggan yang cocok dengan pencarian Anda.
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer-meta">
                        <div>
                            Menampilkan <b><?= mysqli_num_rows($pelanggan); ?></b> dari total pelanggan terfilter.
                        </div>
                        <div style="font-size: 12px; color: #94a3b8;">
                            <i class="fa-solid fa-clock-rotate-left"></i> Realtime Synchronized
                        </div>
                    </div>

                </div>

            </div> </div>

        <?php include '../includes/admin/footer.php'; ?>

    </div>
</div>

</body>
</html>