<?php
require_once __DIR__ . '/includes/functions.php';
$conn = db_connect();
$pageTitle = 'Beranda';

$hasStatus = col_exists_shop($conn, 'barang', 'status');
$statusWhere = $hasStatus ? "WHERE b.status=1" : "";
$typeJoin = table_exists_shop($conn, 'type') ? "LEFT JOIN `type` t ON t.id_type=b.id_type" : "";
$typeSelect = $typeJoin ? ", COALESCE(t.nm_type, '-') AS nm_type" : ", '-' AS nm_type";
$qProducts = mysqli_query($conn, "SELECT b.* $typeSelect FROM barang b $typeJoin $statusWhere ORDER BY b.id_brg DESC LIMIT 8");
$qTypes = table_exists_shop($conn, 'type') ? mysqli_query($conn, "SELECT t.id_type, t.nm_type, COUNT(b.id_brg) AS jml FROM `type` t LEFT JOIN barang b ON b.id_type=t.id_type" . ($hasStatus ? " AND b.status=1" : "") . " GROUP BY t.id_type, t.nm_type ORDER BY jml DESC, t.nm_type LIMIT 7") : false;

function count_table_home($conn, $table, $where=''){
    if (!table_exists_shop($conn, $table)) return 0;
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$table` $where");
    if ($res && ($r=mysqli_fetch_assoc($res))) return (int)$r['total'];
    return 0;
}
$totalProduct = count_table_home($conn,'barang',$hasStatus ? 'WHERE status=1' : '');
$totalCategory = count_table_home($conn,'type');
$totalCustomer = count_table_home($conn,'member');
$waShop = whatsapp_general_url('Halo Admin Hanata Batik, saya ingin melihat koleksi dan memesan produk batik.');
$heroData=['eyebrow'=>'Koleksi batik pilihan','title'=>'Batik yang terasa modern, tetap membawa cerita Nusantara.','description'=>'Temukan kemeja, atasan wanita, batik couple, dan koleksi premium yang dibuat untuk membuat penampilan sehari-hari lebih berkarakter.','button_text'=>'Belanja Koleksi','button_link'=>'koleksi.php','image1'=>'assets/pic/hanata-hero.png','image2'=>'assets/pic/hanata-hero-fabric.jpg','image3'=>'assets/pic/hanata-hero-look.jpg'];
if (table_exists_shop($conn,'hero_settings')) { $qh=mysqli_query($conn,"SELECT * FROM hero_settings WHERE id_hero=1 LIMIT 1"); if($qh && ($rh=mysqli_fetch_assoc($qh))) $heroData=array_merge($heroData,$rh); }
$newsBanner=null;
if(table_exists_shop($conn,'berita_toko')) { $qn=mysqli_query($conn,"SELECT * FROM berita_toko ORDER BY tanggal DESC,id_berita DESC LIMIT 1"); if($qn) $newsBanner=mysqli_fetch_assoc($qn); }

include __DIR__ . '/includes/header.php';
?>

<section class="fashion-hero">
  <div class="hero-pattern" aria-hidden="true"></div>
  <div class="container fashion-hero-grid">
    <div class="hero-copy">
      <span class="eyebrow"><i class="bi bi-stars"></i> <?php echo e($heroData['eyebrow']); ?></span>
      <h1><?php echo e($heroData['title']); ?></h1>
      <p><?php echo e($heroData['description']); ?></p>
      <div class="hero-actions">
        <a href="<?php echo e($heroData['button_link']); ?>" class="btn btn-primary"><i class="bi bi-bag"></i> <?php echo e($heroData['button_text']); ?></a>
        <a href="<?php echo e($waShop); ?>" class="btn btn-outline" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Chat Admin</a>
      </div>
      <div class="hero-trust">
        <span><i class="bi bi-check2-circle"></i> Bahan nyaman</span>
        <span><i class="bi bi-box-seam"></i> Siap dikirim</span>
        <span><i class="bi bi-whatsapp"></i> Chat admin langsung</span>
      </div>
    </div>
    <div class="hero-fashion-card hero-stack-wrap">
      <div class="hero-stack" data-hero-stack aria-label="Galeri Hanata Batik. Klik gambar untuk berganti koleksi.">
        <button class="hero-slide" type="button" aria-label="Lihat foto berikutnya">
          <img src="<?php echo e($heroData['image1']); ?>" alt="Koleksi Hanata Batik">
        </button>
        <button class="hero-slide" type="button" aria-label="Lihat foto berikutnya">
          <img src="<?php echo e($heroData['image2']); ?>" alt="Detail kain dan motif Hanata Batik">
        </button>
        <button class="hero-slide" type="button" aria-label="Lihat foto berikutnya">
          <img src="<?php echo e($heroData['image3']); ?>" alt="Pilihan look Hanata Batik">
        </button>
      </div>
      <div class="hero-stack-hint"><i class="bi bi-hand-index-thumb"></i> Tekan gambar untuk melihat koleksi lain</div>
      <div class="hero-fashion-note"><span>HANATA BATIK</span><strong>Wear & Look Better</strong><small>Koleksi batik untuk gaya modern dengan karakter Nusantara.</small></div>
    </div>
  </div>
</section>

<?php
$newsItems=[];
if(table_exists_shop($conn,'berita_toko')){
  $qn=mysqli_query($conn,"SELECT * FROM berita_toko ORDER BY tanggal DESC,id_berita DESC LIMIT 6");
  if($qn){ while($nr=mysqli_fetch_assoc($qn)){ $newsItems[]=$nr; } }
}
$newsFallbackImages=['assets/pic/hanata-hero-fabric.jpg','assets/pic/hanata-hero-look.jpg','assets/pic/hanata-hero.png'];
?>
<?php if($newsItems): ?>
<section class="news-banner-section" aria-label="Berita Hanata Batik">
  <div class="container">
    <div class="news-banner-slider" data-news-slider>
      <?php foreach($newsItems as $ni=>$nb):
        $img=trim((string)($nb['gambar']??''));
        if($img!==''){
          if(str_starts_with($img,'admin/')||str_starts_with($img,'assets/')) $newsSrc=$img;
          else $newsSrc='admin/pic/'.basename($img);
        }else{ $newsSrc=$newsFallbackImages[$ni % count($newsFallbackImages)]; }
      ?>
      <button type="button" class="news-slide <?php echo $ni===0?'is-active':''; ?>" data-news-slide data-title="<?php echo e($nb['judul']); ?>" data-category="<?php echo e($nb['kategori']); ?>" data-date="<?php echo e(date('d M Y',strtotime($nb['tanggal']))); ?>" data-description="<?php echo e($nb['deskripsi']); ?>">
        <img src="<?php echo e($newsSrc); ?>" alt="<?php echo e($nb['judul']); ?>">
        <span class="news-slide-shade"></span>
        <span class="news-slide-caption"><small><i class="bi bi-stars"></i> <?php echo e($nb['kategori']); ?> • <?php echo e(date('d M Y',strtotime($nb['tanggal']))); ?></small><strong><?php echo e($nb['judul']); ?></strong><em>Klik untuk membaca</em></span>
      </button>
      <?php endforeach; ?>
      <?php if(count($newsItems)>1): ?>
        <button type="button" class="news-nav news-prev" data-news-prev aria-label="Berita sebelumnya"><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="news-nav news-next" data-news-next aria-label="Berita berikutnya"><i class="bi bi-chevron-right"></i></button>
        <div class="news-dots" aria-label="Pilihan berita">
          <?php foreach($newsItems as $ni=>$nb): ?><button type="button" class="news-dot <?php echo $ni===0?'active':''; ?>" data-news-dot="<?php echo $ni; ?>" aria-label="Berita <?php echo $ni+1; ?>"></button><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<div class="news-modal" data-news-modal aria-hidden="true">
  <div class="news-modal-backdrop" data-news-close></div>
  <article class="news-modal-card" role="dialog" aria-modal="true" aria-labelledby="newsModalTitle">
    <button type="button" class="news-modal-close" data-news-close aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
    <div class="news-modal-head"><span id="newsModalMeta"></span><h2 id="newsModalTitle"></h2></div>
    <p id="newsModalDescription"></p>
  </article>
</div>
<?php endif; ?>

<section class="quick-cats">
  <div class="container">
    <div class="quick-cat-grid">
      <a href="koleksi.php?type=1"><span class="quick-icon"><i class="bi bi-person-badge"></i></span><b>Batik Pria</b><small>Kemeja & formal</small></a>
      <a href="koleksi.php?type=3"><span class="quick-icon"><i class="bi bi-person-heart"></i></span><b>Batik Wanita</b><small>Blouse & modern</small></a>
      <a href="koleksi.php?type=6"><span class="quick-icon"><i class="bi bi-people"></i></span><b>Keluarga</b><small>Untuk momen bersama</small></a>
      <a href="koleksi.php?type=7"><span class="quick-icon"><i class="bi bi-hearts"></i></span><b>Couple</b><small>Motif senada</small></a>
      <a href="koleksi.php?type=4"><span class="quick-icon"><i class="bi bi-gem"></i></span><b>Premium</b><small>Koleksi pilihan</small></a>
    </div>
  </div>
</section>

<section class="section" id="kategori">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">Pilih sesuai kebutuhan</span><h2>Kategori Batik</h2><p>Mulai dari kebutuhan kantor sampai pakaian santai dan pasangan.</p></div><a class="text-link" href="koleksi.php">Lihat semua <i class="bi bi-arrow-right"></i></a></div>
    <div class="category-fashion-grid">
      <?php if ($qTypes && mysqli_num_rows($qTypes)>0): while($tp=mysqli_fetch_assoc($qTypes)): ?>
        <a class="category-fashion-card" href="koleksi.php?type=<?php echo e($tp['id_type']); ?>">
          <div class="category-pattern"><span><?php echo e($tp['nm_type']); ?></span></div>
          <div class="category-meta"><div><strong><?php echo e($tp['nm_type']); ?></strong><small><?php echo (int)$tp['jml']; ?> pilihan</small></div><i class="bi bi-arrow-up-right"></i></div>
        </a>
      <?php endwhile; else: ?><div class="empty-card">Kategori belum tersedia.</div><?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-tinted">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">Pilihan minggu ini</span><h2>Koleksi Terbaru</h2><p>Produk batik pilihan dengan harga yang mudah dijangkau.</p></div><a class="btn btn-outline" href="koleksi.php">Semua Produk <i class="bi bi-arrow-right"></i></a></div>
    <div class="product-grid fashion-product-grid">
    <?php if ($qProducts && mysqli_num_rows($qProducts)>0): while($p=mysqli_fetch_assoc($qProducts)): ?>
      <article class="fashion-product-card">
        <a class="product-image-placeholder" href="detail.php?id=<?php echo e($p['id_brg']); ?>"><span>Foto Produk</span><em><?php echo e($p['nm_type']); ?></em></a>
        <div class="product-content">
          <div class="product-meta"><span><?php echo e($p['nm_type']); ?></span><span class="stock-dot"><i class="bi bi-circle-fill"></i> <?php echo ((int)$p['stok']>0)?'Ready':'Habis'; ?></span></div>
          <h3><a href="detail.php?id=<?php echo e($p['id_brg']); ?>"><?php echo e($p['nm_brg']); ?></a></h3>
          <p><?php echo e($p['ket']); ?></p>
          <div class="product-bottom"><strong><?php echo rupiah($p['hrg_jual']); ?></strong><form method="post" action="keranjang.php"><input type="hidden" name="action" value="add"><input type="hidden" name="id_brg" value="<?php echo e($p['id_brg']); ?>"><input type="hidden" name="qty" value="1"><button class="icon-buy" type="submit" aria-label="Tambah ke keranjang"><i class="bi bi-bag-plus"></i></button></form></div>
        </div>
      </article>
    <?php endwhile; else: ?><div class="empty-card">Belum ada produk. Tambahkan dari panel admin.</div><?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container feature-strip">
    <div><span class="eyebrow">Kenapa pilih kami?</span><h2>Belanja batik tanpa terasa rumit.</h2><p>Dari memilih produk sampai menghubungi admin, semua dibuat sederhana dan nyaman di satu website.</p></div>
    <div class="feature-list">
      <div><span><i class="bi bi-patch-check"></i></span><b>Koleksi terkurasi</b><small>Motif dan model dipilih untuk gaya modern.</small></div>
      <div><span><i class="bi bi-rulers"></i></span><b>Deskripsi jelas</b><small>Informasi produk membantu sebelum membeli.</small></div>
      <div><span><i class="bi bi-whatsapp"></i></span><b>Pemesanan mudah</b><small>Pesan langsung melalui WhatsApp admin.</small></div>
    </div>
  </div>
</section>

<section class="stats-bar">
  <div class="container stats-grid">
    <div><strong><?php echo $totalProduct; ?></strong><span>Produk aktif</span></div>
    <div><strong><?php echo $totalCategory; ?></strong><span>Kategori</span></div>
    <div><strong><?php echo $totalCustomer; ?></strong><span>Pelanggan terdaftar</span></div>
    <div><strong>100%</strong><span>Berbasis online</span></div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
