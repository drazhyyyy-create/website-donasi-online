CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- Masukkan user admin
TRUNCATE TABLE admin;
INSERT INTO admin (username, password) VALUES
('zidan', '$2y$10$tHO9GHgfBVLlsYmYf4N6/.AxDL85xgCW/9Cu.W3R6kKwZm/X/5M3u'),
('raihan', '$2y$10$tHO9GHgfBVLlsYmYf4N6/.AxDL85xgCW/9Cu.W3R6kKwZm/X/5M3u'),
('fadhil', '$2y$10$tHO9GHgfBVLlsYmYf4N6/.AxDL85xgCW/9Cu.W3R6kKwZm/X/5M3u'),
('jonathan', '$2y$10$tHO9GHgfBVLlsYmYf4N6/.AxDL85xgCW/9Cu.W3R6kKwZm/X/5M3u'),
('evan', '$2y$10$tHO9GHgfBVLlsYmYf4N6/.AxDL85xgCW/9Cu.W3R6kKwZm/X/5M3u'),
('heru', '$2y$10$tHO9GHgfBVLlsYmYf4N6/.AxDL85xgCW/9Cu.W3R6kKwZm/X/5M3u');
