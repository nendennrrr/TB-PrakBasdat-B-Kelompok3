<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id'];

$query = mysqli_query($koneksi, "
    SELECT *
    FROM users
    WHERE id = $id_user
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data user tidak ditemukan");
}

if (isset($_POST['simpan'])) {
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    $update = mysqli_query($koneksi, "
        UPDATE users
        SET
            nama='$nama',
            email='$email'
        WHERE id=$id_user
    ");

    if ($update) {
        header("Location: profil.php?update=success");
        exit;
    } else {
        $error = "Gagal memperbarui profil!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - LaundryKu</title>

    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           MAIN CORE SYSTEM LAYOUT & WRAPPER
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

        .content-wrapper {
            width: 100%;
            max-width: 700px; /* Lebar optimal untuk fokus pengisian form */
            box-sizing: border-box;
        }

        /* ==========================================================================
           THE CASING: FORM CONTAINER CARD
           ========================================================================== */
        .card {
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        /* HEADER BLOCK */
        .card-header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            padding: 40px;
            color: #ffffff;
        }

        .card-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .card-header h1 i {
            font-size: 22px !important;
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 14px;
        }

        /* BODY BLOCK */
        .card-body {
            padding: 40px;
        }

        /* ALERT MESSAGES */
        .alert {
            background: #fef2f2;
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* METADATA READONLY BOX */
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .info-box span {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-box strong {
            font-size: 15px;
            color: #0f172a;
            font-weight: 700;
        }

        /* INPUT FIELD COMPONENTS */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }

        .form-group label i {
            font-size: 14px !important;
            width: 20px;
            color: #2563eb;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            font-size: 15px;
            color: #0f172a;
            background-color: #ffffff;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* BUTTON SYSTEM CONTROLS */
        .actions {
            margin-top: 35px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #f1f5f9;
            padding-top: 25px;
        }

        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 14px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-sizing: border-box;
        }

        .btn i {
            font-size: 14px !important;
        }

        .btn-save {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .btn-save:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* ==========================================================================
           RESPONSIVE STRUCTURAL OVERRIDES
           ========================================================================== */
        @media(max-width: 768px) {
            .sidebar { width: 80px !important; min-width: 80px !important; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .topbar { left: 80px; height: 65px; }
            .content { margin-top: 65px; padding: 20px; }
            .card-header, .card-body { padding: 25px; }
            .actions { flex-direction: column-reverse; width: 100%; }
            .btn { width: 100%; }
            .card-header h1 { font-size: 20px; }
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

                <div class="card">
                    
                    <div class="card-header">
                        <h1>
                            <i class="fa-solid fa-user-gear"></i>
                            Edit Profil Akun
                        </h1>
                        <p>Perbarui informasi internal identitas publik akun Anda dengan aman.</p>
                    </div>

                    <div class="card-body">

                        <?php if (isset($error)): ?>
                            <div class="alert">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <div class="info-box">
                            <span>ID Pengguna</span>
                            <strong>#<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                        </div>

                        <form method="POST">

                            <div class="form-group">
                                <label for="nama">
                                    <i class="fa-solid fa-user"></i>
                                    Nama Lengkap
                                </label>
                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    value="<?= htmlspecialchars($data['nama']) ?>"
                                    placeholder="Masukkan nama lengkap Anda"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <i class="fa-solid fa-envelope"></i>
                                    Alamat Email
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($data['email']) ?>"
                                    placeholder="nama@domain.com"
                                    required>
                            </div>

                            <div class="actions">
                                <a href="profil.php" class="btn btn-back">
                                    <i class="fa-solid fa-arrow-left"></i> Batal
                                </a>
                                <button type="submit" name="simpan" class="btn btn-save">
                                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>

        <?php require '../includes/user/footer.php'; ?>

    </div>

</div>

</body>
</html>