<?php
session_start(); include "config/koneksi.php"; $conn=db_connect();
$no=mysqli_real_escape_string($conn,$_POST['no_faktur']??''); $aksi=$_POST['aksi']??''; $catatan=mysqli_real_escape_string($conn,trim($_POST['catatan_admin']??'')); $back=mysqli_real_escape_string($conn,$_POST['back']??'all');
if($no===''){header('Location:index.php?menu=orders&status=all');exit;}
$q=mysqli_query($conn,"SELECT order_status FROM faktur_jual WHERE no_faktur='{$no}' LIMIT 1"); $cur=$q?(mysqli_fetch_assoc($q)['order_status']??''):'';
$map=['approve'=>'approved','processing'=>'processing','shipped'=>'shipped','delivered'=>'delivered','decline'=>'declined'];
if($aksi==='note') mysqli_query($conn,"UPDATE faktur_jual SET catatan_admin='{$catatan}', tgl_update_status=NOW() WHERE no_faktur='{$no}'");
elseif(isset($map[$aksi])){
 $new=$map[$aksi]; $legacy=$new==='delivered'?2:($new==='declined'?3:($new==='pending'?0:1)); $extra=$aksi==='approve'?",tgl_approve='".date('Y-m-d H:i:s')."'":'';
 mysqli_query($conn,"UPDATE faktur_jual SET order_status='{$new}',status={$legacy},catatan_admin='{$catatan}',tgl_update_status=NOW(){$extra} WHERE no_faktur='{$no}'") or die(mysqli_error($conn));
 if($new==='delivered' && !in_array($cur,['delivered'],true)){
   $qd=mysqli_query($conn,"SELECT id_brg,jumlah FROM detal_jual WHERE no_faktur='{$no}'");
   while($qd&&($d=mysqli_fetch_assoc($qd))){$idb=(int)$d['id_brg'];$qty=max(1,(int)$d['jumlah']);mysqli_query($conn,"UPDATE barang SET stok=GREATEST(stok-{$qty},0) WHERE id_brg={$idb}");}
 }
}
header('Location:index.php?menu=orders&status='.urlencode($back).'&act=detail&no='.urlencode($_POST['no_faktur']??''));exit;
?>