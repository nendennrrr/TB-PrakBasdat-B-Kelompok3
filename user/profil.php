<?php
session_start();
require '../config/koneksi.php';

if(!isset($_SESSION['id']) || empty($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id'];

$query = mysqli_query($koneksi,"
    SELECT * FROM users
    WHERE id = $id_user
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);

if(!$data){
    die("Data user tidak ditemukan");
}

/* FOTO PATH CHECKING */
$fotoPath = "../assets/img/" . $data['foto'];
$foto = (!empty($data['foto']) && file_exists($fotoPath))
    ? $fotoPath
    : "https://ui-avatars.com/api/?name=" . urlencode($data['nama']) . "&background=2563eb&color=fff&bold=true";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - LaundryKu</title>

    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           MAIN CORE SYSTEM LAYOUT & FIXES
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
            max-width: 850px; /* Lebar optimal untuk layouting profil box tunggal */
            box-sizing: border-box;
        }

        /* ==========================================================================
           THE CASING: PROFILE CONTAINER CARD
           ========================================================================== */
        .profile-card {
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        /* BANNER HERO HEADER */
        .profile-hero {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            text-align: center;
            padding: 50px 20px;
            position: relative;
            overflow: hidden;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            top: -120px;
            right: -60px;
        }

        /* INTERACTIVE AVATAR HOLDER */
        .avatar-container {
            width: 140px;
            height: 140px;
            margin: 0 auto 15px auto;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.25);
            position: relative;
            z-index: 2;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .avatar-container:hover {
            transform: scale(1.04);
        }

        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-display-name {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin: 5px 0;
            letter-spacing: -0.5px;
        }

        .profile-display-role {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(4px);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* INPUT FILE INTERFACE BUTTON */
        .upload-trigger-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 15px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .upload-trigger-btn:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        input[type="file"] {
            display: none;
        }

        /* ==========================================================================
           PROFILE BIO BODY AND GRIDDING
           ========================================================================== */
        .profile-body {
            padding: 40px;
        }

        .section-headline {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .info-tile {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            transition: all 0.2s ease;
        }

        .info-tile:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        }

        .tile-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .tile-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            word-break: break-all;
        }

        .grid-span-all {
            grid-column: 1 / -1;
        }

        /* ==========================================================================
           ACTIONABLE NAVIGATION BUTTON ROW
           ========================================================================== */
        .action-container-row {
            margin-top: 35px;
            display: flex;
            gap: 12px;
            border-top: 1px solid #f1f5f9;
            padding-top: 25px;
        }

        .btn-action {
            flex: 1;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-action.blue-theme { background: #eff6ff; color: #2563eb; }
        .btn-action.blue-theme:hover { background: #dbeafe; }

        .btn-action.amber-theme { background: #fffbeb; color: #b45309; }
        .btn-action.amber-theme:hover { background: #fef3c7; }

        .btn-action.red-theme { background: #fef2f2; color: #dc2626; }
        .btn-action.red-theme:hover { background: #fee2e2; }

        /* ==========================================================================
           RESPONSIVE INTERACTION CONTROL
           ========================================================================== */
        @media(max-width:768px){
            .sidebar { width: 80px !important; min-width: 80px !important; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .topbar { left: 80px; height: 65px; }
            .content { margin-top: 65px; padding: 20px; }
            .profile-body { padding: 25px; }
            .info-grid { grid-template-columns: 1fr; }
            .action-container-row { flex-direction: column; }
            .btn-action { width: 100%; }
        }
    </style>

    <script>
        function autoUpload(){
            document.getElementById('formUpload').submit();
        }
    </script>
</head>

<body>

<div class="container">

    <?php require '../includes/user/sidebar.php'; ?>

    <div class="main-content">

        <?php require '../includes/user/navbar.php'; ?>

        <div class="content">
            <div class="content-wrapper">

                <div class="profile-card">
                    
                    <div class="profile-hero">
                        <div class="avatar-container">
                            <img src="<?= $foto ?>" alt="Avatar Pengguna">
                        </div>

                        <h2 class="profile-display-name">
                            <?= htmlspecialchars($data['nama']) ?>
                        </h2>

                        <span class="profile-display-role">
                            <?= htmlspecialchars($data['role']) ?>
                        </span>

                        <form id="formUpload" action="upload_foto.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="foto" id="file" accept="image/*" onchange="autoUpload()">
                            <label for="file" class="upload-trigger-btn">
                                <i class="fas fa-camera"></i> Ganti Foto Profil
                            </label>
                        </form>
                    </div>

                    <div class="profile-body">
                        
                        <div class="section-headline">
                            <i class="fas fa-user-shield text-primary"></i> Detail Informasi Akun
                        </div>

                        <div class="info-grid">
                            <div class="info-tile">
                                <div class="tile-label">Nama Lengkap</div>
                                <div class="tile-value"><?= htmlspecialchars($data['nama']) ?></div>
                            </div>

                            <div class="info-tile">
                                <div class="tile-label">Hak Akses / Role</div>
                                <div class="tile-value" style="text-transform: capitalize;"><?= htmlspecialchars($data['role']) ?></div>
                            </div>

                            <div class="info-tile grid-span-all">
                                <div class="tile-label">Alamat Surat Elektronik (Email)</div>
                                <div class="tile-value"><?= htmlspecialchars($data['email']) ?></div>
                            </div>

                            <div class="info-tile grid-span-all">
                                <div class="tile-label">Terdaftar Sejak</div>
                                <div class="tile-value">
                                    <i class="far fa-calendar-alt" style="margin-right: 4px; color: #64748b;"></i>
                                    <?= date('d M Y', strtotime($data['created_at'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="action-container-row">
                            <a href="edit_profile.php" class="btn-action blue-theme">
                                <i class="fas fa-user-edit"></i> Edit Profil
                            </a>
                            <a href="ubah_password.php" class="btn-action amber-theme">
                                <i class="fas fa-key"></i> Ubah Password
                            </a>
                            <a href="../auth/logout.php" class="btn-action red-theme" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                                <i class="fas fa-sign-out-alt"></i> Keluar Aplikasi
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <?php require '../includes/user/footer.php'; ?>

    </div>
</div>

</body>
</html>