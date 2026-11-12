# PawHouse — Sistem Pet Shop & Penitipan Hewan Peliharaan

## Website Sistem Pet Shop & Penitipan Hewan Peliharaan (**PawHouse**) yang dibangun menggunakan **PHP Native** dan **MySQL** dengan **PDO Prepared Statements**. Proyek ini dibuat untuk memenuhi tugas kuliah basis data dengan standar kualitas tinggi, tampilan modern responsif menggunakan **Pure Vanilla CSS**.

## 🌟 Fitur Utama Sistem

### 1. Public / Customer Area (Pengunjung)

- **Beranda (Home):**
  - Hero section dengan visual identity yang hangat (_warm cream & deep forest green_).
  - Ringkasan statistik sistem (total pemilik, hewan ditangani, higienitas).
  - Layanan penitipan & hotel anabul unggulan.
  - Produk pet shop unggulan dengan harga dan informasi stok.
  - Panduan alur booking 4 langkah.
  - Kontak, lokasi, dan footer terpadu.
- **Katalog Produk:**
  - Menampilkan seluruh katalog item dari database (`products` JOIN `categories`).
  - Fitur pencarian nama produk dan deskripsi.
  - Filter interaktif berdasarkan kategori produk.
  - Status ketersediaan stok (_Tersedia / Menipis / Habis_).
- **Detail Produk:**
  - Halaman khusus rincian spesifikasi produk, harga resmi, kategori, dan tombol order WhatsApp.
- **Layanan & Penitipan:**
  - Menampilkan daftar kamar hotel hewan, paket spa grooming, dan layanan antar jemput.
  - Durasi layanan dan tarif per hari/sesi.
- **Formulir Booking Penitipan Online:**
  - Pengunjung menginput data pemilik, nomor telepon, nama hewan, jenis hewan, dan pilihan paket penitipan.
  - **Validasi Server-Side:** Validasi format nomor telepon, tanggal mulai tidak boleh di masa lalu, tanggal selesai tidak boleh lebih awal dari tanggal mulai.
  - **Penerbitan Kode Tiket Otomatis:** Menghasilkan kode unik berformat `PAW-YYYYMMDD-XXXX`.
  - Transaksi database atomik (menggunakan PDO Transaction).
- **Cek Status Booking:**
  - Pengunjung dapat melacak status booking tanpa perlu login.
  - Menggunakan kombinasi verifikasi **Kode Booking + Nomor Telepon**.
  - Menampilkan status tiket: _Menunggu_, _Dikonfirmasi_, _Sedang Dititipkan_, _Selesai_, atau _Dibatalkan_.

### 2. Admin Panel (Pengelola)

- **Dashboard Ringkasan:**
  - 8 Kartu statistik (_Total Booking, Perlu Konfirmasi, Sedang Dititip, Total Pendapatan Lunas, Produk, Pelanggan, Hewan, Kategori_).
  - Tabel 5 booking penitipan terbaru dengan relasi 4 tabel.
  - Tabel 5 transaksi keuangan terbaru.
- **Manajemen Kategori Produk (CRUD):** Tambah, lihat, edit, dan hapus kategori (dilengkapi proteksi foreign key).
- **Manajemen Produk Pet Shop (CRUD):** Tambah, lihat, edit, dan hapus produk (dropdown kategori via JOIN, kontrol stok dan harga).
- **Manajemen Pelanggan (CRUD):** Tambah, lihat, edit, dan hapus data kontak pemilik hewan.
- **Manajemen Hewan Peliharaan (CRUD):** Tambah, lihat, edit, dan hapus data anabul dengan dropdown pemilik dari database.
- **Manajemen Layanan & Tarif (CRUD):** Tambah, lihat, edit, dan hapus jenis penitipan/grooming.
- **Manajemen Booking Penitipan:**
  - Monitoring seluruh reservasi dengan 4-tabel JOIN (Pelanggan + Hewan + Layanan).
  - Filter berdasarkan status reservasi dan pencarian tiket/nama.
  - Halaman detail komprehensif (perhitungan durasi hari dan estimasi biaya).
  - Pembaruan status reservasi (_Menunggu -> Dikonfirmasi -> Sedang Dititipkan -> Selesai -> Dibatalkan_).
- **Pencatatan Transaksi & Keuangan (CRUD):**
  - Catat transaksi pembayaran penitipan atau pembelian retail toko.
  - Hubungkan transaksi ke kode tiket booking (relasi opsional).
  - Filter status pembayaran (_Lunas / Belum Lunas_).
- **Keamanan & Autentikasi Admin:**
  - Login terlindungi hash password Bcrypt (`password_verify`).
  - Proteksi Session Admin dengan Session Regeneration untuk mencegah _session fixation_.
  - Token CSRF di seluruh form pengubahan data.
  - Sanitasi output anti-XSS (`htmlspecialchars`).

---

## 🗄️ Struktur Database (`db_petshop`)

Sistem menggunakan database MySQL bernama **`db_petshop`** dengan mesin penyimpanan **InnoDB** dan charset **utf8mb4**. Terdiri dari 8 tabel berelasi:

