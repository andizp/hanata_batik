<?php
require_once __DIR__ . '/includes/functions.php';
$conn = db_connect();
$pageTitle = 'Keranjang Belanja';
$action = $_POST['action'] ?? '';
$error = '';
$notice = '';

if ($action === 'add') {
    $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    $checkoutAfterAdd = !empty($_POST['checkout_after_add']);
    $id = (int)($_POST['id_brg'] ?? 0);
    $size = strtoupper(trim($_POST['size'] ?? ''));
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $q = mysqli_query($conn, "SELECT id_brg, nm_brg, stok, status, ukuran FROM barang WHERE id_brg={$id} LIMIT 1");
    $product = $q ? mysqli_fetch_assoc($q) : null;
    if (!$product || (int)$product['status'] !== 1) {
        $error = 'Produk tidak ditemukan.';
    } elseif ((int)$product['stok'] <= 0) {
        $error = 'Produk sedang habis.';
    } else {
        $sizes = product_sizes($product['ukuran'] ?? 'S,M,L,XL');
        if (!in_array($size, $sizes, true)) { $error = 'Ukuran produk tidak tersedia.'; }
        else {
            $key = cart_key($id, $size);
            $existing = (int)(cart_items()[$key] ?? 0);
            cart_set_item($id, min((int)$product['stok'], $existing + $qty), $size);
            $notice = $product['nm_brg'] . ' ukuran ' . $size . ' berhasil dimasukkan ke keranjang.';
        }
    }
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => $error === '',
            'message' => $error !== '' ? $error : $notice,
            'cart_count' => cart_count()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($checkoutAfterAdd && $error === '') {
        redirect('keranjang.php');
    }
}

if (!empty($_POST['remove_id'])) {
    [$removeId,$removeSize]=cart_key_parts($_POST['remove_id']);
    cart_set_item($removeId,0,$removeSize);
    $notice='Produk dihapus dari keranjang.';
} elseif ($action === 'update') {
    foreach ((array)($_POST['qty'] ?? []) as $key => $qty) {
        [$id,$size]=cart_key_parts($key); $qty=(int)$qty;
        $q=mysqli_query($conn, "SELECT stok,ukuran FROM barang WHERE id_brg={$id} LIMIT 1");
        $row=$q?mysqli_fetch_assoc($q):null; $sizes=product_sizes($row['ukuran']??'S,M,L,XL');
        if(!$row || !in_array($size,$sizes,true) || (int)$row['stok']<=0 || $qty<=0) cart_set_item($id,0,$size);
        else cart_set_item($id,min((int)$row['stok'],$qty),$size);
    }
    $notice='Keranjang berhasil diperbarui.';
}


if ($action === 'clear') {
    cart_clear();
    $notice = 'Keranjang dikosongkan.';
}

if ($action === 'checkout') {
    if (!is_member_login()) {
        redirect('login.php?next=' . rawurlencode('keranjang.php?checkout=1'));
    }
    $products = get_cart_products($conn);
    if (!$products) {
        $error = 'Keranjang masih kosong.';
    } else {
        $valid = array_filter($products, fn($p) => (int)($p['qty'] ?? 0) > 0 && (int)($p['stok'] ?? 0) >= (int)($p['qty'] ?? 0));
        if (count($valid) !== count($products)) {
            $error = 'Ada produk di keranjang yang sudah tidak tersedia. Periksa kembali jumlahnya.';
        } else {
            $total = cart_total($products);
            $no = 'FJ-' . date('YmdHis') . '-' . random_int(100, 999);
            $idMember = current_member_id();
            $name = $_SESSION['nm_member'] ?? 'Member';
            mysqli_begin_transaction($conn);
            try {
                $noSql = mysqli_real_escape_string($conn, $no);
                $now = date('Y-m-d H:i:s');
                mysqli_query($conn, "INSERT INTO faktur_jual(no_faktur,id_member,tgl_faktur,total,status,order_status,tgl_order) VALUES('{$noSql}',{$idMember},CURDATE(),{$total},0,'pending','{$now}')") or throw new Exception(mysqli_error($conn));
                $lines = [];
                foreach ($products as $p) {
                    $idBrg=(int)$p['id_brg']; $qty=(int)$p['qty']; $harga=(int)$p['hrg_jual']; $size=mysqli_real_escape_string($conn,$p['selected_size']??'M');
                    mysqli_query($conn, "INSERT INTO detal_jual(no_faktur,id_brg,ukuran,jumlah,harga,status) VALUES('{$noSql}',{$idBrg},'{$size}',{$qty},{$harga},1)") or throw new Exception(mysqli_error($conn));
                    $lines[]='- '.$p['nm_brg'].' (Size '.($p['selected_size']??'M').') x '.$qty.' = '.rupiah($harga*$qty);
                }
                mysqli_commit($conn);
                $msg = "Halo Admin Hanata Batik, saya {$name} ingin checkout dari keranjang website.\nNo. Pesanan: {$no}\n" . implode("\n", $lines) . "\nTotal: " . rupiah($total) . "\nMohon konfirmasi ketersediaan ukuran, warna, alamat pengiriman, dan proses pesanan. Terima kasih.";
                cart_clear();
                redirect(whatsapp_general_url($msg));
            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $error = 'Checkout gagal: ' . $e->getMessage();
            }
        }
    }
}

