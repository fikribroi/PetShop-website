

-- ===============================================================
-- FILE BASIS DATA TUNGGAL (SINGLE ALL-IN-ONE SQL FILE)
-- SISTEM PET SHOP & PENITIPAN HEWAN PELIHARAAN (PAWHOUSE)
-- ===============================================================
-- Database : db_petshop
-- File     : database/db_petshop.sql
-- Penulis  : Tim Tubes Sistem Pet Shop & Penitipan Hewan
-- 
-- File ini merupakan SATU-SATUNYA file SQL yang dibutuhkan sistem.
-- Berisi 3 bagian terintegrasi:
--   1. DDL: Pembuatan database dan struktur 8 tabel InnoDB berelasi
--   2. DML: Data dummy realistis (minimal 10 baris per tabel, total 84 data)
--   3. DQL: Query JOIN dan agregasi untuk presentasi tugas & dashboard
--
-- Petunjuk Impor Cepat (Sekali Eksekusi):
--   - phpMyAdmin : Tab "Import" -> Pilih file ini -> Klik "Import/Kirim"
--   - Terminal   : mysql -u root < database/db_petshop.sql
-- ===============================================================

CREATE DATABASE IF NOT EXISTS `db_petshop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_petshop`;

-- ===============================================================
-- BAGIAN 1: STRUKTUR TABEL (DDL)
-- ===============================================================

-- Bersihkan tabel jika sebelumnya sudah ada (sesuai urutan foreign key)
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- 1. Tabel users (Admin & Staf Pet Shop)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'staff') NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel categories (Kategori Produk Pet Shop)
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel products (Inventaris Produk & Pakan)
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) 
    REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel customers (Pelanggan / Pemilik Hewan)
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NULL,
  `address` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel pets (Hewan Peliharaan Pelanggan)
