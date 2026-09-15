<?php
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = db_connect();
}
$memberName = $_SESSION['nm_member'] ?? '';
$memberRow = null;
$memberPhoto = '';
if (is_member_login()) {
    $idMemberHeader = current_member_id();
    $qMemberHeader = mysqli_query($conn, "SELECT * FROM member WHERE id_member={$idMemberHeader} LIMIT 1");
    if ($qMemberHeader && mysqli_num_rows($qMemberHeader) > 0) {
        $memberRow = mysqli_fetch_assoc($qMemberHeader);
        $memberName = $memberRow['nm_member'] ?? $memberName;
        $memberPhoto = get_member_photo($memberRow);
        $_SESSION['nm_member'] = $memberName;
        $_SESSION['member_foto'] = $memberPhoto;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | ' : ''; ?>Hanata Batik</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php $shopBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'); if ($shopBase === '/') $shopBase = ''; ?>
    <link rel="stylesheet" href="<?php echo e($shopBase); ?>/assets/css/shop.css?v=20260826-01">
    <script>
        (function(){
            try {
                if (localStorage.getItem('hanatabatik_shop_theme') === 'dark') {
                    document.documentElement.classList.add('shop-theme-dark');
                }
            } catch(e) {}
        })();
    </script>
</head>
<body>
<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="index.php" aria-label="Hanata Batik">
            <img class="brand-logo-img" src="assets/pic/logo-hanata-batik.png" alt="Hanata Batik">
        </a>
        <nav class="nav-menu" id="desktopNavMenu">
            <a class="<?php echo $currentPage==='index.php'?'active':''; ?>" href="index.php">Home</a>
            <a class="<?php echo $currentPage==='koleksi.php'?'active':''; ?>" href="koleksi.php">Koleksi</a>
            <a class="<?php echo $currentPage==='profil.php'?'active':''; ?>" href="profil.php">Tentang Kami</a>
            <a class="<?php echo $currentPage==='kontak.php'?'active':''; ?>" href="kontak.php">Kontak</a>
            <a class="cart-nav-link <?php echo $currentPage==='keranjang.php' ? 'active' : ''; ?>" href="keranjang.php"><i class="bi bi-bag"></i> Keranjang <span class="cart-count"><?php echo cart_count(); ?></span></a>
            <a class="nav-wa" href="<?php echo e(whatsapp_general_url()); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Chat Admin</a>
        </nav>
        <div class="nav-actions">
            <button class="theme-toggle" id="shopThemeToggle" type="button" aria-label="Ubah mode gelap" title="Mode gelap"><i class="bi bi-moon-stars-fill" aria-hidden="true"></i></button>
            <?php if (is_member_login()): ?>
                <?php
                    // Badge pesanan memakai timestamp Unix agar tidak terpengaruh
                    // perbedaan timezone PHP/MySQL. Cookie dibaca pada setiap request.
                    $orderNoticeCount = 0;
                    $ordersSeenAt = isset($_COOKIE['hanata_orders_seen_at']) ? (int)$_COOKIE['hanata_orders_seen_at'] : 0;
                    $suppressOrderNotice = !empty($suppressOrderNotice);
                    if (!$suppressOrderNotice && is_member_login() && col_exists_shop($conn, 'faktur_jual', 'tgl_update_status')) {
                        $seenTs = max(0, $ordersSeenAt);
                        $qNotice = mysqli_query($conn, "SELECT COUNT(*) AS total FROM faktur_jual WHERE id_member=" . current_member_id() . " AND tgl_update_status IS NOT NULL AND UNIX_TIMESTAMP(tgl_update_status) > {$seenTs}");
                        $orderNoticeCount = (int)(mysqli_fetch_assoc($qNotice)['total'] ?? 0);
                    }
                ?>
                <a class="order-nav-link <?php echo $currentPage==='akun.php' ? 'active' : ''; ?>" href="akun.php#pesanan"><i class="bi bi-truck"></i> Pesanan<?php if($orderNoticeCount>0): ?><span class="order-new-badge"><?php echo $orderNoticeCount; ?></span><?php endif; ?></a>
                <a class="member-chip <?php echo $currentPage==='akun.php'?'active':''; ?>" href="akun.php" title="Profil Saya">
                    <span class="member-avatar">
                        <?php if ($memberPhoto): ?>
                            <img src="<?php echo e($memberPhoto); ?>" alt="Foto profil <?php echo e($memberName); ?>">
                        <?php else: ?>
                            <?php echo e(member_initial($memberName)); ?>
                        <?php endif; ?>
                    </span>
                    <span class="member-chip-text">
                        <small>Profil</small>
                        <b><?php echo e($memberName ?: 'Member'); ?></b>
                    </span>
                </a>
                <a class="logout-pill" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <a class="auth-pill <?php echo in_array($currentPage, ['login.php','daftar.php'], true)?'active':''; ?>" href="login.php"><i class="bi bi-person-circle"></i> Masuk / Daftar</a>
            <?php endif; ?>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="mobileMenuPanel">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <div class="mobile-menu-panel" id="mobileMenuPanel">
        <div class="mobile-menu-inner">
            <a class="<?php echo $currentPage==='index.php'?'active':''; ?>" href="index.php"><i class="bi bi-house-door"></i> Home</a>
            <a class="<?php echo $currentPage==='koleksi.php'?'active':''; ?>" href="koleksi.php"><i class="bi bi-grid"></i> Koleksi</a>
            <a class="<?php echo $currentPage==='profil.php'?'active':''; ?>" href="profil.php"><i class="bi bi-stars"></i> Tentang Kami</a>
            <a class="<?php echo $currentPage==='kontak.php'?'active':''; ?>" href="kontak.php"><i class="bi bi-envelope"></i> Kontak</a>
            <a class="<?php echo $currentPage==='keranjang.php'?'active':''; ?>" href="keranjang.php"><i class="bi bi-bag"></i> Keranjang <span class="mobile-cart-badge"><?php echo cart_count(); ?></span></a>
            <a href="<?php echo e(whatsapp_general_url()); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Chat Admin</a>
            <?php if (is_member_login()): ?>
                <a class="<?php echo $currentPage==='akun.php'?'active':''; ?>" href="akun.php"><i class="bi bi-person-circle"></i> Profil & Pesanan<?php if(!empty($orderNoticeCount)): ?><span class="mobile-cart-badge order-mobile-notice"><?php echo $orderNoticeCount; ?></span><?php endif; ?></a>
                <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <a class="<?php echo in_array($currentPage,['login.php','daftar.php'],true)?'active':''; ?>" href="login.php"><i class="bi bi-person-circle"></i> Masuk / Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
