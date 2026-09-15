<?php
$totalBarang = 0; $totalMember = 0; $totalOrder = 0; $totalOmzet = 0;
$pendingOrd = 0;

$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barang");
$totalBarang = (int)(mysqli_fetch_assoc($q)['total'] ?? 0);
$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM member");
$totalMember = (int)(mysqli_fetch_assoc($q)['total'] ?? 0);
$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM faktur_jual");
$totalOrder = (int)(mysqli_fetch_assoc($q)['total'] ?? 0);

// Omzet historis dari order yang sudah diterima.
$q = mysqli_query($conn, "SELECT COALESCE(SUM(total),0) AS omzet FROM faktur_jual WHERE order_status='delivered'");
$totalOmzet = (float)(mysqli_fetch_assoc($q)['omzet'] ?? 0);

$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM faktur_jual WHERE order_status='pending'");
$pendingOrd = (int)(mysqli_fetch_assoc($q)['total'] ?? 0);

// breakdown status order
$ordCounts = ['pending'=>0,'approved'=>0,'processing'=>0,'shipped'=>0,'delivered'=>0,'declined'=>0];
$qb = mysqli_query($conn, "SELECT order_status, COUNT(*) AS jml FROM faktur_jual GROUP BY order_status");
while ($qb && ($r = mysqli_fetch_assoc($qb))) { if (isset($ordCounts[$r['order_status']])) $ordCounts[$r['order_status']] = (int)$r['jml']; }

