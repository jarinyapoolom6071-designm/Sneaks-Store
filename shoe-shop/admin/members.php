<?php require '../config.php'; require_admin();
$roles = ['member' => 'สมาชิก', 'rider' => 'ไรเดอร์', 'admin' => 'แอดมิน'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['id'] != user()['id'] && isset($roles[$_POST['role'] ?? ''])) {
    $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$_POST['role'], $_POST['id']]);
    flash('เปลี่ยนสิทธิ์เรียบร้อย (ผู้ใช้ต้องออกจากระบบแล้วเข้าใหม่)'); redirect('members.php');
}
$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) n,
  (SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=u.id AND status<>'cancelled') spent FROM users u ORDER BY id DESC")->fetchAll();
$title = 'สมาชิก'; include '../includes/header.php'; include '_nav.php'; ?>
<div class="card-box table-responsive"><table class="table align-middle">
  <tr><th>ชื่อ</th><th>อีเมล</th><th>โทร</th><th>คำสั่งซื้อ</th><th>ยอดซื้อ</th><th width="160">สิทธิ์</th></tr>
  <?php foreach ($users as $u): ?>
  <tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td>
    <td><?= $u['n'] ?></td><td><?= money($u['spent']) ?></td>
    <td><?php if ($u['id'] == user()['id']): ?><span class="badge bg-pink">แอดมิน (คุณ)</span>
      <?php else: ?><form method="post"><input type="hidden" name="id" value="<?= $u['id'] ?>">
        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach ($roles as $k => $v): ?><option value="<?= $k ?>" <?= $u['role'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
        </select></form><?php endif; ?></td></tr>
  <?php endforeach; ?>
</table></div>
<?php include '../includes/footer.php'; ?>