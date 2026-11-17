<?php require '../config.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete'])) $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$_POST['delete']]);
    elseif (!empty($_POST['id'])) $pdo->prepare("UPDATE categories SET name=? WHERE id=?")->execute([trim($_POST['name']), $_POST['id']]);
    else $pdo->prepare("INSERT INTO categories(name) VALUES(?)")->execute([trim($_POST['name'])]);
    flash('บันทึกเรียบร้อย'); redirect('categories.php');
}
$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) n FROM categories c ORDER BY id")->fetchAll();
$title = 'หมวดหมู่สินค้า'; include '../includes/header.php'; include '_nav.php'; ?>
<div class="card-box">
  <form method="post" class="d-flex gap-2 mb-4">
    <input name="name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่" required>
    <button class="btn btn-pink text-nowrap"><i class="bi bi-plus"></i> เพิ่ม</button>
  </form>
  <?php foreach ($cats as $c): ?>
  <form method="post" class="d-flex gap-2 mb-2 align-items-center">
    <input type="hidden" name="id" value="<?= $c['id'] ?>">
    <input name="name" class="form-control" value="<?= e($c['name']) ?>">
    <small class="text-muted text-nowrap"><?= $c['n'] ?> สินค้า</small>
    <button class="btn btn-outline-pink">บันทึก</button>
    <button name="delete" value="<?= $c['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('ลบหมวดหมู่นี้?')"><i class="bi bi-trash"></i></button>
  </form>
  <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>