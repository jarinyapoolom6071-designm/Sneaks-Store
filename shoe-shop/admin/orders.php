<?php require '../config.php'; require_admin();
$s = $_GET['status'] ?? '';
$sql = "SELECT o.*, u.name FROM orders o JOIN users u ON u.id=o.user_id" . (isset(STATUS[$s]) ? " WHERE o.status=?" : '') . " ORDER BY o.id DESC";
$st = $pdo->prepare($sql); $st->execute(isset(STATUS[$s]) ? [$s] : []); $orders = $st->fetchAll();
$title = 'คำสั่งซื้อ'; include '../includes/header.php'; include '_nav.php'; ?>
<div class="card-box">
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= !$s ? 'active' : '' ?>" href="orders.php">ทั้งหมด</a></li>
    <?php foreach (STATUS as $k => $v): ?>
      <li class="nav-item"><a class="nav-link <?= $s === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $v ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="table-responsive"><table class="table align-middle">
    <tr><th>#</th><th>วันที่</th><th>ลูกค้า</th><th>ยอด</th><th>ชำระ</th><th>สถานะ</th><th></th></tr>
    <?php foreach ($orders as $o): ?>
    <tr><td><?= $o['id'] ?></td><td><small><?= $o['created_at'] ?></small></td><td><?= e($o['name']) ?></td>
      <td><?= money($o['total']) ?></td><td><?= PAY[$o['payment_method']] ?></td>
      <td><?= badge($o['status']) ?></td>
      <td><a href="order.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-pink">จัดการ</a></td></tr>
    <?php endforeach; ?>
  </table></div>
  <?php if (!$orders): ?><p class="text-center text-muted py-4 mb-0">ไม่มีคำสั่งซื้อ</p><?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>