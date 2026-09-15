<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tentang Kami';
$storeInfo = get_store_info($conn);
include __DIR__ . '/includes/header.php';
?>
<section class="section about-hero-section">
  <div class="container about-grid">
    <div class="about-copy">
      <span class="eyebrow about-eyebrow"><i class="bi bi-stars"></i> Tentang Hanata Batik</span>
      <h1>Warisan motif Nusantara, dibawa ke gaya masa kini.</h1>
      <p><?php echo e($storeInfo['about_text'] ?? "Hanata Batik menghadirkan koleksi batik untuk kebutuhan sehari-hari."); ?></p>
      <div class="about-points">
        <div><span class="about-icon"><i class="bi bi-palette"></i></span><div><b>Motif berkarakter</b><small>Inspirasi Nusantara dalam pilihan koleksi modern.</small></div></div>
        <div><span class="about-icon"><i class="bi bi-bag-check"></i></span><div><b>Belanja lebih mudah</b><small>Pilih produk, masukkan keranjang, lalu checkout melalui WhatsApp.</small></div></div>
        <div><span class="about-icon"><i class="bi bi-chat-heart"></i></span><div><b>Admin siap membantu</b><small>Konsultasi ukuran, warna, stok, dan pengiriman lewat WhatsApp.</small></div></div>
      </div>
      <div class="hero-actions">
        <a class="btn btn-primary" href="koleksi.php"><i class="bi bi-bag"></i> Lihat Koleksi</a>
        <a class="btn btn-outline" href="<?php echo e(whatsapp_general_url('Halo Admin Hanata Batik, saya ingin mengetahui lebih lanjut tentang Hanata Batik.')); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Hubungi Admin</a>
      </div>
    </div>
    <div class="about-visual">
      <img class="about-photo" src="assets/pic/hanata-hero.png" alt="Hanata Batik">
      <div class="about-emblem"><img src="assets/pic/hanata-logo-gold.png" alt="Logo Hanata Batik"></div>
    </div>
  </div>
</section>
<section class="section section-tinted">
  <div class="container about-brand-card">
    <img src="assets/pic/hanata-emblem-gold.png" alt="Emblem Hanata Batik">
    <div><span class="eyebrow">Identitas Hanata Batik</span><h2>Wear &amp; Look Better.</h2><p>Email: <b><?php echo e($storeInfo['email'] ?? ""); ?></b></p><p><?php echo e($storeInfo['business'] ?? "Penjualan pakaian batik berkualitas"); ?>. Pesanan dikonfirmasi langsung melalui WhatsApp <b><?php echo e($storeInfo['phone'] ?? ""); ?></b>.</p></div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
