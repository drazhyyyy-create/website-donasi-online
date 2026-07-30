# SiPeduli — Website Donasi Online dengan Prediksi AI

Platform donasi online berbasis web yang memungkinkan pengguna melakukan donasi, melaporkan pengaduan, serta menyediakan fitur prediksi tren donasi menggunakan model AI sederhana (regresi linear) berbasis data historis.

## ✨ Fitur Utama

- **Manajemen Donasi** — pengguna dapat melakukan donasi, melihat riwayat, dan admin dapat mengelola data donasi & donatur (tambah, edit, hapus)
- **Autentikasi Pengguna** — login, register, dan manajemen biodata/profil pengguna
- **Laporan Pengaduan** — form pengaduan dari pengguna yang dapat dipantau dan diperbarui statusnya oleh admin
- **Prediksi AI** — analisis dan prediksi tren donasi (nominal, kategori, waktu) menggunakan regresi linear berbasis data CSV historis, divisualisasikan dalam bentuk grafik
- **Cetak Laporan PDF** — export laporan donasi ke PDF menggunakan library FPDF
- **Notifikasi Email** — pengiriman email otomatis (konfirmasi, notifikasi) menggunakan PHPMailer
- **Dashboard Admin** — panel khusus admin untuk mengelola data donasi, donatur, dan pengaduan

## 🛠️ Tech Stack

| Kategori | Teknologi |
|---|---|
| Backend | PHP (native/procedural) |
| Database | MySQL (mysqli) |
| Email | PHPMailer |
| PDF Generator | FPDF |
| AI/Prediksi | Regresi Linear (custom PHP implementation) berbasis data CSV |
| Server Lokal | XAMPP (Apache + MySQL) |

## 📁 Struktur Folder

```
├── Admin/              # Modul manajemen untuk admin (edit, hapus, kelola donasi)
├── Function/            # Fungsi-fungsi reusable (login, register, koneksi DB, CRUD)
├── Prediksi_AI/         # Modul prediksi & visualisasi grafik AI
├── fpdf/                # Library FPDF untuk generate laporan PDF
├── vendor/              # Dependency Composer (PHPMailer)
├── uploads/             # Penyimpanan file upload pengguna
├── img/, video/         # Aset statis
├── *.sql                # Skema & seed database
└── *.php                # Halaman-halaman utama aplikasi
```

## 🚀 Cara Menjalankan (Local Development)

1. Clone repository ini ke folder `htdocs` XAMPP:
   ```
   git clone https://github.com/drazhyyyy-create/website-donasi-online.git
   ```
2. Install dependency Composer:
   ```
   composer install
   ```
3. Import database:
   - Buat database baru bernama `donasi` di phpMyAdmin
   - Import file `.sql` yang tersedia (`sipeduli_db.sql`, `donasi.sql`, `user_fixed.sql`, `akumulasi.sql`)
4. Sesuaikan konfigurasi koneksi database di `koneksi.php` jika diperlukan
5. Jalankan Apache & MySQL melalui XAMPP Control Panel
6. Akses aplikasi melalui `http://localhost/website-donasi-online`

## 📸 Screenshot

_Tambahkan screenshot tampilan aplikasi di sini (halaman utama, dashboard admin, grafik prediksi AI, dll)_

## 👥 Kontributor

Dikembangkan oleh Kelompok 7 sebagai bagian dari proyek Tugas Akhir.

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik.
