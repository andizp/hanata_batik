<?php
$act = $_GET['act'] ?? '';

if (!table_exists($conn, 'type')) {
?>
<div class="page-head">
    <div>
        <h1>Type Produk</h1>
        <p>Kelompok kategori batik seperti Kemeja Pria, Atasan Wanita, Premium, dan lainnya.</p>
    </div>
</div>
<div class="alert">Tabel kategori belum ada. Import database Hanata Batik terlebih dahulu.</div>
<?php
    return;
}

if ($act === 'tambah' || $act === 'ubah') {
    $row = ['id_type' => '', 'nm_type' => '', 'ket' => ''];
    if ($act === 'ubah') {
        $id = (int)($_GET['id_type'] ?? 0);
        $q = mysqli_query($conn, "SELECT * FROM `type` WHERE id_type=$id LIMIT 1");
        $row = mysqli_fetch_assoc($q) ?: $row;
    }
?>
<div class="page-head">
    <div>
        <h1><?php echo $act === 'tambah' ? 'Tambah Type' : 'Edit Type'; ?></h1>
        <p>Kategori digunakan untuk mengelompokkan batik di tabel produk.</p>
    </div>
    <a class="btn btn-secondary" href="index.php?menu=type"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="form-card">
    <form class="form-grid" action="dataType.php?menu=type&act=<?php echo e($act); ?>" method="POST">
        <?php if ($act === 'ubah'): ?>
            <input type="hidden" name="id_type" value="<?php echo e($row['id_type']); ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>Nama Type</label>
            <input class="form-control" type="text" name="nm_type" value="<?php echo e($row['nm_type']); ?>" placeholder="Contoh: Batik Couple" required>
        </div>
        <div class="form-group full">
            <label>Keterangan</label>
            <textarea class="form-control" name="ket" placeholder="Keterangan kategori batik" required><?php echo e($row['ket']); ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit"><?php echo $act === 'tambah' ? 'Simpan Type' : 'Update Type'; ?></button>
            <a class="btn btn-secondary" href="index.php?menu=type">Batal</a>
        </div>
    </form>
</div>
<?php
    return;
}

if ($act === 'hapus') {
    $id = (int)($_GET['id_type'] ?? 0);
    $q = mysqli_query($conn, "SELECT * FROM `type` WHERE id_type=$id LIMIT 1");
    $row = mysqli_fetch_assoc($q);
?>
<div class="page-head">
    <div>
        <h1>Hapus Type</h1>
        <p>Type yang masih dipakai barang sebaiknya jangan dihapus.</p>
    </div>
    <a class="btn btn-secondary" href="index.php?menu=type"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="form-card">
    <?php if ($row): ?>
    <form class="form-grid" action="dataType.php?menu=type&act=hapus" method="POST">
        <input type="hidden" name="id_type" value="<?php echo e($row['id_type']); ?>">
        <div class="form-group full">
            <label>Type yang akan dihapus</label>
            <div class="card card-pad"><b><?php echo e($row['nm_type']); ?></b><span class="cell-sub"><?php echo e($row['ket']); ?></span></div>
        </div>
        <div class="form-actions">
            <button class="btn-danger" type="submit">Hapus Type</button>
            <a class="btn btn-secondary" href="index.php?menu=type">Batal</a>
        </div>
    </form>
    <?php else: ?>
        <div class="empty-state">Data tidak ditemukan.</div>
    <?php endif; ?>
</div>
<?php
    return;
}

$qType = mysqli_query($conn, "SELECT * FROM `type` ORDER BY nm_type");
?>
<div class="page-head">
    <div>
        <h1>Type Produk</h1>
        <p>Kelompokkan produk batik agar data lebih mudah dibaca.</p>
    </div>
    <a class="btn btn-primary" href="index.php?menu=type&act=tambah"><i class="bi bi-plus-circle"></i> Tambah Type</a>
</div>
<div class="table-card">
    <div class="table-header">
        <div>
            <h2>Daftar Type</h2>
            <p>Contoh kategori: Kemeja Pria, Atasan Wanita, Batik Premium, Batik Couple.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Type</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($qType && mysqli_num_rows($qType) > 0): $no = 1; ?>
                <?php while ($r = mysqli_fetch_assoc($qType)): ?>
                    <tr class="data-row">
                        <td><?php echo $no++; ?></td>
                        <td><span class="badge-type"><?php echo e($r['nm_type']); ?></span></td>
                        <td><?php echo e($r['ket']); ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn-edit" href="index.php?menu=type&act=ubah&id_type=<?php echo e($r['id_type']); ?>">Edit</a>
                                <a class="btn-delete" href="index.php?menu=type&act=hapus&id_type=<?php echo e($r['id_type']); ?>">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty-state">Belum ada data type.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
