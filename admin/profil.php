<?php
session_start();
require '../config/koneksi.php';

// Proteksi halaman admin
if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

// Ambil data admin yang sedang login
$id_user = $_SESSION['id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id_user'");
$user = mysqli_fetch_assoc($query);

// Proses Update Profil
$sukses = false;
$error = false;
$pesan = "";

if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password_baru = $_POST['password_baru'];
    
    // Validasi data dasar
    if (empty($nama) || empty($email)) {
        $error = true;
        $pesan = "Nama dan Email tidak boleh kosong.";
    } else {
        $sql_update = "UPDATE users SET nama = '$nama', email = '$email'";
        
        // Cek jika ada file foto yang diunggah
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === 0) {
            $nama_file = $_FILES['foto_profil']['name'];
            $ukuran_file = $_FILES['foto_profil']['size'];
            $tmp_name = $_FILES['foto_profil']['tmp_name'];
            
            // Ekstensi yang diperbolehkan
            $ekstensi_valid = ['jpg', 'jpeg', 'png'];
            $ekstensi_file = explode('.', $nama_file);
            $ekstensi_file = strtolower(end($ekstensi_file));
            
            if (!in_array($ekstensi_file, $ekstensi_valid)) {
                $error = true;
                $pesan = "Format gambar harus JPG, JPEG, atau PNG.";
            } elseif ($ukuran_file > 2000000) { // Maksimal 2MB
                $error = true;
                $pesan = "Ukuran gambar terlalu besar! Maksimal 2MB.";
            } else {
                // Generate nama file unik baru
                $nama_file_baru = uniqid() . '.' . $ekstensi_file;
                
                // Pastikan folder assets/img/ target sudah dibuat
                $target_dir = '../assets/img/';
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                // Hapus foto lama jika ada di server
                if (!empty($user['foto']) && file_exists($target_dir . $user['foto'])) {
                    unlink($target_dir . $user['foto']);
                }
                
                // Pindahkan file baru
                move_uploaded_file($tmp_name, $target_dir . $nama_file_baru);
                $sql_update .= ", foto = '$nama_file_baru'";
                $_SESSION['foto'] = $nama_file_baru;
            }
        }
        
        // Jika password baru diisi, enkripsi password baru
        if (!empty($password_baru) && !$error) {
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql_update .= ", password = '$password_hashed'";
        }
        
        $sql_update .= " WHERE id = '$id_user'";
        
        if (!$error) {
            if (mysqli_query($koneksi, $sql_update)) {
                $_SESSION['nama'] = $nama;
                $sukses = true;
                $pesan = "Profil Anda berhasil diperbarui!";
                
                // Ambil data terbaru dari database
                $query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id_user'");
                $user = mysqli_fetch_assoc($query);
            } else {
                $error = true;
                $pesan = "Gagal memperbarui database.";
            }
        }
    }
}

