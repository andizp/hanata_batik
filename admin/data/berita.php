<?php
$act = $_GET['act'] ?? '';

if (!table_exists($conn, 'berita_toko')) {
?>
<div class="page-head"><div><h1>Berita</h1><p>Menu berita untuk website toko.</p></div></div>
<div class="alert">Tabel <b>berita_toko</b> belum ada pada database.</div>
<?php return; }

if ($act === 'tambah' || $act === 'ubah') {
    $row = ['id_berita'=>'','judul'=>'','deskripsi'=>'','gambar'=>'','kategori'=>'Promo','tanggal'=>date('Y-m-d')];
    if ($act === 'ubah') {
        $id = (int)($_GET['id_berita'] ?? 0);
        $q = mysqli_query($conn, "SELECT * FROM berita_toko WHERE id_berita=$id LIMIT 1");
        $row = mysqli_fetch_assoc($q) ?: $row;
    }
?>
<div class="page-head"><div><h1><?php echo $act === 'tambah' ? 'Tambah Berita' : 'Edit Berita'; ?></h1><p>Berita akan tampil di halaman informasi website.</p></div><a class="btn btn-secondary" href="index.php?menu=berita"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="form-card">
<form class="form-grid" action="dataBerita.php?menu=berita&act=<?php echo e($act); ?>" method="POST" enctype="multipart/form-data">
    <?php if ($act === 'ubah'): ?><input type="hidden" name="id_berita" value="<?php echo e($row['id_berita']); ?>"><input type="hidden" name="gambar_lama" value="<?php echo e($row['gambar']); ?>"><?php endif; ?>
    <div class="form-group"><label>Judul</label><input class="form-control" type="text" name="judul" value="<?php echo e($row['judul']); ?>" required></div>
    <div class="form-group"><label>Kategori</label><input class="form-control" type="text" name="kategori" value="<?php echo e($row['kategori']); ?>" required></div>
    <div class="form-group"><label>Tanggal</label><input class="form-control" type="date" name="tanggal" value="<?php echo e($row['tanggal']); ?>" required></div>
    <div class="form-group full"><label>Deskripsi</label><textarea class="form-control" name="deskripsi" required><?php echo e($row['deskripsi']); ?></textarea></div>
    <div class="form-group full"><label>Gambar</label><?php if ($act === 'ubah' && !empty($row['gambar'])): ?><img class="preview-img" src="<?php echo strpos($row['gambar'], 'pic/') === 0 ? e($row['gambar']) : 'pic/' . e($row['gambar']); ?>" alt="Berita"><?php endif; ?><input class="form-control" type="file" name="gambar" accept="image/*" <?php echo $act==='tambah' ? 'required' : ''; ?>><span class="help-text">Kosongkan saat edit jika gambar tidak diganti.</span></div>
    <div class="form-actions"><button type="submit"><?php echo $act === 'tambah' ? 'Simpan Berita' : 'Update Berita'; ?></button><a class="btn btn-secondary" href="index.php?menu=berita">Batal</a></div>
</form>
</div>
<?php return; }

if ($act === 'hapus') {
    $id = (int)($_GET['id_berita'] ?? 0);
    $q = mysqli_query($conn, "SELECT * FROM berita_toko WHERE id_berita=$id LIMIT 1");
    $row = mysqli_fetch_assoc($q);
?>
<div class="page-head"><div><h1>Hapus Berita</h1><p>Berita yang dihapus tidak tampil lagi.</p></div><a class="btn btn-secondary" href="index.php?menu=berita"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="form-card">
<?php if ($row): ?>
<form class="form-grid" action="dataBerita.php?menu=berita&act=hapus" method="POST">
    <input type="hidden" name="id_berita" value="<?php echo e($row['id_berita']); ?>"><input type="hidden" name="gambar" value="<?php echo e($row['gambar']); ?>">
    <div class="form-group full"><label>Berita yang akan dihapus</label><div class="card card-pad"><b><?php echo e($row['judul']); ?></b><span class="cell-sub"><?php echo e($row['deskripsi']); ?></span></div></div>
    <div class="form-actions"><button class="btn-danger" type="submit">Hapus Berita</button><a class="btn btn-secondary" href="index.php?menu=berita">Batal</a></div>
</form>
<?php else: ?><div class="empty-state">Data tidak ditemukan.</div><?php endif; ?>
</div>
<?php return; }

$qBerita = mysqli_query($conn, "SELECT * FROM berita_toko ORDER BY tanggal DESC, id_berita DESC");
?>
<div class="news-hero">
    <h1>Berita Hanata Batik</h1>
    <p>Informasi promo, koleksi batik terbaru, dan tips memilih produk untuk pelanggan.</p>
</div>
<div class="page-head"><div><h1 style="font-size:46px;">Data Berita</h1><p>Kelola berita yang tampil pada website.</p></div><a class="btn btn-primary" href="index.php?menu=berita&act=tambah"><i class="bi bi-plus-circle"></i> Tambah Berita</a></div>
<div class="news-grid">
<?php if ($qBerita && mysqli_num_rows($qBerita)>0): ?>
    <?php while($r=mysqli_fetch_assoc($qBerita)): $gambar = $r['gambar']; $src = strpos($gambar, 'pic/') === 0 ? $gambar : 'pic/' . $gambar; ?>
    <div class="card news-card data-row">
        <img src="<?php echo e($src); ?>" alt="<?php echo e($r['judul']); ?>">
        <div class="card-pad">
            <div class="news-meta"><?php echo e($r['kategori']); ?> • <?php echo e(date('d M Y', strtotime($r['tanggal']))); ?></div>
            <h3><?php echo e($r['judul']); ?></h3>
            <p class="cell-sub"><?php echo e($r['deskripsi']); ?></p>
            <div class="actions" style="margin-top:16px;"><a class="btn-edit" href="index.php?menu=berita&act=ubah&id_berita=<?php echo e($r['id_berita']); ?>">Edit</a><a class="btn-delete" href="index.php?menu=berita&act=hapus&id_berita=<?php echo e($r['id_berita']); ?>">Delete</a></div>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="card card-pad"><div class="empty-state">Belum ada berita.</div></div>
<?php endif; ?>
</div>
