-- Struktur tabel donatur dengan bukti transfer dan foto barang
DROP TABLE IF EXISTS donatur;

CREATE TABLE donatur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(50) NOT NULL,
  email VARCHAR(50) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  jenis_donasi ENUM('Dana', 'Pakaian', 'Sembako', 'Buku') NOT NULL,
  bukti_transfer VARCHAR(100) DEFAULT NULL,
  bukti_barang VARCHAR(100) DEFAULT NULL,
  tanggal_donasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contoh data dummy
INSERT INTO donatur (nama, email, kategori, jenis_donasi, bukti_transfer, bukti_barang) VALUES
('Fahmi', 'fahmi@gmail.com', 'Donasi Kesehatan', 'Dana', 'bukti_fahmi.jpg', NULL),
('Budi', 'budi@mail.com', 'Donasi Pendidikan', 'Buku', NULL, 'bukti_buku_budi.jpg'),
('Sinta', 'sinta@mail.com', 'Donasi Masjid', 'Sembako', NULL, 'foto_sembako_sinta.jpg');
