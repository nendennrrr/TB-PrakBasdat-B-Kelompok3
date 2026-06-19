<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id'];

$pesan = "";
$tipe = "";

$query = mysqli_query($koneksi,"
    SELECT *
    FROM users
    WHERE id = $id_user
    LIMIT 1
");

$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("User tidak ditemukan");
}

if(isset($_POST['simpan'])){

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    if(!password_verify($password_lama, $user['password'])){

        $pesan = "Password lama tidak sesuai!";
        $tipe = "error";

    }elseif(strlen($password_baru) < 6){

        $pesan = "Password baru minimal 6 karakter!";
        $tipe = "error";

    }elseif($password_baru != $konfirmasi){

        $pesan = "Konfirmasi password tidak cocok!";
        $tipe = "error";

    }else{

        $hashBaru = password_hash(
            $password_baru,
            PASSWORD_DEFAULT
        );

        $update = mysqli_query($koneksi,"
            UPDATE users
            SET password='$hashBaru'
            WHERE id=$id_user
        ");

        if($update){

            $pesan = "Password berhasil diubah.";
            $tipe = "success";

        }else{

            $pesan = "Gagal mengubah password.";
            $tipe = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - LaundryKu</title>

    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           MAIN LAYOUT CORE CONFIGURATIONS
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
            max-width: 700px; /* Diperketat agar form input terfokus & elegan */
            box-sizing: border-box;
        }

        /* ==========================================================================
           THE CASING: MAIN STRUCTURE CARD
           ========================================================================== */
        .card {
            background: #ffffff;
            border-radius: 24px;   /* Kebulatan sudut bingkai luar */
            overflow: hidden;      /* KUNCI: Memotong ujung tajam background gradient */
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        /* HEADER ACCENT BLOCK */
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
            letter-spacing: -0.5px;
        }

        .card-header h1 i {
            font-size: 22px !important;
        }

        .card-header p {
            margin-top: 6px;
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 14px;
            line-height: 1.5;
        }

        /* CONTAINER BODY FORM */
        .card-body {
            padding: 40px;
        }

        /* FEEDBACK ALERTS CONTAINER SYSTEM */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-sizing: border-box;
        }

        .alert.success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* READ-ONLY METADATA BOX */
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 25px;
        }

        .info-box span {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-box strong {
            font-size: 15px;
            color: #0f172a;
            font-weight: 700;
        }

        /* INPUT CONFIGURATION ELEMENT */
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

        /* DYNAMIC BUTTON SYSTEMS */
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
           RESPONSIVE INTERACTIVE BREAKPOINTS
           ========================================================================== */
        @media(max-width:768px){
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
                            <i class="fa-solid fa-key"></i>
                            Ubah Password Akun
                        </h1>
                        <p>Gunakan kombinasi password yang kuat untuk menjaga privasi keamanan akun Anda.</p>
                    </div>

                    <div class="card-body">

                        <?php if(!empty($pesan)): ?>
                            <div class="alert <?= $tipe ?>">
                                <?php if($tipe == 'success'): ?>
                                    <i class="fa-solid fa-circle-check"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                <?php endif; ?>
                                <?= $pesan ?>
                            </div>
                        <?php endif; ?>

                        <div class="info-box">
                            <span>Akun Pengguna</span>
                            <strong><?= htmlspecialchars($user['nama']) ?></strong>
                        </div>

                        <form method="POST">

                            <div class="form-group">
                                <label for="password_lama">
                                    <i class="fa-solid fa-lock"></i>
                                    Password Lama
                                </label>
                                <input
                                    type="password"
                                    id="password_lama"
                                    name="password_lama"
                                    placeholder="Masukkan password lama Anda"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="password_baru">
                                    <i class="fa-solid fa-key"></i>
                                    Password Baru
                                </label>
                                <input
                                    type="password"
                                    id="password_baru"
                                    name="password_baru"
                                    placeholder="Minimal 6 karakter"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="konfirmasi_password">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    Konfirmasi Password Baru
                                </label>
                                <input
                                    type="password"
                                    id="konfirmasi_password"
                                    name="konfirmasi_password"
                                    placeholder="Ulangi masukan password baru"
                                    required>
                            </div>

                            <div class="actions">
                                <a href="profil.php" class="btn btn-back">
                                    <i class="fa-solid fa-arrow-left"></i> Batal
                                </a>
                                <button type="submit" name="simpan" class="btn btn-save">
                                    <i class="fa-solid fa-floppy-disk"></i> Simpan Password
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