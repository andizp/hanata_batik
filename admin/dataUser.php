<?php
session_start();
include "config/koneksi.php";
$conn = db_connect();
$act = $_GET['act'] ?? '';

if ($act === 'tambah') {
    $nm_user = mysqli_real_escape_string($conn, $_POST['nm_user'] ?? '');
    $password = md5($_POST['password'] ?? '');
    $level = mysqli_real_escape_string($conn, $_POST['level'] ?? 'admin');
    $status = (int)($_POST['status'] ?? 1);
    $user_login = (int)($_POST['user_login'] ?? 1);
    mysqli_query($conn, "INSERT INTO user (nm_user, password, level, status, user_login) VALUES ('$nm_user', '$password', '$level', $status, $user_login)") or die(mysqli_error($conn));
    header('Location: index.php?menu=user'); exit;
}
if ($act === 'ubah') {
    $id_user = (int)($_POST['id_user'] ?? 0);
    $nm_user = mysqli_real_escape_string($conn, $_POST['nm_user'] ?? '');
    $level = mysqli_real_escape_string($conn, $_POST['level'] ?? 'admin');
    $status = (int)($_POST['status'] ?? 1);
    $user_login = (int)($_POST['user_login'] ?? 1);
    $passSql = '';
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $passSql = ", password='$password'";
    }
    mysqli_query($conn, "UPDATE user SET nm_user='$nm_user', level='$level', status=$status, user_login=$user_login $passSql WHERE id_user=$id_user") or die(mysqli_error($conn));
    header('Location: index.php?menu=user'); exit;
}
if ($act === 'hapus') {
    $id_user = (int)($_POST['id_user'] ?? 0);
    mysqli_query($conn, "DELETE FROM user WHERE id_user=$id_user") or die(mysqli_error($conn));
    header('Location: index.php?menu=user'); exit;
}
header('Location: index.php?menu=user'); exit;
?>
