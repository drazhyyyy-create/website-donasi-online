CREATE TABLE donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_donatur VARCHAR(100),
    kategori VARCHAR(50), -- contoh: pendidikan, kesehatan
    jumlah INT,
    status VARCHAR(20),   -- contoh: 'terverifikasi'
    tanggal DATE
);