// chart: 7 bulan terakhir (order lunas/selesai)
$labels = []; $values = []; $maxValue = 1; $salesMap = [];
$qSales = mysqli_query($conn, "
    SELECT DATE_FORMAT(tgl_order, '%Y-%m') AS ym, COALESCE(SUM(total), 0) AS total
    FROM faktur_jual
    WHERE order_status='delivered'
      AND tgl_order >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m-01')
    GROUP BY DATE_FORMAT(tgl_order, '%Y-%m')
");
if ($qSales) { while ($r = mysqli_fetch_assoc($qSales)) { $salesMap[$r['ym']] = (float)$r['total']; } }
for ($i = 6; $i >= 0; $i--) {
    $dt = new DateTime("first day of -$i month");
    $key = $dt->format('Y-m');
    $labels[] = $dt->format('M');
    $val = $salesMap[$key] ?? 0;
    $values[] = $val;
    if ($val > $maxValue) $maxValue = $val;
}

// pesanan terbaru
$qRecent = mysqli_query($conn, "
    SELECT fj.no_faktur, fj.total, fj.order_status, fj.tgl_order,
           me.nm_member,
           COALESCE(GROUP_CONCAT(DISTINCT b.nm_brg ORDER BY b.nm_brg SEPARATOR ', '), '-') AS nm_brg
    FROM faktur_jual fj
    LEFT JOIN member me ON me.id_member=fj.id_member
    LEFT JOIN detal_jual dj ON dj.no_faktur=fj.no_faktur
    LEFT JOIN barang b ON b.id_brg=dj.id_brg
    GROUP BY fj.id_faktur, fj.no_faktur, fj.total, fj.order_status, fj.tgl_order, me.nm_member
    ORDER BY fj.tgl_order DESC, fj.id_faktur DESC
    LIMIT 5
");
$meta = order_status_meta();
?>
<div class="dashboard-title">
    <div>
        <h1>Dashboard</h1>
        <p>Ringkasan produk, pelanggan, dan riwayat pemesanan batik.</p>
    </div>
    <button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Dashboard</button>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Penjualan (Lunas)</div><div class="stat-value"><?php echo rupiah($totalOmzet); ?></div></div>
    <div class="stat-card"><div class="stat-label">Total Order</div><div class="stat-value"><?php echo number_format($totalOrder, 0, ',', '.'); ?></div></div>
    <div class="stat-card"><div class="stat-label">Pesanan Pending</div><div class="stat-value"><?php echo number_format($pendingOrd, 0, ',', '.'); ?></div></div>
    <div class="stat-card"><div class="stat-label">Produk Aktif</div><div class="stat-value"><?php echo number_format($totalBarang, 0, ',', '.'); ?></div></div>
</div>

<div class="dash-grid">
    <div class="card chart-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;">
            <div>
                <h2>Sales Overview</h2>
                <p class="cell-sub">Grafik penjualan lunas 7 bulan terakhir.</p>
            </div>
            <span class="badge-type">Database</span>
        </div>
        <div class="chart-box" aria-label="Grafik batang penjualan">
            <?php foreach ($values as $i => $val): $h = max(12, ($val / $maxValue) * 260); ?>
                <div class="chart-bar-wrap">
                    <div class="chart-bar" style="height: <?php echo (float)$h; ?>px" title="<?php echo e(rupiah($val)); ?>"></div>
                    <span><?php echo e($labels[$i]); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="side-panel">
        <div class="card card-pad">
            <h2>Status Pesanan</h2>
            <div class="info-list">
                <div class="info-row" style="display:flex;justify-content:space-between;"><b>Pending</b><span class="ostat pending"><?php echo $ordCounts['pending']; ?></span></div>
                <div class="info-row" style="display:flex;justify-content:space-between;"><b>Disetujui</b><span class="ostat approved"><?php echo $ordCounts['approved']; ?></span></div>
                <div class="info-row" style="display:flex;justify-content:space-between;"><b>Diproses</b><span class="ostat processing"><?php echo $ordCounts['processing']; ?></span></div>
                <div class="info-row" style="display:flex;justify-content:space-between;"><b>Diantar</b><span class="ostat shipped"><?php echo $ordCounts['shipped']; ?></span></div>
                <div class="info-row" style="display:flex;justify-content:space-between;"><b>Diterima</b><span class="ostat delivered"><?php echo $ordCounts['delivered']; ?></span></div>
                <div class="info-row" style="display:flex;justify-content:space-between;"><b>Ditolak</b><span class="ostat declined"><?php echo $ordCounts['declined']; ?></span></div>
            </div>
        </div>
        <div class="card card-pad">
            <h2>Ringkasan Data</h2>
            <div class="info-list">
                <div class="info-row"><b>Produk</b><span><?php echo number_format($totalBarang,0,',','.'); ?> unit</span></div>
                
                <div class="info-row"><b>Customer</b><span><?php echo number_format($totalMember, 0, ',', '.'); ?> member</span></div>
            </div>
        </div>
    </div>
</div>

<div class="table-card" style="margin-top:22px;">
    <div class="table-header"><div><h2>Pesanan Terbaru</h2><p>Data pesanan lama yang pernah dibuat melalui sistem website.</p></div><a class="btn btn-secondary" href="index.php?menu=orders&status=all">Semua Order</a></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>No Faktur</th><th>Tanggal</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if ($qRecent && mysqli_num_rows($qRecent) > 0): while ($r = mysqli_fetch_assoc($qRecent)):
                $os = $r['order_status'] ?? 'pending'; if ($os === 'completed') $os = 'delivered'; ?>
                <tr class="data-row">
                    <td><b><?php echo e($r['no_faktur']); ?></b></td>
                    <td><?php echo e(date('d M Y', strtotime($r['tgl_order'] ?: 'now'))); ?></td>
                    <td><?php echo e($r['nm_member'] ?: '-'); ?></td>
                    <td><?php echo e($r['nm_brg'] ?: '-'); ?></td>
                    <td><b><?php echo rupiah($r['total']); ?></b></td>
                    <td><span class="ostat <?php echo e($meta[$os]['class'] ?? 'pending'); ?>"><?php echo e(order_status_label($os)); ?></span></td>
                    <td><a class="btn-edit" href="index.php?menu=orders&status=all&act=detail&no=<?php echo e($r['no_faktur']); ?>">Details</a></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="empty-state">Belum ada pesanan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
