-- ============================================================
-- DATABASE TOKO HANATA BATIK
-- MySQL 8.x
-- Database: db_2355202045_batik
-- ============================================================

DROP DATABASE IF EXISTS `db_2355202045_batik`;
CREATE DATABASE `db_2355202045_batik`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `db_2355202045_batik`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABEL TYPE / KATEGORI PRODUK
-- ============================================================

DROP TABLE IF EXISTS `type`;
CREATE TABLE `type` (
    `id_type` INT NOT NULL AUTO_INCREMENT,
    `nm_type` VARCHAR(80) NOT NULL,
    `ket` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_type`),
    UNIQUE KEY `uk_type_name` (`nm_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `type` (`id_type`, `nm_type`, `ket`) VALUES
(1, 'Kemeja Pria', 'Kemeja batik pria dengan berbagai motif dan potongan.'),
(2, 'Kemeja Formal', 'Batik untuk kebutuhan kerja, meeting, dan acara resmi.'),
(3, 'Atasan Wanita', 'Blouse dan atasan batik wanita dengan potongan modern.'),
(4, 'Batik Premium', 'Koleksi bahan dan motif premium untuk acara spesial.'),
(5, 'Batik Casual', 'Batik santai untuk aktivitas harian dan hangout.'),
(6, 'Batik Keluarga', 'Pilihan batik untuk pasangan dan keluarga.'),
(7, 'Batik Couple', 'Set batik pasangan dengan motif senada.');

-- ============================================================
-- 2. TABEL BARANG / PRODUK
-- Tidak menggunakan merek. Produk hanya menggunakan type.
-- ============================================================

DROP TABLE IF EXISTS `barang`;
CREATE TABLE `barang` (
    `id_brg` INT NOT NULL AUTO_INCREMENT,
    `nm_brg` VARCHAR(120) NOT NULL,
    `id_type` INT NOT NULL,
    `ket` TEXT NOT NULL,
    `hrg_jual` BIGINT NOT NULL,
    `foto` VARCHAR(255) DEFAULT '',
    `status` TINYINT NOT NULL DEFAULT 1,
    `stok` INT NOT NULL DEFAULT 1,
    `ukuran` VARCHAR(30) NOT NULL DEFAULT 'S,M,L,XL',
    PRIMARY KEY (`id_brg`),
    KEY `idx_barang_type` (`id_type`),
    CONSTRAINT `fk_barang_type`
        FOREIGN KEY (`id_type`) REFERENCES `type` (`id_type`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `barang`
(`id_brg`, `nm_brg`, `id_type`, `ket`, `hrg_jual`, `foto`, `status`, `stok`, `ukuran`) VALUES
(1, 'Batik Parang Pria', 1, 'Kemeja batik motif parang dengan potongan regular fit, cocok untuk acara formal maupun kantor.', 189000, '', 1, 12, 'S,M,L,XL'),
(2, 'Batik Kawung Pria', 2, 'Kemeja batik motif kawung dengan bahan katun adem dan nyaman dipakai seharian.', 199000, '', 1, 10, 'S,M,L,XL'),
(3, 'Batik Mega Mendung', 3, 'Kemeja batik motif mega mendung dengan desain modern dan warna elegan.', 219000, '', 1, 8, 'S,M,L,XL'),
(4, 'Batik Sogan Klasik', 4, 'Koleksi batik bernuansa sogan dengan siluet klasik dan bahan ringan.', 229000, '', 1, 7, 'S,M,L,XL'),
(5, 'Batik Floral Wanita', 3, 'Blouse batik floral dengan potongan feminin untuk tampilan kerja dan semi-formal.', 179000, '', 1, 15, 'S,M,L,XL'),
(6, 'Batik Truntum Wanita', 6, 'Atasan batik motif truntum dengan detail modern, nyaman untuk acara keluarga.', 199000, '', 1, 9, 'S,M,L,XL'),
(7, 'Batik Couple Senada', 7, 'Set atasan batik pasangan dengan motif senada untuk momen spesial.', 389000, '', 1, 6, 'S,M,L,XL'),
(8, 'Batik Lereng Modern', 1, 'Kemeja batik motif lereng dengan potongan clean dan mudah dipadukan.', 209000, '', 1, 11, 'S,M,L,XL'),
(9, 'Batik Sekar Jagad', 3, 'Atasan wanita motif sekar jagad dengan karakter warna lembut dan modern.', 219000, '', 1, 8, 'S,M,L,XL'),
(10, 'Batik Nusantara Premium', 4, 'Kemeja batik premium dengan detail motif khas Nusantara dan bahan katun berkualitas.', 279000, '', 1, 5, 'S,M,L,XL'),
(11, 'Batik Hitam Elegan', 2, 'Kemeja hitam bermotif batik untuk acara resmi, kondangan, dan meeting.', 239000, '', 1, 6, 'S,M,L,XL'),
(12, 'Batik Pastel Casual', 5, 'Batik casual bernuansa pastel yang cocok dipakai hangout maupun bekerja.', 189000, '', 1, 13, 'S,M,L,XL');

-- ============================================================
-- 3. MEMBER / PELANGGAN
-- Login: username / email / nomor HP
-- ============================================================

DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
    `id_member` INT NOT NULL AUTO_INCREMENT,
    `nm_member` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `no_hp` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `alamat` VARCHAR(255) DEFAULT NULL,
    `foto_profile` VARCHAR(255) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_member`),
    UNIQUE KEY `uk_member_username` (`username`),
    UNIQUE KEY `uk_member_hp` (`no_hp`),
    UNIQUE KEY `uk_member_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- password semua akun contoh: member123
INSERT INTO `member`
(`id_member`, `nm_member`, `username`, `no_hp`, `email`, `alamat`, `foto_profile`, `password`, `status`) VALUES
(1, 'Budi', 'budi', '085712345678', 'budi@gmail.com', NULL, NULL, '$2y$12$ItMaSkHyO8h3CYnMJvK7xeMIPwL9z1LqDGv0IlwM7qmxZxI2bIJ.S', 1),
(2, 'Siti', 'siti', '085798765432', 'siti@gmail.com', NULL, NULL, '$2y$12$ItMaSkHyO8h3CYnMJvK7xeMIPwL9z1LqDGv0IlwM7qmxZxI2bIJ.S', 1),
(3, 'Andi', 'andi', '081255667788', 'andi@gmail.com', NULL, NULL, '$2y$12$ItMaSkHyO8h3CYnMJvK7xeMIPwL9z1LqDGv0IlwM7qmxZxI2bIJ.S', 1),
(4, 'Dewi', 'dewi', '081300000004', 'dewi@gmail.com', NULL, NULL, '$2y$12$ItMaSkHyO8h3CYnMJvK7xeMIPwL9z1LqDGv0IlwM7qmxZxI2bIJ.S', 1),
(5, 'Eka', 'eka', '081300000005', 'eka@gmail.com', NULL, NULL, '$2y$12$ItMaSkHyO8h3CYnMJvK7xeMIPwL9z1LqDGv0IlwM7qmxZxI2bIJ.S', 1),
(6, 'Adcho', 'adcho', '082284326992', NULL, NULL, NULL, '$2y$12$ItMaSkHyO8h3CYnMJvK7xeMIPwL9z1LqDGv0IlwM7qmxZxI2bIJ.S', 1);

-- ============================================================
-- 4. USER / ADMIN
-- ============================================================

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id_user` INT NOT NULL AUTO_INCREMENT,
    `nm_user` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `level` VARCHAR(20) NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    `user_login` TINYINT NOT NULL DEFAULT 1,
    `email` VARCHAR(120) DEFAULT NULL,
    `no_hp` VARCHAR(30) DEFAULT NULL,
    PRIMARY KEY (`id_user`),
    UNIQUE KEY `uk_user_name` (`nm_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`id_user`, `nm_user`, `password`, `level`, `status`, `user_login`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', 1, 1),
(2, 'gudang', '0192023a7bbd73250516f069df18b500', 'gudang', 1, 1);

-- ============================================================
-- 5. SUPPLIER
-- Dipertahankan karena masih bagian dari struktur backend lama.
-- Menu supplier tidak ditampilkan pada website admin.
-- ============================================================

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
    `id_sup` INT NOT NULL AUTO_INCREMENT,
    `nm_supp` VARCHAR(100) NOT NULL,
    `alamat` VARCHAR(255) NOT NULL,
    `no_hp` VARCHAR(20) NOT NULL,
    PRIMARY KEY (`id_sup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `supplier` (`id_sup`, `nm_supp`, `alamat`, `no_hp`) VALUES
(1, 'CV Batik Nusantara', 'Yogyakarta', '081234567890'),
(2, 'PT Batik Indonesia', 'Solo', '081298765432'),
(3, 'Batik Sejahtera', 'Pekalongan', '082233445566'),
(4, 'CV Hanata Kreasi', 'Pekanbaru', '081211112222'),
(5, 'PT Cahaya Batik Abadi', 'Bandung', '081233334444');

-- ============================================================
-- 6. FAKTUR PEMBELIAN
-- ============================================================

DROP TABLE IF EXISTS `faktur_beli`;
CREATE TABLE `faktur_beli` (
    `id_faktur` INT NOT NULL AUTO_INCREMENT,
    `no_faktur` VARCHAR(20) NOT NULL,
    `id_supp` INT NOT NULL,
    `tgl_faktur` DATE NOT NULL,
    `tot_nilai` BIGINT NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_faktur`),
    UNIQUE KEY `uk_no_faktur_beli` (`no_faktur`),
    KEY `idx_faktur_beli_supplier` (`id_supp`),
    CONSTRAINT `fk_faktur_beli_supplier`
        FOREIGN KEY (`id_supp`) REFERENCES `supplier` (`id_sup`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `faktur_beli`
(`id_faktur`, `no_faktur`, `id_supp`, `tgl_faktur`, `tot_nilai`, `status`) VALUES
(1, 'FB-0001', 1, '2026-04-01', 378000, 0),
(2, 'FB-0002', 2, '2026-04-02', 597000, 1),
(3, 'FB-0003', 3, '2026-04-03', 219000, 2),
(4, 'FB-0004', 1, '2026-04-04', 458000, 0),
(5, 'FB-0005', 2, '2026-04-05', 716000, 1),
(6, 'FB-0006', 1, '2026-04-06', 398000, 2),
(7, 'FB-0007', 2, '2026-04-07', 1167000, 0),
(8, 'FB-0008', 3, '2026-04-08', 209000, 1),
(9, 'FB-0009', 4, '2026-04-09', 438000, 2),
(10, 'FB-0010', 5, '2026-04-10', 279000, 0),
(11, 'FB-0011', 1, '2026-04-11', 478000, 1),
(12, 'FB-0012', 2, '2026-04-12', 567000, 2),
(13, 'FB-0013', 3, '2026-04-13', 438000, 0),
(14, 'FB-0014', 4, '2026-04-14', 398000, 1),
(15, 'FB-0015', 5, '2026-04-15', 558000, 2);

-- ============================================================
-- 7. DETAIL PEMBELIAN
-- ============================================================

DROP TABLE IF EXISTS `detail_beli`;
CREATE TABLE `detail_beli` (
    `id_beli` INT NOT NULL AUTO_INCREMENT,
    `id_faktur` INT NOT NULL,
    `id_brg` INT NOT NULL,
    `jumlah` INT NOT NULL,
    `harga` BIGINT NOT NULL,
    PRIMARY KEY (`id_beli`),
    KEY `idx_detail_beli_faktur` (`id_faktur`),
    KEY `idx_detail_beli_barang` (`id_brg`),
    CONSTRAINT `fk_detail_beli_faktur`
        FOREIGN KEY (`id_faktur`) REFERENCES `faktur_beli` (`id_faktur`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_detail_beli_barang`
        FOREIGN KEY (`id_brg`) REFERENCES `barang` (`id_brg`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detail_beli` (`id_beli`, `id_faktur`, `id_brg`, `jumlah`, `harga`) VALUES
(1, 1, 1, 2, 189000),
(2, 2, 2, 3, 199000),
(3, 3, 3, 1, 219000),
(4, 4, 4, 2, 229000),
(5, 5, 5, 4, 179000),
(6, 6, 6, 2, 199000),
(7, 7, 7, 3, 389000),
(8, 8, 8, 1, 209000),
(9, 9, 9, 2, 219000),
(10, 10, 10, 1, 279000),
(11, 11, 11, 2, 239000),
(12, 12, 12, 3, 189000),
(13, 13, 3, 2, 219000),
(14, 14, 6, 2, 199000),
(15, 15, 10, 2, 279000);

-- ============================================================
-- 8. FAKTUR PENJUALAN / ORDER
-- Tidak memakai alur cash/kredit di website; pembelian dilakukan
-- melalui WhatsApp admin. Kolom lama payment dipertahankan agar
-- kompatibel dengan struktur backend.
-- ============================================================

DROP TABLE IF EXISTS `faktur_jual`;
CREATE TABLE `faktur_jual` (
    `id_faktur` INT NOT NULL AUTO_INCREMENT,
    `no_faktur` VARCHAR(30) NOT NULL,
    `id_member` INT NOT NULL,
    `tgl_faktur` DATE NOT NULL,
    `total` BIGINT NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 0,
    `order_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `payment_method` VARCHAR(20) NOT NULL DEFAULT '',
    `payment_status` VARCHAR(20) NOT NULL DEFAULT 'unpaid',
    `dp_amount` BIGINT NOT NULL DEFAULT 0,
    `tenor_bulan` INT NOT NULL DEFAULT 0,
    `cicilan_perbulan` BIGINT NOT NULL DEFAULT 0,
    `sisa_tagihan` BIGINT NOT NULL DEFAULT 0,
    `catatan_admin` VARCHAR(255) DEFAULT NULL,
    `tgl_approve` DATETIME DEFAULT NULL,
    `tgl_order` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `tgl_update_status` DATETIME NULL,
    PRIMARY KEY (`id_faktur`),
    UNIQUE KEY `uk_no_faktur_jual` (`no_faktur`),
    KEY `idx_order_member` (`id_member`),
    KEY `idx_order_status` (`order_status`),
    CONSTRAINT `fk_faktur_jual_member`
        FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_order_status`
        CHECK (`order_status` IN ('pending','approved','processing','shipped','delivered','declined'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `faktur_jual`
(`id_faktur`, `no_faktur`, `id_member`, `tgl_faktur`, `total`, `status`, `order_status`, `payment_method`, `payment_status`, `dp_amount`, `tenor_bulan`, `cicilan_perbulan`, `sisa_tagihan`, `catatan_admin`, `tgl_approve`, `tgl_order`) VALUES
(1, 'FJ-0001', 1, '2026-08-01', 189000, 0, 'pending', '', 'unpaid', 0, 0, 0, 189000, NULL, NULL, '2026-08-01 10:00:00'),
(2, 'FJ-0002', 2, '2026-08-02', 199000, 1, 'approved', '', 'unpaid', 0, 0, 0, 199000, 'Pesanan disetujui admin', '2026-08-02 11:00:00', '2026-08-02 09:00:00'),
(3, 'FJ-0003', 3, '2026-08-03', 219000, 1, 'processing', '', 'unpaid', 0, 0, 0, 219000, 'Pesanan sedang disiapkan', '2026-08-03 11:00:00', '2026-08-03 09:30:00'),
(4, 'FJ-0004', 4, '2026-08-04', 389000, 1, 'shipped', '', 'unpaid', 0, 0, 0, 389000, 'Pesanan sedang diantar', '2026-08-04 11:00:00', '2026-08-04 09:45:00'),
(5, 'FJ-0005', 5, '2026-08-05', 279000, 2, 'delivered', '', 'unpaid', 0, 0, 0, 279000, 'Pesanan telah diterima pelanggan', '2026-08-05 11:00:00', '2026-08-05 09:00:00'),
(6, 'FJ-0006', 6, '2026-08-06', 229000, 3, 'declined', '', 'unpaid', 0, 0, 0, 229000, 'Stok produk tidak mencukupi', NULL, '2026-08-06 09:00:00');

-- ============================================================
-- 9. DETAIL PENJUALAN / DETAIL ORDER
-- ============================================================

DROP TABLE IF EXISTS `detal_jual`;
CREATE TABLE `detal_jual` (
    `id_jual` INT NOT NULL AUTO_INCREMENT,
    `no_faktur` VARCHAR(30) NOT NULL,
    `id_brg` INT NOT NULL,
    `ukuran` VARCHAR(5) NOT NULL DEFAULT 'M',
    `jumlah` INT NOT NULL DEFAULT 1,
    `harga` BIGINT NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_jual`),
    KEY `idx_detail_faktur` (`no_faktur`),
    KEY `idx_detail_produk` (`id_brg`),
    CONSTRAINT `fk_detal_jual_faktur`
        FOREIGN KEY (`no_faktur`) REFERENCES `faktur_jual` (`no_faktur`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_detal_jual_barang`
        FOREIGN KEY (`id_brg`) REFERENCES `barang` (`id_brg`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detal_jual` (`id_jual`, `no_faktur`, `id_brg`, `ukuran`, `jumlah`, `harga`, `status`) VALUES
(1, 'FJ-0001', 1, 'M', 1, 189000, 1),
(2, 'FJ-0002', 2, 'M', 1, 199000, 1),
(3, 'FJ-0003', 3, 'M', 1, 219000, 1),
(4, 'FJ-0004', 7, 'M', 1, 389000, 1),
(5, 'FJ-0005', 10, 'M', 1, 279000, 1),
(6, 'FJ-0006', 4, 'M', 1, 229000, 0);

-- ============================================================
-- 10. PEMBAYARAN
-- Dipertahankan untuk kompatibilitas data lama. Tidak digunakan
-- sebagai alur checkout pelanggan.
-- ============================================================

DROP TABLE IF EXISTS `pembayaran`;
CREATE TABLE `pembayaran` (
    `id_bayar` INT NOT NULL AUTO_INCREMENT,
    `no_faktur` VARCHAR(30) NOT NULL,
    `jenis` VARCHAR(15) NOT NULL DEFAULT 'cash',
    `cicilan_ke` INT NOT NULL DEFAULT 0,
    `jumlah` BIGINT NOT NULL DEFAULT 0,
    `bank_tujuan` VARCHAR(60) DEFAULT NULL,
    `bukti` VARCHAR(255) DEFAULT NULL,
    `tgl_bayar` DATETIME DEFAULT NULL,
    `status` VARCHAR(15) NOT NULL DEFAULT 'pending',
    `catatan` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id_bayar`),
    KEY `idx_pembayaran_faktur` (`no_faktur`),
    CONSTRAINT `fk_pembayaran_faktur`
        FOREIGN KEY (`no_faktur`) REFERENCES `faktur_jual` (`no_faktur`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 11. REKENING TOKO
-- ============================================================

DROP TABLE IF EXISTS `rekening_toko`;
CREATE TABLE `rekening_toko` (
    `id_rek` INT NOT NULL AUTO_INCREMENT,
    `bank` VARCHAR(40) NOT NULL,
    `no_rek` VARCHAR(40) NOT NULL,
    `atas_nama` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_rek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rekening_toko` (`id_rek`, `bank`, `no_rek`, `atas_nama`) VALUES
(1, 'BCA', '1234567890', 'Hanata Batik'),
(2, 'Mandiri', '9876543210', 'Hanata Batik'),
(3, 'BNI', '5566778899', 'Hanata Batik');

-- ============================================================
-- 12. BERITA TOKO
-- ============================================================

DROP TABLE IF EXISTS `berita_toko`;
CREATE TABLE `berita_toko` (
    `id_berita` INT NOT NULL AUTO_INCREMENT,
    `judul` VARCHAR(200) NOT NULL,
    `deskripsi` TEXT NOT NULL,
    `gambar` VARCHAR(255) DEFAULT NULL,
    `kategori` VARCHAR(100) DEFAULT 'Info',
    `tanggal` DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (`id_berita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `berita_toko` (`id_berita`, `judul`, `deskripsi`, `gambar`, `kategori`, `tanggal`) VALUES
(1, 'Promo Batik Akhir Bulan', 'Dapatkan penawaran menarik untuk berbagai koleksi batik Hanata Batik selama periode promo.', '', 'Promo', '2026-05-25'),
(2, 'Koleksi Batik Terbaru', 'Koleksi batik pria, wanita, premium, casual, keluarga, dan couple terbaru tersedia di Hanata Batik.', '', 'Koleksi Baru', '2026-05-25');

-- ============================================================
-- 13. DATA AWAL PEMBAYARAN LAMA (OPSIONAL / KOMPATIBILITAS)
-- ============================================================

INSERT INTO `pembayaran`
(`no_faktur`, `jenis`, `cicilan_ke`, `jumlah`, `bank_tujuan`, `bukti`, `tgl_bayar`, `status`, `catatan`) VALUES
('FJ-0003', 'transfer', 0, 219000, 'BCA', NULL, '2026-04-05 11:00:00', 'approved', 'Data historis'),
('FJ-0004', 'transfer', 0, 389000, 'Mandiri', NULL, '2026-04-12 14:00:00', 'approved', 'Data historis'),
('FJ-0005', 'transfer', 0, 279000, 'BNI', NULL, '2026-04-16 14:00:00', 'approved', 'Data historis');

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'DATABASE TOKO HANATA BATIK BERHASIL DIBUAT' AS hasil;
SHOW TABLES;
SELECT COUNT(*) AS jumlah_type FROM `type`;
SELECT COUNT(*) AS jumlah_barang FROM `barang`;
SELECT COUNT(*) AS jumlah_member FROM `member`;
SELECT COUNT(*) AS jumlah_order FROM `faktur_jual`;
SELECT COUNT(*) AS jumlah_detail_order FROM `detal_jual`;

-- ============================================================
-- 13. PENGATURAN HERO BERANDA
-- ============================================================
DROP TABLE IF EXISTS `hero_settings`;
CREATE TABLE `hero_settings` (
  `id_hero` INT NOT NULL AUTO_INCREMENT,
  `eyebrow` VARCHAR(120) NOT NULL DEFAULT 'Koleksi batik pilihan',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `button_text` VARCHAR(80) NOT NULL DEFAULT 'Belanja Koleksi',
  `button_link` VARCHAR(255) NOT NULL DEFAULT 'koleksi.php',
  `image1` VARCHAR(255) DEFAULT '',
  `image2` VARCHAR(255) DEFAULT '',
  `image3` VARCHAR(255) DEFAULT '',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_hero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `hero_settings` (`id_hero`,`eyebrow`,`title`,`description`,`button_text`,`button_link`,`image1`,`image2`,`image3`) VALUES
(1,'Koleksi batik pilihan','Batik yang terasa modern, tetap membawa cerita Nusantara.','Temukan kemeja, atasan wanita, batik couple, dan koleksi premium yang dibuat untuk membuat penampilan sehari-hari lebih berkarakter.','Belanja Koleksi','koleksi.php','assets/pic/hanata-hero.png','assets/pic/hanata-hero-fabric.jpg','assets/pic/hanata-hero-look.jpg');


-- ============================================================
-- INFORMASI TOKO / KONTAK WEBSITE
-- ============================================================
DROP TABLE IF EXISTS `store_settings`;
CREATE TABLE `store_settings` (
    `id_store` INT NOT NULL AUTO_INCREMENT,
    `store_name` VARCHAR(120) NOT NULL DEFAULT 'Hanata Batik',
    `business` VARCHAR(160) NOT NULL DEFAULT 'Penjualan pakaian batik berkualitas',
    `address` VARCHAR(255) NOT NULL DEFAULT 'Pekanbaru, Riau',
    `phone` VARCHAR(30) NOT NULL DEFAULT '082284326992',
    `whatsapp` VARCHAR(30) NOT NULL DEFAULT '6282284326992',
    `email` VARCHAR(120) NOT NULL DEFAULT 'hanatakreasiindonesia@gmail.com',
    `hours_weekday` VARCHAR(120) NOT NULL DEFAULT 'Senin - Sabtu, 08.00 - 17.00',
    `hours_sunday` VARCHAR(120) NOT NULL DEFAULT 'Minggu, 09.00 - 14.00',
    `about_text` TEXT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_store`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `store_settings`
(`id_store`,`store_name`,`business`,`address`,`phone`,`whatsapp`,`email`,`hours_weekday`,`hours_sunday`,`about_text`)
VALUES
(1,'Hanata Batik','Penjualan pakaian batik berkualitas','Pekanbaru, Riau','082284326992','6282284326992','hanatakreasiindonesia@gmail.com','Senin - Sabtu, 08.00 - 17.00','Minggu, 09.00 - 14.00','Hanata Batik menghadirkan koleksi batik untuk pria, wanita, keluarga, couple, casual, dan premium dengan pengalaman belanja yang sederhana.');

