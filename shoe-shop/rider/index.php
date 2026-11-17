<?php require '../config.php'; require_rider();
$st = $pdo->prepare("SELECT * FROM orders WHERE rider_id=? AND status IN('confirmed','shipping') ORDER BY status DESC, id");
$st->execute([user()['id']]); $jobs = $st->fetchAll();
$title = 'งานจัดส่งของฉัน'; include '../includes/header.php'; ?>
<h4 class="mb-3"><i class="bi bi-scooter"></i> งานจัดส่งของฉัน (<?= count($jobs) ?>)</h4>
<?php foreach ($jobs as $j): ?>
  <div class="card-box job-card mb-3">
    <div class="d-flex justify-content-between mb-2"><b>#<?= $j['id'] ?> · <?= e($j['receiver']) ?></b><?= badge($j['status']) ?></div>
    <p class="small mb-2"><?= e($j['address']) ?></p>
    <?php if ($j['payment_method'] === 'cod'): ?>
      <div class="alert alert-warning py-1 small mb-2"><i class="bi bi-cash-coin"></i> เก็บเงินปลายทาง <?= money($j['total']) ?></div>
    <?php endif; ?>
    <div class="d-flex gap-2">
      <a href="tel:<?= e($j['phone']) ?>" class="btn btn-outline-pink btn-sm"><i class="bi bi-telephone"></i> โทร</a>
      <a href="run.php?id=<?= $j['id'] ?>" class="btn btn-pink btn-sm flex-grow-1">เปิดงานจัดส่ง</a>
    </div>
  </div>
<?php endforeach; ?>
<?php if (!$jobs): ?><p class="text-center text-muted py-5">ยังไม่มีงานที่ได้รับมอบหมาย</p><?php endif; ?>
<?php include '../includes/footer.php'; ?>