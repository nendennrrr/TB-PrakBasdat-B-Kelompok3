<?php
// Hubungkan ke database
require 'config/koneksi.php';

// 1. Ambil jumlah Pelanggan Aktif
$query_pelanggan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$total_pelanggan = $data_pelanggan['total'] ?? 0;

// 2. Ambil jumlah Pesanan Selesai
$query_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status='Selesai'");
$data_selesai = mysqli_fetch_assoc($query_selesai);
$total_pesanan_selesai = $data_selesai['total'] ?? 0;

// 3. Ambil jumlah Varian Layanan
$query_layanan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM layanan");
$data_layanan = mysqli_fetch_assoc($query_layanan);
$total_layanan = $data_layanan['total'] ?? 0;

// 4. Ambil Rata-rata Rating & Total Ulasan dari Database
$query_rating = mysqli_query($koneksi, "SELECT AVG(rating) AS rata_rata, COUNT(*) AS total_ulasan FROM ulasan");
$data_rating = mysqli_fetch_assoc($query_rating);
$rata_rata_rating = round($data_rating['rata_rata'] ?? 0, 1);
$total_ulasan = $data_rating['total_ulasan'] ?? 0;

// Set nilai kepuasan pelanggan secara dinamis
$kepuasan_pelanggan = $total_ulasan > 0 ? round(($rata_rata_rating / 5) * 100) : 99;

