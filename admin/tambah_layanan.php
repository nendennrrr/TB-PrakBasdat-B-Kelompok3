<?php
session_start();
require '../config/koneksi.php';

if (isset($_POST['simpan'])) {
    $nama_layanan = mysqli_real_escape_string($koneksi, $_POST['nama_layanan']);
    $harga_perkg = (int) $_POST['harga_perkg'];

    if ($nama_layanan == "" || $harga_perkg <= 0) {
        $pesan = "Data tidak boleh kosong atau tidak valid!";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO layanan (nama_layanan, harga_perkg) VALUES ('$nama_layanan', '$harga_perkg')");
        if ($insert) {
            $_SESSION['success'] = "Data berhasil ditambahkan!";
            header("Location: layanan.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Layanan Baru | LaundryKu</title>
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Reset & Layout Utama */
        body { 
            background-color: #f1f5f9;
            font-family: 'Segoe UI', sans-serif; 
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .main-content { 
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Memastikan konten di tengah */
        .page-container {
            flex-grow: 1;
            display: flex;
            align-items: center; /* Tengah vertikal */
            justify-content: center; /* Tengah horizontal */
            padding: 40px;
        }

        /* Card Form */
        .form-card {
            background: #ffffff;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 700px; /* Lebar form diperbesar */
        }

        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 28px; color: #1e293b; margin: 0; }
        .header-section p { color: #64748b; margin-top: 5px; }

        .input-group { margin-bottom: 25px; }
        .input-group label { display: block; font-weight: 600; margin-bottom: 10px; color: #334155; }
        
        .input-group input {
            width: 100%;
            padding: 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        
        .input-group input:focus { 
            border-color: #2563eb; 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1); 
        }

        .btn-wrapper { display: flex; gap: 15px; margin-top: 40px; }
        .btn {
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            font-size: 16px;
        }
        .btn-primary { background: #2563eb; color: white; flex-grow: 2; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #f1f5f9; color: #475569; flex-grow: 1; }
        .btn-secondary:hover { background: #e2e8f0; }
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

            <div class="form-card">
                <div class="header-section">
                    <h1>Tambah Layanan Baru</h1>
                    <p>Masukkan detail informasi layanan untuk pelanggan.</p>
                </div>

                <form method="POST">
                    <div class="input-group">
                        <label><i class="fa-solid fa-tag"></i> Nama Layanan</label>
                        <input type="text" name="nama_layanan" placeholder="Contoh: Cuci Setrika Express" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fa-solid fa-money-bill-wave"></i> Harga per Kg (Rp)</label>
                        <input type="number" name="harga_perkg" placeholder="Contoh: 10000" required>
                    </div>

                    <div class="btn-wrapper">
                        <a href="layanan.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" name="simpan" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php include '../includes/admin/footer.php'; ?>
    </div>

</body>
</html>