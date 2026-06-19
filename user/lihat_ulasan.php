<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];

// Ambil data ulasan termasuk kolom tanggal_tanggapan
$ulasan = mysqli_query($koneksi,"
    SELECT
        u.id_ulasan,
        u.rating,
        u.komentar,
        u.tanggal_ulasan,
        u.tanggapan,
        u.tanggal_tanggapan,
        p.kode_pesanan,
        l.nama_layanan
    FROM ulasan u
    LEFT JOIN pesanan p
        ON u.id_pesanan = p.id_pesanan
    LEFT JOIN detail_pesanan d
        ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l
        ON d.id_layanan = l.id_layanan
    WHERE u.id_user='$id_user'
    ORDER BY u.tanggal_ulasan DESC
");

$totalUlasan = mysqli_num_rows($ulasan);

$totalDitanggapi = 0;
$totalRating = 0;
$dataUlasan = [];

while($row = mysqli_fetch_assoc($ulasan)){
    $dataUlasan[] = $row;
    $totalRating += $row['rating'];
    if(!empty($row['tanggapan'])){
        $totalDitanggapi++;
    }
}

$rataRating = $totalUlasan > 0 ? round($totalRating / $totalUlasan, 1) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Saya - LaundryKu</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">

    <style>
        /* ==========================================================================
           MAIN LAYOUT SYSTEM
           ========================================================================== */
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
            padding: 25px;
            margin-top: 75px;
            flex: 1;
        }

        /* HEADER BANNER */
        .header-banner {
            padding: 35px;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white;
            border-radius: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 35px rgba(37, 99, 235, 0.1);
        }

        .header-banner h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header-banner p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }

        /* STATS WIDGETS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.total { background: #eff6ff; color: #2563eb; }
        .stat-icon.reply { background: #f0fdf4; color: #16a34a; }
        .stat-icon.stars { background: #fff7ed; color: #ea580c; }

        .stat-info span {
            display: block;
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
        }

        .stat-info h2 {
            font-size: 24px;
            color: #0f172a;
            font-weight: 700;
            margin-top: 2px;
        }

        /* REVIEWS CONTAINER GRID */
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .card-review {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card-review:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .kode-order {
            font-size: 16px;
            font-weight: 700;
            color: #2563eb;
        }

        .nama-layanan {
            font-size: 14px;
            color: #64748b;
            margin-top: 3px;
            font-weight: 500;
        }

        .badge-score {
            background: #f1f5f9;
            color: #334155;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .stars-display {
            color: #f59e0b;
            font-size: 15px;
            margin-bottom: 18px;
            letter-spacing: 2px;
        }

        .komentar-text {
            color: #334155;
            font-size: 15px;
            line-height: 1.6;
            flex: 1;
            margin-bottom: 18px;
        }

        .footer-card {
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .timestamp {
            color: #94a3b8;
            font-size: 13px;
        }

        /* ACTIONS BUTTONS BLOCK */
        .action-row {
            display: flex;
            gap: 12px;
        }

        .btn-act {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, transform 0.1s;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-act:active {
            transform: scale(0.98);
        }

        .btn-act.edit { background: #eff6ff; color: #2563eb; }
        .btn-act.edit:hover { background: #dbeafe; }

        .btn-act.delete { background: #fef2f2; color: #dc2626; }
        .btn-act.delete:hover { background: #fee2e2; }

        .btn-act.reply-btn { 
            width: 100%; 
            margin-bottom: 12px; 
            background: #f0fdf4; 
            color: #16a34a; 
        }
        .btn-act.reply-btn:hover { background: #dcfce7; }

        .btn-act.reply-btn.belum { 
            background: #f1f5f9; 
            color: #64748b; 
        }
        .btn-act.reply-btn.belum:hover { background: #e2e8f0; }

        /* ==========================================================================
           MODAL DIALOG POPUP
           ========================================================================== */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active { opacity: 1; pointer-events: auto; }

        .modal-box {
            background: #ffffff;
            border-radius: 24px;
            width: 100%;
            max-width: 480px;
            padding: 30px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15);
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-box { transform: translateY(0); }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .close-modal {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: background 0.2s;
        }

        .close-modal:hover { background: #e2e8f0; color: #0f172a; }

        .modal-body {
            padding: 16px;
            border-radius: 12px;
            font-size: 15px;
            line-height: 1.6;
        }

        .modal-body.sudah-ditanggapi {
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #14532d;
        }

        .modal-body.belum-ditanggapi {
            background: #f8fafc;
            border-left: 4px solid #94a3b8;
            color: #475569;
            font-style: italic;
        }

        /* Style Baru Untuk Tanggal Balasan Di Dalam Modal */
        .modal-reply-date {
            display: block;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed rgba(22, 163, 74, 0.2);
            font-size: 12px;
            color: #16a34a;
            font-weight: 600;
        }

        /* EMPTY SCREEN */
        .empty-wrapper {
            background: white;
            border-radius: 24px;
            padding: 60px 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.02);
        }

        .empty-wrapper i { font-size: 50px; color: #cbd5e1; margin-bottom: 15px; }
        .empty-wrapper h2 { font-size: 20px; color: #0f172a; margin-bottom: 8px; }
        .empty-wrapper p { color: #64748b; font-size: 14px; max-width: 400px; margin: 0 auto 20px auto; line-height: 1.6; }
        
        .btn-redirect {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }

        /* RESPONSIVE MOBILE */
        @media(max-width: 768px){
            .main-content { margin-left: 0; width: 100%; }
            .content { padding: 15px; }
            .header-banner { padding: 25px; }
            .header-banner h1 { font-size: 22px; }
            .reviews-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

<div class="container">

    <?php require '../includes/user/sidebar.php'; ?>

    <div class="main-content">

        <?php require '../includes/user/navbar.php'; ?>
        <div class="content">

            <div class="header-banner">
                <h1>Ulasan Saya</h1>
                <p>Pantau semua riwayat ulasan, kritik, dan saran yang telah Anda berikan untuk performa laundry kami.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-icon total"><i class="fas fa-comment-dots"></i></div>
                    <div class="stat-info">
                        <span>Total Ulasan</span>
                        <h2><?= $totalUlasan ?></h2>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon reply"><i class="fas fa-reply"></i></div>
                    <div class="stat-info">
                        <span>Ditanggapi Admin</span>
                        <h2><?= $totalDitanggapi ?></h2>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon stars"><i class="fas fa-star"></i></div>
                    <div class="stat-info">
                        <span>Rata-rata Rating</span>
                        <h2><?= $rataRating ?> <span style="display:inline; font-size:14px; color:#cbd5e1;">/ 5</span></h2>
                    </div>
                </div>
            </div>

            <?php if($totalUlasan > 0): ?>

            <div class="reviews-grid">
                <?php foreach($dataUlasan as $row): ?>
                <div class="card-review">
                    
                    <div>
                        <div class="card-top">
                            <div>
                                <div class="kode-order">#<?= htmlspecialchars($row['kode_pesanan']); ?></div>
                                <div class="nama-layanan"><?= htmlspecialchars($row['nama_layanan']); ?></div>
                            </div>
                            <div class="badge-score">
                                <?= $row['rating']; ?> / 5
                            </div>
                        </div>

                        <div class="stars-display">
                            <?= str_repeat('★', $row['rating']); ?><?= str_repeat('☆', 5 - $row['rating']); ?>
                        </div>

                        <div class="komentar-text">
                            <?= nl2br(htmlspecialchars($row['komentar'])); ?>
                        </div>
                    </div>

                    <div>
                        <div class="footer-card">
                            <span class="timestamp">
                                <i class="far fa-clock"></i> <?= date('d M Y H:i', strtotime($row['tanggal_ulasan'])); ?>
                            </span>
                        </div>

                        <?php if(!empty($row['tanggapan'])): 
                            // Format waktu tanggapan dari database untuk dikirim ke JS modal
                            $waktu_balas = !empty($row['tanggal_tanggapan']) ? date('d M Y - H:i', strtotime($row['tanggal_tanggapan'])) . ' WIB' : '';
                        ?>
                            <button class="btn-act reply-btn" onclick="bukaTanggapan('<?= htmlspecialchars(rawurlencode($row['tanggapan'])); ?>', true, '<?= $waktu_balas ?>')">
                                <i class="fas fa-envelope-open-text"></i> Lihat Tanggapan
                            </button>
                        <?php else: ?>
                            <button class="btn-act reply-btn belum" onclick="bukaTanggapan('', false, '')">
                                <i class="fas fa-clock"></i> Belum Ditanggapi
                            </button>
                        <?php endif; ?>

                        <div class="action-row">
                            <a href="edit_ulasan.php?id=<?= $row['id_ulasan']; ?>" class="btn-act edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="hapus_ulasan.php?id=<?= $row['id_ulasan']; ?>" class="btn-act delete" onclick="return confirm('Apakah Anda yakin ingin menghapus permanen ulasan ini?')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </a>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>

            <div class="empty-wrapper">
                <i class="fas fa-folder-open"></i>
                <h2>Ulasan Masih Kosong</h2>
                <p>Anda belum pernah menuliskan ulasan untuk pesanan laundry Anda. Selesaikan cucian Anda lalu berikan bintang terbaik!</p>
                <a href="riwayat.php" class="btn-redirect">
                    <i class="fas fa-history"></i> Pergi ke Riwayat Pesanan
                </a>
            </div>

            <?php endif; ?>

        </div> 
        <?php require '../includes/user/footer.php'; ?>
    </div> 
</div> 

<div class="modal-overlay" id="tanggapanModal" onclick="tutupTanggapanLuar(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-comment-dots" style="color:#2563eb;"></i> Status Tanggapan</h3>
            <button class="close-modal" onclick="tutupTanggapan()">&times;</button>
        </div>
        <div class="modal-body" id="isiTanggapanAdmin">
        </div>
    </div>
</div>

<script>
    // Menambahkan parameter tglBalas ke dalam fungsi modal
    function bukaTanggapan(teksMentah, statusTanggapan, tglBalas) {
        const modalBody = document.getElementById('isiTanggapanAdmin');
        
        if (statusTanggapan) {
            const teksTanggapan = decodeURIComponent(teksMentah);
            modalBody.className = "modal-body sudah-ditanggapi";
            
            // Render text tanggapan
            let htmlContent = teksTanggapan.replace(/\n/g, "<br>");
            
            // Jika tanggal balasan ada, sisipkan info waktu di bagian bawah text ulasan
            if(tglBalas !== '') {
                htmlContent += `<span class="modal-reply-date"><i class="far fa-clock"></i> Dibalas pada: ${tglBalas}</span>`;
            }
            
            modalBody.innerHTML = htmlContent;
        } else {
            modalBody.className = "modal-body belum-ditanggapi";
            modalBody.innerHTML = "<i class='fas fa-info-circle'></i> Ulasan Anda belum ditanggapi oleh admin. Terima kasih telah memberikan penilaian!";
        }
        
        document.getElementById('tanggapanModal').classList.add('active');
    }

    function tutupTanggapan() {
        document.getElementById('tanggapanModal').classList.remove('active');
    }

    function tutupTanggapanLuar(event) {
        if (event.target.id === 'tanggapanModal') {
            tutupTanggapan();
        }
    }
</script>

</body>
</html>