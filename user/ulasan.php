<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id_pesanan <= 0){
    header("Location: riwayat.php");
    exit;
}

/* ==========================
   AMBIL DATA PESANAN
========================== */
$query = mysqli_query($koneksi,"
    SELECT
        p.*,
        l.nama_layanan
    FROM pesanan p
    LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN layanan l ON d.id_layanan = l.id_layanan
    WHERE p.id_pesanan='$id_pesanan'
    AND p.id_user='$id_user'
");

if(mysqli_num_rows($query) == 0){
    echo "
    <script>
        alert('Data pesanan tidak ditemukan');
        window.location='riwayat.php';
    </script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

/* ==========================
   SIMPAN ULASAN
========================== */
if(isset($_POST['kirim'])){
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $komentar = mysqli_real_escape_string($koneksi, trim($_POST['komentar']));

    if($rating < 1 || $rating > 5){
        echo "
        <script>
            alert('Silakan pilih rating terlebih dahulu');
        </script>";
    } else {
        $cek = mysqli_query($koneksi,"
            SELECT id_ulasan FROM ulasan WHERE id_pesanan='$id_pesanan' AND id_user='$id_user'
        ");

        if(mysqli_num_rows($cek) > 0){
            echo "
            <script>
                alert('Anda sudah memberikan ulasan');
                window.location='lihat_ulasan.php';
            </script>";
            exit;
        } else {
            $simpan = mysqli_query($koneksi,"
                INSERT INTO ulasan(id_pesanan, id_user, rating, komentar, tanggal_ulasan)
                VALUES('$id_pesanan', '$id_user', '$rating', '$komentar', NOW())
            ");

            if($simpan){
                echo "
                <script>
                    alert('Terima kasih atas ulasan Anda');
                    window.location='lihat_ulasan.php';
                </script>";
                exit;
            } else {
                die(mysqli_error($koneksi));
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Ulasan - LaundryKu</title>
<link rel="stylesheet" href="../assets/css/stylee.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f4f7fa;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #1e293b;
}

.container {
    display: flex;
    min-height: 100vh;
}

.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
}

.content-wrapper {
    flex: 1;
    max-width: 680px;
    width: 100%;
    margin: 40px auto;
    padding: 0 24px;
}

/* Navigasi Kembali - Lebih Minimalis */
.back {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 24px;
    transition: all 0.2s ease;
    gap: 8px;
}

.back:hover {
    color: #2563eb;
    transform: translateX(-4px);
}

/* Card Style - Elegan & Premium */
.card {
    background: #ffffff;
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02);
}

/* Header Gradient Smooth */
.header {
    padding: 36px 40px;
    background: linear-gradient(135deg, #1e40af, #2563eb);
    position: relative;
}

.header h2 {
    color: #ffffff;
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}

.header p {
    color: rgba(255, 255, 255, 0.85);
    font-size: 14px;
    line-height: 1.5;
    font-weight: 400;
}

.content {
    padding: 40px;
}

/* Kotak Detail Pesanan yang Bersih */
.order-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 32px;
}

.row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
}

.row:not(:last-child) {
    border-bottom: 1px solid #edf2f7;
}

.label {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}

.value {
    color: #0f172a;
    font-weight: 600;
    font-size: 15px;
}

.value.order-code {
    color: #2563eb;
    background: rgba(37, 99, 235, 0.06);
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 14px;
    letter-spacing: 0.5px;
}

/* Section Rating */
.rating-section {
    text-align: center;
    margin-bottom: 32px;
}

.rating-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 14px;
    color: #334155;
}

/* Efek Bintang Premium */
.star-group {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 12px;
}

.star-group input {
    display: none;
}

.star-group label {
    font-size: 40px;
    color: #e2e8f0;
    cursor:pointer;
    transition: all 0.2s ease-in-out;
}

/* Animasi Hover & State Checked */
.star-group label:hover,
.star-group label:hover ~ label {
    color: #ffb800;
    transform: scale(1.15);
}

.star-group input:checked ~ label {
    color: #ffb800;
}

/* Form Input Komentar */
.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 10px;
}

textarea {
    width: 100%;
    min-height: 150px;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    padding: 18px;
    resize: none;
    outline: none;
    font-size: 14px;
    font-family: inherit;
    line-height: 1.6;
    color: #0f172a;
    transition: all 0.2s ease;
    background: #ffffff;
}

textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

textarea::placeholder {
    color: #94a3b8;
}

/* Tombol Submit Modern */
.btn {
    width: 100%;
    height: 54px;
    border: none;
    border-radius: 16px;
    background: #2563eb;
    color: white;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 0.3px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn:hover {
    background: #1d4ed8;
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(1px);
}

/* Responsive Breakpoints */
@media(max-width: 768px) {
    .content-wrapper { margin: 20px auto; padding: 0 16px; }
    .content { padding: 28px 24px; }
    .header { padding: 28px 24px; }
    .header h2 { font-size: 22px; }
    .star-group label { font-size: 34px; }
}
</style>
</head>
<body>

<div class="container">
    <?php require '../includes/user/sidebar.php'; ?>

    <div class="main-content">
        <?php require '../includes/user/navbar.php'; ?>

        <div class="content-wrapper">
            <a href="ulasan.php" class="back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12" 19="5" 12="12" 5=""></polyline></svg>
                Kembali ke Ulasan
            </a>

            <div class="card">
                <div class="header">
                    <h2>Tambah Ulasan</h2>
                    <p>Bagikan pengalaman berharga Anda menggunakan layanan kami untuk membantu kami menjadi lebih baik.</p>
                </div>

                <div class="content">
                    <div class="order-box">
                        <div class="row">
                            <span class="label">Kode Pesanan</span>
                            <span class="value order-code">
                                #<?= htmlspecialchars($data['kode_pesanan'] ?? '') ?>
                            </span>
                        </div>
                        <div class="row">
                            <span class="label">Layanan Laundry</span>
                            <span class="value"><?= htmlspecialchars($data['nama_layanan'] ?? '') ?></span>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="rating-section">
                            <div class="rating-title">Berikan Nilai Kepuasan Anda</div>
                            <div class="star-group">
                                <input type="radio" name="rating" id="star5" value="5">
                                <label for="star5">★</label>

                                <input type="radio" name="rating" id="star4" value="4">
                                <label for="star4">★</label>

                                <input type="radio" name="rating" id="star3" value="3">
                                <label for="star3">★</label>

                                <input type="radio" name="rating" id="star2" value="2">
                                <label for="star2">★</label>

                                <input type="radio" name="rating" id="star1" value="1">
                                <label for="star1">★</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="komentar">Ulasan / Komentar Anda</label>
                            <textarea
                                id="komentar"
                                name="komentar"
                                placeholder="Ceritakan bagaimana kualitas cucian, keramahan kurir, atau ketepatan waktu kami..."
                                required></textarea>
                        </div>

                        <button type="submit" name="kirim" class="btn">
                            Kirim Ulasan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php require '../includes/user/footer.php'; ?>
    </div>
</div>

</body>
</html>