// Pengondisian Gambar Profil (Foto Upload vs Inisial Nama)
$foto_path = "../assets/img/" . ($user['foto'] ?? '');
if (!empty($user['foto']) && file_exists($foto_path)) {
    $avatar_url = $foto_path;
} else {
    // Gunakan inisial in-house premium dari Dicebear
    $avatar_url = "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($user['nama'] ?? 'Admin') . "&backgroundColor=2563eb,0f172a,3b82f6&fontSize=45&bold=true";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | LaundryKu Admin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/stylee.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { 
            --primary: #0f172a; 
            --accent: #2563eb;
            --bg-main: #f8fafc;
            --card-bg: #ffffff; 
            --dark: #0f172a; 
            --border: #e2e8f0;
            --text-muted: #64748b;
            --success: #16a34a;
            --danger: #dc2626;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-main); 
            margin: 0; 
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
        }

        .container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; display: flex; flex-direction: column; background-color: var(--bg-main); }
        .content-wrapper { flex: 1; padding: 32px 40px; box-sizing: border-box; }

        /* Header */
        .page-header { margin-bottom: 28px; }
        .page-title { margin: 0 0 4px 0; font-size: 24px; font-weight: 700; letter-spacing: -0.02em; }
        .page-subtitle { margin: 0; font-size: 14px; color: var(--text-muted); }

        /* Layout Grid */
        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
            align-items: start;
        }

        /* Card */
        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        /* Avatar Section */
        .avatar-section {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        .avatar-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #f1f5f9;
            box-shadow: 0 8px 16px -4px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            background: #e2e8f0;
        }
        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Tombol Upload Cantik */
        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .btn-upload-custom {
            border: 1px solid var(--border);
            color: var(--dark);
            background-color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-upload-custom:hover {
            background-color: #f8fafc;
            border-color: var(--text-muted);
        }
        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .role-badge {
            background: #eff6ff;
            color: var(--accent);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid rgba(37, 99, 235, 0.1);
        }

        /* Form Kanan */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .form-group-full {
            grid-column: span 2;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 14px;
            font-family: inherit;
            font-size: 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--dark);
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--accent);
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-save:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #dcfce7; color: var(--success); border: 1px solid rgba(22, 163, 74, 0.1); }
        .alert-danger { background: #fee2e2; color: var(--danger); border: 1px solid rgba(220, 38, 38, 0.1); }

        @media (max-width: 768px) {
            .profile-layout { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .form-group-full { grid-column: span 1; }
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
                <h1 class="page-title">Pengaturan Profil</h1>
                <p class="page-subtitle">Kelola informasi pribadi, unggah foto resmi, dan amankan akun Anda.</p>
            </div>

            <?php if($sukses): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i> <?= $pesan ?>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="profile-layout">
                    
                    <div class="profile-card avatar-section">
                        <div class="avatar-wrapper">
                            <img id="avatar-preview" src="<?= $avatar_url ?>" alt="Avatar">
                        </div>
                        
                        <div class="upload-btn-wrapper">
                            <button type="button" class="btn-upload-custom">
                                <i class="fa-solid fa-camera"></i> Ganti Foto
                            </button>
                            <input type="file" name="foto_profil" id="foto_profil" accept="image/*">
                        </div>

                        <h3 id="text-nama-display" style="margin: 4px 0 0 0; font-size: 16px; font-weight: 700;"><?= htmlspecialchars($user['nama'] ?? 'Administrator') ?></h3>
                        <span class="role-badge">Super Admin</span>
                    </div>

                    <div class="profile-card">
                        <div class="form-grid">
                            
                            <div class="form-group form-group-full">
                                <label for="nama">Nama Lengkap Admin</label>
                                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" required>
                            </div>

                            <div class="form-group form-group-full">
                                <label for="email">Alamat Email Aktif</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>

                            <div class="form-group form-group-full" style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                                <label for="password_baru">Kata Sandi Baru <span style="font-weight: normal; color: var(--text-muted);">(Biarkan kosong jika tidak ingin diganti)</span></label>
                                <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="••••••••">
                            </div>

                        </div>

                        <button type="submit" name="update_profil" class="btn-save">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>
            </form>

        </div>

        <?php require '../includes/admin/footer.php'; ?>
    </div>
</div>

<script>
    const namaInput = document.getElementById('nama');
    const avatarPreview = document.getElementById('avatar-preview');
    const textNamaDisplay = document.getElementById('text-nama-display');
    const fileInput = document.getElementById('foto_profil');
    
    // Status awal apakah user sudah punya foto upload asli
    const memilikiFotoAsli = <?= (!empty($user['foto']) && file_exists($foto_path)) ? 'true' : 'false' ?>;

    // 1. Sinkronisasi perubahan nama ke teks display & generator inisial (jika tidak pakai foto asli)
    namaInput.addEventListener('input', function() {
        const namaValue = this.value.trim() || 'Admin';
        textNamaDisplay.textContent = this.value.trim() || 'Administrator';
        
        if (!memilikiFotoAsli && fileInput.files.length === 0) {
            avatarPreview.src = `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(namaValue)}&backgroundColor=2563eb,0f172a,3b82f6&fontSize=45&bold=true`;
        }
    });

    // 2. Fitur Live Preview saat user memilih file gambar dari komputer/HP
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>