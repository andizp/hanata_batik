<?php
require_once __DIR__ . '/includes/functions.php';
$conn = db_connect();
$pageTitle = 'Masuk';
$next = trim($_GET['next'] ?? $_POST['next'] ?? 'akun.php');
if (!preg_match('/^(?:[A-Za-z0-9_-]+\.php)(?:\?[^#]*)?(?:#.*)?$/', $next)) $next = 'akun.php';
$error = '';

if (is_member_login()) {
    redirect('akun.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['username'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    if ($loginInput === '' || $passwordInput === '') {
        $error = 'Username / email / nomor HP dan password wajib diisi.';
    } else {
        $loginSql = mysqli_real_escape_string($conn, $loginInput);
        $adminPass = md5($passwordInput);

        // Admin tetap login menggunakan username.
        $qAdmin = mysqli_query($conn, "
            SELECT * FROM user
            WHERE nm_user='{$loginSql}' AND password='{$adminPass}' AND status=1
            LIMIT 1
        ");
        if ($qAdmin && mysqli_num_rows($qAdmin) > 0) {
            $row = mysqli_fetch_assoc($qAdmin);
            session_regenerate_id(true);
            unset($_SESSION['id_member'], $_SESSION['nm_member'], $_SESSION['member_foto'], $_SESSION['member_logged_in']);
            $_SESSION['proses'] = 1;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nm_user'] = $row['nm_user'];
            $_SESSION['level'] = $row['level'];
            redirect('admin/index.php');
        }

        // Member dapat login dengan username, email, atau nomor HP.
        $passSql = mysqli_real_escape_string($conn, $passwordInput);
        $qMember = mysqli_query($conn, "SELECT * FROM member WHERE (username='{$loginSql}' OR email='{$loginSql}' OR no_hp='{$loginSql}') AND status=1 LIMIT 1");
        if ($qMember && mysqli_num_rows($qMember) > 0) {
            $row = mysqli_fetch_assoc($qMember);
            if (verify_member_password($passwordInput, $row['password'] ?? '')) {
                if (password_get_info((string)$row['password'])['algo'] === 0) {
                    $newHash = mysqli_real_escape_string($conn, hash_member_password($passwordInput));
                    mysqli_query($conn, "UPDATE member SET password='{$newHash}' WHERE id_member=".(int)$row['id_member']);
                }
                session_regenerate_id(true);
                unset($_SESSION['proses'], $_SESSION['id_user'], $_SESSION['nm_user'], $_SESSION['level']);
                $_SESSION['id_member']=(int)$row['id_member']; $_SESSION['nm_member']=$row['nm_member']; $_SESSION['member_foto']=$row['foto_profile']??''; $_SESSION['member_logged_in']=true;
                redirect($next);
            }
        }

        $error = 'Login gagal. Periksa username, email, nomor HP, dan password Anda.';
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="section login-page-section">
    <div class="container">
        <div class="auth-box-modern hanata-login-card">
            <div class="auth-side hanata-login-showcase">
                <img class="login-logo" src="assets/pic/logo-hanata-batik.png" alt="Hanata Batik">
                <span class="eyebrow">Hanata Batik</span>
                <h1>Wear &amp; Look Better.</h1>
                <p>Masuk ke akun Anda untuk melihat pesanan, memantau proses pengiriman, dan mendapatkan pengalaman belanja batik yang lebih nyaman.</p>
                <div class="login-points">
                    <span><i class="bi bi-stars"></i> Koleksi batik pilihan</span>
                    <span><i class="bi bi-whatsapp"></i> Pemesanan melalui WhatsApp</span>
                    <span><i class="bi bi-truck"></i> Status pesanan dapat dipantau</span>
                </div>
            </div>
            <div class="auth-form">
                <div class="login-heading">
                    <span class="eyebrow">Akun Member</span>
                    <h2>Selamat Datang Kembali</h2>
                    <p>Gunakan username, email, atau nomor HP untuk masuk.</p>
                </div>
                <?php if ($error): ?><div class="alert danger"><?php echo e($error); ?></div><?php endif; ?>
                <form class="form-grid" method="post">
                    <label>Username / Email / No. HP</label>
                    <input type="hidden" name="next" value="<?php echo e($next); ?>">
                    <input class="input" type="text" name="username" placeholder="Masukkan username, email, atau nomor HP" required autofocus>
                    <label>Password</label>
                    <input class="input" type="password" name="password" placeholder="Masukkan password" required>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-box-arrow-in-right"></i> Masuk ke Akun</button>
                </form>
                <p class="muted login-register">Belum punya akun? <a href="daftar.php">Daftar sebagai member</a></p>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
