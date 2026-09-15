<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "config/koneksi.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_login = trim($_POST['user_login'] ?? '');
    $pass_hash  = md5(trim($_POST['password'] ?? ''));

    $conn = db_connect();
    $user_login_sql = mysqli_real_escape_string($conn, $user_login);

    $q = mysqli_query($conn, "
        SELECT *
        FROM user
        WHERE nm_user='{$user_login_sql}'
          AND password='{$pass_hash}'
          AND status=1
        LIMIT 1
    ");

    if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        $_SESSION['proses'] = 1;
        $_SESSION['id_user'] = $row['id_user'] ?? null;
        $_SESSION['nm_user'] = $row['nm_user'] ?? $user_login;
        $_SESSION['level'] = $row['level'] ?? '-';
        header("Location: index.php");
        exit;
    }
    $error = "Username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Hanata Batik</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/login.css?v=20260525-andrza">
</head>
<body>
    <div class="login-box">
        <div class="login-hero">
            <div class="brand"><span class="brand-mark"><i class="bi bi-car-front-fill"></i></span>Hanata Batik</div>
            <h1>Dashboard penjualan batik</h1>
            <p>Masuk sebagai admin untuk mengelola data produk batik, kategori, transaksi, pelanggan, berita, dan laporan toko.</p>
        </div>

        <div class="login-form">
            <div class="brand"><span class="brand-mark"><i class="bi bi-person-lock"></i></span>Login Admin</div>
            <form method="post" action="">
                <label for="user_login">Username</label>
                <input type="text" name="user_login" id="user_login" placeholder="Contoh: Admin" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password" required>

                <button type="submit">Masuk Dashboard</button>
            </form>
            <div class="login-link">Ingin masuk sebagai member? <a href="../login.php">Login member di website</a></div>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
