<?php
// Hanata Batik - koneksi MySQL lokal + Railway.
$servername = getenv('MYSQLHOST') ?: '127.0.0.1';
$username   = getenv('MYSQLUSER') ?: 'root';
$password   = getenv('MYSQLPASSWORD');
if ($password === false) {
    $password = '1234';
}
$database   = getenv('MYSQLDATABASE') ?: 'db_2355202045_batik';
$mysqlPort  = getenv('MYSQLPORT') ?: '3306';

function db_connect() {
    global $servername, $username, $password, $database, $mysqlPort;
    $conn = mysqli_connect($servername, $username, $password, $database, (int)$mysqlPort);
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8mb4");
    ensure_batik_schema($conn);
    return $conn;
}

function ensure_column_exists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $q = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return $q && mysqli_num_rows($q) > 0;
}
function ensure_table_exists($conn, $table) {
    $table = mysqli_real_escape_string($conn, $table);
    $q = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
    return $q && mysqli_num_rows($q) > 0;
}
function ensure_batik_schema($conn) {
    static $done = false;
    if ($done) return;
    $done = true;
    if (!ensure_table_exists($conn, 'type')) {
        mysqli_query($conn, "CREATE TABLE `type` (id_type INT AUTO_INCREMENT PRIMARY KEY, nm_type VARCHAR(80) NOT NULL, ket VARCHAR(255) NOT NULL, UNIQUE KEY uk_type_name(nm_type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    $types = [
        ['Kemeja Pria','Kemeja batik pria dengan berbagai motif dan potongan.'],
        ['Kemeja Formal','Batik untuk kebutuhan kerja, meeting, dan acara resmi.'],
        ['Atasan Wanita','Blouse dan atasan batik wanita dengan potongan modern.'],
        ['Batik Premium','Koleksi bahan dan motif premium untuk acara spesial.'],
        ['Batik Casual','Batik santai untuk aktivitas harian dan hangout.'],
        ['Batik Keluarga','Pilihan batik untuk pasangan dan keluarga.'],
        ['Batik Couple','Set batik pasangan dengan motif senada.']
    ];
    foreach ($types as $t) {
        $n = mysqli_real_escape_string($conn,$t[0]); $k = mysqli_real_escape_string($conn,$t[1]);
        mysqli_query($conn, "INSERT IGNORE INTO `type` (nm_type,ket) VALUES ('{$n}','{$k}')");
    }
    // Migrasi ringan untuk instalasi lama. Struktur lama dipertahankan sebisa mungkin; fitur aktif Hanata Batik memakai type dan alur order WhatsApp.
    if (ensure_table_exists($conn,'barang')) {
        if (!ensure_column_exists($conn,'barang','id_type')) mysqli_query($conn,"ALTER TABLE barang ADD COLUMN id_type INT NULL");
        if (!ensure_column_exists($conn,'barang','status')) mysqli_query($conn,"ALTER TABLE barang ADD COLUMN status TINYINT NOT NULL DEFAULT 1");
        if (!ensure_column_exists($conn,'barang','stok')) mysqli_query($conn,"ALTER TABLE barang ADD COLUMN stok INT NOT NULL DEFAULT 1");
        if (!ensure_column_exists($conn,'barang','ukuran')) mysqli_query($conn,"ALTER TABLE barang ADD COLUMN ukuran VARCHAR(30) NOT NULL DEFAULT 'S,M,L,XL'");
        mysqli_query($conn, "UPDATE barang SET ukuran='S,M,L,XL' WHERE ukuran IS NULL OR ukuran=''");
    }
    if (ensure_table_exists($conn,'member')) {
        foreach ([
            'username'=>"VARCHAR(50) NULL",
            'email'=>"VARCHAR(100) NULL",
            'alamat'=>"VARCHAR(255) NULL",
            'foto_profile'=>"VARCHAR(255) NULL"
        ] as $col=>$def) if (!ensure_column_exists($conn,'member',$col)) mysqli_query($conn,"ALTER TABLE member ADD COLUMN `{$col}` {$def}");
        mysqli_query($conn,"UPDATE member SET username=LOWER(REPLACE(TRIM(nm_member),' ','.')) WHERE username IS NULL OR username=''");
    }
    if (ensure_table_exists($conn,'faktur_jual')) {
        $cols = [
          'order_status'=>"VARCHAR(20) NOT NULL DEFAULT 'pending'",
          'catatan_admin'=>"VARCHAR(255) NULL",
          'tgl_approve'=>"DATETIME NULL",
          'tgl_order'=>"DATETIME NULL",
          'tgl_update_status'=>"DATETIME NULL"
        ];
        foreach($cols as $c=>$d) if(!ensure_column_exists($conn,'faktur_jual',$c)) mysqli_query($conn,"ALTER TABLE faktur_jual ADD COLUMN `{$c}` {$d}");
        mysqli_query($conn,"UPDATE faktur_jual SET order_status='approved' WHERE (order_status IS NULL OR order_status='') AND status=1");
        mysqli_query($conn,"UPDATE faktur_jual SET order_status='delivered' WHERE order_status='completed'");
        mysqli_query($conn,"UPDATE faktur_jual SET order_status='declined' WHERE order_status='' AND status=3");
        mysqli_query($conn,"UPDATE faktur_jual SET tgl_order=COALESCE(tgl_order,tgl_faktur)");
    }
    // Pastikan detail order tersedia agar halaman profil pelanggan dan admin tidak
    // gagal pada instalasi lama yang belum pernah membuat tabel ini.
    if (ensure_table_exists($conn,'barang') && ensure_table_exists($conn,'faktur_jual') && !ensure_table_exists($conn,'detal_jual')) {
        mysqli_query($conn, "CREATE TABLE `detal_jual` (
            `id_jual` INT NOT NULL AUTO_INCREMENT,
            `no_faktur` VARCHAR(30) NOT NULL,
            `id_brg` INT NOT NULL,
            `ukuran` VARCHAR(5) NOT NULL DEFAULT 'M',
            `jumlah` INT NOT NULL DEFAULT 1,
            `harga` BIGINT NOT NULL,
            `status` TINYINT NOT NULL DEFAULT 1,
            PRIMARY KEY (`id_jual`),
            KEY `idx_detail_faktur` (`no_faktur`),
            KEY `idx_detail_produk` (`id_brg`),
            CONSTRAINT `fk_detal_jual_faktur` FOREIGN KEY (`no_faktur`) REFERENCES `faktur_jual` (`no_faktur`) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT `fk_detal_jual_barang` FOREIGN KEY (`id_brg`) REFERENCES `barang` (`id_brg`) ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }
    if (ensure_table_exists($conn,'detal_jual') && !ensure_column_exists($conn,'detal_jual','ukuran')) {
        mysqli_query($conn, "ALTER TABLE detal_jual ADD COLUMN ukuran VARCHAR(5) NOT NULL DEFAULT 'M' AFTER id_brg");
    }
    // Satu sumber data informasi toko untuk seluruh website.
    if (!ensure_table_exists($conn, 'store_settings')) {
        mysqli_query($conn, "CREATE TABLE `store_settings` (
            `id_store` INT NOT NULL AUTO_INCREMENT,
            `store_name` VARCHAR(120) NOT NULL DEFAULT 'Hanata Batik',
            `business` VARCHAR(160) NOT NULL DEFAULT 'Penjualan pakaian batik berkualitas',
            `address` VARCHAR(255) NOT NULL DEFAULT 'Pekanbaru, Riau',
            `phone` VARCHAR(30) NOT NULL DEFAULT '082284326992',
            `whatsapp` VARCHAR(30) NOT NULL DEFAULT '6282284326992',
            `email` VARCHAR(120) NOT NULL DEFAULT 'hanatakreasiindonesia@gmail.com',
            `hours_weekday` VARCHAR(120) NOT NULL DEFAULT 'Senin - Sabtu, 08.00 - 17.00',
            `hours_sunday` VARCHAR(120) NOT NULL DEFAULT 'Minggu, 09.00 - 14.00',
            `about_text` TEXT NULL,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_store`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        mysqli_query($conn, "INSERT INTO `store_settings` (`id_store`,`store_name`,`business`,`address`,`phone`,`whatsapp`,`email`,`hours_weekday`,`hours_sunday`,`about_text`) VALUES (1,'Hanata Batik','Penjualan pakaian batik berkualitas','Pekanbaru, Riau','082284326992','6282284326992','hanatakreasiindonesia@gmail.com','Senin - Sabtu, 08.00 - 17.00','Minggu, 09.00 - 14.00','Hanata Batik menghadirkan koleksi batik untuk pria, wanita, keluarga, couple, casual, dan premium dengan pengalaman belanja yang sederhana.')");
    } else {
        $storeCols = [
            'store_name'=>"VARCHAR(120) NOT NULL DEFAULT 'Hanata Batik'",
            'business'=>"VARCHAR(160) NOT NULL DEFAULT 'Penjualan pakaian batik berkualitas'",
            'address'=>"VARCHAR(255) NOT NULL DEFAULT 'Pekanbaru, Riau'",
            'phone'=>"VARCHAR(30) NOT NULL DEFAULT '082284326992'",
            'whatsapp'=>"VARCHAR(30) NOT NULL DEFAULT '6282284326992'",
            'email'=>"VARCHAR(120) NOT NULL DEFAULT 'hanatakreasiindonesia@gmail.com'",
            'hours_weekday'=>"VARCHAR(120) NOT NULL DEFAULT 'Senin - Sabtu, 08.00 - 17.00'",
            'hours_sunday'=>"VARCHAR(120) NOT NULL DEFAULT 'Minggu, 09.00 - 14.00'",
            'about_text'=>"TEXT NULL",
            'updated_at'=>"DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        foreach ($storeCols as $c=>$d) if(!ensure_column_exists($conn,'store_settings',$c)) mysqli_query($conn,"ALTER TABLE store_settings ADD COLUMN `$c` $d");
        $qsc=mysqli_query($conn,"SELECT COUNT(*) c FROM store_settings");
        if($qsc && (int)(mysqli_fetch_assoc($qsc)['c']??0)===0) mysqli_query($conn,"INSERT INTO store_settings (id_store) VALUES (1)");
    }
    // Data kontak untuk akun admin yang sedang login.
    if (ensure_table_exists($conn,'user')) {
        if(!ensure_column_exists($conn,'user','email')) mysqli_query($conn,"ALTER TABLE user ADD COLUMN email VARCHAR(120) NULL");
        if(!ensure_column_exists($conn,'user','no_hp')) mysqli_query($conn,"ALTER TABLE user ADD COLUMN no_hp VARCHAR(30) NULL");
    }
    if (!ensure_table_exists($conn,'hero_settings')) {
        mysqli_query($conn, "CREATE TABLE `hero_settings` (
            `id_hero` INT NOT NULL AUTO_INCREMENT,
            `eyebrow` VARCHAR(120) NOT NULL DEFAULT 'Koleksi batik pilihan',
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `button_text` VARCHAR(80) NOT NULL DEFAULT 'Belanja Koleksi',
            `button_link` VARCHAR(255) NOT NULL DEFAULT 'koleksi.php',
            `image1` VARCHAR(255) DEFAULT '',
            `image2` VARCHAR(255) DEFAULT '',
            `image3` VARCHAR(255) DEFAULT '',
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_hero`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        mysqli_query($conn, "INSERT INTO hero_settings (id_hero,eyebrow,title,description,button_text,button_link,image1,image2,image3) VALUES (1,'Koleksi batik pilihan','Batik yang terasa modern, tetap membawa cerita Nusantara.','Temukan kemeja, atasan wanita, batik couple, dan koleksi premium yang dibuat untuk membuat penampilan sehari-hari lebih berkarakter.','Belanja Koleksi','koleksi.php','assets/pic/hanata-hero.png','assets/pic/hanata-hero-fabric.jpg','assets/pic/hanata-hero-look.jpg')");
    }
}

function order_status_meta() {
    return [
        'pending'=>['label'=>'Pesanan Masuk','class'=>'pending','icon'=>'bi-inbox'],
        'approved'=>['label'=>'Disetujui','class'=>'approved','icon'=>'bi-patch-check'],
        'processing'=>['label'=>'Diproses','class'=>'processing','icon'=>'bi-box-seam'],
        'shipped'=>['label'=>'Diantar','class'=>'shipped','icon'=>'bi-truck'],
        'delivered'=>['label'=>'Diterima','class'=>'delivered','icon'=>'bi-check2-circle'],
        'completed'=>['label'=>'Diterima','class'=>'delivered','icon'=>'bi-check2-circle'],
        'declined'=>['label'=>'Ditolak','class'=>'declined','icon'=>'bi-x-circle']
    ];
}
function order_status_label($status) {
    $m = order_status_meta();
    return $m[$status]['label'] ?? ucfirst((string)$status);
}
if (!function_exists('order_step_index')) {
    function order_step_index($status) {
        $steps=['pending','approved','processing','shipped','delivered'];
        $idx=array_search($status,$steps,true);
        return $idx===false?0:$idx;
    }
}
function whatsapp_admin_number() {
    global $conn;
    $wa='6282284326992';
    if (isset($conn) && $conn instanceof mysqli && function_exists('ensure_table_exists') && ensure_table_exists($conn,'store_settings')) {
        $q=mysqli_query($conn,"SELECT whatsapp FROM store_settings WHERE id_store=1 LIMIT 1");
        if($q && ($row=mysqli_fetch_assoc($q))) {
            $candidate=preg_replace('/\D+/', '', (string)($row['whatsapp']??''));
            if(str_starts_with($candidate,'0')) $candidate='62'.substr($candidate,1);
            if($candidate!=='') $wa=$candidate;
        }
    }
    return $wa;
}
function whatsapp_general_url($message='') {
    $message=trim((string)$message);
    if($message==='') $message='Halo Admin Hanata Batik, saya ingin bertanya mengenai produk batik.';
    return 'https://wa.me/'.whatsapp_admin_number().'?text='.rawurlencode($message);
}
function whatsapp_product_url($name,$price=0,$id=0,$orderNo='') {
    $msg='Halo Admin Hanata Batik, saya tertarik membeli produk: '.trim((string)$name);
    if((float)$price>0) $msg.="\nHarga: ".rupiah($price);
    if((int)$id>0) $msg.="\nID Produk: ".(int)$id;
    if($orderNo!=='') $msg.="\nNo. Pesanan: ".$orderNo;
    $msg.="\nMohon informasi ukuran, warna, stok, dan pengiriman. Terima kasih.";
    return 'https://wa.me/'.whatsapp_admin_number().'?text='.rawurlencode($msg);
}
if (!function_exists('rupiah')) {
    function rupiah($value) { return 'Rp '.number_format((float)$value,0,',','.'); }
}
if (!function_exists('admin_product_photo_url')) {
    function admin_product_photo_url($photo) {
        $photo=trim((string)$photo);
        if($photo==='') return 'pic/no-image.png';
        if(preg_match('~^(https?:)?//~i',$photo)) return $photo;
        if(str_starts_with($photo,'uploads/')) return '../'.ltrim($photo,'/');
        $filename=basename($photo);
        if(is_file(__DIR__.'/../pic/'.$filename)) return 'pic/'.$filename;
        if(is_file(__DIR__.'/../../uploads/products/'.$filename)) return '../uploads/products/'.$filename;
        return 'pic/'.$filename;
    }
}
?>
