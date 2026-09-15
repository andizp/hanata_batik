<?php
session_start();
if (($_SESSION['proses'] ?? 0) != 1) {
    include "login.php";
    exit;
}

include "config/koneksi.php";
$conn = db_connect();

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

function table_exists($conn, $table) {
    $table = mysqli_real_escape_string($conn, $table);
    $q = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    return $q && mysqli_num_rows($q) > 0;
}

function col_exists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $q = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $q && mysqli_num_rows($q) > 0;
}

$menu = $_GET['menu'] ?? 'dashboard';
$act  = $_GET['act'] ?? '';
$ostatus = $_GET['status'] ?? '';
$namaLogin  = $_SESSION['nm_user'] ?? 'Admin';
$levelLogin = $_SESSION['level'] ?? 'admin';

$ordersMenus   = ['orders'];
$productMenus  = ['barang', 'catalog'];
$customerMenus = ['member'];
$dataMenus     = ['type', 'laporan'];
$websiteMenus  = ['berita', 'profile', 'hero'];

$ordersOpen   = in_array($menu, $ordersMenus, true);
$payOpen      = false;
$productOpen  = in_array($menu, $productMenus, true);
$customerOpen = in_array($menu, $customerMenus, true);
$webOpen      = in_array($menu, $websiteMenus, true);

$pendingOrders = 0;
if (table_exists($conn, 'faktur_jual') && col_exists($conn, 'faktur_jual', 'order_status')) {
    $qPending = mysqli_query($conn, "SELECT COUNT(*) AS total FROM faktur_jual WHERE order_status='pending'");
    $pendingOrders = (int)(mysqli_fetch_assoc($qPending)['total'] ?? 0);
}
$notifTotal = $pendingOrders;

