<?php
include '../config/koneksi.php';

if(isset($_POST['register'])){

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Menggunakan query standar sesuai kode awal Anda, namun dibersihkan variabelnya
    $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', 'user')";
    
    if(mysqli_query($koneksi, $query)){
        echo "
        <script>
        alert('Registrasi Berhasil! Silakan login.');
        window.location='login.php';
        </script>
        ";
    } else {
        echo "
        <script>
        alert('Registrasi Gagal, silakan coba lagi.');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi LaundryKu</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           RESET & BASE STYLE (Menyesuaikan dengan Landing Page Dark Premium)
           ========================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #0b1329; /* Menyamakan dengan --bg-main landing page */
            /* Efek pendaran neon halus di latar belakang */
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 85% 85%, rgba(168, 85, 247, 0.08) 0%, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #e2e8f0;
        }

        /* ==========================================================================
           AUTH CARD CONTAINER (Menggunakan Gaya Frosted Glass Gelap)
           ========================================================================== */
        .auth-card {
            background: rgba(15, 23, 42, 0.65); /* Menyamakan dengan --card-bg landing page */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            width: 100%;
            max-width: 440px;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08); /* Menyamakan dengan --border landing page */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        /* ==========================================================================
           HEADER SECTION
           ========================================================================== */
        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-header .logo-icon {
            width: 56px;
            height: 56px;
            background: rgba(59, 130, 246, 0.12); /* Menyamakan dengan --primary-light */
            color: #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            border-radius: 14px;
            margin: 0 auto 16px auto;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .auth-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #f8fafc; /* Warna teks utama terang */
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .auth-header p {
            font-size: 14px;
            color: #94a3b8; /* Menyamakan dengan --text-muted */
            font-weight: 500;
        }

        /* ==========================================================================
           FORM INPUT GROUPS
           ========================================================================== */
        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        /* Wrapper penempatan ikon di dalam input */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i.prefix-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 16px 12px 46px; /* Ruang untuk ikon di sebelah kiri */
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            font-size: 14px;
            color: #f8fafc;
            background-color: rgba(7, 13, 30, 0.4); /* Input sedikit lebih gelap */
            outline: none;
            transition: all 0.2s ease;
        }

        /* State ketika input aktif/fokus */
        .input-wrapper input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            background-color: rgba(7, 13, 30, 0.6);
        }

        .input-wrapper input:focus ~ i.prefix-icon {
            color: #60a5fa;
        }

        /* Tombol Sembunyi/Lihat Password */
        .toggle-password {
            position: absolute;
            right: 16px;
            color: #64748b;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #94a3b8;
        }

        /* ==========================================================================
           BUTTON & FOOTER STYLE
           ========================================================================== */
        .btn-auth {
            width: 100%;
            background: linear-gradient(135deg, #60a5fa, #2563eb); /* Gradient khas landing page */
            color: #ffffff;
            border: none;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
            filter: brightness(1.05);
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }

        .auth-footer a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
            transition: color 0.2s;
        }

        .auth-footer a:hover {
            text-decoration: underline;
            color: #3b82f6;
        }

        /* Responsive Breakpoint Mobile */
        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <div class="auth-header">
        <div class="logo-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2>Registrasi Akun</h2>
        <p>Mulai kelola laundry Anda bersama kami</p>
    </div>

    <form method="POST" action="">

        <div class="input-group">
            <label>Nama Lengkap</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user prefix-icon"></i>
                <input 
                    type="text" 
                    name="nama" 
                    placeholder="Masukkan nama lengkap Anda" 
                    required>
            </div>
        </div>

        <div class="input-group">
            <label>Email</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-envelope prefix-icon"></i>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Contoh: nama@gmail.com" 
                    required>
            </div>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock prefix-icon"></i>
                <input 
                    type="password" 
                    name="password" 
                    id="passwordField"
                    placeholder="Buat password minimal 6 karakter" 
                    required>
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div>
        </div>

        <button type="submit" name="register" class="btn-auth">
            Daftar Sekarang
        </button>

    </form>

    <div class="auth-footer">
        Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
    </div>

</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#passwordField');

    togglePassword.addEventListener('click', function () {
        // Switch tipe input tipe data
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Ganti visual font awesome icon saat diklik
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>