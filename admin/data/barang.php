<?php
$act = $_GET['act'] ?? '';
$filter = $_GET['filter'] ?? 'active';
$idTypeAda = true;
$hasStatus = col_exists($conn, 'barang', 'status');
$hasStok = col_exists($conn, 'barang', 'stok');

function option_type($conn, $selected = 0) {
    $html = '';
    if (!table_exists($conn, 'type')) return $html;
    $q = mysqli_query($conn, "SELECT id_type, nm_type FROM `type` ORDER BY nm_type");
    while ($row = mysqli_fetch_assoc($q)) {
        $sel = ((int)$row['id_type'] === (int)$selected) ? 'selected' : '';
        $html .= '<option value="' . e($row['id_type']) . '" ' . $sel . '>' . e($row['nm_type']) . '</option>';
    }
    return $html;
}

if ($act === 'tambah' || $act === 'ubah') {
    $row = ['id_brg' => '', 'nm_brg' => '', 'id_type' => '', 'ket' => '', 'hrg_jual' => '', 'foto' => '', 'stok' => 1, 'status' => 1, 'ukuran' => 'S,M,L,XL'];
    if ($act === 'ubah') {
        $id = (int)($_GET['id_brg'] ?? 0);
        $q = mysqli_query($conn, "SELECT * FROM barang WHERE id_brg=$id LIMIT 1");
        $row = mysqli_fetch_assoc($q) ?: $row;
    }
?>
<div class="page-head">
    <div>
        <h1><?php echo $act === 'tambah' ? 'Tambah Produk Batik' : 'Edit Produk'; ?></h1>
        <p>Isi data batik dengan koleksi, kategori, harga, stok, keterangan, dan foto.</p>
    </div>
    <a class="btn btn-secondary" href="index.php?menu=barang"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="form-card">
    <form class="form-grid" action="dataBarang.php?menu=barang&act=<?php echo e($act); ?>" method="POST" enctype="multipart/form-data">
        <?php if ($act === 'ubah'): ?>
            <input type="hidden" name="id_brg" value="<?php echo e($row['id_brg']); ?>">
            <input type="hidden" name="foto_lama" value="<?php echo e($row['foto']); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Nama Produk</label>
            <input class="form-control" type="text" name="nm_brg" value="<?php echo e($row['nm_brg']); ?>" placeholder="Contoh: Batik Parang Premium" required>
        </div>

        <div class="form-group">
            <label>Type Produk</label>
            <select class="form-control" name="id_type" <?php echo $idTypeAda ? 'required' : 'disabled'; ?>>
                <option value="">Pilih type</option>
                <?php echo option_type($conn, $row['id_type'] ?? 0); ?>
            </select>
        </div>

        <div class="form-group">
            <label>Harga Jual</label>
            <input class="form-control" type="number" name="hrg_jual" value="<?php echo e($row['hrg_jual']); ?>" placeholder="Contoh: 249000" required>
        </div>

        <div class="form-group">
            <label>Stok (pcs)</label>
            <input class="form-control" type="number" name="stok" value="<?php echo e($row['stok'] ?? 1); ?>" min="0" required>
        </div>

        <div class="form-group full">
            <label>Ukuran Tersedia</label>
            <?php $selectedSizes = product_sizes($row['ukuran'] ?? 'S,M,L,XL'); ?>
            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:8px;">
            <?php foreach (['S','M','L','XL'] as $sz): ?>
                <label style="display:flex;align-items:center;gap:6px;font-weight:600;"><input type="checkbox" name="ukuran[]" value="<?php echo $sz; ?>" <?php echo in_array($sz,$selectedSizes,true)?'checked':''; ?>> <?php echo $sz; ?></label>
            <?php endforeach; ?>
            </div>
            <span class="help-text">Ukuran yang dapat dipilih pelanggan saat membeli.</span>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status">
                <option value="1" <?php echo ((int)($row['status'] ?? 1) === 1) ? 'selected' : ''; ?>>Aktif (tampil di website)</option>
                <option value="0" <?php echo ((int)($row['status'] ?? 1) === 0) ? 'selected' : ''; ?>>Nonaktif (disembunyikan)</option>
            </select>
        </div>

        <div class="form-group full">
            <label>Keterangan</label>
            <textarea class="form-control" name="ket" placeholder="Deskripsi singkat produk batik" required><?php echo e($row['ket']); ?></textarea>
        </div>

        <div class="form-group full">
            <label>Foto Produk</label>
            <?php if ($act === 'ubah' && !empty($row['foto'])): ?>
                <img class="preview-img" src="<?php echo e(admin_product_photo_url($row['foto'])); ?>" alt="Preview produk" onerror="this.src='pic/no-image.png'">
            <?php endif; ?>
            <input class="form-control" type="file" name="foto" accept="image/*" <?php echo $act === 'tambah' ? 'required' : ''; ?>>
            <span class="help-text">Untuk edit, kosongkan jika foto tidak ingin diganti.</span>
        </div>

        <div class="form-actions">
            <button type="submit"><?php echo $act === 'tambah' ? 'Simpan Produk' : 'Update Produk'; ?></button>
            <a class="btn btn-secondary" href="index.php?menu=barang">Batal</a>
        </div>
    </form>
</div>
<?php
    return;
}

