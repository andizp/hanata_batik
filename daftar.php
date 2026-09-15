<?php
require_once __DIR__ . '/includes/functions.php';
$conn = db_connect();
$pageTitle = 'Daftar Member';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nm_member'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $hp = trim($_POST['no_hp'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($nama === '' || $username === '' || $hp === '' || $password === '') {
        $error = 'Nama, username, No HP, dan password wajib diisi.';
    } elseif (!preg_match('/^[a-z0-9._-]{3,30}$/', $username)) {
        $error = 'Username minimal 3 karakter dan hanya boleh berisi huruf kecil, angka, titik, underscore, atau tanda minus.';
    } else {
        $usernameSql = mysqli_real_escape_string($conn, $username);
        $hpSql = mysqli_real_escape_string($conn, $hp);
        $emailSql = mysqli_real_escape_string($conn, $email);
        $cek = mysqli_query($conn, "SELECT id_member FROM member WHERE username='{$usernameSql}' OR no_hp='{$hpSql}'" . ($email !== '' ? " OR email='{$emailSql}'" : '') . " LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $error = 'Username, email, atau nomor HP sudah digunakan member lain.';
        } else {
            $uploadError = '';
            $photoPath = upload_member_photo('foto_profile', $uploadError);
            if ($uploadError !== '') {
                $error = $uploadError;
            } else {
                $namaSql = mysqli_real_escape_string($conn, $nama);
                $passSql = mysqli_real_escape_string($conn, hash_member_password($password));
                $photoSql = mysqli_real_escape_string($conn, $photoPath);
                $sql = "INSERT INTO member (nm_member, username, no_hp, email, foto_profile, password, status) VALUES ('{$namaSql}','{$usernameSql}','{$hpSql}','{$emailSql}','{$photoSql}','{$passSql}',1)";
                if (mysqli_query($conn, $sql)) {
                    $success = 'Pendaftaran berhasil. Silakan login menggunakan username, email, atau nomor HP.';
                } else {
                    $error = 'Pendaftaran gagal: ' . mysqli_error($conn);
                }
            }
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="section login-page-section">
    <div class="container">
        <div class="auth-box-modern hanata-login-card">
            <div class="auth-side hanata-login-showcase registration-showcase">
                <img class="login-logo" src="assets/pic/logo-hanata-batik.png" alt="Hanata Batik">
                <span class="eyebrow">MEMBER HANATA BATIK</span>
                <h1>Buat akun &amp; belanja lebih mudah.</h1>
                <p>Simpan data pelanggan, akses riwayat pesanan, dan pantau pesanan Anda dari satu halaman.</p>
            </div>
            <div class="auth-form">
                <div class="login-heading">
                    <span class="eyebrow">DAFTAR MEMBER</span>
                    <h2>Buat Akun Baru</h2>
                    <p>Isi data berikut dengan benar.</p>
                </div>
                <?php if ($error): ?><div class="alert danger"><?php echo e($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert success"><?php echo e($success); ?></div><?php endif; ?>
                <form class="form-grid" method="post" enctype="multipart/form-data">
                    <label>Nama Lengkap</label>
                    <input class="input" type="text" name="nm_member" placeholder="Nama lengkap" required>
                    <label>Username</label>
                    <input class="input" type="text" name="username" placeholder="Contoh: andri_lez" required>
                    <label>No. HP</label>
                    <input class="input" type="text" name="no_hp" placeholder="Contoh: 081234567890" required>
                    <label>Email</label>
                    <input class="input" type="email" name="email" placeholder="nama@email.com">
                    <label>Foto Profil</label>
                    <input class="input" type="file" name="foto_profile" accept="image/png,image/jpeg,image/webp">
                    <label>Password</label>
                    <input class="input" type="password" name="password" placeholder="Buat password" required>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-person-check"></i> Daftar Member</button>
                </form>
                <p class="muted login-register">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
