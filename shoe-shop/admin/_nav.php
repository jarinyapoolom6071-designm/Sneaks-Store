<?php $cur = basename($_SERVER['PHP_SELF']);
$menu = ['index.php'=>['speedometer2','แดชบอร์ด'], 'orders.php'=>['receipt','คำสั่งซื้อ'],
         'products.php'=>['bag','สินค้า'], 'categories.php'=>['tags','หมวดหมู่'],
         'coupons.php'=>['ticket-perforated','คูปอง'], 'members.php'=>['people','สมาชิก'],
         'defects.php'=>['exclamation-triangle','แจ้งชำรุด']]; ?>
<div class="card-box mb-4 d-flex gap-2 flex-wrap">
  <?php foreach ($menu as $file => [$icon, $label]): ?>
    <a href="<?= $file ?>" class="btn <?= $cur === $file || ($cur === 'order.php' && $file === 'orders.php') ? 'btn-pink' : 'btn-outline-pink' ?>">
      <i class="bi bi-<?= $icon ?>"></i> <?= $label ?></a>
  <?php endforeach; ?>
</div>