$products = get_cart_products($conn);
$total = cart_total($products);
include __DIR__ . '/includes/header.php';
?>
<section class="section cart-page">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow"><i class="bi bi-bag-heart"></i> Belanja Hanata Batik</span>
        <h1 class="cart-title">Keranjang Belanja</h1>
        <p>Pilih beberapa produk sekaligus, atur jumlahnya, lalu checkout ke WhatsApp admin.</p>
      </div>
      <a class="btn btn-outline" href="koleksi.php"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
    </div>

    <?php if ($notice): ?><div class="alert success"><?php echo e($notice); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert danger"><?php echo e($error); ?></div><?php endif; ?>

    <?php if (!$products): ?>
      <div class="cart-empty">
        <div class="cart-empty-icon"><i class="bi bi-bag-x"></i></div>
        <h2>Keranjang masih kosong</h2>
        <p>Tambahkan produk batik yang Anda sukai terlebih dahulu.</p>
        <a class="btn btn-primary" href="koleksi.php"><i class="bi bi-shop"></i> Lihat Koleksi</a>
      </div>
    <?php else: ?>
      <div class="cart-layout">
        <form class="cart-list-card" method="post">
          <input type="hidden" name="action" value="update">
          <?php foreach ($products as $p): ?>
            <article class="cart-item">
              <a class="cart-item-image" href="detail.php?id=<?php echo (int)$p['id_brg']; ?>">
                <?php $photoUrl = product_photo_url($p['foto'] ?? ''); if ($photoUrl): ?><img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($p['nm_brg']); ?>"><?php else: ?><span>Foto Produk</span><?php endif; ?>
              </a>
              <div class="cart-item-info">
                <span class="badge"><?php echo e($p['nm_type']); ?></span>
                <h3><a href="detail.php?id=<?php echo (int)$p['id_brg']; ?>"><?php echo e($p['nm_brg']); ?></a></h3>
                <p><?php echo e($p['ket']); ?></p>
                <div class="cart-size"><b>Size <?php echo e($p['selected_size']); ?></b></div>
                <strong><?php echo rupiah($p['hrg_jual']); ?></strong>
              </div>
              <div class="cart-item-actions">
                <label>Jumlah</label>
                <div class="qty-box"><button type="button" class="qty-btn" data-minus>-</button><input type="number" name="qty[<?php echo e($p['cart_key']); ?>]" value="<?php echo (int)$p['qty']; ?>" min="1" max="<?php echo (int)$p['stok']; ?>"><button type="button" class="qty-btn" data-plus>+</button></div>
                <b class="cart-subtotal"><?php echo rupiah($p['subtotal']); ?></b>
                <button class="cart-remove" type="submit" name="remove_id" value="<?php echo e($p['cart_key']); ?>"><i class="bi bi-trash3"></i> Hapus</button>
              </div>
            </article>
          <?php endforeach; ?>
          <div class="cart-list-footer">
            <button class="btn btn-outline" type="submit"><i class="bi bi-arrow-repeat"></i> Perbarui Keranjang</button>
            <button class="btn btn-link-danger" type="submit" name="action" value="clear"><i class="bi bi-trash"></i> Kosongkan</button>
          </div>
        </form>

        <aside class="cart-summary-card">
          <span class="eyebrow">Ringkasan Pesanan</span>
          <h2>Checkout</h2>
          <div class="summary-row"><span>Jumlah item</span><b><?php echo cart_count(); ?> pcs</b></div>
          <div class="summary-row summary-total"><span>Total</span><strong><?php echo rupiah($total); ?></strong></div>
          <p class="cart-note"><i class="bi bi-whatsapp"></i> Setelah checkout, detail semua item akan otomatis dibawa ke chat WhatsApp admin.</p>
          <?php if (is_member_login()): ?>
            <form method="post"><input type="hidden" name="action" value="checkout"><button class="btn btn-primary btn-full" type="submit"><i class="bi bi-whatsapp"></i> Checkout ke WhatsApp</button></form>
          <?php else: ?>
            <a class="btn btn-primary btn-full" href="login.php?next=<?php echo rawurlencode('keranjang.php?checkout=1'); ?>"><i class="bi bi-person-check"></i> Login untuk Checkout</a>
            <small class="cart-note-small">Login diperlukan agar pesanan tersimpan dan statusnya bisa dipantau dari Profil.</small>
          <?php endif; ?>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
<script>
document.querySelectorAll('.qty-box').forEach(function(box){
  const input=box.querySelector('input');
  box.querySelector('[data-minus]').addEventListener('click',()=>{input.value=Math.max(parseInt(input.min||1),parseInt(input.value||1)-1)});
  box.querySelector('[data-plus]').addEventListener('click',()=>{input.value=Math.min(parseInt(input.max||999),parseInt(input.value||1)+1)});
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
