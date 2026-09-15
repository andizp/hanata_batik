<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../admin/config/koneksi.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


function table_exists_shop($conn, $table) {
    $table = mysqli_real_escape_string($conn, $table);
    $q = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    return $q && mysqli_num_rows($q) > 0;
}

function col_exists_shop($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $q = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $q && mysqli_num_rows($q) > 0;
}

function get_store_info($conn = null) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $fallback = [
        'store_name'=>'Hanata Batik',
        'business'=>'Penjualan pakaian batik berkualitas',
        'address'=>'Pekanbaru, Riau',
        'phone'=>'082284326992',
        'whatsapp'=>'6282284326992',
        'email'=>'hanatakreasiindonesia@gmail.com',
        'hours_weekday'=>'Senin - Sabtu, 08.00 - 17.00',
        'hours_sunday'=>'Minggu, 09.00 - 14.00',
        'about_text'=>'Hanata Batik menghadirkan koleksi batik untuk pria, wanita, keluarga, couple, casual, dan premium dengan pengalaman belanja yang sederhana.'
    ];
    if (!$conn || !($conn instanceof mysqli) || !table_exists_shop($conn, 'store_settings')) return $cache=$fallback;
    $q=mysqli_query($conn, "SELECT * FROM store_settings WHERE id_store=1 LIMIT 1");
    $cache=($q && ($row=mysqli_fetch_assoc($q))) ? array_merge($fallback,$row) : $fallback;
    return $cache;
}

function current_member_id() {
    return (int)($_SESSION['id_member'] ?? 0);
}

function mark_member_orders_seen() {
    $_SESSION['member_orders_seen_at'] = date('Y-m-d H:i:s');
}

function is_member_login() {
    return current_member_id() > 0 && !empty($_SESSION['member_logged_in']);
}

function get_member_orders($conn, $idMember) {
    $idMember = (int)$idMember;
    $items = [];
    $sql = "
        SELECT fj.*, COALESCE(pi.nm_brg_list, '-') AS nm_brg_list
        FROM faktur_jual fj
        LEFT JOIN (
            SELECT dj.no_faktur,
                   GROUP_CONCAT(CONCAT(COALESCE(b.nm_brg, 'Produk'), ' (Size ', COALESCE(dj.ukuran,'M'), ') × ', GREATEST(COALESCE(dj.jumlah,1),1)) ORDER BY b.nm_brg, dj.ukuran SEPARATOR ', ') AS nm_brg_list
            FROM detal_jual dj
            LEFT JOIN barang b ON b.id_brg=dj.id_brg
            GROUP BY dj.no_faktur
        ) pi ON pi.no_faktur=fj.no_faktur
        WHERE fj.id_member={$idMember}
        ORDER BY COALESCE(fj.tgl_order, TIMESTAMP(fj.tgl_faktur)), fj.id_faktur DESC
    ";
    $q = mysqli_query($conn, $sql);
    while ($q && ($row = mysqli_fetch_assoc($q))) $items[] = $row;
    return $items;
}

function order_progress_steps() { return ['pending','approved','processing','shipped','delivered']; }