function active_menu($current, $target) {
    return $current === $target ? 'active' : '';
}
function active_order($menu, $st, $want) {
    return ($menu === 'orders' && ($st === $want)) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Hanata Batik</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css?v=20260630-hanata-clean">
</head>
<body>
<div class="admin-shell" id="adminShell">
    <aside class="sidebar" id="sidebar">
        <a class="brand-box" href="index.php" title="Kembali ke Dashboard">
            <div class="brand-logo"><i class="bi bi-bag-heart-fill"></i></div>
            <div>
                <div class="brand-title">Admin</div>
                <div class="brand-name">Hanata Batik</div>
            </div>
        </a>

        <nav class="side-nav">
            <a class="side-link <?php echo active_menu($menu, 'dashboard'); ?>" href="index.php">
                <span class="side-icon"><i class="bi bi-speedometer2"></i></span><span>Dashboard</span>
            </a>

            <button type="button" class="side-link side-toggle <?php echo $ordersOpen ? 'active' : ''; ?>" data-collapse-target="ordersMenu">
                <span><span class="side-icon"><i class="bi bi-bag-check"></i></span><span>Orders<?php if ($pendingOrders>0): ?> <span class="side-pill"><?php echo $pendingOrders; ?></span><?php endif; ?></span></span>
                <span class="chevron"><i class="bi bi-chevron-down"></i></span>
            </button>
            <div class="side-submenu <?php echo $ordersOpen ? 'show' : ''; ?>" id="ordersMenu">
                <a href="index.php?menu=orders&status=all" class="<?php echo active_order($menu,$ostatus,'all'); ?>"><i class="bi bi-list-ul"></i> Semua Order</a>
                <a href="index.php?menu=orders&status=pending" class="<?php echo active_order($menu,$ostatus,'pending'); ?>"><i class="bi bi-hourglass-split"></i> Pending</a>
                <a href="index.php?menu=orders&status=approved" class="<?php echo active_order($menu,$ostatus,'approved'); ?>"><i class="bi bi-patch-check"></i> Disetujui</a>
                <a href="index.php?menu=orders&status=processing" class="<?php echo active_order($menu,$ostatus,'processing'); ?>"><i class="bi bi-gear"></i> Diproses</a>
                <a href="index.php?menu=orders&status=shipped" class="<?php echo active_order($menu,$ostatus,'shipped'); ?>"><i class="bi bi-truck"></i> Diantar</a>
                <a href="index.php?menu=orders&status=delivered" class="<?php echo active_order($menu,$ostatus,'delivered'); ?>"><i class="bi bi-check2-circle"></i> Diterima</a>
                <a href="index.php?menu=orders&status=declined" class="<?php echo active_order($menu,$ostatus,'declined'); ?>"><i class="bi bi-x-circle"></i> Ditolak</a>
            </div>

            <button type="button" class="side-link side-toggle <?php echo $productOpen ? 'active' : ''; ?>" data-collapse-target="productMenu">
                <span><span class="side-icon"><i class="bi bi-box-seam"></i></span><span>Produk</span></span>
                <span class="chevron"><i class="bi bi-chevron-down"></i></span>
            </button>
            <div class="side-submenu <?php echo $productOpen ? 'show' : ''; ?>" id="productMenu">
                <a href="index.php?menu=barang&act=tambah" class="<?php echo ($menu==='barang' && $act==='tambah') ? 'active':''; ?>"><i class="bi bi-plus-square"></i> Tambah Produk</a>
                <a href="index.php?menu=barang&filter=active" class="<?php echo ($menu==='barang' && $act==='' && ($_GET['filter']??'')!=='off') ? 'active':''; ?>"><i class="bi bi-grid"></i> Semua Produk</a>
                <a href="index.php?menu=barang&filter=off" class="<?php echo ($menu==='barang' && ($_GET['filter']??'')==='off') ? 'active':''; ?>"><i class="bi bi-slash-circle"></i> Produk Nonaktif</a>
                <a href="index.php?menu=catalog" class="<?php echo active_menu($menu,'catalog'); ?>"><i class="bi bi-collection"></i> Katalog Produk</a>
            </div>

            <a class="side-link <?php echo active_menu($menu, 'type'); ?>" href="index.php?menu=type">
                <span class="side-icon"><i class="bi bi-grid-3x3-gap"></i></span><span>Kategori Produk</span>
            </a>

            <button type="button" class="side-link side-toggle <?php echo $customerOpen ? 'active' : ''; ?>" data-collapse-target="customerMenu">
                <span><span class="side-icon"><i class="bi bi-people"></i></span><span>Pelanggan</span></span>
                <span class="chevron"><i class="bi bi-chevron-down"></i></span>
            </button>
            <div class="side-submenu <?php echo $customerOpen ? 'show' : ''; ?>" id="customerMenu">
                <a href="index.php?menu=member" class="<?php echo ($menu==='member' && $act==='') ? 'active':''; ?>"><i class="bi bi-person-lines-fill"></i> Daftar Pelanggan</a>
                <a href="index.php?menu=member&act=tambah" class="<?php echo ($menu==='member' && $act==='tambah') ? 'active':''; ?>"><i class="bi bi-person-plus"></i> Tambah Pelanggan</a>
            </div>

            <a class="side-link <?php echo ($menu === 'laporan') ? 'active' : ''; ?>" href="index.php?menu=laporan&jenis=penjualan">
                <span class="side-icon"><i class="bi bi-file-earmark-bar-graph"></i></span><span>Laporan</span>
            </a>

            <button type="button" class="side-link side-toggle <?php echo $webOpen ? 'active' : ''; ?>" data-collapse-target="websiteMenu">
                <span><span class="side-icon"><i class="bi bi-globe2"></i></span><span>Website</span></span>
                <span class="chevron"><i class="bi bi-chevron-down"></i></span>
            </button>
            <div class="side-submenu <?php echo $webOpen ? 'show' : ''; ?>" id="websiteMenu">
                <a href="index.php?menu=berita" class="<?php echo active_menu($menu, 'berita'); ?>"><i class="bi bi-newspaper"></i> Berita</a>
                <a href="index.php?menu=profile" class="<?php echo active_menu($menu, 'profile'); ?>"><i class="bi bi-shop"></i> Profil Toko</a>
                <a href="index.php?menu=hero" class="<?php echo active_menu($menu, 'hero'); ?>"><i class="bi bi-layout-text-window-reverse"></i> Hero Beranda</a>
                <a href="../index.php"><i class="bi bi-box-arrow-up-right"></i> Lihat Website</a>
            </div>
        </nav>

        <a class="logout-btn" href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></a>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="icon-btn" id="sidebarToggle" aria-label="Buka tutup sidebar"><i class="bi bi-list"></i></button>
                <a class="home-link" href="index.php"><i class="bi bi-house-door"></i> Home</a>
            </div>
            <div class="topbar-right">
                <label class="search-wrap" title="Cari data pada tabel">
                    <i class="bi bi-search"></i>
                    <input type="search" id="quickSearch" placeholder="Cari data...">
                </label>
                <button type="button" class="icon-btn" id="themeToggle" aria-label="Ubah tema"><i class="bi bi-moon"></i></button>
                <button type="button" class="icon-btn bell-btn" id="notifToggle" aria-label="Notifikasi"><i class="bi bi-bell"></i><?php if ($notifTotal > 0): ?><span><?php echo $notifTotal; ?></span><?php endif; ?></button>
                <div class="user-mini" id="userMenuBtn">
                    <div class="user-avatar"><i class="bi bi-person-fill"></i></div>
                    <div class="user-text">
                        <b><?php echo e($namaLogin); ?></b>
                        <small><?php echo e($levelLogin); ?></small>
                    </div>
                </div>
            </div>

            <div class="floating-box" id="notifBox" hidden>
                <b>Notifikasi</b>
                <p><?php echo $pendingOrders > 0 ? 'Ada ' . $pendingOrders . ' pesanan pending menunggu persetujuan.' : 'Tidak ada pesanan pending.'; ?></p>
                <p>Pemesanan dilakukan langsung melalui WhatsApp admin.</p>
                <a href="index.php?menu=orders&status=pending">Lihat pesanan pending</a>
                <a href="../index.php#kategori">Lihat koleksi</a>
            </div>
            <div class="floating-box user-box" id="userBox" hidden>
                <b><?php echo e($namaLogin); ?></b>
                <p>Level: <?php echo e($levelLogin); ?></p>
                <a href="index.php?menu=profile">Lihat profil toko</a>
                <a href="logout.php" class="text-danger">Logout</a>
            </div>
        </header>

        <section class="page-content">
            <?php
            switch ($menu) {
                case 'dashboard':
                case 'home': include "home.php"; break;
                case 'user': include "data/user.php"; break;
                case 'type': include "data/type.php"; break;
                case 'barang': include "data/barang.php"; break;
                case 'catalog': include "data/catalog.php"; break;
                case 'member': include "data/member.php"; break;
                case 'orders': include "data/orders.php"; break;
                case 'laporan': include "data/laporan.php"; break;
                case 'berita': include "data/berita.php"; break;
                case 'profile': include "data/profile.php"; break;
                case 'hero': include "data/hero.php"; break;
                default: include "home.php";
            }
            ?>
        </section>
    </main>
</div>

<script>
(function () {
    const shell = document.getElementById('adminShell');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const themeToggle = document.getElementById('themeToggle');
    const quickSearch = document.getElementById('quickSearch');
    const notifToggle = document.getElementById('notifToggle');
    const notifBox = document.getElementById('notifBox');
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userBox = document.getElementById('userBox');

    const savedTheme = localStorage.getItem('andrza-admin-theme');
    if (savedTheme === 'dark') document.body.classList.add('dark-mode');

    function setThemeIcon() {
        if (!themeToggle) return;
        themeToggle.innerHTML = document.body.classList.contains('dark-mode') ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
    }
    setThemeIcon();

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            shell.classList.toggle('sidebar-collapsed');
        });
    }

    document.querySelectorAll('[data-collapse-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-collapse-target');
            const target = document.getElementById(id);
            if (target) target.classList.toggle('show');
        });
    });

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('andrza-admin-theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
            setThemeIcon();
        });
    }

    function hideFloating(except) {
        [notifBox, userBox].forEach(function (box) {
            if (box && box !== except) box.hidden = true;
        });
    }

    if (notifToggle && notifBox) {
        notifToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            hideFloating(notifBox);
            notifBox.hidden = !notifBox.hidden;
        });
    }
    if (userMenuBtn && userBox) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            hideFloating(userBox);
            userBox.hidden = !userBox.hidden;
        });
    }
    document.addEventListener('click', function () { hideFloating(null); });

    if (quickSearch) {
        quickSearch.addEventListener('input', function () {
            const value = quickSearch.value.toLowerCase().trim();
            document.querySelectorAll('.data-row').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
            });
        });
    }
})();
</script>
</body>
</html>
