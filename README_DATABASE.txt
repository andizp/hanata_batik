HANATA BATIK - DATABASE

1. Database utama: db_2355202045_batik
2. Import file: db_2355202045_batik.sql
3. Struktur produk hanya menggunakan tabel `type`; tabel `merek` dan `type_mobil` tidak digunakan.
4. Tabel supplier, pembelian, pembayaran, dan rekening toko dipertahankan untuk kompatibilitas backend lama, tetapi tidak ditampilkan sebagai alur belanja pelanggan.
5. Belanja pelanggan menggunakan WhatsApp admin: 082284326992.
6. Status order: pending -> approved -> processing -> shipped -> delivered.
7. Bila tabel `detal_jual` belum ada pada database lama, aplikasi akan mencoba membuatnya otomatis ketika koneksi database dijalankan.
8. Setelah import database baru, restart server PHP dan buka kembali website.
