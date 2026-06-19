<?php
session_start();
require '../config/koneksi.php';

// Proteksi halaman admin
if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

/* ==========================================================================
   PROSES SIMPAN / UPDATE TANGGAPAN ADMIN (JIKA FORM DISUBMIT)
   ========================================================================== */
if(isset($_POST['kirim_balasan'])){
    $id_ulasan = (int)$_POST['id_ulasan'];
    $tanggapan = mysqli_real_escape_string($koneksi, trim($_POST['tanggapan']));

    // Update kolom tanggapan DAN tanggal_tanggapan secara real-time (NOW())
    $update = mysqli_query($koneksi, "
        UPDATE ulasan 
        SET tanggapan = '$tanggapan',
            tanggal_tanggapan = NOW()
        WHERE id_ulasan = '$id_ulasan'
    ");

    if($update){
        echo "
        <script>
            alert('Tanggapan ulasan berhasil disimpan!');
            window.location='ulasan.php';
        </script>";
        exit;
    } else {
        die(mysqli_error($koneksi));
    }
}

/* ==========================================================================
   AMBIL SEMUA DATA ULASAN DARI PELANGGAN
   ========================================================================== */
$query_ulasan = mysqli_query($koneksi, "
    SELECT 
        u.*, 
        p.kode_pesanan,
        usr.nama AS nama_pelanggan,
        l.nama_layanan
    FROM ulasan u
    LEFT JOIN pesanan p ON u.id_pesanan = p.id_pesanan
    LEFT JOIN users usr ON u.id_user = usr.id
    LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l ON d.id_layanan = l.id_layanan
    ORDER BY u.tanggal_ulasan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ulasan Pelanggan | LaundryKu</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { 
            --primary: #2563eb; 
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --bg-main: #f8fafc;
            --card-bg: #ffffff; 
            --dark: #0f172a; 
            --border: #e2e8f0;
            --text-muted: #64748b;
            --star-color: #fbbf24;
            --success-bg: #dcfce7;
            --success-text: #15803d;
            --pending-bg: #f1f5f9;
            --pending-text: #475569;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-main); 
            margin: 0; 
            color: var(--dark);
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
            padding: 40px 32px;
            box-sizing: border-box;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            margin: 0 0 6px 0; 
            font-size: 28px; 
            font-weight: 700; 
            letter-spacing: -0.03em;
            color: var(--dark);
        }

        .page-subtitle {
            margin: 0;
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Container Card Modern */
        .reviews-feed {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .review-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.01);
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .review-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.04);
        }

        .card-header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 16px;
        }

        .customer-profile {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .customer-avatar {
            width: 48px;
            height: 48px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
        }

        .customer-name {
            font-weight: 700;
            color: var(--dark);
            display: block;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .order-code {
            font-size: 11px;
            color: var(--primary);
            font-weight: 700;
            background-color: var(--primary-light);
            padding: 3px 10px;
            border-radius: 20px;
        }

        .service-date-info {
            text-align: right;
        }

        @media (max-width: 576px) {
            .service-date-info { text-align: left; }
        }

        .service-name {
            font-weight: 600; 
            color: var(--dark); 
            display: block; 
            margin-bottom: 4px;
            font-size: 14px;
        }

        .review-date {
            font-size: 12px; 
            color: var(--text-muted); 
            display: block;
        }

        .card-body-content {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stars {
            color: var(--star-color);
            font-size: 16px;
            letter-spacing: 2px;
        }

        .review-text {
            color: #334155;
            font-weight: 400;
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            font-style: italic;
        }

        /* Box Tanggapan Balasan Admin */
        .admin-reply-content {
            margin-top: 8px;
            padding: 16px 20px;
            background-color: var(--bg-main);
            border-left: 4px solid var(--primary);
            border-radius: 4px 12px 12px 4px;
        }

        .admin-reply-content strong {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            text-transform: uppercase;
            color: var(--primary);
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .admin-reply-content p {
            margin: 0;
            color: #475569;
            font-size: 14.5px;
            line-height: 1.5;
        }

        /* Tambahan Style untuk Waktu Balasan */
        .reply-date {
            display: block;
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .card-footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.replied {
            background-color: var(--success-bg);
            color: var(--success-text);
        }

        .status-badge.pending {
            background-color: var(--pending-bg);
            color: var(--pending-text);
        }

        .reply-box {
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            margin-top: 10px;
            display: none;
            animation: slideDown 0.25s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reply-box textarea {
            width: 100%;
            min-height: 100px;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s ease;
            margin-bottom: 12px;
            color: var(--dark);
            background-color: #ffffff;
        }

        .reply-box textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        .btn-action {
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .btn-action:hover {
            border-color: var(--primary);
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .btn-submit-reply {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-submit-reply:hover {
            background-color: var(--primary-hover);
        }
        
        .btn-cancel {
            background: none;
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            padding: 0 12px;
        }
        
        .btn-cancel:hover {
            color: var(--dark);
        }

        .empty-state {
            text-align: center; 
            color: var(--text-muted); 
            padding: 80px 24px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>

<div class="container">
    <?php require '../includes/admin/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require '../includes/admin/navbar.php'; ?>
         <br>
        <br>
        <br>
        <div class="content-wrapper">
            <div class="page-header">
                <h1 class="page-title">Ulasan Pelanggan</h1>
                <p class="page-subtitle">Pantau tingkat kepuasan pelanggan dan berikan respons langsung terhadap masukan mereka.</p>
            </div>

            <div class="reviews-feed">
                <?php if(mysqli_num_rows($query_ulasan) == 0): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-comment-slash" style="font-size: 40px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
                        Belum ada ulasan yang masuk dari pelanggan.
                    </div>
                <?php endif; ?>

                <?php while($row = mysqli_fetch_assoc($query_ulasan)): ?>
                    <div class="review-card">
                        
                        <div class="card-header-info">
                            <div class="customer-profile">
                                <div class="customer-avatar">
                                    <?= strtoupper(substr(htmlspecialchars($row['nama_pelanggan']), 0, 1)) ?>
                                </div>
                                <div>
                                    <span class="customer-name"><?= htmlspecialchars($row['nama_pelanggan']) ?></span>
                                    <span class="order-code">#<?= htmlspecialchars($row['kode_pesanan']) ?></span>
                                </div>
                            </div>
                            <div class="service-date-info">
                                <span class="service-name"><?= htmlspecialchars($row['nama_layanan'] ?? 'Layanan Umum') ?></span>
                                <span class="review-date">
                                    <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>
                                    <?= date('d M Y', strtotime($row['tanggal_ulasan'])) ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body-content">
                            <div class="stars">
                                <?php 
                                $rating = (int)$row['rating'];
                                for($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <p class="review-text">"<?= htmlspecialchars($row['komentar']) ?>"</p>

                            <?php if(!empty($row['tanggapan'])): ?>
                                <div class="admin-reply-content">
                                    <strong><i class="fa-solid fa-reply fa-flip-horizontal"></i> Tanggapan Resmi Admin</strong> 
                                    <p><?= htmlspecialchars($row['tanggapan']) ?></p>
                                    
                                    <?php if(!empty($row['tanggal_tanggapan'])): ?>
                                        <span class="reply-date">
                                            <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                                            Dibalas pada: <?= date('d M Y - H:i', strtotime($row['tanggal_tanggapan'])) ?> WIB
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="reply-box" id="box-reply-<?= $row['id_ulasan'] ?>">
                                <form method="POST">
                                    <input type="hidden" name="id_ulasan" value="<?= $row['id_ulasan'] ?>">
                                    <textarea name="tanggapan" placeholder="Tulis ucapan terima kasih atau jawaban atas masukan pelanggan..." required><?= htmlspecialchars($row['tanggapan'] ?? '') ?></textarea>
                                    <div style="text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
                                        <button type="button" class="btn-cancel" onclick="toggleReplyForm(<?= $row['id_ulasan'] ?>)">Batal</button>
                                        <button type="submit" name="kirim_balasan" class="btn-submit-reply">Simpan Tanggapan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card-footer-actions">
                            <div>
                                <?php if(!empty($row['tanggapan'])): ?>
                                    <span class="status-badge replied">
                                        <i class="fa-solid fa-circle-check"></i> Sudah Ditanggapi
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge pending">
                                        <i class="fa-regular fa-clock"></i> Belum Ditanggapi
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <button class="btn-action" onclick="toggleReplyForm(<?= $row['id_ulasan'] ?>)">
                                <i class="fa-solid <?= empty($row['tanggapan']) ? 'fa-reply' : 'fa-pen-to-square' ?>"></i>
                                <?= empty($row['tanggapan']) ? 'Balas' : 'Edit Tanggapan' ?>
                            </button>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
            
        </div>

        <?php require '../includes/admin/footer.php'; ?>
    </div>
</div>

<script>
function toggleReplyForm(id) {
    var formBox = document.getElementById('box-reply-' + id);
    if (formBox.style.display === 'none' || formBox.style.display === '') {
        formBox.style.display = 'block';
        formBox.querySelector('textarea').focus();
    } else {
        formBox.style.display = 'none';
    }
}
</script>

</body>
</html>