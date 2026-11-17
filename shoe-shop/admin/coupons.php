<?php require '../config.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['toggle'])) {
        $pdo->prepare("UPDATE coupons SET active=1-active WHERE id=?")->execute([$_POST['toggle']]);
    } elseif (!empty($_POST['delete'])) {
        $pdo->prepare("DELETE FROM coupons WHERE id=?")->execute([$_POST['delete']]);
    } else {
        $type  = $_POST['type'] === 'percent' ? 'percent' : 'fixed';
        $value = (float)$_POST['value'];
        if ($type === 'percent') $value = min($value, 100);
        try {
            $pdo->prepare("INSERT INTO coupons(code,type,value,min_total,max_uses,expires_at) VALUES(?,?,?,?,?,?)")
                ->execute([strtoupper(trim($_POST['code'])), $type, $value, (float)$_POST['min_total'],
                           $_POST['max_uses'] ?: null, $_POST['expires_at'] ?: null]);
        } catch (PDOException) { flash('รหัสคูปองนี้มีอยู่แล้ว', 'danger'); redirect('coupons.php'); }
    }
    flash('บันทึกเรียบร้อย'); redirect('coupons.php');
}
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
$title = 'คูปองส่วนลด'; include '../includes/header.php'; include '_nav.php'; ?>
<div class="row g-4">
  <div class="col-lg-4"><div class="card-box">
    <h5>สร้างคูปองใหม่</h5>
    <form method="post">
      <input name="code" class="form-control mb-2 text-uppercase" placeholder="รหัสคูปอง เช่น SALE20" required>
      <div class="row g-2 mb-2">
        <div class="col"><select name="type" class="form-select">
          <option value="percent">ลดเป็น %</option><option value="fixed">ลดเป็นบาท</option></select></div>
        <div class="col"><input name="value" type="number" step="0.01" min="1" class="form-control" placeholder="มูลค่า" required></div>
      </div>
      <input name="min_total" type="number" step="0.01" class="form-control mb-2" placeholder="ยอดซื้อขั้นต่ำ (บาท)" value="0">
      <input name="max_uses" type="number" class="form-control mb-2" placeholder="จำกัดจำนวนครั้ง (เว้นว่าง = ไม่จำกัด)">
      <label class="small text-muted">วันหมดอายุ (ไม่บังคับ)</label>
      <input name="expires_at" type="date" class="form-control mb-3">
      <button class="btn btn-pink w-100">สร้างคูปอง</button>
    </form>
  </div></div>
  <div class="col-lg-8"><div class="card-box table-responsive">
    <table class="table align-middle">
      <tr><th>รหัส</th><th>ส่วนลด</th><th>ขั้นต่ำ</th><th>ใช้แล้ว</th><th>หมดอายุ</th><th>สถานะ</th><th></th></tr>
      <?php foreach ($coupons as $c): ?>
      <tr>
        <td><span class="coupon-tag"><?= e($c['code']) ?></span></td>
        <td><?= $c['type'] === 'percent' ? (float)$c['value'] . '%' : money($c['value']) ?></td>
        <td><?= money($c['min_total']) ?></td>
        <td><?= $c['used'] ?> / <?= $c['max_uses'] ?? '∞' ?></td>
        <td><small><?= $c['expires_at'] ?? '-' ?></small></td>
        <td><span class="badge <?= $c['active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $c['active'] ? 'เปิด' : 'ปิด' ?></span></td>
        <td class="text-nowrap"><form method="post" class="d-inline">
          <button name="toggle" value="<?= $c['id'] ?>" class="btn btn-sm btn-outline-pink"><i class="bi bi-power"></i></button>
          <button name="delete" value="<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบคูปองนี้?')"><i class="bi bi-trash"></i></button>
        </form></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div></div>
</div>
<?php include '../includes/footer.php'; ?>