// 5. Ambil Semua Ulasan dari database untuk dimasukkan ke Slider Geser
$query_list_ulasan = mysqli_query($koneksi, "
    SELECT * FROM ulasan 
    ORDER BY id_ulasan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaundryKu - Sistem Pengelolaan Laundry Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* Modern Design System Tokens */
        :root {
            --primary: #2563eb;
            --primary-gradient: linear-gradient(135deg, #3b82f6, #1d4ed8);
            --primary-light: rgba(37, 99, 235, 0.1);
            --dark: #f8fafc;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --bg-main: #0a1128; /* Diubah dari hitam ke Luxury Deep Navy */
            --bg-secondary: #101f42; /* Kombinasi warna latar kedua */
            --card-bg: rgba(22, 38, 76, 0.45); 
            --border: rgba(255, 255, 255, 0.08);
            --border-light: rgba(255, 255, 255, 0.04);
            --max-width: 1200px;
        }

        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, var(--bg-main) 0%, var(--bg-secondary) 100%);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }

        /* --- ORNAMEN GELEMBUNG AIR SABUN --- */
        .bubble-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }
        
        .laundry-bubble {
            position: absolute;
            bottom: -120px;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.15) 0%, rgba(59, 130, 246, 0.08) 40%, transparent 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation: laundryBubbles 15s infinite linear;
        }

        .b1 { left: 6%; width: 60px; height: 60px; animation-duration: 12s; animation-delay: 0s; }
        .b2 { left: 25%; width: 35px; height: 35px; animation-duration: 18s; animation-delay: 2s; }
        .b3 { left: 45%; width: 80px; height: 80px; animation-duration: 14s; animation-delay: 1s; opacity: 0.7; }
        .b4 { left: 68%; width: 40px; height: 40px; animation-duration: 13s; animation-delay: 4s; }
        .b5 { left: 88%; width: 75px; height: 75px; animation-duration: 19s; animation-delay: 0s; }

        @keyframes laundryBubbles {
            0% { transform: translateY(0) translateX(0) scale(1); opacity: 0; }
            15% { opacity: 0.6; }
            85% { opacity: 0.3; }
            100% { transform: translateY(-115vh) translateX(50px) scale(0.8); opacity: 0; }
        }

        /* Scroll Reveal Animation */
        .reveal {
            position: relative;
            transform: translateY(40px);
            opacity: 0;
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .reveal.active {
            transform: translateY(0);
            opacity: 1;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.03);
            color: #93c5fd;
            border: 1px solid var(--border);
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .btn-secondary:hover {
            background: var(--primary-light);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
            color: white;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: rgba(10, 17, 40, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        nav.scrolled {
            background: rgba(10, 17, 40, 0.95);
            padding: 15px 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .logo {
            font-size: 22px;
            font-weight: 800;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo i {
            color: #3b82f6;
            background: var(--primary-light);
            padding: 10px;
            border-radius: 10px;
        }

        nav ul { display: flex; list-style: none; gap: 32px; }
        nav ul a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 15px;
            transition: color 0.2s ease;
        }
        nav ul a:hover { color: #3b82f6; }

        .nav-buttons { display: flex; align-items: center; gap: 12px; }
        .btn-login {
            color: var(--text-main); text-decoration: none; font-weight: 600;
            padding: 10px 20px; border-radius: 10px; transition: all 0.2s ease; font-size: 14px;
        }
        .btn-login:hover { background: rgba(255, 255, 255, 0.05); color: #3b82f6; }
        
        .btn-register {
            background: var(--primary-light); color: #3b82f6; text-decoration: none;
            font-weight: 700; padding: 10px 20px; border-radius: 10px; transition: all 0.2s ease; font-size: 14px;
        }
        .btn-register:hover {
            background: var(--primary-gradient); color: white;
        }

        /* --- HERO SECTION --- */
        .hero {
            max-width: var(--max-width); margin: 140px auto 80px auto; padding: 0 24px;
            display: grid; grid-template-columns: 1.1fr 0.9fr; align-items: center; gap: 60px;
        }

        .hero-title { 
            font-size: 52px; font-weight: 800; line-height: 1.25; color: var(--dark); letter-spacing: -1.5px; margin-bottom: 22px; 
            animation: fadeInUpTitle 1s ease-out forwards; opacity: 0;
        }
        .hero-title span { 
            display: block; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; 
        }
        .hero-desc { font-size: 17px; color: var(--text-muted); margin-bottom: 35px; max-width: 540px; }
        .hero-btn { display: flex; gap: 16px; }

        @keyframes fadeInUpTitle {
            0% { transform: translateY(25px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        /* --- DOUBLE CARD INTERAKTIF (AMBING/FLOATING EFFECT) --- */
        .hero-premium-display { 
            position: relative; width: 100%; height: 420px; 
            display: flex; justify-content: center; align-items: center; 
            perspective: 1200px; 
        }

        /* Efek Mengambang Utama */
        .double-card-wrapper {
            position: relative; width: 290px; height: 370px; cursor: pointer;
            transform-style: preserve-3d; 
            transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
            animation: floatAnimation 4s ease-in-out infinite; /* Efek Mengambang Lembut */
        }
        
        @keyframes floatAnimation {
            0% { transform: translateY(0px) rotateY(0deg); }
            50% { transform: translateY(-12px) rotateY(2deg); }
            100% { transform: translateY(0px) rotateY(0deg); }
        }
        
        /* Matikan animasi mengambang standar saat status kartu aktif melebar ke samping */
        .double-card-wrapper.is-active {
            animation: none;
        }

        .card-front {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(145deg, #1b264f, #111836);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 22px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            display: flex; flex-direction: column; justify-content: space-between;
            z-index: 10; transform: translateZ(15px);
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .card-back {
            position: absolute; top: 15px; left: 15px; width: 100%; height: 100%;
            background: linear-gradient(145deg, #142142, #0d1428);
            border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 24px; padding: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            display: flex; flex-direction: column; justify-content: space-between;
            z-index: 5; opacity: 0.85;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .wm-panel { width: 100%; height: 48px; background: rgba(7, 13, 30, 0.4); border-radius: 12px; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
        .wm-led-display { width: 62px; height: 22px; background: #000; border-radius: 4px; border: 1px solid #2563eb; color: #60a5fa; font-family: monospace; font-size: 12px; font-weight: bold; text-align: center; line-height: 20px; transition: all 0.3s; }
        .wm-controls { display: flex; gap: 6px; }
        .wm-knob { width: 16px; height: 16px; background: #475569; border-radius: 50%; border: 2px solid #3b82f6; transition: transform 0.5s; }
        .wm-lights { display: flex; gap: 4px; }
        .wm-dot { width: 6px; height: 6px; background: #ef4444; border-radius: 50%; }
        .wm-dot.active { background: #10b981; }

        .wm-door-outer {
            width: 190px; height: 190px; background: linear-gradient(135deg, #1e293b, #0f172a);
            border-radius: 50%; margin: 15px auto; display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 20px rgba(0,0,0,0.2); position: relative;
        }
        .wm-door-glass {
            width: 140px; height: 140px; background: radial-gradient(circle, rgba(37, 99, 235, 0.2) 0%, rgba(29, 78, 216, 0.4) 70%);
            border-radius: 50%; overflow: hidden; position: relative; border: 1px solid rgba(255,255,255,0.1);
        }
        .wm-water-spin {
            width: 130px; height: 130px; border-radius: 50%; position: absolute; top: 4px; left: 2px;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.15) 50%, rgba(59,130,246,0.1) 70%, transparent 90%);
            animation: drumSpin 3s linear infinite; transition: animation-duration 0.4s;
        }
        .wm-clothes {
            width: 75px; height: 75px; background: #3b82f6; border-radius: 40% 60% 50% 50%;
            position: absolute; top: 30px; left: 30px; opacity: 0.6; filter: blur(1.5px);
            box-shadow: 20px 10px 0 #1d4ed8, -10px 20px 0 #60a5fa; 
            animation: drumSpin 3.5s linear infinite; transition: animation-duration 0.4s;
        }
        .wm-bottom { width: 100%; height: 15px; display: flex; justify-content: space-between; padding: 0 15px; }
        .wm-foot { width: 22px; height: 6px; background: #070d1e; border-radius: 0 0 4px 4px; }

        .back-title { font-size: 16px; font-weight: 800; color: #60a5fa; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; }
        .back-status-box { background: rgba(255,255,255,0.02); border-radius: 12px; padding: 14px; margin-top: 15px; border: 1px solid rgba(255,255,255,0.05); }
        .status-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
        .status-row:last-child { margin-bottom: 0; }
        .status-lbl { color: var(--text-muted); }
        .status-val { color: var(--dark); font-weight: 600; }
        .status-badge { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; transition: all 0.3s; }
        .back-hint { text-align: center; font-size: 11.5px; color: #60a5fa; font-weight: 600; opacity: 0.7; letter-spacing: 0.5px; }

        @keyframes drumSpin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .double-card-wrapper.is-active .card-front {
            transform: translateX(-110px) translateZ(30px) rotateY(-15deg); 
            z-index: 5;
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: -15px 25px 40px rgba(0,0,0,0.4);
        }
        
        .double-card-wrapper.is-active .card-back {
            transform: translateX(110px) translateZ(10px) rotateY(15deg); 
            z-index: 10;
            opacity: 1;
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 15px 25px 40px rgba(0,0,0,0.4);
        }

        .double-card-wrapper.is-active .wm-water-spin { animation-duration: 0.7s; }
        .double-card-wrapper.is-active .wm-clothes { animation-duration: 0.9s; }
        .double-card-wrapper.is-active .wm-led-display { border-color: #10b981; color: #10b981; }
        .double-card-wrapper.is-active .wm-knob { transform: rotate(120deg); border-color: #10b981; }

        /* About & Stats Section */
        .about { max-width: 700px; margin: 120px auto 50px auto; text-align: center; padding: 0 24px; }
        .about h2 { font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 16px; }
        .about p { font-size: 16px; color: var(--text-muted); }

        .stats { max-width: var(--max-width); margin: 0 auto 100px auto; padding: 0 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .stat-card {
            background: var(--card-bg); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-light);
            border-radius: 20px; padding: 30px 20px; text-align: center;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.4s ease;
        }
        .stat-card:hover { transform: translateY(-8px); border-color: rgba(59, 130, 246, 0.3); }
        .stat-card h3 { font-size: 36px; font-weight: 800; color: #60a5fa; margin-bottom: 6px; }
        .stat-card p { font-size: 14px; color: var(--text-muted); font-weight: 600; }

        /* Features Section */
        .features { max-width: var(--max-width); margin: 100px auto; padding: 0 24px; }
        .features h2 { text-align: center; font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 50px; }
        .feature-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .features .card {
            background: var(--card-bg); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-light);
            border-radius: 24px; padding: 40px 30px;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.4s ease;
        }
        .features .card:hover { transform: translateY(-8px); border-color: rgba(59, 130, 246, 0.3); }
        .features .card:hover i { transform: scale(1.1) rotate(5deg); }
        .features .card i {
            font-size: 24px; color: #3b82f6; background: var(--primary-light); width: 54px; height: 54px;
            display: flex; align-items: center; justify-content: center; border-radius: 14px; margin-bottom: 24px; transition: transform 0.3s ease;
        }
        .features .card h3 { font-size: 18px; font-weight: 700; color: var(--dark); margin-bottom: 12px; }
        .features .card p { font-size: 14px; color: var(--text-muted); }

        /* Steps Section */
        .steps { background: #0e1838; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); padding: 110px 0; position: relative; }
        .steps h2 { text-align: center; font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 70px; }
        
        .step-container { 
            max-width: var(--max-width); margin: 0 auto; padding: 0 24px; 
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 50px; position: relative; 
        }
        
        @media (min-width: 1025px) {
            .step-container::before {
                content: ''; position: absolute; top: 30px; left: 16%; width: 68%; height: 2px;
                background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.4), rgba(59, 130, 246, 0.4), transparent); 
                z-index: 1;
            }
        }
        
        .step {
            text-align: center; position: relative; z-index: 2; padding: 10px;
            background: transparent; border: none; border-radius: 0; box-shadow: none;
            transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .step span {
            width: 64px; height: 64px; background: var(--primary-gradient); color: white;
            display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;
            font-size: 22px; font-weight: 800; margin-bottom: 24px; 
            box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.1);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .step h3 { font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 14px; transition: all 0.3s ease; }
        .step p { font-size: 14.5px; color: var(--text-muted); padding: 0 10px; transition: all 0.3s ease; }

        .step:hover { transform: translateY(-8px); }
        .step:hover span { transform: scale(1.12) rotate(360deg); }
        .step:hover h3 { color: #60a5fa; }
        .step:hover p { color: #f1f5f9; }

        /* Reviews Section */
        .reviews-section { max-width: var(--max-width); margin: 100px auto; padding: 0 24px; }
        .reviews-header-wrapper { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }
        .reviews-header-text h2 { font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
        .reviews-header-text p { color: var(--text-muted); font-size: 15px; }
        
        .slider-navigation { display: flex; gap: 12px; }
        .nav-arrow-btn {
            width: 48px; height: 48px; border-radius: 50%; background: var(--card-bg);
            border: 1px solid var(--border); color: #60a5fa; display: flex;
            align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; font-size: 16px;
        }
        .nav-arrow-btn:hover { background: var(--primary-gradient); color: white; transform: scale(1.08); border-color: transparent; }

        .rating-summary-card {
            background: linear-gradient(135deg, #16264c, #0a1128); color: white;
            border: 1px solid var(--border-light); border-radius: 24px; padding: 30px;
            display: flex; align-items: center; justify-content: space-around; flex-wrap: wrap; gap: 24px; margin-bottom: 40px;
        }
        .rating-big-num h1 { font-size: 56px; font-weight: 800; line-height: 1; color: #fbbf24; }
        .rating-big-num p { font-size: 14px; color: #93c5fd; font-weight: 600; margin-top: 4px; }
        .rating-stars-info { text-align: center; }
        .rating-stars-info .stars-row { color: #fbbf24; font-size: 22px; margin-bottom: 4px; }
        .rating-stars-info span { font-size: 14px; color: #cbd5e1; }

        .reviews-slider-track {
            display: flex; gap: 24px; overflow-x: auto; scroll-behavior: smooth;
            padding: 10px 5px 30px 5px; scroll-snap-type: x mandatory;
            scrollbar-width: thin; scrollbar-color: rgba(59, 130, 246, 0.3) transparent;
        }
        .reviews-slider-track::-webkit-scrollbar { height: 6px; }
        .reviews-slider-track::-webkit-scrollbar-track { background: transparent; }
        .reviews-slider-track::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.2); border-radius: 10px; }
        .reviews-slider-track::-webkit-scrollbar-thumb:hover { background: rgba(59, 130, 246, 0.5); }

        .review-card {
            flex: 0 0 380px; scroll-snap-align: start;
            background: var(--card-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-light); border-radius: 24px; padding: 28px;
            transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;
        }
        .review-card:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.3); }
        .stars { color: #fbbf24; font-size: 13px; margin-bottom: 14px; }
        .review-text { font-size: 14px; color: var(--text-main); line-height: 1.6; margin-bottom: 20px; font-style: italic; }

        .admin-response { background: rgba(59, 130, 246, 0.08); border-left: 3px solid #3b82f6; border-radius: 12px; padding: 14px; margin-top: 5px; margin-bottom: 15px; }
        .admin-meta { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #60a5fa; margin-bottom: 4px; }
        .admin-text { font-size: 12.5px; color: #cbd5e1; line-height: 1.5; }

        .user-profile { display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 16px; margin-top: auto; }
        .user-profile .avatar-initial { width: 42px; height: 42px; border-radius: 50%; border: 2px solid rgba(59,130,246,0.3); }
        .user-info h4 { font-size: 13.5px; font-weight: 700; color: var(--dark); }
        .user-info span { font-size: 11.5px; color: var(--text-muted); }
        .empty-review { width: 100%; text-align: center; padding: 50px 0; color: var(--text-muted); font-weight: 500; }

        /* Footer */
        footer { background: #070d1e; border-top: 1px solid var(--border-light); padding: 60px 40px 30px 40px; text-align: center; }
        footer h3 { font-size: 20px; font-weight: 800; color: var(--dark); margin-bottom: 12px; }
        footer p { font-size: 14px; color: var(--text-muted); max-width: 400px; margin: 0 auto 24px auto; }
        .social { display: flex; justify-content: center; gap: 16px; margin-bottom: 35px; }
        .social i { font-size: 18px; color: var(--text-muted); background: var(--card-bg); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer; transition: all 0.2s ease; }
        .social i:hover { background: var(--primary-light); color: #60a5fa; transform: translateY(-4px); }
        footer .copyright { font-size: 13px; border-top: 1px solid var(--border-light); padding-top: 24px; max-width: var(--max-width); margin: 0 auto; }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero { grid-template-columns: 1fr; text-align: center; margin-top: 120px; gap: 50px; }
            .hero-desc { margin-left: auto; margin-right: auto; }
            .hero-btn { justify-content: center; }
            .feature-container { grid-template-columns: repeat(2, 1fr); }
            .step-container { grid-template-columns: 1fr; gap: 35px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .hero-premium-display { height: auto; padding: 20px 0; }
        }

        @media (max-width: 768px) {
            nav { padding: 20px 20px; }
            nav.scrolled { padding: 15px 20px; }
            nav ul { display: none; }
            .feature-container, .stats { grid-template-columns: 1fr; }
            .hero-title { font-size: 38px; }
            .rating-summary-card { flex-direction: column; text-align: center; }
            .review-card { flex: 0 0 300px; padding: 20px; }
            .reviews-header-wrapper { flex-direction: column; align-items: flex-start; }
            .double-card-wrapper.is-active .card-front { transform: translateX(-40px) translateZ(20px); }
            .double-card-wrapper.is-active .card-back { transform: translateX(40px) translateZ(10px); }
        }
    </style>
</head>
<body>

    <div class="bubble-wrapper">
        <div class="laundry-bubble b1"></div>
        <div class="laundry-bubble b2"></div>
        <div class="laundry-bubble b3"></div>
        <div class="laundry-bubble b4"></div>
        <div class="laundry-bubble b5"></div>
    </div>

    <nav id="navbar">
        <div class="logo">
            <i class="fa-solid fa-shirt"></i>
            LaundryKu
        </div>
        <ul>
            <li><a href="#home">Beranda</a></li>
            <li><a href="#fitur">Fitur</a></li>
            <li><a href="#cara-kerja">Cara Kerja</a></li>
            <li><a href="#ulasan">Ulasan</a></li>
        </ul>
        <div class="nav-buttons">
            <a href="auth/login.php" class="btn-login">Login</a>
            <a href="auth/registrasi.php" class="btn-register">Registrasi</a>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-text">
            <h1 class="hero-title">
                Transformasi Operasional
                <span>Laundry Lebih Modern</span>
            </h1>
            <p class="hero-desc">
                Ekosistem manajemen laundry cerdas yang dirancang khusus untuk mengoptimalkan pelacakan pesanan, otomatisasi pembukuan finansial, dan mempercepat pertumbuhan bisnis Anda secara real-time.
            </p>
            <div class="hero-btn">
                <a href="auth/registrasi.php" class="btn-primary">Mulai Integrasi <i class="fa-solid fa-arrow-right"></i></a>
                <a href="#fitur" class="btn-secondary">Eksplorasi Fitur</a>
            </div>
        </div>

        <div class="hero-premium-display">
            <div class="double-card-wrapper" id="laundryDoubleCard">
                
                <div class="card-front">
                    <div class="wm-panel">
                        <div class="wm-led-display" id="wmLedTimer">00:26</div>
                        <div class="wm-controls">
                            <div class="wm-knob" id="wmKnobElement"></div>
                            <div class="wm-lights">
                                <div class="wm-dot"></div>
                                <div class="wm-dot active"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wm-door-outer">
                        <div class="wm-door-glass">
                            <div class="wm-water-spin"></div>
                            <div class="wm-clothes"></div>
                        </div>
                    </div>
                    
                    <div class="wm-bottom">
                        <div class="wm-foot"></div>
                        <div class="wm-foot"></div>
                    </div>
                </div>

                <div class="card-back">
                    <div>
                        <div class="back-title">
                            <i class="fa-solid fa-circle-nodes"></i>
                            <span>Sistem Core Engine</span>
                        </div>
                        
                        <div class="back-status-box">
                            <div class="status-row">
                                <span class="status-lbl">Status Unit</span>
                                <span class="status-badge" id="wmStatusBadge">IDLE</span>
                            </div>
                            <div class="status-row">
                                <span class="status-lbl">Model IoT</span>
                                <span class="status-val" style="color:#60a5fa;">LK-V2026</span>
                            </div>
                            <div class="status-row">
                                <span class="status-lbl">Efisiensi Air</span>
                                <span class="status-val">99.4%</span>
                            </div>
                        </div>
                    </div>

                    <div class="back-hint">
                        <i class="fa-solid fa-hand-pointer" style="animation: bounce 1s infinite alternate;margin-right:4px;"></i> 
                        KLIK UNTUK DETAIL MODUL
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="about reveal">
        <h2>Mengapa LaundryKu?</h2>
        <p>
            Kami memberikan solusi efisiensi menyeluruh bagi pemilik usaha laundry guna mengoptimalkan 
            pelacakan pesanan, mempercepat transaksi pembayaran, serta menyajikan laporan bisnis berkala secara presisi.
        </p>
    </section>

    <section class="stats reveal">
        <div class="stat-card">
            <h3 class="counter" data-target="<?= $total_pelanggan; ?>">0</h3>
            <p>Pelanggan Aktif</p>
        </div>
        <div class="stat-card">
            <h3 class="counter" data-target="<?= $total_pesanan_selesai; ?>">0</h3>
            <p>Pesanan Selesai</p>
        </div>
        <div class="stat-card">
            <h3 class="counter" data-target="<?= $total_layanan; ?>">0</h3>
            <p>Varian Layanan</p>
        </div>
        <div class="stat-card">
            <h3 class="counter" data-target="<?= $kepuasan_pelanggan; ?>">0</h3>
            <p>Kepuasan Pelanggan</p>
        </div>
    </section>

    <section class="features reveal" id="fitur">
        <h2>Fitur Unggulan</h2>
        <div class="feature-container">
            <div class="card">
                <i class="fa-solid fa-users"></i>
                <h3>Data Pelanggan</h3>
                <p>Penyimpanan basis data profil konsumen terpusat, aman, dan mudah diakses kapan saja.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>Manajemen Pesanan</h3>
                <p>Memantau antrean masuk dan pemrosesan tahapan laundry secara akurat dari satu dasbor tunggal.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-money-bill"></i>
                <h3>Pencatatan Finansial</h3>
                <p>Melacak status kas masuk, manajemen tagihan, serta konfirmasi pelunasan secara instan.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-chart-column"></i>
                <h3>Analisis Laporan</h3>
                <p>Visualisasi rekapitulasi performa omzet harian, mingguan, maupun laporan berkala bulanan.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-bell"></i>
                <h3>Notifikasi Otomatis</h3>
                <p>Sistem otomatisasi notifikasi status pengerjaan laundry begitu pakaian siap dikembalikan.</p>
            </div>
            <div class="card">
                <i class="fa-solid fa-mobile-screen"></i>
                <h3>Akses Multi-Device</h3>
                <p>Aplikasi responsif yang optimal diakses baik via desktop, tablet maupun perangkat ponsel pintar.</p>
            </div>
        </div>
    </section>

    <section class="steps reveal" id="cara-kerja">
        <h2>Cara Kerja Sistem</h2>
        <div class="step-container">
            <div class="step">
                <span>1</span>
                <h3>Input Pesanan</h3>
                <p>Petugas memasukkan data pelanggan, berat timbangan kuantitas (kg), dan jenis layanan terpilih.</p>
            </div>
            <div class="step">
                <span>2</span>
                <h3>Proses Pemrosesan</h3>
                <p>Status pengerjaan diperbarui bertahap di sistem dari diterima hingga selesai di-packing.</p>
            </div>
            <div class="step">
                <span>3</span>
                <h3>Penyerahan & Selesai</h3>
                <p>Pelanggan menerima pemberitahuan otomatis dan transaksi ditutup setelah pengambilan barang.</p>
            </div>
        </div>
    </section>

    <section class="reviews-section reveal" id="ulasan">
        <div class="reviews-header-wrapper">
            <div class="reviews-header-text">
                <h2>Ulasan Kebahagiaan Pelanggan</h2>
                <p>Geser ke samping untuk melihat testimoni dari para pelanggan setia kami.</p>
            </div>
            <div class="slider-navigation">
                <button class="nav-arrow-btn" id="btnSlideLeft" aria-label="Geser Kiri"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="nav-arrow-btn" id="btnSlideRight" aria-label="Geser Kanan"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="rating-summary-card">
            <div class="rating-big-num">
                <h1><?= $rata_rata_rating > 0 ? $rata_rata_rating : '0.0'; ?></h1>
                <p>Skor Skala Komunitas</p>
            </div>
            <div class="rating-stars-info">
                <div class="stars-row">
                    <?php 
                    $floor_rating = floor($rata_rata_rating);
                    for($i = 1; $i <= 5; $i++) {
                        if($i <= $floor_rating) {
                            echo '<i class="fa-solid fa-star"></i>';
                        } elseif($i - $rata_rata_rating < 1 && $i - $rata_rata_rating > 0) {
                            echo '<i class="fa-solid fa-star-half-stroke"></i>';
                        } else {
                            echo '<i class="fa-regular fa-star"></i>';
                        }
                    }
                    ?>
                </div>
                <span>Total Distribusi <strong><?= $total_ulasan; ?></strong> Ulasan Masuk</span>
            </div>
        </div>
        
        <div class="reviews-slider-track" id="laundryReviewsSlider">
            <?php 
            if(mysqli_num_rows($query_list_ulasan) > 0) {
                $counter_anonim = 1;
                while($row = mysqli_fetch_assoc($query_list_ulasan)) {
                    $star_count = intval($row['rating']);
                    $id_user_display = $row['id_user'] ?? $counter_anonim++;
                    $nama_user = "Pelanggan #" . $id_user_display;
                    $inisial_huruf = "P" . $id_user_display;
                    
                    $isi_review = htmlspecialchars($row['komentar']);
                    $tanggapan = htmlspecialchars($row['tanggapan'] ?? '');
            ?>
                <div class="review-card">
                    <div>
                        <div class="stars">
                            <?php 
                            for($i = 1; $i <= 5; $i++) {
                                echo ($i <= $star_count) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                            }
                            ?>
                        </div>
                        <p class="review-text">"<?= $isi_review; ?>"</p>
                    </div>

                    <?php if(!empty($tanggapan)): ?>
                        <div class="admin-response">
                            <div class="admin-meta">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Respon Manajemen Laundry</span>
                            </div>
                            <p class="admin-text"><?= $tanggapan; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="user-profile">
                        <img class="avatar-initial" src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($inisial_huruf); ?>&backgroundColor=2563eb" alt="Inisial <?= $inisial_huruf; ?>">
                        <div class="user-info">
                            <h4><?= $nama_user; ?></h4>
                            <span>Verifikasi Konsumen</span>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo '<div class="empty-review"><i class="fa-regular fa-comment-dots" style="font-size:32px;margin-bottom:10px;display:block;"></i>Belum ada ulasan tersimpan di database.</div>';
            } 
            ?>
        </div>
    </section>

    <footer id="kontak">
        <h3>LaundryKu</h3>
        <p>Solusi manajemen digital mutakhir penunjang akselerasi dan efisiensi operasional bisnis laundry modern.</p>
        <div class="social">
            <i class="fab fa-facebook-f"></i>
            <i class="fab fa-instagram"></i>
            <i class="fab fa-whatsapp"></i>
        </div>
        <p class="copyright">
            &copy; 2026 LaundryKu. All Rights Reserved.
        </p>
    </footer>

    <script>
    // 1. Logic Interaktif Klik Double Card (Mekanisme Switch Posisi & Ubah Status)
    const doubleCard = document.getElementById('laundryDoubleCard');
    const statusBadge = document.getElementById('wmStatusBadge');
    const ledTimer = document.getElementById('wmLedTimer');

    if(doubleCard) {
        doubleCard.addEventListener('click', () => {
            doubleCard.classList.toggle('is-active');
            
            if(doubleCard.classList.contains('is-active')) {
                statusBadge.innerText = 'RUNNING';
                statusBadge.style.background = 'rgba(16, 185, 129, 0.1)';
                statusBadge.style.color = '#10b981';
                ledTimer.innerText = '00:45';
            } else {
                statusBadge.innerText = 'IDLE';
                statusBadge.style.background = 'rgba(239, 68, 68, 0.1)';
                statusBadge.style.color = '#ef4444';
                ledTimer.innerText = '00:26';
            }
        });
    }

    // 2. Animasi Counter Angka Berjalan
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCounter = () => {
            const target = +counter.getAttribute('data-target');
            const current = +counter.innerText;
            const increment = Math.max(target / 50, 1);

            if(current < target){
                counter.innerText = Math.ceil(current + increment);
                setTimeout(updateCounter, 30);
            } else {
                if(target === 99 || target === 100){
                    counter.innerText = target + "%";
                } else {
                    counter.innerText = target + "+";
                }
            }
        }
        updateCounter();
    });

    // 3. Efek Mengubah Navbar Saat Di-scroll
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 4. JavaScript Scroll Reveal Event
    const revealElements = document.querySelectorAll('.reveal');
    const revealOnScroll = () => {
        for (let i = 0; i < revealElements.length; i++) {
            let windowHeight = window.innerHeight;
            let elementTop = revealElements[i].getBoundingClientRect().top;
            let elementVisible = 120;

            if (elementTop < windowHeight - elementVisible) {
                revealElements[i].classList.add('active');
            }
        }
    }
    window.addEventListener('scroll', revealOnScroll);
    window.addEventListener('load', revealOnScroll);

    // 5. Logika Geser Slider Ulasan dengan Tombol Navigasi
    const sliderTrack = document.getElementById('laundryReviewsSlider');
    const arrowLeft = document.getElementById('btnSlideLeft');
    const arrowRight = document.getElementById('btnSlideRight');

    if(sliderTrack && arrowLeft && arrowRight) {
        arrowRight.addEventListener('click', () => {
            sliderTrack.scrollLeft += 404;
        });

        arrowLeft.addEventListener('click', () => {
            sliderTrack.scrollLeft -= 404;
        });
    }
    </script>
</body>
</html>
