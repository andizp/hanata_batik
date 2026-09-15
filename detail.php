<?php
require_once __DIR__.'/includes/functions.php'; $conn=db_connect(); $id=(int)($_GET['id']??0);
$q=mysqli_query($conn,"SELECT b.*,COALESCE(t.nm_type,'-') AS nm_type FROM barang b LEFT JOIN `type` t ON t.id_type=b.id_type WHERE b.id_brg={$id} AND b.status=1 LIMIT 1");
$product=$q?mysqli_fetch_assoc($q):null; if(!$product) redirect('koleksi.php'); $stock=(int)$product['stok']; $pageTitle=$product['nm_brg'];
$availableSizes=product_sizes($product['ukuran']??'S,M,L,XL');
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="container detail"><div class="detail-img"><div class="detail-image-placeholder"><?php $photoUrl = product_photo_url($product['foto'] ?? ''); if ($photoUrl): ?><img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($product['nm_brg']); ?>"><?php else: ?><span>Foto Produk</span><?php endif; ?></div></div><div class="detail-info"><span class="badge"><i class="bi bi-tag"></i> <?php echo e($product['nm_type']); ?></span><h1><?php echo e($product['nm_brg']); ?></h1><p class="muted"><?php echo e($product['ket']); ?></p><div class="price"><?php echo rupiah($product['hrg_jual']); ?></div><div class="info-list"><div class="info-row"><span>Type</span><b><?php echo e($product['nm_type']); ?></b></div><div class="info-row"><span>Ukuran</span><b><?php echo e(implode(', ',$availableSizes)); ?></b></div><div class="info-row"><span>Stok</span><b><?php echo $stock>0?$stock.' pcs (Ready)':'Habis'; ?></b></div><div class="info-row"><span>Pembelian</span><b>Keranjang → Checkout WhatsApp</b></div></div><?php if($stock>0): ?><form class="detail-buy-box" id="detailBuyForm" method="post" action="keranjang.php"><input type="hidden" name="action" value="add"><input type="hidden" name="id_brg" value="<?php echo (int)$product['id_brg']; ?>"><div class="detail-qty"><label>Ukuran</label><select class="input" name="size" required><?php foreach($availableSizes as $sz): ?><option value="<?php echo e($sz); ?>"><?php echo e($sz); ?></option><?php endforeach; ?></select><label>Jumlah</label><div class="qty-box"><button type="button" class="qty-btn" data-minus>-</button><input type="number" name="qty" value="1" min="1" max="<?php echo $stock; ?>"><button type="button" class="qty-btn" data-plus>+</button></div></div><div class="detail-buy-actions"><button class="btn btn-primary" type="button" id="addCartBtn"><i class="bi bi-bag-plus"></i> Tambah ke Keranjang</button><button class="btn btn-gold" type="submit" name="checkout_after_add" value="1"><i class="bi bi-arrow-right-circle"></i> Checkout</button></div><div class="cart-inline-status" id="cartInlineStatus" role="status" aria-live="polite"></div></form><?php else: ?><button class="btn btn-soft" disabled>Stok Habis</button><?php endif; ?><div class="pay-alert"><i class="bi bi-whatsapp"></i> Setelah semua produk dipilih, checkout dilakukan melalui WhatsApp Admin.</div><div class="card-actions"><a class="btn btn-outline" href="koleksi.php"><i class="bi bi-arrow-left"></i> Kembali ke Koleksi</a></div></div></div></section>
<script>
(function(){
  document.querySelectorAll('.qty-box').forEach(function(box){
    const input=box.querySelector('input');
    box.querySelector('[data-minus]').addEventListener('click',()=>{input.value=Math.max(parseInt(input.min||1),parseInt(input.value||1)-1)});
    box.querySelector('[data-plus]').addEventListener('click',()=>{input.value=Math.min(parseInt(input.max||999),parseInt(input.value||1)+1)});
  });
  const form=document.getElementById('detailBuyForm');
  const addBtn=document.getElementById('addCartBtn');
  const status=document.getElementById('cartInlineStatus');
  if(form && addBtn){
    addBtn.addEventListener('click', async function(){
      addBtn.disabled=true;
      const old=addBtn.innerHTML;
      addBtn.innerHTML='<i class="bi bi-arrow-repeat spin"></i> Menambahkan...';
      status.className='cart-inline-status';
      status.textContent='';
      try{
        const body=new URLSearchParams(new FormData(form));
        const response=await fetch('keranjang.php',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body});
        const data=await response.json();
        if(!data.ok) throw new Error(data.message||'Produk gagal dimasukkan ke keranjang.');
        const badge=document.querySelector('.cart-count');
        if(badge) badge.textContent=data.cart_count;
        const mobileBadge=document.querySelector('.mobile-cart-badge');
        if(mobileBadge) mobileBadge.textContent=data.cart_count;
        status.className='cart-inline-status success';
        status.innerHTML='<i class="bi bi-check-circle"></i> '+(data.message||'Produk berhasil dimasukkan ke keranjang.');
        addBtn.innerHTML='<i class="bi bi-check2"></i> Sudah di Keranjang';
      }catch(err){
        status.className='cart-inline-status error';
        status.textContent=err.message;
        addBtn.innerHTML=old;
      }finally{
        setTimeout(()=>{addBtn.disabled=false; if(addBtn.innerHTML.includes('Sudah di Keranjang')) addBtn.innerHTML=old;},1800);
      }
    });
  }
})();
</script>
<?php include __DIR__.'/includes/footer.php'; ?>