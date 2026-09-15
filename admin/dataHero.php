<?php
session_start();
require_once __DIR__.'/config/koneksi.php';
$conn=db_connect();
function hero_upload($field){
    if(!isset($_FILES[$field]) || ($_FILES[$field]['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) return '';
    $ext=strtolower(pathinfo($_FILES[$field]['name']??'',PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png','webp'],true)) return '';
    if(($_FILES[$field]['size']??0)>5*1024*1024) return '';
    $dir=__DIR__.'/../pic/'; if(!is_dir($dir)) mkdir($dir,0777,true);
    $name='hero_'.date('YmdHis').'_'.bin2hex(random_bytes(3)).'.'.$ext;
    return move_uploaded_file($_FILES[$field]['tmp_name'],$dir.$name) ? 'admin/pic/'.$name : '';
}
$eyebrow=mysqli_real_escape_string($conn,$_POST['eyebrow']??'Koleksi batik pilihan');
$title=mysqli_real_escape_string($conn,$_POST['title']??'');
$description=mysqli_real_escape_string($conn,$_POST['description']??'');
$buttonText=mysqli_real_escape_string($conn,$_POST['button_text']??'Belanja Koleksi');
$buttonLink=mysqli_real_escape_string($conn,$_POST['button_link']??'koleksi.php');
$q=mysqli_query($conn,"SELECT * FROM hero_settings WHERE id_hero=1 LIMIT 1"); $old=$q?mysqli_fetch_assoc($q):[];
$images=[];
for($i=1;$i<=3;$i++){ $new=hero_upload('image'.$i); $images[$i]=$new!=='' ? $new : ($old['image'.$i]??''); }
mysqli_query($conn,"UPDATE hero_settings SET eyebrow='{$eyebrow}',title='{$title}',description='{$description}',button_text='{$buttonText}',button_link='{$buttonLink}',image1='".mysqli_real_escape_string($conn,$images[1])."',image2='".mysqli_real_escape_string($conn,$images[2])."',image3='".mysqli_real_escape_string($conn,$images[3])."' WHERE id_hero=1") or die(mysqli_error($conn));
header('Location: index.php?menu=hero&saved=1'); exit;