| No  | Nama Tabel     | Deskripsi                                         | Minimal Baris Data |
| --- | -------------- | ------------------------------------------------- | ------------------ |
| 1   | `users`        | Akun staf & administrator sistem                  | 10 Data            |
| 2   | `categories`   | Klasifikasi kategori produk pet shop              | 10 Data            |
| 3   | `products`     | Katalog produk, harga, dan stok barang            | 12 Data            |
| 4   | `customers`    | Data pelanggan dan pemilik hewan peliharaan       | 10 Data            |
| 5   | `pets`         | Data identitas hewan peliharaan milik pelanggan   | 12 Data            |
| 6   | `services`     | Daftar paket hotel penitipan dan layanan grooming | 10 Data            |
| 7   | `bookings`     | Reservasi penitipan dari pelanggan untuk hewannya | 10 Data            |
| 8   | `transactions` | Catatan transaksi pembayaran dan keuangan         | 10 Data            |

### Aturan Foreign Key & Constraint:

- `products.category_id` ➔ `categories.id` (`ON DELETE RESTRICT`)
- `pets.customer_id` ➔ `customers.id` (`ON DELETE CASCADE`)
- `bookings.customer_id` ➔ `customers.id` (`ON DELETE RESTRICT`)
- `bookings.pet_id` ➔ `pets.id` (`ON DELETE RESTRICT`)
- `bookings.service_id` ➔ `services.id` (`ON DELETE RESTRICT`)
- `transactions.customer_id` ➔ `customers.id` (`ON DELETE RESTRICT`)
- `transactions.booking_id` ➔ `bookings.id` (`ON DELETE SET NULL`)

