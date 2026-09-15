<?php
require_once __DIR__ . '/includes/functions.php';
$conn = db_connect();
$pageTitle = 'Koleksi Batik';
$search = trim($_GET['q'] ?? '');
$idType = (int)($_GET['type'] ?? 0);
$where = ['b.status=1'];
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(b.nm_brg LIKE '%{$s}%' OR b.ket LIKE '%{$s}%' OR t.nm_type LIKE '%{$s}%')";
}
if ($idType > 0) $where[] = "b.id_type={$idType}";
$whereSql = 'WHERE '.implode(' AND ', $where);
$qProducts = mysqli_query($conn, "SELECT b.*, COALESCE(t.nm_type,'-') AS nm_type FROM barang b LEFT JOIN `type` t ON t.id_type=b.id_type {$whereSql} ORDER BY b.id_brg DESC");
$totalFound = $qProducts ? mysqli_num_rows($qProducts) : 0;
$qTypes = mysqli_query($conn, "SELECT id_type,nm_type FROM `type` ORDER BY nm_type");
include __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">Hanata Batik Collection</span><h2>Koleksi Batik</h2><p>Menampilkan <b><?php echo (int)$totalFound; ?></b> produk. Cari berdasarkan nama, kategori, atau motif.</p></div></div>
    <form class="filters" id="productFilterForm" method="get" action="koleksi.php">
      <input class="input" type="search" name="q" value="<?php echo e($search); ?>" placeholder="Cari batik, kategori, atau motif">
      <select name="type" class="filter-select" onchange="this.form.submit()"><option value="0">Semua Type</option><?php while($t=mysqli_fetch_assoc($qTypes)): ?><option value="<?php echo (int)$t['id_type']; ?>" <?php echo $idType===(int)$t['id_type']?'selected':''; ?>><?php echo e($t['nm_type']); ?></option><?php endwhile; ?></select>
      <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
    </form>
    <div class="product-grid">
      <?php if($qProducts && mysqli_num_rows($qProducts)>0): while($product=mysqli_fetch_assoc($qProducts)):
        $stock=(int)($product['stok']??0); ?>
        <article class="fashion-product-card">
          <?php $photoUrl = product_photo_url($product['foto'] ?? ''); ?><a class="product-image-placeholder" href="detail.php?id=<?php echo (int)$product['id_brg']; ?>"><?php if ($photoUrl): ?><img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($product['nm_brg']); ?>"><?php else: ?><span>Foto Produk</span><?php endif; ?><em><?php echo e($product['nm_type']); ?></em><em style="left:auto;right:13px;"><i class="bi bi-<?php echo $stock>0?'check-circle-fill':'dash-circle-fill'; ?>"></i> <?php echo $stock>0?'Ready':'Habis'; ?></em></a>
          <div class="product-content"><div class="product-meta"><span><i class="bi bi-grid"></i> <?php echo e($product['nm_type']); ?></span><span class="stock-dot"><i class="bi bi-circle-fill"></i> <?php echo $stock>0?'Ready':'Habis'; ?></span></div>
            <h3><a href="detail.php?id=<?php echo (int)$product['id_brg']; ?>"><?php echo e($product['nm_brg']); ?></a></h3><p class="product-desc"><?php echo e($product['ket']); ?></p>
            <div class="product-bottom"><strong><?php echo rupiah($product['hrg_jual']); ?></strong><span class="stock-label">Stok <?php echo $stock; ?></span></div>
            <div class="product-actions-modern"><a class="btn btn-soft btn-sm" href="detail.php?id=<?php echo (int)$product['id_brg']; ?>"><i class="bi bi-eye"></i> Detail</a><?php if($stock>0): ?><a class="btn btn-primary btn-sm" href="detail.php?id=<?php echo (int)$product['id_brg']; ?>"><i class="bi bi-bag-plus"></i> Pilih Size</a><?php endif; ?></div>
          </div>
        </article>
      <?php endwhile; else: ?><div class="empty-card">Produk batik tidak ditemukan.</div><?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
