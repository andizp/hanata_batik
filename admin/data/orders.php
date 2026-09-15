<?php
/* ============================================================
 * Admin > Orders : tinjau & ubah status pesanan
 * ============================================================ */
$status = $_GET['status'] ?? 'all';
$act = $_GET['act'] ?? '';
$validStatus = ['all','pending','approved','processing','shipped','delivered','declined'];
if (!in_array($status, $validStatus, true)) $status = 'all';

$typeJoin = table_exists($conn, 'type') && col_exists($conn, 'barang', 'id_type') ? "LEFT JOIN `type` t ON t.id_type=b.id_type" : "";
$typeSelect = $typeJoin ? ", COALESCE(t.nm_type,'-') AS nm_type" : ", '-' AS nm_type";

/* ---------------- DETAIL SATU ORDER ---------------- */
if ($act === 'detail') {
    $no = mysqli_real_escape_string($conn, $_GET['no'] ?? '');
    $q = mysqli_query($conn, "
        SELECT fj.*, me.nm_member, me.no_hp, me.email, me.alamat,
               dj.id_brg, dj.ukuran, dj.jumlah, dj.harga, dj.ukuran, b.nm_brg, b.foto $typeSelect
        FROM faktur_jual fj
        LEFT JOIN member me ON me.id_member=fj.id_member
        LEFT JOIN detal_jual dj ON dj.no_faktur=fj.no_faktur
        LEFT JOIN barang b ON b.id_brg=dj.id_brg
        $typeJoin
        WHERE fj.no_faktur='$no' LIMIT 1
    ");
    $o = $q ? mysqli_fetch_assoc($q) : null;
    if (!$o) { echo '<div class="empty-state">Order tidak ditemukan.</div>'; return; }
    $os = $o['order_status'] ?? 'pending'; if ($os === 'completed') $os = 'delivered';
    $meta = order_status_meta();
?>
<div class="page-head">
    <div><h1>Detail Order <?php echo e($o['no_faktur']); ?></h1><p>Tinjau pesanan dan ubah statusnya.</p></div>
    <a class="btn btn-secondary" href="index.php?menu=orders&status=<?php echo e($status); ?>"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="detail-grid">
    <div>
        <div class="table-card" style="padding:20px;margin-bottom:18px;">
            <img class="order-img" src="<?php echo e(admin_product_photo_url($o['foto'] ?? '')); ?>" onerror="this.src='pic/no-image.png'" alt="<?php echo e($o['nm_brg']); ?>">
            <h2 style="margin:14px 0 4px;"><?php echo e($o['nm_brg']); ?></h2>
            <div class="cell-sub">Kategori: <?php echo e($o['nm_type']); ?> · Size: <?php echo e($o['ukuran'] ?: 'M'); ?> · Jumlah: <?php echo (int)$o['jumlah']; ?></div>
        </div>



        <div class="table-card" style="padding:20px;margin-bottom:18px;">
            <h2 style="margin:0 0 14px;">Customer</h2>
            <div class="kv-list">
                <div class="kv-row"><span>Nama</span><b><?php echo e($o['nm_member'] ?: '-'); ?></b></div>
                <div class="kv-row"><span>No HP</span><b><?php echo e($o['no_hp'] ?: '-'); ?></b></div>
                <div class="kv-row"><span>Email</span><b><?php echo e($o['email'] ?: '-'); ?></b></div>
                <div class="kv-row"><span>Alamat</span><b><?php echo e($o['alamat'] ?: '-'); ?></b></div>
            </div>
            <a class="btn btn-secondary" style="margin-top:14px;" href="index.php?menu=member&act=detail&id_member=<?php echo (int)$o['id_member']; ?>"><i class="bi bi-person"></i> Lihat Customer</a>
        </div>

        <div class="table-card" style="padding:20px;">
            <h2 style="margin:0 0 14px;">Ubah Status Order</h2>
            <form method="post" action="dataOrder.php" class="form-grid" style="grid-template-columns:1fr;">
                <input type="hidden" name="no_faktur" value="<?php echo e($o['no_faktur']); ?>">
                <input type="hidden" name="back" value="<?php echo e($status); ?>">
                <div class="form-group"><label>Catatan (opsional)</label><input class="form-control" type="text" name="catatan_admin" value="<?php echo e($o['catatan_admin'] ?? ''); ?>" placeholder="Catatan untuk pesanan ini"></div>
                <div class="status-actions">
                    <?php if ($os === 'pending'): ?>
                        <button class="btn btn-approve" name="aksi" value="approve" type="submit"><i class="bi bi-patch-check"></i> Setujui Pesanan</button>
                        <button class="btn btn-danger" name="aksi" value="decline" type="submit"><i class="bi bi-x-circle"></i> Tolak Pesanan</button>
                    <?php elseif ($os === 'approved'): ?>
                        <button class="btn btn-process" name="aksi" value="processing" type="submit"><i class="bi bi-gear"></i> Tandai Diproses</button>
                        <button class="btn btn-process" name="aksi" value="shipped" type="submit"><i class="bi bi-truck"></i> Tandai Diantar</button>
                        <button class="btn btn-danger" name="aksi" value="decline" type="submit"><i class="bi bi-x-circle"></i> Tolak</button>
                    <?php elseif ($os === 'processing'): ?>
                        <button class="btn btn-process" name="aksi" value="shipped" type="submit"><i class="bi bi-truck"></i> Tandai Diantar</button>
                        <button class="btn btn-danger" name="aksi" value="decline" type="submit"><i class="bi bi-x-circle"></i> Tolak</button>
                    <?php elseif ($os === 'shipped'): ?>
                        <button class="btn btn-complete" name="aksi" value="delivered" type="submit"><i class="bi bi-check2-circle"></i> Tandai Diterima</button>
                    <?php elseif ($os === 'delivered' || $os === 'completed'): ?>
                        <span class="ostat delivered"><i class="bi bi-check2-circle"></i> Pesanan Diterima</span>
                    <?php elseif ($os === 'declined'): ?>
                        <button class="btn btn-approve" name="aksi" value="approve" type="submit"><i class="bi bi-arrow-counterclockwise"></i> Aktifkan & Setujui</button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" name="aksi" value="note" type="submit"><i class="bi bi-save"></i> Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
    return;
}

/* ---------------- LIST ORDERS ---------------- */
// hitung jumlah per status
$counts = ['all'=>0,'pending'=>0,'approved'=>0,'processing'=>0,'shipped'=>0,'delivered'=>0,'declined'=>0];
$qc = mysqli_query($conn, "SELECT order_status, COUNT(*) AS jml FROM faktur_jual GROUP BY order_status");
while ($qc && ($c = mysqli_fetch_assoc($qc))) {
    $st = $c['order_status'] ?? 'pending';
    if (isset($counts[$st])) $counts[$st] += (int)$c['jml'];
    $counts['all'] += (int)$c['jml'];
}

$where = $status === 'all' ? '' : "WHERE fj.order_status='" . mysqli_real_escape_string($conn, $status) . "'";
$q = mysqli_query($conn, "
    SELECT fj.*, me.nm_member, me.no_hp,
           dj.harga, dj.ukuran, b.nm_brg, b.foto
    FROM faktur_jual fj
    LEFT JOIN member me ON me.id_member=fj.id_member
    LEFT JOIN detal_jual dj ON dj.no_faktur=fj.no_faktur
    LEFT JOIN barang b ON b.id_brg=dj.id_brg
    $where
    ORDER BY fj.tgl_order DESC, fj.id_faktur DESC
");
$titles = ['all'=>'Semua Order','pending'=>'Pesanan Masuk','approved'=>'Disetujui','processing'=>'Diproses','shipped'=>'Diantar','delivered'=>'Diterima','declined'=>'Ditolak'];
$meta = order_status_meta();
?>
<div class="page-head">
    <div><h1><?php echo e($titles[$status]); ?></h1><p>Tinjau, setujui, atau tolak pesanan pelanggan.</p></div>
</div>

<div class="status-tabs">
    <a class="status-tab <?php echo $status==='all'?'active':''; ?>" href="index.php?menu=orders&status=all">Semua <span class="cnt"><?php echo $counts['all']; ?></span></a>
    <a class="status-tab <?php echo $status==='pending'?'active':''; ?>" href="index.php?menu=orders&status=pending">Pending <span class="cnt"><?php echo $counts['pending']; ?></span></a>
    <a class="status-tab <?php echo $status==='approved'?'active':''; ?>" href="index.php?menu=orders&status=approved">Approved <span class="cnt"><?php echo $counts['approved']; ?></span></a>
    <a class="status-tab <?php echo $status==='processing'?'active':''; ?>" href="index.php?menu=orders&status=processing">Processing <span class="cnt"><?php echo $counts['processing']; ?></span></a>
    <a class="status-tab <?php echo $status==='shipped'?'active':''; ?>" href="index.php?menu=orders&status=shipped">Diantar <span class="cnt"><?php echo $counts['shipped']; ?></span></a>
    <a class="status-tab <?php echo $status==='delivered'?'active':''; ?>" href="index.php?menu=orders&status=delivered">Diterima <span class="cnt"><?php echo $counts['delivered']; ?></span></a>
    <a class="status-tab <?php echo $status==='declined'?'active':''; ?>" href="index.php?menu=orders&status=declined">Declined <span class="cnt"><?php echo $counts['declined']; ?></span></a>
</div>

<div class="table-card">
    <div class="table-header"><div><h2><?php echo e($titles[$status]); ?></h2><p>Klik Details untuk meninjau dan mengubah status.</p></div></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>No</th><th>Tanggal</th><th>No Faktur</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status Order</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if ($q && mysqli_num_rows($q) > 0): $no=1; while ($r = mysqli_fetch_assoc($q)):
                $os = $r['order_status'] ?? 'pending'; if ($os === 'completed') $os = 'delivered'; ?>
                <tr class="data-row">
                    <td><?php echo $no++; ?></td>
                    <td><?php echo e(date('d M Y', strtotime($r['tgl_order'] ?: $r['tgl_faktur']))); ?></td>
                    <td><b><?php echo e($r['no_faktur']); ?></b></td>
                    <td><?php echo e($r['nm_member'] ?: '-'); ?><span class="cell-sub"><?php echo e($r['no_hp'] ?: ''); ?></span></td>
                    <td><?php echo e($r['nm_brg'] ?: '-'); ?><span class="cell-sub">Size <?php echo e($r['ukuran'] ?: 'M'); ?> · <?php echo (int)($r['jumlah'] ?? 1); ?> pcs</span></td>
                    <td><b><?php echo rupiah($r['total']); ?></b></td>
                    <td><span class="ostat <?php echo e($meta[$os]['class'] ?? 'pending'); ?>"><?php echo e(order_status_label($os)); ?></span></td>
                        <td><a class="btn-edit" href="index.php?menu=orders&status=<?php echo e($status); ?>&act=detail&no=<?php echo e($r['no_faktur']); ?>"><i class="bi bi-eye"></i> Details</a></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8" class="empty-state">Tidak ada order pada kategori ini.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
