<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

$conn = db_connect();
$user_login = trim($_POST['user_login'] ?? '');
$password1 = md5(trim($_POST['password'] ?? ''));
$user_login_sql = mysqli_real_escape_string($conn, $user_login);

$cari = mysqli_query($conn, "
    SELECT *
    FROM user
    WHERE nm_user='$user_login_sql'
      AND password='$password1'
      AND status=1
    LIMIT 1
");

$r = mysqli_fetch_assoc($cari);

if (!empty($r['id_user'])) {
    $_SESSION['proses'] = 1;
    $_SESSION['id_user'] = $r['id_user'];
    $_SESSION['nm_user'] = $r['nm_user'];
    $_SESSION['level'] = $r['level'];
    header('location:index.php');
    exit;
}

$_SESSION['proses'] = 0;
header('location:index.php');
exit;