if ($act === 'hapus') {
    $id = (int)($_GET['id_brg'] ?? 0);
    $q = mysqli_query($conn, "SELECT * FROM barang WHERE id_brg=$id LIMIT 1");
    $row = mysqli_fetch_assoc($q);
?>
<div class="page-head">
    <div><h1>Hapus Produk</h1><p>Pastikan data yang dihapus sudah benar.</p></div>
    <a class="btn btn-secondary" href="index.php?menu=barang"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<div class="form-card">
    <?php if ($row): ?>
    <form class="form-grid" action="dataBarang.php?menu=barang&act=hapus" method="POST">
        <input type="hidden" name="id_brg" value="<?php echo e($row['id_brg']); ?>">
        <input type="hidden" name="foto" value="<?php echo e($row['foto']); ?>">
        <div class="form-group full">
            <label>Produk yang akan dihapus</label>
            <div class="card card-pad">
                <b><?php echo e($row['nm_brg']); ?></b><br>
                <span class="cell-sub"><?php echo e($row['ket']); ?></span>
                <span class="cell-sub"><?php echo rupiah($row['hrg_jual']); ?></span>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-danger">Hapus Data</button>
            <a class="btn btn-secondary" href="index.php?menu=barang">Batal</a>
        </div>
    </form>
    <?php else: ?>
        <div class="empty-state">Data tidak ditemukan.</div>
    <?php endif; ?>
</div>
<?php
    return;
}

$selectType = ", COALESCE(t.nm_type, '-') AS nm_type";
$joinType = "LEFT JOIN `type` t ON t.id_type = b.id_type";
$statusWhere = '';
if ($hasStatus) {
    $statusWhere = ($filter === 'off') ? "WHERE b.status=0" : "WHERE b.status=1";
}
$qBarang = mysqli_query($conn, "
    SELECT b.* $selectType
    FROM barang b
    $joinType
    $statusWhere
    ORDER BY b.id_brg DESC
");
$isOff = ($filter === 'off');
?>
<div class="page-head">
    <div>
        <h1><?php echo $isOff ? 'Produk Nonaktif' : 'Semua Produk'; ?></h1>
        <p><?php echo $isOff ? 'Produk yang disembunyikan dari website.' : 'Daftar produk aktif yang tampil di website.'; ?></p>
    </div>
    <a class="btn btn-primary" href="index.php?menu=barang&act=tambah"><i class="bi bi-plus-circle"></i> Tambah Produk Batik</a>
</div>

<div class="status-tabs">
    <a class="status-tab <?php echo !$isOff?'active':''; ?>" href="index.php?menu=barang&filter=active"><i class="bi bi-grid"></i> Semua Produk</a>
    <a class="status-tab <?php echo $isOff?'active':''; ?>" href="index.php?menu=barang&filter=off"><i class="bi bi-slash-circle"></i> Deactivated</a>
</div>

<div class="table-card">
    <div class="table-header">
        <div><h2>Daftar Produk</h2><p>Gunakan pencarian di atas untuk mencari nama produk atau kategori.</p></div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>No</th><th>Foto</th><th>Nama Produk</th><th>Kategori</th><th>Ukuran</th><th>Stok</th><th>Harga</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if ($qBarang && mysqli_num_rows($qBarang) > 0): $no = 1; ?>
                    <?php while ($r = mysqli_fetch_assoc($qBarang)):
                        $st = (int)($r['status'] ?? 1); ?>
                        <tr class="data-row">
                            <td><?php echo $no++; ?></td>
                            <td><img class="table-img" src="<?php echo e(admin_product_photo_url($r['foto'] ?? '')); ?>" alt="<?php echo e($r['nm_brg']); ?>" onerror="this.src='pic/no-image.png'"></td>
                            <td>
                                <span class="cell-title"><?php echo e($r['nm_brg']); ?></span>
                                <span class="cell-sub"><?php echo e($r['ket']); ?></span>
                            </td>
                            <td><span class="badge-type"><?php echo e($r['nm_type']); ?></span></td>
                            <td><span class="badge-type"><?php echo e(implode(' / ', product_sizes($r['ukuran'] ?? 'S,M,L,XL'))); ?></span></td>
                            <td><b><?php echo (int)($r['stok'] ?? 0); ?></b> pcs</td>
                            <td><b><?php echo rupiah($r['hrg_jual']); ?></b></td>
                            <td><span class="badge-status <?php echo $st===1?'active':'off'; ?>"><?php echo $st===1?'Aktif':'Nonaktif'; ?></span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn-edit" href="index.php?menu=barang&act=ubah&id_brg=<?php echo e($r['id_brg']); ?>">Edit</a>
                                    <?php if ($hasStatus): ?>
                                        <?php if ($st === 1): ?>
                                            <a class="btn-delete" href="dataBarang.php?act=nonaktif&id_brg=<?php echo e($r['id_brg']); ?>" style="color:#9a6b00;">Nonaktifkan</a>
                                        <?php else: ?>
                                            <a class="btn-edit" href="dataBarang.php?act=aktif&id_brg=<?php echo e($r['id_brg']); ?>" style="color:var(--success);">Aktifkan</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <a class="btn-delete" href="index.php?menu=barang&act=hapus&id_brg=<?php echo e($r['id_brg']); ?>">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="empty-state">Belum ada data produk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
