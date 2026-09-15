CREATE TABLE IF NOT EXISTS store_settings (
  id_store INT NOT NULL AUTO_INCREMENT,
  store_name VARCHAR(120) NOT NULL DEFAULT 'Hanata Batik',
  business VARCHAR(160) NOT NULL DEFAULT 'Penjualan pakaian batik berkualitas',
  address VARCHAR(255) NOT NULL DEFAULT 'Pekanbaru, Riau',
  phone VARCHAR(30) NOT NULL DEFAULT '082284326992',
  whatsapp VARCHAR(30) NOT NULL DEFAULT '6282284326992',
  email VARCHAR(120) NOT NULL DEFAULT 'hanatakreasiindonesia@gmail.com',
  hours_weekday VARCHAR(120) NOT NULL DEFAULT 'Senin - Sabtu, 08.00 - 17.00',
  hours_sunday VARCHAR(120) NOT NULL DEFAULT 'Minggu, 09.00 - 14.00',
  about_text TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_store)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO store_settings (id_store) VALUES (1) ON DUPLICATE KEY UPDATE id_store=id_store;
