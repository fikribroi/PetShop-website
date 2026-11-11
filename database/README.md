# Panduan Basis Data PawHouse (`db_petshop`)

Dokumen ini memandu inisialisasi basis data tunggal **`db_petshop`**, struktur 8 tabel InnoDB berelasi, data dummy bawaan, serta kumpulan query JOIN & agregasi untuk **Tugas Besar Sistem Basis Data**.

---

## 1. Spesifikasi Teknis Basis Data

- **Database Engine:** MySQL / MariaDB (InnoDB)
- **Nama Database:** `db_petshop`
- **Charset / Collation:** `utf8mb4` / `utf8mb4_unicode_ci`
- **File Database:** **Hanya 1 file SQL tunggal** (`database/db_petshop.sql`)
- **Jumlah Tabel:** 8 Tabel Berelasi:
  1. `users` — Akun Administrator dan Staf Pet Shop
  2. `categories` — Kategori Produk & Pakan
  3. `products` — Inventaris Produk, Harga, dan Stok
  4. `customers` — Data Pelanggan / Pemilik Hewan
  5. `pets` — Data Hewan Peliharaan Pelanggan
  6. `services` — Layanan Hotel Penitipan & Perawatan (Grooming)
  7. `bookings` — Reservasi Penitipan & Perawatan Hewan
  8. `transactions` — Pencatatan Transaksi Keuangan & Pembayaran
- **Ketentuan Minimum Data:** Setiap tabel telah terisi minimal 10 baris data dummy realistis berbahasa Indonesia (Total 84 data).

---

## 2. Cara Impor 1 File SQL (`db_petshop.sql`)

Seluruh proses pembuatan database, struktur 8 tabel, relasi foreign key, data dummy awal, dan query pengujian sudah digabungkan ke dalam **SATU file SQL saja**: `database/db_petshop.sql`.

### Cara A: Melalui phpMyAdmin (Rekomendasi)
1. Pastikan modul **Apache** dan **MySQL** di Laragon atau XAMPP sudah berjalan.
2. Buka browser dan akses `http://localhost/phpmyadmin/`.
3. Klik menu **Import** di bagian atas (tidak perlu membuat database manual terlebih dahulu, skrip SQL otomatis menjalankan `CREATE DATABASE IF NOT EXISTS db_petshop; USE db_petshop;`).
4. Klik tombol **Choose File / Pilih File**, lalu pilih file:
   ```
   database/db_petshop.sql
   ```
5. Gulir ke bawah dan klik tombol **Import / Kirim**.
6. Seluruh database, 8 tabel, constraint foreign key, serta data awal langsung terpasang dan siap digunakan.

### Cara B: Melalui Terminal / Command Prompt
Jalankan perintah berikut:
```bash
mysql -u root -p < database/db_petshop.sql
```
*(Tekan Enter jika root MySQL tanpa password)*.

---

## 3. Verifikasi Data Dummy (Minimal 10 Data per Tabel)

| No | Nama Tabel | Total Data | Status Ketentuan Tugas |
|---|---|---|---|
| 1 | `users` | 10 Data | ✅ Memenuhi (>= 10) |
| 2 | `categories` | 10 Data | ✅ Memenuhi (>= 10) |
| 3 | `products` | 12 Data | ✅ Memenuhi (>= 10) |
| 4 | `customers` | 10 Data | ✅ Memenuhi (>= 10) |
| 5 | `pets` | 12 Data | ✅ Memenuhi (>= 10) |
| 6 | `services` | 10 Data | ✅ Memenuhi (>= 10) |
| 7 | `bookings` | 10 Data | ✅ Memenuhi (>= 10) |
| 8 | `transactions` | 10 Data | ✅ Memenuhi (>= 10) |

---

## 4. Akun Bawaan untuk Pengujian Sistem

| Peran (Role) | Username | Password Default | Akses |
|---|---|---|---|
| **Super Admin** | `admin` | `admin123` | Akses penuh dashboard, kelola seluruh 8 tabel CRUD |
| **Staf Caretaker** | `sarah` | `admin123` | Staf pengasuh anabul & pengelola hotel hewan |
| **Staf Groomer** | `budi` | `admin123` | Staf salon, grooming & perawatan anabul |
| **Staf Kasir** | `dina` | `admin123` | Staf pencatatan pembayaran & kasir toko |

> **Catatan Keamanan:** Password telah di-hash secara aman menggunakan algoritma Bcrypt (`password_hash`).

---

## 5. Kumpulan Query JOIN & Agregasi (DQL)

Seluruh query berikut sudah disertakan di **Bagian 3** pada file `db_petshop.sql` dan dapat langsung di-copy untuk demo atau laporan:

1. **JOIN Produk + Kategori (`INNER JOIN`):** Menampilkan nama produk, kategori, harga, dan sisa stok.
2. **JOIN Hewan + Pemilik (`INNER JOIN`):** Menampilkan identitas hewan, ras, usia, dan kontak pemilik hewan.
3. **JOIN 4 Tabel Booking Penitipan (`INNER JOIN` 4 Tabel):** Menampilkan kode booking, nama pemilik, kontak, nama hewan, jenis layanan, tanggal mulai, tanggal selesai, estimasi hari, dan status.
4. **JOIN Transaksi + Pelanggan + Booking (`LEFT JOIN`):** Menampilkan rekap pembayaran baik dari penitipan maupun transaksi retail produk.
5. **Agregasi Dashboard:** Menghitung metrik total produk, kategori, pelanggan, hewan, booking aktif, dan total pendapatan lunas.

---

## 6. Diagram Relasi Entitas (ERD)

File diagram Mermaid Entity Relationship Diagram tersedia pada:
- [`database/erd.mmd`](erd.mmd)
