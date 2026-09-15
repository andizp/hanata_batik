<?php
session_start(); include "config/koneksi.php"; $conn=db_connect(); $act=$_GET['act']??'';
if($act==='tambah'){
 $nm=mysqli_real_escape_string($conn,trim($_POST['nm_member']??''));$u=mysqli_real_escape_string($conn,strtolower(trim($_POST['username']??'')));$hp=mysqli_real_escape_string($conn,trim($_POST['no_hp']??''));$em=mysqli_real_escape_string($conn,strtolower(trim($_POST['email']??'')));$al=mysqli_real_escape_string($conn,trim($_POST['alamat']??''));$pw=hash_member_password($_POST['password']??'');$st=(int)($_POST['status']??1);
 mysqli_query($conn,"INSERT INTO member(nm_member,username,no_hp,email,alamat,password,status) VALUES('{$nm}','{$u}','{$hp}','{$em}','{$al}','".mysqli_real_escape_string($conn,$pw)."',{$st})") or die(mysqli_error($conn));
}elseif($act==='ubah'){
 $id=(int)($_POST['id_member']??0);$nm=mysqli_real_escape_string($conn,trim($_POST['nm_member']??''));$u=mysqli_real_escape_string($conn,strtolower(trim($_POST['username']??'')));$hp=mysqli_real_escape_string($conn,trim($_POST['no_hp']??''));$em=mysqli_real_escape_string($conn,strtolower(trim($_POST['email']??'')));$al=mysqli_real_escape_string($conn,trim($_POST['alamat']??''));$st=(int)($_POST['status']??1);$sets="nm_member='{$nm}',username='{$u}',no_hp='{$hp}',email='{$em}',alamat='{$al}',status={$st}";if(trim($_POST['password']??'')!=='')$sets.=" ,password='".mysqli_real_escape_string($conn,hash_member_password($_POST['password']))."'";mysqli_query($conn,"UPDATE member SET {$sets} WHERE id_member={$id}") or die(mysqli_error($conn));
}elseif($act==='hapus'){$id=(int)($_POST['id_member']??0);mysqli_query($conn,"DELETE FROM member WHERE id_member={$id}") or die(mysqli_error($conn));}
header('Location:index.php?menu=member');exit;
?>
