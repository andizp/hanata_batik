<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Kontak';
$storeInfo = get_store_info($conn);
$sent = ($_SERVER['REQUEST_METHOD'] === 'POST');
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container info-page-grid">
        <div class="detail-info">
            <span class="badge"><i class="bi bi-envelope"></i> Kontak</span>
            <h1>Hubungi Kami</h1>
            <p class="muted">Gunakan form ini sebagai simulasi pesan pelanggan. Untuk tugas lokal, pesan belum dikirim ke email, tetapi tampil sebagai konfirmasi di halaman.</p>
            <?php if ($sent): ?><div class="alert success">Pesan berhasil disiapkan. Admin dapat menambahkan integrasi email/WhatsApp jika dibutuhkan.</div><?php endif; ?>
            <form class="form-grid" method="post">
                <label>Nama</label>
                <input class="input" type="text" name="nama" required>
                <label>No HP</label>
                <input class="input" type="text" name="no_hp" required>
                <label>Pesan</label>
                <textarea name="pesan" placeholder="Tulis pesan atau produk yang ingin ditanyakan" required></textarea>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Kirim Pesan</button>
            </form>
        </div>
        <div class="detail-info">
            <h2>Informasi Toko</h2>
            <div class="info-list">
                <div class="info-row"><span>WhatsApp</span><b><?php echo e($storeInfo['phone'] ?? ""); ?></b></div>
                <div class="info-row"><span>Email</span><b><?php echo e($storeInfo['email'] ?? ""); ?></b></div>
                <div class="info-row"><span>Alamat</span><b><?php echo e($storeInfo['address'] ?? ""); ?></b></div><div class="info-row"><span>Jam Operasional</span><b><?php echo e($storeInfo['hours_weekday'] ?? ""); ?></b></div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