CREATE TABLE `pets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `breed` VARCHAR(100) NULL,
  `age` INT NOT NULL DEFAULT 1,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pets_customer` FOREIGN KEY (`customer_id`) 
    REFERENCES `customers` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel services (Layanan Hotel Penitipan & Perawatan/Grooming)
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel bookings (Reservasi Penitipan & Layanan)
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(30) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `pet_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `notes` TEXT NULL,
  `status` ENUM('Menunggu', 'Dikonfirmasi', 'Sedang Dititipkan', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) 
    REFERENCES `customers` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_pet` FOREIGN KEY (`pet_id`) 
    REFERENCES `pets` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) 
    REFERENCES `services` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabel transactions (Pencatatan Keuangan / Pembayaran)
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `booking_id` INT NULL,
  `transaction_date` DATE NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  `payment_status` ENUM('Lunas', 'Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_transactions_customer` FOREIGN KEY (`customer_id`) 
    REFERENCES `customers` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_transactions_booking` FOREIGN KEY (`booking_id`) 
    REFERENCES `bookings` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================================
-- BAGIAN 2: DATA AWAL / SEEDER (DML)
-- Minimal 10 data realistis per tabel (Total 84 baris data dummy)
-- ===============================================================

-- 1. Data Dummy users (10 data)
INSERT INTO `users` (`id`, `name`, `username`, `password`, `role`) VALUES
(1, 'Administrator Utama', 'admin', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'admin'),
(2, 'Sarah Caretaker', 'sarah', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(3, 'Budi Groomer', 'budi', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(4, 'Dina Kasir', 'dina', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(5, 'Rian Supervisor', 'rian', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'admin'),
(6, 'Mega Paramita', 'mega', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(7, 'Faisal Adiputra', 'faisal', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(8, 'Anisa Rahma', 'anisa', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(9, 'Hendra Gunawan', 'hendra', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'staff'),
(10, 'Taufik Hidayat', 'taufik', '$2y$10$BBL98X7hN4liX/kVXYKG7ebSmVCtLXRv/jRrVHYPeDLBGTtRpuasi', 'admin');

-- 2. Data Dummy categories (10 data)
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Makanan Kucing', 'Pakan kering (dry food) dan basah (wet food) bernutrisi untuk kucing.'),
(2, 'Makanan Anjing', 'Pakan berkualitas tinggi untuk anjing ras kecil, sedang, dan besar.'),
(3, 'Pasir & Litter Box', 'Pasir gumpal wangi, pasir tahu (tofu), zeolit, dan bak pasir higienis.'),
(4, 'Kandang & Pet Carrier', 'Kandang besi lipat, carrier travel, dan tas astronot untuk mobilitas hewan.'),
(5, 'Obat & Vitamin', 'Suplemen imun, vitamin bulu, obat kutu tetes, dan minyak ikan salmon.'),
(6, 'Shampo & Grooming Tools', 'Shampo anti-kutu/jamur, sisir bulu shedding, gunting kuku, dan handuk microfiber.'),
(7, 'Aksesoris & Kalung', 'Kalung lonceng, tali tuntun (leash), harness rompi, dan mangkuk makan anti-tumpah.'),
(8, 'Mainan Hewan', 'Garukan kucing (scratching post), bola gigit anjing, laser pointer, dan catnip.'),
(9, 'Pakan Kelinci & Rodent', 'Pelet rumput hay timothy, serbuk kayu steril, dan camilan kelinci/hamster.'),
(10, 'Snack & Treats', 'Camilan creamy lickable treat, dental stick, dan biskuit renyah hewan.');

-- 3. Data Dummy products (12 data)
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`) VALUES
(1, 1, 'Royal Canin Kitten 2kg', 'Makanan kucing formula khusus untuk anak kucing usia 4-12 bulan, mendukung imun dan pencernaan.', 285000.00, 24, 'rc-kitten.jpg'),
(2, 1, 'Whiskas Wet Food Tuna 85g', 'Pouch makanan basah rasa tuna segar kaya omega 3 & 6.', 9500.00, 150, 'whiskas-pouch.jpg'),
(3, 2, 'Pro Plan Adult Dog Salmon 2.5kg', 'Formula sensitif untuk anjing dewasa dengan daging salmon asli tanpa jagung.', 340000.00, 18, 'proplan-dog.jpg'),
(4, 2, 'Pedigree Dentastix Medium Dog', 'Camilan pembersih karang gigi untuk anjing ukuran sedang isi 7 stick.', 42000.00, 45, 'pedigree-dentastix.jpg'),
(5, 3, 'Pasir Kucing Tofu Soya Flavour 6L', 'Pasir organik ramah lingkungan, menggumpal cepat dan dapat diflush ke toilet.', 65000.00, 60, 'pasir-tofu.jpg'),
(6, 3, 'Bentonite Clumping Cat Litter 10L', 'Pasir gumpal wangi lavender daya serap tinggi pengendali bau optimal.', 55000.00, 40, 'bentonite-litter.jpg'),
(7, 4, 'Pet Carrier Cargo Standar IATA M', 'Kotak pengangkut hewan berstandar maskapai penerbangan untuk berat hingga 8kg.', 215000.00, 12, 'pet-cargo.jpg'),
(8, 5, 'Virbac Megaderm 28 Sachet', 'Suplemen nutrisi dermatologi untuk kesehatan kulit dan kemilau bulu.', 220000.00, 15, 'virbac-megaderm.jpg'),
(9, 6, 'Shampo Medicated Anti-Fungal 250ml', 'Shampo terapi ketombe, jamur microsporum, dan bakteri gatal pada kulit hewan.', 88000.00, 30, 'shampo-medicated.jpg'),
(10, 7, 'Harness Rompi Reflektif + Leash', 'Tali dada empuk anti-cekik dengan strip pemantul cahaya untuk jalan malam.', 68000.00, 28, 'harness-rompi.jpg'),
(11, 8, 'Cat Tree Scratching Tower 3 Tingkat', 'Pohon garukan kokoh dengan tali rami alami dan rumah persembunyian empuk.', 375000.00, 8, 'cat-tree.jpg'),
(12, 10, 'Me-O Creamy Treats Salmon 4x15g', 'Snack basah cair favorit kucing dengan taurin dan prebiotic.', 25000.00, 95, 'meo-creamy.jpg');

