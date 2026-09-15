<?php
if (!table_exists($conn, 'store_settings')) {
    echo '<div class="alert">Tabel informasi toko belum tersedia.</div>'; return;
}
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['profile_form'] ?? '') === '1') {
    $store = [];
    foreach (['store_name','business','address','phone','whatsapp','email','hours_weekday','hours_sunday','about_text'] as $k) {
        $store[$k] = trim($_POST[$k] ?? '');
    }
    $store['store_name'] = $store['store_name'] ?: 'Hanata Batik';
    $store['whatsapp'] = preg_replace('/\D+/', '', $store['whatsapp']);
    if ($store['whatsapp'] !== '' && str_starts_with($store['whatsapp'], '0')) $store['whatsapp'] = '62'.substr($store['whatsapp'],1);
    $set=[];
    foreach ($store as $k=>$v) $set[] = "`$k`='".mysqli_real_escape_string($conn,$v)."'";
    $ok = mysqli_query($conn, "UPDATE store_settings SET ".implode(',', $set)." WHERE id_store=1");
    $notice = $ok ? 'Informasi toko berhasil diperbarui.' : 'Informasi toko gagal diperbarui: '.mysqli_error($conn);
    if ($ok) { $q=mysqli_query($conn,"SELECT * FROM store_settings WHERE id_store=1 LIMIT 1"); $store = mysqli_fetch_assoc($q) ?: $store; }
} else {
    $q = mysqli_query($conn, "SELECT * FROM store_settings WHERE id_store=1 LIMIT 1");
    $store = $q ? (mysqli_fetch_assoc($q) ?: []) : [];
}
$admin = [];
$idAdmin = (int)($_SESSION['id_user'] ?? 0);
if ($idAdmin > 0) {
    $qAdmin = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$idAdmin LIMIT 1");
    $admin = $qAdmin ? (mysqli_fetch_assoc($qAdmin) ?: []) : [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['profile_form'] ?? '') === '2' && $idAdmin > 0) {
    $nm = mysqli_real_escape_string($conn, trim($_POST['nm_user'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $hp = mysqli_real_escape_string($conn, trim($_POST['no_hp'] ?? ''));
    $sets = "nm_user='$nm', email='$email', no_hp='$hp'";
    $newPass = trim($_POST['password'] ?? '');
    if ($newPass !== '') $sets .= ", password='".md5($newPass)."'";
    mysqli_query($conn, "UPDATE user SET $sets WHERE id_user=$idAdmin");
    $_SESSION['nm_user'] = trim($_POST['nm_user'] ?? '') ?: $_SESSION['nm_user'];
    $notice = 'Profil admin berhasil diperbarui.';
    $qAdmin = mysqli_query($conn, "SELECT * FROM user WHERE id_user=$idAdmin LIMIT 1");
    $admin = $qAdmin ? (mysqli_fetch_assoc($qAdmin) ?: $admin) : $admin;
}
?>
<div class="page-head">
    <div><h1>Profil & Informasi Toko</h1><p>Kelola informasi yang benar-benar ditampilkan pada website Hanata Batik.</p></div>
</div>
<?php if($notice): ?><div class="alert success"><?php echo e($notice); ?></div><?php endif; ?>
<div class="profile-grid">
    <div class="card card-pad">
        <h2>Informasi Toko</h2>
        <form class="form-grid" method="post">
            <input type="hidden" name="profile_form" value="1">
            <div class="form-group"><label>Nama Toko</label><input class="form-control" name="store_name" value="<?php echo e($store['store_name']??''); ?>" required></div>
            <div class="form-group"><label>Bidang Usaha</label><input class="form-control" name="business" value="<?php echo e($store['business']??''); ?>" required></div>
            <div class="form-group full"><label>Alamat</label><input class="form-control" name="address" value="<?php echo e($store['address']??''); ?>" required></div>
            <div class="form-group"><label>No. HP</label><input class="form-control" name="phone" value="<?php echo e($store['phone']??''); ?>"></div>
            <div class="form-group"><label>WhatsApp Admin</label><input class="form-control" name="whatsapp" value="<?php echo e($store['whatsapp']??''); ?>" required><span class="help-text">Boleh diawali 0 atau 62.</span></div>
            <div class="form-group"><label>Email</label><input class="form-control" type="email" name="email" value="<?php echo e($store['email']??''); ?>" required></div>
            <div class="form-group"><label>Jam Senin - Sabtu</label><input class="form-control" name="hours_weekday" value="<?php echo e($store['hours_weekday']??''); ?>"></div>
            <div class="form-group"><label>Jam Minggu</label><input class="form-control" name="hours_sunday" value="<?php echo e($store['hours_sunday']??''); ?>"></div>
            <div class="form-group full"><label>Deskripsi Tentang Toko</label><textarea class="form-control" name="about_text" rows="5"><?php echo e($store['about_text']??''); ?></textarea></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Informasi Toko</button></div>
        </form>
    </div>
    <div class="side-panel">
        <div class="card card-pad">
            <h2>Profil Admin</h2>
            <form class="form-grid" method="post">
                <input type="hidden" name="profile_form" value="2">
                <div class="form-group full"><label>Nama Admin / Username</label><input class="form-control" name="nm_user" value="<?php echo e($admin['nm_user']??''); ?>" required></div>
                <div class="form-group full"><label>Email Admin</label><input class="form-control" type="email" name="email" value="<?php echo e($admin['email']??''); ?>"></div>
                <div class="form-group full"><label>No. HP Admin</label><input class="form-control" name="no_hp" value="<?php echo e($admin['no_hp']??''); ?>"></div>
                <div class="form-group full"><label>Password Baru</label><input class="form-control" type="password" name="password" placeholder="Kosongkan jika tidak diganti"></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-person-check"></i> Simpan Profil Admin</button></div>
            </form>
        </div>
        <div class="card card-pad"><h2>Catatan</h2><p class="cell-sub">Informasi toko pada form kiri digunakan sebagai sumber data untuk halaman Kontak, Tentang Kami, serta nomor WhatsApp pemesanan.</p></div>
    </div>
</div>
