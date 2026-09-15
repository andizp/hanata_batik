<?php
require_once __DIR__ . '/includes/functions.php';
$conn = db_connect();
if (!is_member_login()) redirect('login.php');

$pageTitle = 'Profil Saya';
$idMember = current_member_id();
$error = '';
$success = '';

function get_member_by_id_page($conn, $idMember) {
    $q = mysqli_query($conn, "SELECT * FROM member WHERE id_member=" . (int)$idMember . " LIMIT 1");
    return $q ? mysqli_fetch_assoc($q) : null;
}

$member = get_member_by_id_page($conn, $idMember);
if (!$member) {
    session_destroy();
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $nama = trim($_POST['nm_member'] ?? '');
        $hp = trim($_POST['no_hp'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $alamat = trim($_POST['alamat'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));

        if ($nama === '' || $hp === '' || $username === '') {
            $error = 'Nama, username, dan No HP wajib diisi.';
        } else {
            $hpSql = mysqli_real_escape_string($conn, $hp);
            $usernameSql = mysqli_real_escape_string($conn, $username);
            $emailSql = mysqli_real_escape_string($conn, $email);
            $cek = mysqli_query($conn, "SELECT id_member FROM member WHERE (no_hp='{$hpSql}' OR username='{$usernameSql}' OR (email<>'' AND email='{$emailSql}')) AND id_member<>" . (int)$idMember . " LIMIT 1");
            if ($cek && mysqli_num_rows($cek) > 0) {
                $error = 'Username, email, atau No HP sudah dipakai member lain.';
            } else {
                $uploadError = '';
                $photoPath = upload_member_photo('foto_profile', $uploadError);
                if ($uploadError !== '') {
                    $error = $uploadError;
                } else {
                    $namaSql = mysqli_real_escape_string($conn, $nama);
                    $alamatSql = mysqli_real_escape_string($conn, $alamat);
                    $sets = "nm_member='{$namaSql}', username='{$usernameSql}', no_hp='{$hpSql}', email='{$emailSql}', alamat='{$alamatSql}'";
                    if ($photoPath !== '') {
                        $photoSql = mysqli_real_escape_string($conn, $photoPath);
                        $sets .= ", foto_profile='{$photoSql}'";
                    }
                    if (mysqli_query($conn, "UPDATE member SET {$sets} WHERE id_member=" . (int)$idMember)) {
                        $_SESSION['nm_member'] = $nama;
                        if ($photoPath !== '') $_SESSION['member_foto'] = $photoPath;
                        $success = 'Profil berhasil diperbarui.';
                        $member = get_member_by_id_page($conn, $idMember);
                    } else {
                        $error = 'Gagal menyimpan profil: ' . mysqli_error($conn);
                    }
                }
            }
        }
    }

    if ($action === 'password') {
        $passLama = trim($_POST['password_lama'] ?? '');
        $passBaru = trim($_POST['password_baru'] ?? '');
        $konfirmasi = trim($_POST['konfirmasi_password'] ?? '');
        if ($passLama === '' || $passBaru === '' || $konfirmasi === '') {
            $error = 'Semua field password wajib diisi.';
        } elseif (!verify_member_password($passLama, (string)($member['password'] ?? ''))) {
            $error = 'Password lama tidak sesuai.';
        } elseif ($passBaru !== $konfirmasi) {
            $error = 'Konfirmasi password baru tidak sama.';
        } else {
            $passSql = mysqli_real_escape_string($conn, hash_member_password($passBaru));
            if (mysqli_query($conn, "UPDATE member SET password='{$passSql}' WHERE id_member=" . (int)$idMember)) {
                $success = 'Password berhasil diganti.';
                $member = get_member_by_id_page($conn, $idMember);
            } else {
                $error = 'Gagal mengganti password: ' . mysqli_error($conn);
            }
        }
    }
}

$photo = get_member_photo($member);
// Menekan/masuk ke Pesanan dianggap sebagai tindakan membaca notifikasi.
// Simpan timestamp Unix di cookie agar tetap terbaca setelah refresh dan tidak
// terpengaruh perbedaan timezone antara PHP dan MySQL.
$ordersSeenNow = time();
setcookie('hanata_orders_seen_at', (string)$ordersSeenNow, [
    'expires' => $ordersSeenNow + (60 * 60 * 24 * 365),
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
$_SESSION['member_orders_seen_at'] = date('Y-m-d H:i:s');
$suppressOrderNotice = true;
include __DIR__ . '/includes/header.php';
?>
<section class="section account-section">
    <div class="container account-layout">
        <aside class="account-card account-summary">
            <div class="account-photo-wrap">
                <div class="account-photo">
                    <?php if ($photo): ?>
                        <img src="<?php echo e($photo); ?>" alt="Foto profil <?php echo e($member['nm_member']); ?>">
                    <?php else: ?>
                        <span><?php echo e(member_initial($member['nm_member'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <h1><?php echo e($member['nm_member']); ?></h1>
            <p>@<?php echo e($member['username'] ?? 'member'); ?></p><p><?php echo e($member['no_hp']); ?></p>
            <div class="account-mini-list">
                <div><i class="bi bi-person-badge"></i><span>Member Hanata Batik</span></div>
                <div><i class="bi bi-receipt"></i><a href="#pesanan">Pantau pesanan saya</a></div>
                <div><i class="bi bi-bag"></i><a href="koleksi.php">Cari produk lagi</a></div>
            </div>
        </aside>

        <div class="account-main">
            <div class="section-head account-head">
                <div>
                    <h2>Profil Saya</h2>
                    <p>Ubah data akun member dan foto profil.</p>
                </div>
            </div>

            <?php if ($error): ?><div class="alert danger"><?php echo e($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert success"><?php echo e($success); ?></div><?php endif; ?>

            <div class="account-card">
                <h3><i class="bi bi-person-gear"></i> Edit Profil</h3>
                <form class="form-grid account-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="profile">
                    <label>Nama Member</label>
                    <input class="input" type="text" name="nm_member" value="<?php echo e($member['nm_member']); ?>" required>

                    <label>Username</label>
                    <input class="input" type="text" name="username" value="<?php echo e($member['username'] ?? ''); ?>" required>

                    <label>No HP</label>
                    <input class="input" type="text" name="no_hp" value="<?php echo e($member['no_hp']); ?>" required>

                    <label>Email</label>
                    <input class="input" type="email" name="email" value="<?php echo e($member['email'] ?? ''); ?>" placeholder="emailmember@gmail.com">

                    <label>Alamat</label>
                    <textarea class="input" name="alamat" placeholder="Alamat member"><?php echo e($member['alamat'] ?? ''); ?></textarea>

                    <label>Foto Profil</label>
                    <input class="input" type="file" name="foto_profile" accept="image/png,image/jpeg,image/webp">
                    <p class="form-note">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>

                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Profil</button>
                </form>
            </div>

            <div class="account-card">
                <h3><i class="bi bi-shield-lock"></i> Ganti Password</h3>
                <form class="form-grid account-form" method="post">
                    <input type="hidden" name="action" value="password">
                    <label>Password Saat Ini</label>
                    <input class="input" type="password" name="password_lama" required>
                    <label>Password Baru</label>
                    <input class="input" type="password" name="password_baru" required>
                    <label>Konfirmasi Password Baru</label>
                    <input class="input" type="password" name="konfirmasi_password" required>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-key"></i> Ganti Password</button>
                </form>
            </div>
        
            <div class="account-card order-tracker-card" id="pesanan">
                <div class="account-order-head">
                    <div>
                        <span class="eyebrow">Pesanan Saya</span>
                        <h3><i class="bi bi-truck"></i> Pantau Status Pesanan</h3>
                        <p class="muted">Status akan berubah ketika admin memproses pesanan Anda.</p>
                    </div>
                    <a class="btn btn-outline btn-sm" href="koleksi.php"><i class="bi bi-bag"></i> Belanja Lagi</a>
                </div>
                <?php $memberOrders = get_member_orders($conn, $idMember); $metaOrder = order_status_meta(); ?>
                <?php if (!$memberOrders): ?>
                    <div class="empty-card order-empty"><i class="bi bi-bag-x"></i><strong>Belum ada pesanan.</strong><span>Pesanan yang terdaftar pada akun Anda akan muncul di sini.</span></div>
                <?php else: ?>
                    <div class="member-orders-list">
                    <?php foreach ($memberOrders as $order):
                        $os = $order['order_status'] ?? 'pending';
                        if ($os === 'completed') $os = 'delivered';
                        $activeIndex = order_step_index($os);
                        $meta = $metaOrder[$os] ?? ['label'=>ucfirst($os), 'class'=>'pending', 'icon'=>'bi-hourglass-split'];
                    ?>
                        <article class="member-order-item">
                            <div class="order-item-top">
                                <div><span class="order-number"><?php echo e($order['no_faktur']); ?></span><small><?php echo e(date('d M Y • H:i', strtotime($order['tgl_order'] ?: $order['tgl_faktur']))); ?></small></div>
                                <span class="ostat <?php echo e($meta['class']); ?>"><i class="bi <?php echo e($meta['icon']); ?>"></i> <?php echo e($meta['label']); ?></span>
                            </div>
                            <p class="order-products"><i class="bi bi-bag"></i> <?php echo e($order['nm_brg_list']); ?></p>
                            <div class="order-total-row"><b>Total Pesanan</b><strong><?php echo rupiah($order['total']); ?></strong></div>
                            <div class="order-progress">
                                <?php $steps = [
                                    ['pending','Pesanan masuk','bi-inbox'],
                                    ['approved','Disetujui','bi-patch-check'],
                                    ['processing','Diproses','bi-box-seam'],
                                    ['shipped','Diantar','bi-truck'],
                                    ['delivered','Diterima','bi-check2-circle'],
                                ]; foreach ($steps as $idx=>$step):
                                    $done = ($idx <= $activeIndex) && $os !== 'declined';
                                    $current = $idx === $activeIndex && $os !== 'declined';
                                ?>
                                    <div class="order-step <?php echo $done?'done':''; ?> <?php echo $current?'current':''; ?>">
                                        <span><i class="bi <?php echo e($step[2]); ?>"></i></span><small><?php echo e($step[1]); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($os === 'declined'): ?><div class="order-note declined-note"><i class="bi bi-info-circle"></i><?php echo e($order['catatan_admin'] ?: 'Pesanan ditolak oleh admin. Silakan hubungi admin melalui WhatsApp.'); ?></div><?php elseif (!empty($order['catatan_admin'])): ?><div class="order-note"><i class="bi bi-chat-left-text"></i><?php echo e($order['catatan_admin']); ?></div><?php endif; ?>
                            <a class="btn btn-outline btn-sm" href="<?php echo e(whatsapp_general_url('Halo Admin Hanata Batik, saya ingin menanyakan status pesanan ' . $order['no_faktur'] . '.')); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Tanyakan ke Admin</a>
                        </article>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
