<?php require '../config.php'; require_admin();
$id = (int)($_GET['id'] ?? 0); $r = null;
if ($id) {
    $st = $pdo->prepare("SELECT d.*, u.name uname, u.phone uphone, u.email uemail FROM defect_reports d JOIN users u ON u.id=d.user_id WHERE d.id=?");
    $st->execute([$id]); $r = $st->fetch();
    if (!$r) { not_found(); }
}
if ($r && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? ''); $new = $_POST['status'] ?? $r['status'];
    if (!isset(DEFECT_STATUS[$new])) $new = $r['status'];
    if (mb_strlen($msg) > 2000) { flash('ข้อความยาวเกิน 2,000 ตัวอักษร', 'danger'); redirect("defects.php?id=$id"); }
    $imgs = upload_images('img', 3, 'defects');
    if ($imgs === null) { flash(upload_error(), 'danger'); redirect("defects.php?id=$id"); }
    if ($msg !== '' || $imgs)
        $pdo->prepare("INSERT INTO defect_messages(report_id,sender,message,images) VALUES(?,?,?,?)")
            ->execute([$id, 'admin', $msg ?: null, $imgs ? implode(',', $imgs) : null]);
    $pdo->prepare("UPDATE defect_reports SET status=?, updated_at=NOW() WHERE id=?")->execute([$new, $id]);
    flash('บันทึกเรียบร้อย'); redirect("defects.php?id=$id");
}
$title = 'แจ้งสินค้าชำรุด'; include '../includes/header.php'; include '_nav.php';
if ($r):
    $st = $pdo->prepare("SELECT * FROM defect_messages WHERE report_id=? ORDER BY id"); $st->execute([$id]); $msgs = $st->fetchAll();
    $viewer = 'admin'; ?>
<div class="row g-4">
  <div class="col-lg-8"><div class="card-box">
    <div class="d-flex justify-content-between mb-2">
      <h5 class="price mb-0">แจ้งชำรุด #<?= $r['id'] ?> · <?= e($r['product_name']) ?> (ไซซ์ <?= e($r['size']) ?>)</h5><?= defect_badge($r['status']) ?>
    </div>
    <?php include '../includes/defect_thread.php'; ?>
    <form method="post" enctype="multipart/form-data" class="mt-3">
      <textarea name="message" class="form-control mb-2" rows="3" maxlength="2000" placeholder="ตอบกลับลูกค้า..."></textarea>
      <div class="row g-1 mb-2">
        <div class="col-4"><input type="file" name="img1" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm"></div>
        <div class="col-4"><input type="file" name="img2" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm"></div>
        <div class="col-4"><input type="file" name="img3" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm"></div>
      </div>
      <div class="input-group">
        <select name="status" class="form-select">
          <?php foreach (DEFECT_STATUS as $k => $v): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-pink"><i class="bi bi-send"></i> ส่ง / บันทึกสถานะ</button>
      </div>
    </form>
  </div></div>
  <div class="col-lg-4"><div class="card-box small">
    <h6>ข้อมูลลูกค้า</h6>
    <p class="mb-2"><b><?= e($r['uname']) ?></b><br><?= e($r['uphone']) ?><br><?= e($r['uemail']) ?></p>
    <p class="mb-2">ลักษณะปัญหา: <b><?= e(DEFECT_REASONS[$r['reason']] ?? '') ?></b></p>
    <?php if ($r['order_id']): ?><a href="order.php?id=<?= (int)$r['order_id'] ?>" class="btn btn-outline-pink btn-sm">ดูคำสั่งซื้อ #<?= (int)$r['order_id'] ?></a><?php endif; ?>
    <a href="defects.php" class="btn btn-link btn-sm">&laquo; กลับ</a>
  </div></div>
</div>
<?php else:
    $list = $pdo->query("SELECT d.*, u.name uname, (SELECT COUNT(*) FROM defect_messages WHERE report_id=d.id) n
                         FROM defect_reports d JOIN users u ON u.id=d.user_id
                         ORDER BY FIELD(d.status,'open','reviewing','resolved','rejected'), d.updated_at DESC")->fetchAll(); ?>
<div class="card-box table-responsive">
  <table class="table align-middle">
    <tr><th>#</th><th>ลูกค้า</th><th>สินค้า</th><th>ปัญหา</th><th>ข้อความ</th><th>อัปเดตล่าสุด</th><th>สถานะ</th><th></th></tr>
    <?php foreach ($list as $d): ?>
    <tr><td><?= $d['id'] ?></td><td><?= e($d['uname']) ?></td><td><?= e($d['product_name']) ?> <small class="text-muted">(<?= e($d['size']) ?>)</small></td>
      <td><?= e(DEFECT_REASONS[$d['reason']] ?? '') ?></td><td><?= $d['n'] ?></td><td><small><?= e($d['updated_at']) ?></small></td>
      <td><?= defect_badge($d['status']) ?></td>
      <td><a href="?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-pink">เปิดแชท</a></td></tr>
    <?php endforeach; ?>
  </table>
  <?php if (!$list): ?><p class="text-muted text-center py-4 mb-0">ยังไม่มีรายการแจ้งสินค้าชำรุด</p><?php endif; ?>
</div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>