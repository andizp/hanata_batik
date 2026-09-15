<?php
session_start();
include "config/koneksi.php";
$conn=db_connect();
function upload_foto_barang($field,$dir){
  if(!isset($_FILES[$field])||($_FILES[$field]['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) return '';
  $ext=strtolower(pathinfo($_FILES[$field]['name'],PATHINFO_EXTENSION));
  if(!in_array($ext,['jpg','jpeg','png','gif','webp'],true)) return '';
  if(!is_dir($dir)) mkdir($dir,0777,true);
  $file='produk_'.date('YmdHis').'_'.bin2hex(random_bytes(3)).'.'.$ext;
  return move_uploaded_file($_FILES[$field]['tmp_name'],$dir.'/'.$file)?$file:'';
}
$act=$_GET['act']??''; $dir=dirname(__DIR__) . '/uploads/products'; $dirUrl='uploads/products/';
if(in_array($act,['aktif','nonaktif'],true)){
 $id=(int)($_GET['id_brg']??0); $val=$act==='aktif'?1:0; mysqli_query($conn,"UPDATE barang SET status={$val} WHERE id_brg={$id}"); header('Location:index.php?menu=barang'); exit;
}
if($act==='tambah'){
 $nm=mysqli_real_escape_string($conn,trim($_POST['nm_brg']??'')); $type=(int)($_POST['id_type']??0); $ket=mysqli_real_escape_string($conn,trim($_POST['ket']??'')); $harga=(int)($_POST['hrg_jual']??0); $stok=(int)($_POST['stok']??0); $status=(int)($_POST['status']??1); $sizesRaw=(array)($_POST['ukuran']??[]); $ukuran=implode(',',array_values(array_intersect(['S','M','L','XL'],array_map('strtoupper',$sizesRaw)))); if($ukuran==='') $ukuran='S,M,L,XL'; $foto=upload_foto_barang('foto',$dir);
 if($nm===''||$type<=0||$harga<=0||$foto==='') die('Data produk atau foto belum lengkap.');
 mysqli_query($conn,"INSERT INTO barang(nm_brg,id_type,ket,hrg_jual,foto,status,stok,ukuran) VALUES('{$nm}',{$type},'{$ket}',{$harga},'".mysqli_real_escape_string($conn,$dirUrl.$foto)."',{$status},{$stok},'".mysqli_real_escape_string($conn,$ukuran)."')") or die(mysqli_error($conn)); header('Location:index.php?menu=barang'); exit;
}
if($act==='ubah'){
 $id=(int)($_POST['id_brg']??0); $nm=mysqli_real_escape_string($conn,trim($_POST['nm_brg']??'')); $type=(int)($_POST['id_type']??0); $ket=mysqli_real_escape_string($conn,trim($_POST['ket']??'')); $harga=(int)($_POST['hrg_jual']??0); $stok=(int)($_POST['stok']??0); $status=(int)($_POST['status']??1); $sizesRaw=(array)($_POST['ukuran']??[]); $ukuran=implode(',',array_values(array_intersect(['S','M','L','XL'],array_map('strtoupper',$sizesRaw)))); if($ukuran==='') $ukuran='S,M,L,XL'; $old=basename($_POST['foto_lama']??''); $new=upload_foto_barang('foto',$dir); $foto=$new!==''?$dirUrl.$new:$old;
 if($new!==''&&$old!==''){
  $oldBase=basename($old);
  foreach([dirname(__DIR__).'/uploads/products/'.$oldBase, dirname(__DIR__).'/admin/pic/'.$oldBase] as $oldPath){ if(is_file($oldPath)){ @unlink($oldPath); break; } }
 }
 $fotoSql=mysqli_real_escape_string($conn,$foto);
 mysqli_query($conn,"UPDATE barang SET nm_brg='{$nm}',id_type={$type},ket='{$ket}',hrg_jual={$harga},foto='{$fotoSql}',status={$status},stok={$stok},ukuran='".mysqli_real_escape_string($conn,$ukuran)."' WHERE id_brg={$id}") or die(mysqli_error($conn)); header('Location:index.php?menu=barang'); exit;
}
if($act==='hapus'){
 $id=(int)($_POST['id_brg']??0); $foto=basename($_POST['foto']??''); if($foto!==''){ $oldBase=basename($foto); foreach([dirname(__DIR__).'/uploads/products/'.$oldBase, dirname(__DIR__).'/admin/pic/'.$oldBase] as $oldPath){ if(is_file($oldPath)){ @unlink($oldPath); break; } } }
 mysqli_query($conn,"DELETE FROM barang WHERE id_brg={$id}") or die(mysqli_error($conn)); header('Location:index.php?menu=barang'); exit;
}
header('Location:index.php?menu=barang'); exit;
?>