<?php
session_start();
require '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];

// Ambil Data Layanan untuk Dropdown dan parsing ke JS nanti
$layanan_query = mysqli_query($koneksi, "SELECT * FROM layanan ORDER BY nama_layanan ASC");
$arr_layanan = [];
while ($row = mysqli_fetch_assoc($layanan_query)) {
    $arr_layanan[] = $row;
}

if (isset($_POST['simpan'])) {
    $id_layanan = mysqli_real_escape_string($koneksi, $_POST['id_layanan']);
    $berat      = mysqli_real_escape_string($koneksi, $_POST['berat']);

    // 1. AMBIL HARGA PER KG DARI DATABASE BERDASARKAN LAYANAN YANG DIPILIH
    $query_harga = mysqli_query($koneksi, "SELECT harga_perkg FROM layanan WHERE id_layanan = '$id_layanan'");
    $data_harga  = mysqli_fetch_assoc($query_harga);
    $harga_perkg = $data_harga['harga_perkg'] ?? 0;

    // 2. HITUNG TOTAL HARGA DI SISI PHP (BERAT X HARGA PER KG)
    $total_harga = $harga_perkg * $berat;

    $kode_pesanan = 'PSN' . date('ymd') . rand(100, 999);
    $tanggal_masuk = date('Y-m-d');
    $status = "Diterima";

    // Insert ke tabel utama pesanan
    $query_pesanan = "INSERT INTO pesanan (kode_pesanan, id_user, tanggal_masuk, status) 
                      VALUES ('$kode_pesanan', '$id_user', '$tanggal_masuk', '$status')";
    mysqli_query($koneksi, $query_pesanan);

    // Ambil ID pesanan yang baru saja terbuat
    $id_pesanan = mysqli_insert_id($koneksi);

    // 3. SEKARANG MEMASUKKAN KOLOM total_harga KE DALAM QUERY INSERT DETAIL_PESANAN
    $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_layanan, berat, total_harga) 
                     VALUES ('$id_pesanan', '$id_layanan', '$berat', '$total_harga')";
    mysqli_query($koneksi, $query_detail);

    echo "
    <script>
        alert('Pesanan berhasil dibuat dengan total Rp " . number_format($total_harga, 0, ',', '.') . "');
        window.location='riwayat.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - LaundryKu</title>

    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           MAIN CORE SYSTEM LAYOUT
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
            padding: 30px;
            margin-top: 75px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .page-wrapper {
            width: 100%;
            max-width: 700px;
            box-sizing: border-box;
        }

        /* ==========================================================================
           ELEGANT FORM COMPONENT
           ========================================================================== */
        .form-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        .form-header {
            margin-bottom: 35px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 20px;
        }

        .form-header h1 {
            font-size: 26px;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .form-header p {
            color: #64748b;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            outline: none;
            font-size: 15px;
            color: #0f172a;
            background-color: #fff;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            background-size: 16px;
            padding-right: 45px;
        }

        /* ESTIMATION LIVE BILLING BOX */
        .estimation-box {
            background: #f0fdf4;
            border: 1px dashed #16a34a;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .est-left span {
            font-size: 12px;
            color: #16a34a;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }

        .est-left p {
            margin: 0;
            font-size: 14px;
            color: #14532d;
        }

        .estimation-box h2 {
            margin: 0;
            font-size: 24px;
            color: #16a34a;
            font-weight: 700;
        }

        /* BUTTON SYSTEM GROUP */
        .button-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #f1f5f9;
            padding-top: 25px;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            box-sizing: border-box;
        }

        .btn-submit {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .btn-kembali {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-kembali:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* ==========================================================================
           RESPONSIVE STRUCTURAL OVERRIDES
           ========================================================================== */
        @media(max-width: 768px){
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .content { padding: 15px; }
            .form-card { padding: 25px; border-radius: 20px; }
            .button-group { flex-direction: column-reverse; width: 100%; }
            .btn { width: 100%; }
            .estimation-box { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>

<body>

<div class="container">

    <?php include '../includes/user/sidebar.php'; ?>

    <div class="main-content">

        <?php include '../includes/user/navbar.php'; ?>

        <div class="content">
            <div class="page-wrapper">

                <div class="form-card">
                    <div class="form-header">
                        <h1>Buat Pesanan Laundry</h1>
                        <p>Silakan tentukan jenis layanan dan beban berat pakaian Anda di bawah ini.</p>
                    </div>

                    <form method="POST" id="formOrder">
                        
                        <div class="form-group">
                            <label for="id_layanan">Pilih Paket Layanan</label>
                            <select 
                                name="id_layanan" 
                                id="id_layanan" 
                                class="form-control" 
                                onchange="hitungEstimasi()" 
                                required>
                                <option value="" data-harga="0">-- Pilih Layanan Laundry --</option>
                                <?php foreach($arr_layanan as $l): ?>
                                    <option value="<?= $l['id_layanan']; ?>" data-harga="<?= $l['harga_perkg']; ?>">
                                        <?= htmlspecialchars($l['nama_layanan']); ?> — (Rp <?= number_format($l['harga_perkg'], 0, ',', '.'); ?>/Kg)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="berat">Estimasi Berat Pakaian (Kg)</label>
                            <input 
                                type="number" 
                                id="berat"
                                step="0.1" 
                                min="0.5" 
                                name="berat" 
                                class="form-control" 
                                placeholder="Contoh: 3.5"
                                oninput="hitungEstimasi()" 
                                required>
                        </div>

                        <div class="estimation-box">
                            <div class="est-left">
                                <span>Estimasi Total</span>
                                <p id="est-detail">Silakan pilih layanan dan isi berat pakaian</p>
                            </div>
                            <h2 id="total-harga">Rp 0</h2>
                        </div>

                        <div class="button-group">
                            <a href="riwayat.php" class="btn btn-kembali">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" name="simpan" class="btn btn-submit">
                                <i class="fas fa-paper-plane"></i> Kirim Pesanan
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>

        <?php include '../includes/user/footer.php'; ?>

    </div>
</div>

<script>
function hitungEstimasi() {
    const selectLayanan = document.getElementById('id_layanan');
    const inputBerat = document.getElementById('berat');
    const textDetail = document.getElementById('est-detail');
    const textTotal = document.getElementById('total-harga');

    const hargaPerKg = parseFloat(selectLayanan.options[selectLayanan.selectedIndex].getAttribute('data-harga')) || 0;
    const berat = parseFloat(inputBerat.value) || 0;

    if (hargaPerKg > 0 && berat > 0) {
        const total = hargaPerKg * berat;
        
        const formatRupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(total);

        textDetail.innerHTML = `Perhitungan: ${berat} Kg &times; Rp ${hargaPerKg.toLocaleString('id-ID')}`;
        textTotal.innerText = formatRupiah;
    } else {
        textDetail.innerText = "Silakan pilih layanan dan isi berat pakaian";
        textTotal.innerText = "Rp 0";
    }
}
</script>

</body>
</html>