File diagram ERD tersedia pada: [`database/erd.mmd`](file:///d:/Project/TUBES/database/erd.mmd).

---

## 🔑 Kredensial Login Demo Admin

Gunakan akun berikut untuk mengakses dashboard admin:

- **URL Login:** `http://localhost/TUBES/public/login.php` (atau `http://localhost:8000/public/login.php`)
- **Username:** `admin`
- **Password:** `admin123`

_(Tersedia juga akun staf demo lainnya seperti `sarah`, `budi`, `dina` dengan password yang sama: `admin123`)._

---

## 💻 Panduan Instalasi & Menjalankan

### Cara 1: Menggunakan Laragon / XAMPP (Rekomendasi Tugas Kuliah)

1. Buka aplikasi **Laragon** atau **XAMPP**.
2. Pastikan service **Apache** dan **MySQL** telah aktif (Start All).
3. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Buat database baru bernama `db_petshop` (atau cukup import file SQL).
5. Masuk ke tab **Import**, pilih file:
   ```
   d:/Project/TUBES/database/db_petshop.sql
   ```
   dan klik **Go/Kirim**. Seluruh 8 tabel dan data dummy minimal 10 baris per tabel akan terbuat secara otomatis.
6. Akses website melalui browser di:
   ```
   http://localhost/TUBES/
   ```

### Cara 2: Menggunakan PHP Built-in Server

Jika ingin menjalankan langsung dari folder proyek melalui command prompt / terminal:

```bash
cd d:\Project\TUBES
php -S localhost:8000
```

Buka browser ke alamat:

```
http://localhost:8000
```

---

## 📊 Contoh Query JOIN untuk Presentasi Tugas

Seluruh query JOIN telah diuji dan disatukan langsung pada file [`database/db_petshop.sql`](file:///d:/Project/TUBES/database/db_petshop.sql) (Bagian 3: DQL):

### 1. JOIN Produk + Kategori

```sql
SELECT
    p.id,
    p.name AS nama_produk,
    c.name AS nama_kategori,
    p.price AS harga,
    p.stock AS stok
FROM products p
INNER JOIN categories c ON p.category_id = c.id
ORDER BY c.name ASC, p.name ASC;
```

### 2. JOIN Hewan Peliharaan + Pelanggan (Pemilik)

```sql
SELECT
    pet.name AS nama_hewan,
    pet.type AS jenis_hewan,
    pet.breed AS ras,
    cust.name AS nama_pemilik,
    cust.phone AS no_telepon
FROM pets pet
INNER JOIN customers cust ON pet.customer_id = cust.id;
```

### 3. JOIN 4 Tabel (Booking + Pelanggan + Hewan + Layanan)

```sql
SELECT
    b.booking_code,
    cust.name AS nama_pelanggan,
    pet.name AS nama_hewan,
    s.name AS nama_layanan,
    b.start_date,
    b.end_date,
    b.status
FROM bookings b
INNER JOIN customers cust ON b.customer_id = cust.id
INNER JOIN pets pet ON b.pet_id = pet.id
INNER JOIN services s ON b.service_id = s.id
ORDER BY b.created_at DESC;
```

### 4. LEFT JOIN Transaksi + Pelanggan + Booking

```sql
SELECT
    t.id,
    t.transaction_date,
    cust.name AS nama_pelanggan,
    COALESCE(b.booking_code, 'Pembelian Retail') AS referensi_booking,
    t.description,
    t.total,
    t.payment_status
FROM transactions t
INNER JOIN customers cust ON t.customer_id = cust.id
LEFT JOIN bookings b ON t.booking_id = b.id;
```

---

## 📁 Struktur Direktori Proyek

```text
TUBES/
├── admin/                         # Area Dashboard & CRUD Pengelola
│   ├── index.php                  # Halaman ringkasan dashboard statistik
│   ├── logout.php                 # Proses destroy session admin
│   ├── categories/                # CRUD Kategori Produk
│   ├── products/                  # CRUD Produk Pet Shop
│   ├── customers/                 # CRUD Data Pelanggan
│   ├── pets/                      # CRUD Data Hewan Peliharaan
│   ├── services/                  # CRUD Layanan & Tarif Penitipan
│   ├── bookings/                  # Kelola Status & Rincian Booking
│   └── transactions/              # CRUD Transaksi Keuangan
├── assets/
│   ├── css/
│   │   ├── style.css              # Styling Vanilla CSS Public Website
│   │   └── admin.css              # Styling Vanilla CSS Admin Dashboard
│   ├── js/
│   │   └── main.js                # Interaktivitas UI & Konfirmasi Hapus
│   └── images/                    # Asset gambar pendukung
├── config/
│   └── database.php               # Konfigurasi PDO connection & URL helper
├── includes/
│   ├── auth.php                   # Autentikasi & proteksi akses session
│   ├── functions.php              # Helper XSS escaping, CSRF, flash, format
│   ├── header.php                 # Layout navbar public
│   ├── footer.php                 # Layout footer public
│   ├── admin-header.php           # Layout header & navigasi atas admin
│   ├── admin-sidebar.php          # Komponen sidebar admin 8 menu
│   └── admin-footer.php           # Script & penutup layout admin
├── database/
│   ├── db_petshop.sql             # SQL Tunggal: DDL (8 tabel), DML (10+ data per tabel), & Query DQL
│   └── erd.mmd                    # Mermaid Entity Relationship Diagram
├── public/
│   ├── index.php                  # Halaman Beranda Public
│   ├── products.php               # Katalog & filter produk
│   ├── product-detail.php         # Rincian detail produk
│   ├── services.php               # Katalog layanan & penitipan
│   ├── booking.php                # Form reservasi online & tiket
│   ├── booking-status.php         # Lacak status tiket booking
│   └── login.php                  # Halaman login admin/staf
├── index.php                      # Root entry point (auto-redirect)
└── README.md                      # Dokumentasi lengkap proyek
```

---

## 🧪 Hasil Pengujian Fungsional (Tahap 7)

| No  | Modul Pengujian              | Skenario                                                                   | Hasil                                       |
| --- | ---------------------------- | -------------------------------------------------------------------------- | ------------------------------------------- |
| 1   | **Koneksi Database**         | PDO terkoneksi ke MySQL 9.1 / 8.x dengan UTF8MB4                           | **BERHASIL**                                |
| 2   | **Import Database**          | Import `db_petshop.sql` ke MySQL via phpMyAdmin/CLI                        | **BERHASIL (8 tabel terisi >= 10 data)**    |
| 3   | **Login Admin**              | Login dengan `admin` / `admin123`                                          | **BERHASIL** (Session terbentuk)            |
| 4   | **Login Gagal**              | Login dengan password salah                                                | **BERHASIL** (Pesan kesalahan muncul)       |
| 5   | **Proteksi Halaman Admin**   | Akses direct URL admin tanpa session login                                 | **BERHASIL** (Otomatis redirect ke login)   |
| 6   | **Logout Admin**             | Klik tombol logout                                                         | **BERHASIL** (Session & cookie dibersihkan) |
| 7   | **Public Booking Online**    | Pengunjung isi form booking penitipan                                      | **BERHASIL** (Data tersimpan, tiket terbit) |
| 8   | **Validasi Tanggal Booking** | Memilih tanggal lampau atau selesai < mulai                                | **BERHASIL** (Validasi menolak input)       |
| 9   | **Cek Status Tiket**         | Memasukkan kode tiket + nomor telepon                                      | **BERHASIL** (Status akurat ditampilkan)    |
| 10  | **CRUD 6 Tabel Master**      | Tambah, Edit, Hapus Kategori, Produk, Pelanggan, Hewan, Layanan, Transaksi | **BERHASIL**                                |
| 11  | **Update Status Booking**    | Admin mengubah status Menunggu ➔ Sedang Dititipkan ➔ Selesai               | **BERHASIL**                                |
| 12  | **Integrasi JOIN**           | Menampilkan relasi nama pelanggan, hewan, dan layanan pada tabel           | **BERHASIL (4 query JOIN teruji)**          |
| 13  | **Keamanan CSRF & XSS**      | Pengiriman form dicek token CSRF, output di-escape                         | **BERHASIL**                                |
| 14  | **Tampilan Responsif**       | Pengujian layout desktop, tablet, dan smartphone                           | **BERHASIL (Pure Vanilla CSS)**             |

# PetShop-website
