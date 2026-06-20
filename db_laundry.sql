-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Jun 2026 pada 11.28
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_laundry`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_layanan` int(11) NOT NULL,
  `berat` decimal(5,2) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_layanan`, `berat`, `total_harga`) VALUES
(9, 9, 4, 4.00, 160000.00),
(11, 11, 4, 2.00, 80000.00),
(15, 15, 12, 3.00, 75000.00),
(16, 16, 6, 5.00, 50000.00),
(19, 19, 4, 3.00, 120000.00),
(20, 20, 12, 70.00, 1750000.00),
(21, 21, 4, 5.00, 200000.00),
(22, 22, 13, 2.00, 30000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga_perkg` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `harga_perkg`) VALUES
(2, 'Cuci Setrika', 20000.00),
(4, 'Cuci Express', 40000.00),
(5, 'Dry Cleaning', 50000.00),
(6, 'Cuci Kering', 10000.00),
(12, 'Cuci boneka', 25000.00),
(13, 'Cuci Setrika', 15000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `kode_pesanan` varchar(20) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('Diterima','Diproses','Selesai') DEFAULT 'Diterima'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_user`, `tanggal_masuk`, `tanggal_selesai`, `status`) VALUES
(9, 'PSN260615723', 4, '2026-06-15', '2026-06-15', 'Selesai'),
(11, 'PSN260615104', 7, '2026-06-15', '2026-06-15', 'Selesai'),
(15, 'PSN260618151', 4, '2026-06-18', NULL, 'Diterima'),
(16, 'PSN260618446', 4, '2026-06-18', NULL, 'Diterima'),
(19, 'PSN260620718', 11, '2026-06-20', '2026-06-20', 'Selesai'),
(20, 'PSN260620282', 13, '2026-06-20', '2026-06-20', 'Selesai'),
(21, 'PSN260620771', 4, '2026-06-20', '2026-06-20', 'Selesai'),
(22, 'PSN260620470', 11, '2026-06-20', NULL, 'Diterima');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ulasan`
--

CREATE TABLE `ulasan` (
  `id_ulasan` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `tanggal_ulasan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggapan` text DEFAULT NULL,
  `tanggal_tanggapan` datetime DEFAULT NULL,
  `status_baca_user` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ulasan`
--

INSERT INTO `ulasan` (`id_ulasan`, `id_pesanan`, `id_user`, `rating`, `komentar`, `tanggal_ulasan`, `tanggapan`, `tanggal_tanggapan`, `status_baca_user`) VALUES
(8, 9, 4, 5, 'sangat suka', '2026-06-15 12:32:07', 'terimakasih kakak', '2026-06-15 19:32:57', 1),
(9, 11, 7, 5, 'cuciannya bersih', '2026-06-15 14:40:51', 'terimakasi kak', '2026-06-15 21:51:41', 0),
(13, 19, 11, 5, 'pelayanannya bagus', '2026-06-20 08:26:23', 'makasih besttt', '2026-06-20 14:50:26', 0),
(14, 20, 13, 1, 'rifki na marah marah wae', '2026-06-20 08:58:31', 'maksut kou?', '2026-06-20 15:59:24', 0),
(15, 21, 4, 5, 'oke', '2026-06-20 09:04:02', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`, `foto`) VALUES
(1, 'adminlaundry', 'adminlaundry@gmail.com', '$2y$10$h1HhgCe3riaFtvgeImVo.OTdN5UtbXZeIbuhEMSBNly/le5qtOZ5m', 'admin', '2026-05-31 08:00:41', '6a365cfb9e29d.png'),
(4, 'Syifa Nurhidayah ', 'syifa@gmail.com', '$2y$10$frAgfxBadfz4UK5kPRUFJeVBLjLVPPQJrSKR28WX/LAbWc3t5ZJbS', 'user', '2026-05-31 10:02:27', 'profile_4_1781759878.jpg'),
(7, 'Rifki', 'rifki@gmail.com', '$2y$10$2XWNqOwAUp3hZQlQw6hAaOsgz7aJ4w8arCk1WGLxjMAdLhbnhRTRi', 'user', '2026-06-04 04:38:28', NULL),
(11, 'Tyaz Hakim', 'tyaz@gmail.com', '$2y$10$JcVX84r7ktMU0.XXTjx8QOooUY9jEiudkUJe5vtLbWu8JjukAelAK', 'user', '2026-06-20 07:37:52', 'profile_11_1781943856.png'),
(12, 'admin', 'admin@gmail.com', '$2y$10$Mn/1KgKal7UYGnR/0RcUIe4Lr5gH7U0vSNwa8vvKiFZFIsxsCemLa', 'admin', '2026-06-20 08:06:30', NULL),
(13, 'alexanser gubal gabel', 'masbam@gmail.com', '$2y$10$bGcvdgqfPEYGThYEy7pXyuEvEGQHnDXzURlIxLq/8ZhGQ5geuAC.W', 'user', '2026-06-20 08:50:16', 'profile_13_1781945643.jpg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_pesanan` (`id_pesanan`),
  ADD KEY `fk_detail_layanan` (`id_layanan`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `fk_ulasan_pesanan` (`id_pesanan`),
  ADD KEY `fk_ulasan_user` (`id_user`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id_layanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_layanan` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `fk_ulasan_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ulasan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