-- 4. Data Dummy customers (10 data)
INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`) VALUES
(1, 'Dimas Pratama', '081234567890', 'dimas.pratama@gmail.com', 'Jl. Merpati No. 12, Sukajadi, Bandung'),
(2, 'Aditya Nugraha', '081398765432', 'aditya.nugraha@yahoo.com', 'Jl. Anggrek Melati Blok B4, Cimahi'),
(3, 'Siti Nurhaliza', '085711223344', 'siti.nur@gmail.com', 'Komplek Permata Buah Batu No. 45, Bandung'),
(4, 'Kevin Wijaya', '082199887766', 'kevin.wijaya@outlook.com', 'Jl. Dago Asri No. 8, Coblong, Bandung'),
(5, 'Rina Anggraini', '087855443322', 'rina.anggraini@gmail.com', 'Jl. Pasir Kaliki No. 102, Bandung'),
(6, 'Bayu Wicaksono', '081288334455', 'bayu.wicak@hotmail.com', 'Jl. Cihampelas No. 56, Bandung'),
(7, 'Nadya Amanda', '085812345678', 'nadya.amanda@gmail.com', 'Jl. Gandapura No. 23, Riau, Bandung'),
(8, 'Rizky Firmansyah', '089677889900', 'rizky.firman@gmail.com', 'Jl. Antapani Raya No. 78, Bandung'),
(9, 'Dewi Lestari', '081344556677', 'dewi.lestari@gmail.com', 'Jl. Setiabudhi Regency Kav 5, Bandung'),
(10, 'Farhan Maulana', '082233445566', 'farhan.m@gmail.com', 'Jl. Gegerkalong Hilir No. 19, Bandung');

-- 5. Data Dummy pets (12 data)
INSERT INTO `pets` (`id`, `customer_id`, `name`, `type`, `breed`, `age`, `notes`) VALUES
(1, 1, 'Mochi', 'Kucing', 'British Shorthair', 2, 'Sangat ramah, menyukai snack basah, vaksin lengkap.'),
(2, 1, 'Luna', 'Kucing', 'Persia Peaknose', 3, 'Bulu lebat putih, sensitif pada mata berair.'),
(3, 2, 'Rocky', 'Anjing', 'Golden Retriever', 4, 'Aktif, butuh jalan pagi 15 menit, alergi daging ayam.'),
(4, 3, 'Milo', 'Kucing', 'Domestic Shorthair', 1, 'Lincah, suka melompat, baru disteril bulan lalu.'),
(5, 4, 'Bruno', 'Anjing', 'French Bulldog', 3, 'Napas pendek jika terlalu lelah, suka berada di ruangan AC.'),
(6, 5, 'Chiki', 'Kelinci', 'Holland Lop', 1, 'Telinga turun, diet wajib hay timothy segar setiap pagi.'),
(7, 6, 'Simba', 'Kucing', 'Maine Coon', 5, 'Tubuh besar 8kg, bulu tebal, tidak suka disentuh ekornya.'),
(8, 7, 'Bella', 'Anjing', 'Pomeranian', 2, 'Bulu tebal oranye, manja dan sering menggonggong pada orang baru.'),
(9, 8, 'Kuro', 'Kucing', 'Scottish Fold', 2, 'Telinga lipat, tenang dan gemar tidur siang di bantalan empuk.'),
(10, 9, 'Oreo', 'Kucing', 'Domestic Tuxedo', 3, 'Sehat bugar, suka bermain laser dan bola kerincing.'),
(11, 10, 'Coco', 'Anjing', 'Poodle Mini', 2, 'Rambut ikal cokelat, rutin potong bulu dan styling teddy bear.'),
(12, 3, 'Cleo', 'Kucing', 'Anggora Campuran', 2, 'Mata heterokromia (beda warna), nafsu makan sangat baik.');

-- 6. Data Dummy services (10 data)
INSERT INTO `services` (`id`, `name`, `description`, `price`, `duration`) VALUES
(1, 'Penitipan Kucing Reguler', 'Kandang individual nyaman, pembersihan litter box 3x sehari, update foto & video via WA.', 60000.00, '1 Hari'),
(2, 'Penitipan Kucing VIP Room', 'Kamar AC bertingkat privasi penuh, cctv akses berkala, main bersama caregiver, free snack.', 95000.00, '1 Hari'),
(3, 'Penitipan Anjing Ras Kecil', 'Kamar bermain indoor, jalan pagi & sore di taman rumput khusus, pemberian makan teratur.', 85000.00, '1 Hari'),
(4, 'Penitipan Anjing Ras Besar', 'Kandang luas berlantai vinyl anti-slip, pengawasan 24 jam, porsi makan & exercise teratur.', 130000.00, '1 Hari'),
(5, 'Grooming Basic Kucing', 'Mandi shampo premium, blow dry lembut, sisir bulu kusut, potong kuku, dan parfum hewan.', 65000.00, '60 Menit'),
(6, 'Grooming Kutu & Jamur', 'Terapi rendam shampo ketoconazole & sulfur medikasi untuk basmi tuntas parasit dan jamur.', 95000.00, '90 Menit'),
(7, 'Grooming Lengkap Anjing', 'Mandi aromaterapi, pembersihan kelenjar anal, blow dry, potong kuku, dan styling bulu wajah.', 110000.00, '90 Menit'),
(8, 'Potong Kuku & Bersih Telinga', 'Perawatan higienis ekspres telinga bebas ear mites dan pemotongan kuku presisi aman.', 30000.00, '20 Menit'),
(9, 'Penitipan Kelinci & Hamster', 'Area tenang bebas dari kebisingan gonggongan, kandang bersih dengan litter box alas kayu.', 45000.00, '1 Hari'),
(10, 'Antar Jemput Hewan (Pick & Drop)', 'Layanan transportasi aman ber-AC untuk penjemputan dan pengantaran pulang hewan peliharaan.', 40000.00, 'Sesuai Jarak');

-- 7. Data Dummy bookings (10 data)
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `pet_id`, `service_id`, `start_date`, `end_date`, `notes`, `status`) VALUES
(1, 'PAW-20261101-001', 1, 1, 2, '2026-11-01', '2026-11-05', 'Bawakan makanan basah sendiri 1 pouch per malam.', 'Selesai'),
(2, 'PAW-20261103-002', 2, 3, 4, '2026-11-03', '2026-11-07', 'Jangan diberikan pakan ayam, ada alergi.', 'Selesai'),
(3, 'PAW-20261108-003', 3, 4, 1, '2026-11-08', '2026-11-12', 'Titip kucing karena ada acara keluarga keluar kota.', 'Sedang Dititipkan'),
(4, 'PAW-20261109-004', 4, 5, 3, '2026-11-09', '2026-11-14', 'Mohon AC diset 24 derajat, bawa selimut favorit.', 'Sedang Dititipkan'),
(5, 'PAW-20261111-005', 5, 6, 9, '2026-11-11', '2026-11-15', 'Hay timothy sudah disertakan di tas travel.', 'Dikonfirmasi'),
(6, 'PAW-20261112-006', 6, 7, 2, '2026-11-12', '2026-11-16', 'Kucing bertubuh besar, mohon kandang VIP luas.', 'Dikonfirmasi'),
(7, 'PAW-20261113-007', 7, 8, 3, '2026-11-13', '2026-11-15', 'Tolong jangan dicampur saat sesi bermain taman.', 'Menunggu'),
(8, 'PAW-20261114-008', 8, 9, 1, '2026-11-14', '2026-11-17', 'Booking melalui form website, mohon segera dikonfirmasi.', 'Menunggu'),
(9, 'PAW-20261025-009', 9, 10, 1, '2026-10-25', '2026-10-28', 'Dibatalkan karena rencana perjalanan pemilik ditunda.', 'Dibatalkan'),
(10, 'PAW-20261115-010', 10, 11, 3, '2026-11-15', '2026-11-20', 'Sekalian minta grooming lengkap sebelum dijemput pulang.', 'Menunggu');

-- 8. Data Dummy transactions (10 data)
INSERT INTO `transactions` (`id`, `customer_id`, `booking_id`, `transaction_date`, `description`, `total`, `payment_status`) VALUES
(1, 1, 1, '2026-11-01', 'Penitipan VIP Kucing Mochi (4 hari) + Wet Food', 380000.00, 'Lunas'),
(2, 2, 2, '2026-11-03', 'Penitipan Anjing Ras Besar Rocky (4 hari)', 520000.00, 'Lunas'),
(3, 3, 3, '2026-11-08', 'Penitipan Reguler Milo (4 hari)', 240000.00, 'Lunas'),
(4, 4, 4, '2026-11-09', 'Penitipan Bruno (5 hari) + Grooming Lengkap', 535000.00, 'Lunas'),
(5, 5, 5, '2026-11-11', 'Penitipan Kelinci Chiki (4 hari)', 180000.00, 'Belum Lunas'),
(6, 6, 6, '2026-11-12', 'Penitipan VIP Simba (4 hari)', 380000.00, 'Belum Lunas'),
(7, 1, NULL, '2026-11-06', 'Pembelian Royal Canin Kitten 2kg & Mainan Cat Tree', 660000.00, 'Lunas'),
(8, 2, NULL, '2026-11-07', 'Pembelian Pro Plan Dog & Snack Dentastix', 382000.00, 'Lunas'),
(9, 7, NULL, '2026-11-10', 'Layanan Grooming basic & Shampo Medicated', 153000.00, 'Lunas'),
(10, 10, 10, '2026-11-15', 'Penitipan Poodle Coco (5 hari) + Grooming Styling', 535000.00, 'Belum Lunas');

-- ===============================================================
-- BAGIAN 3: QUERY PENGUJIAN & LAPORAN JOIN (DQL)
-- Kumpulan query JOIN dan agregasi untuk presentasi tugas & pengujian
-- ===============================================================

-- ---------------------------------------------------------------
-- Query 1. JOIN Produk + Kategori (Inner Join)
-- Menampilkan nama produk, kategori, harga, dan stok
-- ---------------------------------------------------------------
SELECT 
    p.id,
    p.name AS nama_produk,
    c.name AS nama_kategori,
    p.price AS harga,
    p.stock AS stok,
    p.created_at AS ditambahkan_pada
FROM products p
INNER JOIN categories c ON p.category_id = c.id
ORDER BY c.name ASC, p.name ASC;

-- ---------------------------------------------------------------
-- Query 2. JOIN Hewan Peliharaan + Pelanggan (Pemilik)
-- Menampilkan identitas hewan, ras, umur, dan data kontak pemilik
-- ---------------------------------------------------------------
SELECT 
    pet.id,
    pet.name AS nama_hewan,
    pet.type AS jenis_hewan,
    pet.breed AS ras,
    pet.age AS umur_tahun,
    cust.name AS nama_pemilik,
    cust.phone AS no_telepon,
    cust.email,
    pet.notes AS catatan_khusus
FROM pets pet
INNER JOIN customers cust ON pet.customer_id = cust.id
ORDER BY cust.name ASC;

-- ---------------------------------------------------------------
-- Query 3. JOIN 4 Tabel: Booking + Pelanggan + Hewan + Layanan
-- Menampilkan data lengkap pemesanan penitipan
-- ---------------------------------------------------------------
SELECT 
    b.id,
    b.booking_code,
    cust.name AS nama_pelanggan,
    cust.phone AS kontak_pelanggan,
    pet.name AS nama_hewan,
    pet.type AS jenis_hewan,
    s.name AS nama_layanan,
    s.price AS tarif_layanan,
    b.start_date AS tanggal_mulai,
    b.end_date AS tanggal_selesai,
    DATEDIFF(b.end_date, b.start_date) + 1 AS estimasi_hari,
    b.status AS status_booking,
    b.notes AS catatan
FROM bookings b
INNER JOIN customers cust ON b.customer_id = cust.id
INNER JOIN pets pet ON b.pet_id = pet.id
INNER JOIN services s ON b.service_id = s.id
ORDER BY b.created_at DESC;

-- ---------------------------------------------------------------
-- Query 4. JOIN Transaksi + Pelanggan + Booking (LEFT JOIN)
-- Menampilkan catatan transaksi (baik dari reservasi booking maupun retail)
-- ---------------------------------------------------------------
SELECT 
    t.id AS id_transaksi,
    t.transaction_date AS tanggal_transaksi,
    cust.name AS nama_pelanggan,
    COALESCE(b.booking_code, 'Pembelian Retail') AS referensi_booking,
    t.description AS keterangan,
    t.total,
    t.payment_status AS status_pembayaran
FROM transactions t
INNER JOIN customers cust ON t.customer_id = cust.id
LEFT JOIN bookings b ON t.booking_id = b.id
ORDER BY t.transaction_date DESC;

-- ---------------------------------------------------------------
-- Query 5. Query Agregasi Dashboard: Ringkasan Total Data Sistem
-- Digunakan untuk metrik kartu statistik pada Dashboard Admin
-- ---------------------------------------------------------------
SELECT 
    (SELECT COUNT(*) FROM products) AS total_produk,
    (SELECT COUNT(*) FROM categories) AS total_kategori,
    (SELECT COUNT(*) FROM customers) AS total_pelanggan,
    (SELECT COUNT(*) FROM pets) AS total_hewan,
    (SELECT COUNT(*) FROM bookings) AS total_booking,
    (SELECT COUNT(*) FROM bookings WHERE status = 'Sedang Dititipkan') AS hewan_sedang_dititip,
    (SELECT COUNT(*) FROM bookings WHERE status = 'Menunggu') AS booking_menunggu,
    (SELECT COALESCE(SUM(total), 0) FROM transactions WHERE payment_status = 'Lunas') AS total_pendapatan;
