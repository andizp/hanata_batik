HANATA BATIK - INSTALASI
========================

1. Ekstrak project ke folder web server, misalnya:
   C:\xampp\htdocs\toko-batik-andri

2. Import file:
   db_2355202045_batik.sql
   ke MySQL / phpMyAdmin.

3. Pastikan koneksi pada:
   admin/config/koneksi.php
   sesuai dengan username, password, dan host MySQL Anda.
   Nilai bawaan: root / 1234 / 127.0.0.1

4. Buka:
   http://localhost/toko-batik-andri/

AKUN ADMIN
===========
Username : admin
Password : admin123

AKUN MEMBER CONTOH
==================
Username : budi
Email    : budi@gmail.com
No. HP   : 085712345678
Password : member123

ALUR BELANJA
============
- Pelanggan memilih produk.
- Klik Pesan via WhatsApp.
- Jika belum login, pelanggan login/daftar terlebih dahulu.
- Sistem membuat pesanan berstatus "Pesanan Masuk".
- Setelah itu pelanggan diarahkan ke WhatsApp Admin 082284326992.
- Pelanggan dapat melihat status pesanan di menu Profil > Pantau Status Pesanan.

STATUS PESANAN
==============
Pesanan Masuk -> Disetujui -> Diproses -> Diantar -> Diterima
Admin juga dapat menolak pesanan bila diperlukan.

DATABASE
========
Database sudah dibersihkan dari tabel/kolom Merek, Supplier, Type Mobil, pembelian pemasok, dan pembayaran cash/kredit.
Pengelompokan produk hanya menggunakan tabel `type`.

IDENTITAS TOKO
==============
Nama : Hanata Batik
Email: hanatakreasiindonesia@gmail.com
WA   : 082284326992
Tema : Hitam + Emas + Biru Tua
Font : Berkshire Swash sebagai aksen heading/brand
