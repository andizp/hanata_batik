<?php
session_start(); include "config/koneksi.php"; $conn=db_connect(); $act=$_GET['act']??'';
if($act==='tambah'){
 $n=mysqli_real_escape_string($conn,trim($_POST['nm_type']??'')); $k=mysqli_real_escape_string($conn,trim($_POST['ket']??'')); mysqli_query($conn,"INSERT INTO `type`(nm_type,ket) VALUES('{$n}','{$k}')") or die(mysqli_error($conn));
}elseif($act==='ubah'){
 $id=(int)($_POST['id_type']??0); $n=mysqli_real_escape_string($conn,trim($_POST['nm_type']??'')); $k=mysqli_real_escape_string($conn,trim($_POST['ket']??'')); mysqli_query($conn,"UPDATE `type` SET nm_type='{$n}',ket='{$k}' WHERE id_type={$id}") or die(mysqli_error($conn));
}elseif($act==='hapus'){
 $id=(int)($_POST['id_type']??0); mysqli_query($conn,"DELETE FROM `type` WHERE id_type={$id}") or die('Type masih dipakai produk atau tidak ditemukan.');
}
header('Location:index.php?menu=type'); exit;
?>