<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];
$id_ulasan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id_ulasan <= 0){
    header("Location: lihat_ulasan.php");
    exit;
}

/* ==========================================================================
   AMBIL DATA ULASAN
   ========================================================================== */
$query = mysqli_query($koneksi,"
    SELECT 
        u.*,
        p.kode_pesanan,
        l.nama_layanan
    FROM ulasan u
    JOIN pesanan p ON u.id_pesanan = p.id_pesanan
    LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l ON d.id_layanan = l.id_layanan
    WHERE u.id_ulasan='$id_ulasan'
    AND u.id_user='$id_user'
");

if(mysqli_num_rows($query) == 0){
    echo "
    <script>
        alert('Data ulasan tidak ditemukan');
        window.location='lihat_ulasan.php';
    </script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

/* ==========================================================================
   UPDATE ULASAN
   ========================================================================== */
if(isset($_POST['update'])){
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $komentar = mysqli_real_escape_string($koneksi, trim($_POST['komentar']));

    if($rating < 1 || $rating > 5){
        echo "<script>alert('Rating harus 1 - 5');</script>";
    } else {
        $update = mysqli_query($koneksi,"
            UPDATE ulasan SET
                rating='$rating',
                komentar='$komentar',
                tanggal_ulasan=NOW()
            WHERE id_ulasan='$id_ulasan'
            AND id_user='$id_user'
        ");

        if($update){
            echo "
            <script>
                alert('Ulasan berhasil diperbarui');
                window.location='lihat_ulasan.php';
            </script>";
            exit;
        } else {
            die(mysqli_error($koneksi));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ulasan - LaundryKu</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">

    <style>
        /* ==========================================================================
           MAIN LAYOUT INTEGRATION (Konsisten dengan Beri Ulasan)
           ========================================================================== */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 280px);
            box-sizing: border-box;
        }

        .content {
            padding: 25px;
            margin-top: 75px;
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .content-wrapper {
            width: 100%;
            max-width: 680px; 
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* Tombol Kembali */
        .back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #2563eb;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: color 0.3s;
        }

        .back:hover {
            color: #1e3a8a;
        }

        /* Card Wrapper */
        .card-review {
            background: #ffffff;
            border-radius: 25px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
        }

        /* Header Card Gradien Biru Dongker Senada */
        .header-review {
            padding: 35px;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white;
        }

        .header-review h2 {
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .header-review p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .body-review {
            padding: 35px;
        }

        /* Informasi Detail Pesanan */
        .order-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .row-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
        }

        .row-info:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
        }

        .row-info .label {
            color: #64748b;
            font-size: 14px;
        }

        .row-info .value {
            color: #0f172a;
            font-weight: 700;
        }

        .row-info .kode-pesanan {
            color: #2563eb;
        }

        /* ==========================================================================
           STAR RATING INTERACTIVE SYSTEM (Warna Jingga Amber)
           ========================================================================== */
        .rating-title {
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 15px;
        }

        .star-group {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .star-group input {
            display: none;
        }

        .star-group label {
            font-size: 46px;
            color: #cbd5e1;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        /* Efek Hover & Seleksi Sibling */
        .star-group label:hover,
        .star-group label:hover ~ label {
            color: #f59e0b; 
            transform: scale(1.1);
        }

        /* Menampilkan Bintang yang Sudah Tersimpan di Database */
        .star-group input:checked ~ label {
            color: #f59e0b;
        }

        /* Form Textarea */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
        }

        textarea {
            width: 100%;
            min-height: 130px;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 18px;
            resize: none;
            outline: none;
            font-size: 14px;
            font-family: inherit;
            line-height: 1.6;
            color: #0f172a;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }

        textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        /* Tombol Update */
        .btn-submit {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
            transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        /* ==========================================================================
           RESPONSIVE MOBILE BREAKPOINTS
           ========================================================================== */
        @media(max-width: 768px){
            .sidebar {
                width: 80px;
                padding: 15px 10px;
            }

            .main-content {
                margin-left: 80px;
                width: calc(100% - 80px);
            }

            .topbar {
                left: 80px;
                height: 65px;
                padding: 0 15px;
            }

            .content {
                margin-top: 65px;
                padding: 15px;
            }

            .content-wrapper {
                padding: 5px;
            }

            .header-review {
                padding: 25px;
            }

            .header-review h2 {
                font-size: 22px;
            }

            .body-review {
                padding: 25px;
            }

            .star-group label {
                font-size: 38px;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <?php require '../includes/user/sidebar.php'; ?>

    <div class="main-content">

        <?php require '../includes/user/navbar.php'; ?>

        <div class="content">
            <div class="content-wrapper">

                <a href="lihat_ulasan.php" class="back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Ulasan
                </a>

                <div class="card-review">
                    <div class="header-review">
                        <h2>Edit Ulasan</h2>
                        <p>Perbarui penilaian dan komentar Anda mengenai layanan kami.</p>
                    </div>

                    <div class="body-review">
                        
                        <div class="order-box">
                            <div class="row-info">
                                <span class="label">Kode Pesanan</span>
                                <span class="value kode-pesanan">
                                    #<?= htmlspecialchars($data['kode_pesanan']) ?>
                                </span>
                            </div>
                            <div class="row-info">
                                <span class="label">Layanan Laundry</span>
                                <span class="value">
                                    <?= htmlspecialchars($data['nama_layanan']) ?>
                                </span>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="rating-title">Perbarui Kualitas Penilaian</div>
                            
                            <div class="star-group">
                                <?php for($i=5; $i>=1; $i--): ?>
                                    <input 
                                        type="radio" 
                                        name="rating" 
                                        id="star<?= $i ?>" 
                                        value="<?= $i ?>"
                                        <?= ($data['rating'] == $i) ? 'checked' : '' ?>
                                    >
                                    <label for="star<?= $i ?>">★</label>
                                <?php endfor; ?>
                            </div>

                            <div class="form-group">
                                <label for="komentar">Ulasan / Kritik & Saran</label>
                                <textarea
                                    id="komentar"
                                    name="komentar"
                                    required><?= htmlspecialchars($data['komentar']) ?></textarea>
                            </div>

                            <button type="submit" name="update" class="btn-submit">
                                Perbarui Ulasan Layanan
                            </button>
                        </form>

                    </div> </div> </div> </div> <?php require '../includes/user/footer.php'; ?>

    </div> </div> </body>
</html>