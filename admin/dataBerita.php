<?php
session_start();
include "config/koneksi.php";
$conn = db_connect();
$act = $_GET['act'] ?? '';
$uploadDir = "pic/";

function upload_gambar_berita($field, $uploadDir) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) return '';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $name = uniqid('berita_', true) . '.' . $ext;
    return move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $name) ? $name : '';
}
function clean_pic_name($value) {
    $value = (string)$value;
    if (strpos($value, 'pic/') === 0) $value = substr($value, 4);
    return basename($value);
}

if ($act === 'tambah') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori'] ?? 'Info');
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? date('Y-m-d'));
    $gambar = upload_gambar_berita('gambar', $uploadDir);
    if ($gambar === '') die('Upload gambar gagal.');
    mysqli_query($conn, "INSERT INTO berita_toko (judul, deskripsi, gambar, kategori, tanggal) VALUES ('$judul', '$deskripsi', '$gambar', '$kategori', '$tanggal')") or die(mysqli_error($conn));
    header('Location: index.php?menu=berita'); exit;
}
if ($act === 'ubah') {
    $id = (int)($_POST['id_berita'] ?? 0);
    $judul = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori'] ?? 'Info');
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? date('Y-m-d'));
    $gambar_lama = clean_pic_name($_POST['gambar_lama'] ?? '');
    $gambar_baru = upload_gambar_berita('gambar', $uploadDir);
    if ($gambar_baru !== '') {
        if ($gambar_lama !== '' && file_exists($uploadDir . $gambar_lama)) unlink($uploadDir . $gambar_lama);
        $gambar = mysqli_real_escape_string($conn, $gambar_baru);
    } else {
        $gambar = mysqli_real_escape_string($conn, $gambar_lama);
    }
    mysqli_query($conn, "UPDATE berita_toko SET judul='$judul', deskripsi='$deskripsi', gambar='$gambar', kategori='$kategori', tanggal='$tanggal' WHERE id_berita=$id") or die(mysqli_error($conn));
    header('Location: index.php?menu=berita'); exit;
}
if ($act === 'hapus') {
    $id = (int)($_POST['id_berita'] ?? 0);
    $gambar = clean_pic_name($_POST['gambar'] ?? '');
    if ($gambar !== '' && file_exists($uploadDir . $gambar)) unlink($uploadDir . $gambar);
    mysqli_query($conn, "DELETE FROM berita_toko WHERE id_berita=$id") or die(mysqli_error($conn));
    header('Location: index.php?menu=berita'); exit;
}
header('Location: index.php?menu=berita'); exit;
?>
