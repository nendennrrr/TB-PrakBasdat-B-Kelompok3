<?php
session_start();
require '../config/koneksi.php';

/* =========================
   CEK ID
========================= */
$id = isset($_GET['id_layanan']) ? (int) $_GET['id_layanan'] : 0;

if ($id <= 0) {
    header("Location: layanan.php");
    exit;
}

/* =========================
   AMBIL DATA LAMA
========================= */
$query = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id_layanan=$id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    $_SESSION['error'] = "Data layanan tidak ditemukan!";
    header("Location: layanan.php");
    exit;
}

$pesan = "";

/* =========================
   PROSES UPDATE
========================= */
if (isset($_POST['update'])) {
    $nama_layanan = mysqli_real_escape_string($koneksi, $_POST['nama_layanan']);
    $harga_perkg = (int) $_POST['harga_perkg'];

    if ($nama_layanan == "" || $harga_perkg <= 0) {
        $pesan = "Data tidak boleh kosong atau tidak valid!";
    } else {
        $update = mysqli_query($koneksi, "
            UPDATE layanan 
            SET nama_layanan='$nama_layanan',
                harga_perkg='$harga_perkg'
            WHERE id_layanan=$id
        ");

        if ($update) {
            $_SESSION['success'] = "Data layanan berhasil diupdate!";
            header("Location: layanan.php");
            exit;
        } else {
            $pesan = "Gagal mengupdate data!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan | LaundryKu</title>
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; width: 100%; }
        
        .page-container {
            flex-grow: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 60px 40px;
        }

        .form-card {
            background: #ffffff;
            padding: 50px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 750px;
        }

        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 32px; color: #1e293b; margin: 0; }
        .header-section p { color: #64748b; margin-top: 10px; font-size: 16px; }

        .input-group { margin-bottom: 30px; }
        .input-group label { display: block; font-weight: 600; margin-bottom: 12px; color: #334155; }
        
        .input-group input {
            width: 100%;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        
        .input-group input:focus { 
            border-color: #2563eb; 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(37,99,235,0.15); 
        }

        .btn-wrapper { display: flex; gap: 20px; margin-top: 50px; }
        .btn {
            padding: 18px 30px;
            border-radius: 16px;
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

        .alert { background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #fecaca; }
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
                    <h1>Edit Layanan</h1>
                    <p>Ubah informasi layanan yang sudah ada.</p>
                </div>

                <?php if($pesan != ""): ?>
                    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?= $pesan; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label><i class="fa-solid fa-tag"></i> Nama Layanan</label>
                        <input type="text" name="nama_layanan" value="<?= htmlspecialchars($data['nama_layanan']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fa-solid fa-money-bill-wave"></i> Harga per Kg (Rp)</label>
                        <input type="number" name="harga_perkg" value="<?= $data['harga_perkg']; ?>" required>
                    </div>

                    <div class="btn-wrapper">
                        <a href="layanan.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" name="update" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php include '../includes/admin/footer.php'; ?>
    </div>

</body>
</html>