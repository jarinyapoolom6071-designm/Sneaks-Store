<?php require '../config.php'; require_admin();
$q = fn($sql) => $pdo->query($sql)->fetchColumn();
$stats = [
    ['ยอดขาย', money($q("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN('confirmed','shipping','delivered')")), 'cash-stack'],
    ['คำสั่งซื้อทั้งหมด', $q("SELECT COUNT(*) FROM orders"), 'receipt'],
    ['รอตรวจสอบ', $q("SELECT COUNT(*) FROM orders WHERE status='verifying'"), 'hourglass-split'],
    ['สมาชิก', $q("SELECT COUNT(*) FROM users WHERE role='member'"), 'people'],
];
$recent = $pdo->query("SELECT o.*, u.name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 10")->fetchAll();
$low = $pdo->query("SELECT id, name, stock FROM products WHERE stock < 5 ORDER BY stock")->fetchAll();
$title = 'แดชบอร์ด'; include '../includes/header.php'; include '_nav.php'; ?>
<div class="row g-3 mb-4">
  <?php foreach ($stats as [$label, $val, $icon]): ?>
  <div class="col-6 col-lg-3"><div class="card-box d-flex justify-content-between align-items-center">
    <div><small class="text-muted"><?= $label ?></small><div class="fs-4 price"><?= $val ?></div></div>
    <i class="bi bi-<?= $icon ?> fs-1" style="color:var(--pink)"></i>
  </div></div>
  <?php endforeach; ?>
</div>
<?php if ($low): ?>
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> สินค้าใกล้หมด:
  <?php foreach ($low as $l): ?><a href="products.php?edit=<?= $l['id'] ?>" class="me-2"><?= e($l['name']) ?> (<?= $l['stock'] ?>)</a><?php endforeach; ?>
</div>
<?php endif; ?>
<div class="card-box table-responsive">
  <h5>คำสั่งซื้อล่าสุด</h5>
  <table class="table align-middle">
    <tr><th>#</th><th>ลูกค้า</th><th>ยอด</th><th>ชำระ</th><th>สถานะ</th><th></th></tr>
    <?php foreach ($recent as $o): ?>
    <tr><td><?= $o['id'] ?></td><td><?= e($o['name']) ?></td><td><?= money($o['total']) ?></td>
      <td><?= PAY[$o['payment_method']] ?></td><td><?= badge($o['status']) ?></td>
      <td><a href="order.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-pink">จัดการ</a></td></tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include '../includes/footer.php'; ?>