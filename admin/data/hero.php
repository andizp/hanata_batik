<?php
$act=$_GET['act']??'';
if(!table_exists($conn,'hero_settings')){echo '<div class="alert">Tabel hero_settings belum tersedia.</div>';return;}
$q=mysqli_query($conn,"SELECT * FROM hero_settings WHERE id_hero=1 LIMIT 1");
$hero=$q?mysqli_fetch_assoc($q):null;
if(!$hero){$hero=['id_hero'=>1,'eyebrow'=>'Koleksi batik pilihan','title'=>'Batik yang terasa modern, tetap membawa cerita Nusantara.','description'=>'Temukan koleksi batik Hanata Batik untuk gaya modern.','button_text'=>'Belanja Koleksi','button_link'=>'koleksi.php','image1'=>'','image2'=>'','image3'=>''];}
?>
<div class="page-head"><div><h1>Edit Hero Beranda</h1><p>Atur tulisan, tombol, dan tiga gambar hero yang tampil pada halaman Home.</p></div></div>
<form class="form-card" action="dataHero.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_hero" value="1">
<div class="form-grid">
<div class="form-group"><label>Eyebrow / Label Kecil</label><input class="form-control" name="eyebrow" value="<?php echo e($hero['eyebrow']); ?>" required></div>
<div class="form-group"><label>Teks Tombol</label><input class="form-control" name="button_text" value="<?php echo e($hero['button_text']); ?>" required></div>
<div class="form-group full"><label>Judul Hero</label><input class="form-control" name="title" value="<?php echo e($hero['title']); ?>" required></div>
<div class="form-group full"><label>Deskripsi Hero</label><textarea class="form-control" name="description" required><?php echo e($hero['description']); ?></textarea></div>
<div class="form-group"><label>Link Tombol</label><input class="form-control" name="button_link" value="<?php echo e($hero['button_link']); ?>"></div>
<?php for($i=1;$i<=3;$i++): $img=$hero['image'.$i]??''; ?>
<div class="form-group"><label>Gambar Hero <?php echo $i; ?></label>
<?php if($img): $preview = str_starts_with($img,'admin/pic/') ? 'pic/'.basename($img) : (str_starts_with($img,'assets/') ? '../'.ltrim($img,'/') : '../'.ltrim($img,'/')); ?><img class="preview-img hero-preview-admin" src="<?php echo e($preview); ?>" alt="Hero <?php echo $i; ?>"><?php endif; ?>
<input class="form-control" type="file" name="image<?php echo $i; ?>" accept="image/*">
<input type="hidden" name="old_image<?php echo $i; ?>" value="<?php echo e($img); ?>">
<span class="help-text">Kosongkan bila tidak ingin mengganti gambar.</span>
</div>
<?php endfor; ?>
<div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Hero</button><a class="btn btn-secondary" href="index.php?menu=hero">Batal</a></div>
</div></form>
<div class="table-card"><div class="table-header"><h2>Preview Konten</h2></div><div class="card-body"><span class="news-meta">Judul:</span><h2><?php echo e($hero['title']); ?></h2><p><?php echo nl2br(e($hero['description'])); ?></p></div></div>
