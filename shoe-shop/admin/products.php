<?php require '../config.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete'])) {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$_POST['delete']]);
    } else {
        $audPost = implode(',', array_intersect(['men', 'women', 'kids'], (array)($_POST['audience'] ?? []))) ?: 'men,women';
        $salePost = ($_POST['sale_price'] ?? '') !== '' && (float)$_POST['sale_price'] > 0 && (float)$_POST['sale_price'] < (float)$_POST['price'] ? $_POST['sale_price'] : null;
        $d = [$_POST['category_id'] ?: null, trim($_POST['name']), $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['sizes'], $audPost, $salePost];
        $image = upload_image('image', 'products');
        if (!$image && upload_error()) { flash(upload_error(), 'danger'); redirect('products.php'); }
        if (!empty($_POST['id'])) {
            $pdo->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,stock=?,sizes=?,audience=?,sale_price=? WHERE id=?")->execute([...$d, $_POST['id']]);
            if ($image) $pdo->prepare("UPDATE products SET image=? WHERE id=?")->execute([$image, $_POST['id']]);
        } else {
            $pdo->prepare("INSERT INTO products(category_id,name,description,price,stock,sizes,audience,sale_price,image) VALUES(?,?,?,?,?,?,?,?,?)")->execute([...$d, $image]);
        }
    }
    flash('บันทึกสินค้าเรียบร้อย'); redirect('products.php');
}
$edit = null;
if (!empty($_GET['edit'])) { $st = $pdo->prepare("SELECT * FROM products WHERE id=?"); $st->execute([$_GET['edit']]); $edit = $st->fetch(); }
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name cat FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC")->fetchAll();
$title = 'จัดการสินค้า'; include '../includes/header.php'; include '_nav.php'; ?>
<div class="row g-4">
  <div class="col-lg-4"><div class="card-box">
    <h5><?= $edit ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' ?></h5>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
      <input name="name" class="form-control mb-2" placeholder="ชื่อสินค้า" required value="<?= e($edit['name'] ?? '') ?>">
      <select name="category_id" class="form-select mb-2">
        <option value="">-- เลือกหมวดหมู่ --</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
      <div class="row g-2 mb-2">
        <div class="col"><input name="price" type="number" step="0.01" class="form-control" placeholder="ราคา" required value="<?= $edit['price'] ?? '' ?>"></div>
        <div class="col"><input name="stock" type="number" class="form-control" placeholder="สต็อก" required value="<?= $edit['stock'] ?? '' ?>"></div>
      </div>
      <input name="sizes" class="form-control mb-2" placeholder="ไซซ์ คั่นด้วย , เช่น 36,37,38" value="<?= e($edit['sizes'] ?? '36,37,38,39,40,41,42') ?>">
      <?php $audSel = explode(',', $edit['audience'] ?? 'men,women'); ?>
      <div class="mb-2 small">แสดงในหมวด:
        <?php foreach (['men' => 'ผู้ชาย', 'women' => 'ผู้หญิง', 'kids' => 'เด็ก'] as $k => $v): ?>
          <label class="ms-2"><input type="checkbox" name="audience[]" value="<?= $k ?>" <?= in_array($k, $audSel) ? 'checked' : '' ?>> <?= $v ?></label>
        <?php endforeach; ?>
      </div>
      <input name="sale_price" type="number" step="0.01" min="0" class="form-control mb-2" placeholder="ราคาโปรโมชั่น (เว้นว่าง = ไม่ลดราคา)" value="<?= e($edit['sale_price'] ?? '') ?>">
      <textarea name="description" class="form-control mb-2" rows="4" placeholder="รายละเอียดสินค้า"><?= e($edit['description'] ?? '') ?></textarea>
      <?php if ($edit): ?><img src="<?= img($edit['image']) ?>" alt="<?= e($edit['name']) ?>" width="80" class="rounded mb-2"><?php endif; ?>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control mb-3">
      <button class="btn btn-pink w-100">บันทึก</button>
      <?php if ($edit): ?><a href="products.php" class="btn btn-link w-100">ยกเลิก</a><?php endif; ?>
    </form>
  </div></div>
  <div class="col-lg-8"><div class="card-box table-responsive">
    <table class="table align-middle">
      <tr><th></th><th>สินค้า</th><th>หมวด</th><th>ราคา</th><th>สต็อก</th><th>ขายแล้ว</th><th>ดาว</th><th></th></tr>
      <?php foreach ($products as $p): ?>
      <tr><td><img src="<?= img($p['image']) ?>" alt="<?= e($p['name']) ?>" width="50" height="50" loading="lazy" class="rounded"></td>
        <td><?= e($p['name']) ?></td><td><?= e($p['cat']) ?></td><td><?= $p['sale_price'] !== null ? '<s class="text-muted small">' . money($p['price']) . '</s> <span class="text-danger">' . money($p['sale_price']) . '</span>' : money($p['price']) ?></td>
        <td class="<?= $p['stock'] < 5 ? 'text-danger fw-bold' : '' ?>"><?= $p['stock'] ?></td><td><?= $p['sold'] ?></td>
        <td><small><?= $p['rating_avg'] ?> (<?= $p['rating_count'] ?>)</small></td>
        <td class="text-nowrap"><a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-pink"><i class="bi bi-pencil"></i></a>
          <form method="post" class="d-inline"><button name="delete" value="<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบสินค้านี้?')"><i class="bi bi-trash"></i></button></form></td></tr>
      <?php endforeach; ?>
    </table>
  </div></div>
</div>
<?php include '../includes/footer.php'; ?>