function product_sizes($ukuran) {
    $allowed = ['S','M','L','XL'];
    $raw = preg_split('/\s*,\s*/', strtoupper(trim((string)$ukuran)), -1, PREG_SPLIT_NO_EMPTY);
    $sizes = array_values(array_intersect($allowed, $raw));
    return $sizes ?: $allowed;
}
function cart_key($id, $size='M') {
    $id=(int)$id; $size=strtoupper(trim((string)$size));
    if(!in_array($size,['S','M','L','XL'],true)) $size='M';
    return $id.':'.$size;
}
function cart_key_parts($key) {
    $parts=explode(':',(string)$key,2); $id=(int)($parts[0]??0); $size=strtoupper(trim($parts[1]??'M'));
    if(!in_array($size,['S','M','L','XL'],true)) $size='M';
    return [$id,$size];
}
function cart_items() {
    if(!isset($_SESSION['hanata_cart']) || !is_array($_SESSION['hanata_cart'])) $_SESSION['hanata_cart']=[];
    return $_SESSION['hanata_cart'];
}
function cart_count() { $count=0; foreach(cart_items() as $qty) $count+=max(0,(int)$qty); return $count; }
function cart_clear() { $_SESSION['hanata_cart']=[]; }
function cart_set_item($id,$qty,$size='M') {
    $id=(int)$id; $qty=(int)$qty; if($id<=0) return; $key=cart_key($id,$size);
    if($qty<=0) unset($_SESSION['hanata_cart'][$key]); else $_SESSION['hanata_cart'][$key]=$qty;
}
function cart_add_item($id,$qty=1,$size='M') {
    $id=(int)$id; $qty=max(1,(int)$qty); if($id<=0) return; $key=cart_key($id,$size);
    $_SESSION['hanata_cart'][$key]=max(0,(int)(cart_items()[$key]??0))+$qty;
}
function get_cart_products($conn) {
    $cart=cart_items(); if(!$cart) return [];
    $wanted=[];
    foreach($cart as $key=>$qty){ [$id,$size]=cart_key_parts($key); if($id>0 && $qty>0) $wanted[$id][]=$size; }
    if(!$wanted) return [];
    $ids=array_keys($wanted); $idSql=implode(',',array_map('intval',$ids)); $rows=[];
    $q=mysqli_query($conn,"SELECT b.*,COALESCE(t.nm_type,'-') AS nm_type FROM barang b LEFT JOIN `type` t ON t.id_type=b.id_type WHERE b.id_brg IN ({$idSql}) AND b.status=1 ORDER BY FIELD(b.id_brg, {$idSql})");
    while($q && ($row=mysqli_fetch_assoc($q))){
        $id=(int)$row['id_brg']; $availableSizes=product_sizes($row['ukuran']??'S,M,L,XL');
        foreach($wanted[$id]??[] as $size){
            if(!in_array($size,$availableSizes,true)) continue;
            $key=cart_key($id,$size); $qty=min(max(1,(int)($cart[$key]??1)),max(1,(int)($row['stok']??1)));
            if((int)($row['stok']??0)<=0) $qty=0;
            $item=$row; $item['qty']=$qty; $item['selected_size']=$size; $item['cart_key']=$key; $item['available_sizes']=$availableSizes; $item['subtotal']=(int)$qty*(int)$row['hrg_jual']; $rows[]=$item;
        }
    }
    return $rows;
}

function cart_total($products) {
    $total = 0;
    foreach ((array)$products as $p) $total += (int)($p['subtotal'] ?? ((int)($p['qty'] ?? 0) * (int)($p['hrg_jual'] ?? 0)));
    return $total;
}





function redirect($url) {
    header("Location: $url");
    exit;
}


function upload_member_photo($inputName, &$error = '') {
    if (!isset($_FILES[$inputName]) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload foto gagal. Silakan coba lagi.';
        return '';
    }

    if (($_FILES[$inputName]['size'] ?? 0) > 2 * 1024 * 1024) {
        $error = 'Ukuran foto maksimal 2 MB.';
        return '';
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES[$inputName]['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        $error = 'Format foto harus JPG, JPEG, PNG, atau WEBP.';
        return '';
    }

    $dir = __DIR__ . '/../uploads/profile';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $filename = 'member_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
        $error = 'Foto gagal disimpan ke folder uploads/profile.';
        return '';
    }

    return 'uploads/profile/' . $filename;
}


function verify_member_password($plain, $stored) {
    $stored = (string)$stored;
    if ($stored === '') return false;
    if (password_get_info($stored)['algo'] !== 0) return password_verify((string)$plain, $stored);
    return hash_equals($stored, (string)$plain);
}
function hash_member_password($plain) { return password_hash((string)$plain, PASSWORD_DEFAULT); }

function product_photo_url($photo) {
    $photo = trim((string)$photo);
    if ($photo === '') return '';

    if (preg_match('~^(https?:)?//~i', $photo)) return $photo;
    if (str_starts_with($photo, '/')) return $photo;

    // Path lengkap yang sudah disimpan database.
    if (str_starts_with($photo, 'uploads/') || str_starts_with($photo, 'assets/') || str_starts_with($photo, 'admin/')) {
        $candidate = __DIR__ . '/../' . ltrim($photo, '/');
        if (is_file($candidate)) return $photo;
    }

    $filename = basename($photo);
    $candidates = [
        ['path' => __DIR__ . '/../uploads/products/' . $filename, 'url' => 'uploads/products/' . $filename],
        ['path' => __DIR__ . '/../admin/pic/' . $filename, 'url' => 'admin/pic/' . $filename],
        ['path' => __DIR__ . '/../pic/' . $filename, 'url' => 'pic/' . $filename],
    ];

    foreach ($candidates as $item) {
        if (is_file($item['path'])) return $item['url'];
    }

    // Fallback untuk data lama: biarkan filename tetap terlihat sebagai path relatif.
    return $filename;
}

function get_member_photo($row) {
    $photo = trim((string)($row['foto_profile'] ?? ''));
    return $photo !== '' ? $photo : '';
}

function member_initial($name) {
    $name = trim((string)$name);
    if ($name === '') return 'M';
    return strtoupper(function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1));
}

?>
