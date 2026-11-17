<?php $needMap = true; require '../config.php'; require_admin();
$o = get_order($pdo, $_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?"); $st->execute([$o['id']]); $items = $st->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset(STATUS[$_POST['status'] ?? ''])) {
    $new = $_POST['status'];
    $pdo->beginTransaction();
    if ($new === 'cancelled' && $o['status'] !== 'cancelled') {        // คืนสต็อกและคืนสิทธิ์คูปอง
        $r = $pdo->prepare("UPDATE products SET stock=stock+?, sold=GREATEST(sold-?,0) WHERE id=?");
        foreach ($items as $it) $r->execute([$it['qty'], $it['qty'], $it['product_id']]);
        if ($o['coupon_code']) $pdo->prepare("UPDATE coupons SET used=GREATEST(used-1,0) WHERE code=?")->execute([$o['coupon_code']]);
    }
    $pdo->prepare("UPDATE orders SET tracking_no=?, rider_id=? WHERE id=?")
        ->execute([trim($_POST['tracking_no']) ?: null, $_POST['rider_id'] ?: null, $o['id']]);
    set_status($pdo, $o['id'], $new, trim($_POST['note']) ?: STATUS[$new], $_POST['lat'], $_POST['lng']);
    $pdo->commit();
    flash('อัปเดตคำสั่งซื้อเรียบร้อย'); redirect("order.php?id={$o['id']}");
}
$st = $pdo->prepare("SELECT * FROM order_tracking WHERE order_id=? ORDER BY id"); $st->execute([$o['id']]); $tracks = $st->fetchAll();
$points = array_values(array_map(fn($t) => [(float)$t['lat'], (float)$t['lng']], array_filter($tracks, fn($t) => $t['lat'] !== null)));
$riders = $pdo->query("SELECT id, name FROM users WHERE role='rider' ORDER BY name")->fetchAll();
$title = 'จัดการคำสั่งซื้อ #' . $o['id']; include '../includes/header.php'; include '_nav.php'; ?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card-box mb-3">
      <div class="d-flex justify-content-between"><h5>คำสั่งซื้อ #<?= $o['id'] ?></h5><?= badge($o['status']) ?></div>
      <p class="small mb-2"><b><?= e($o['receiver']) ?></b> · <?= e($o['phone']) ?><br><?= e($o['address']) ?></p>
      <?php foreach ($items as $it): ?>
        <div class="d-flex justify-content-between small"><span><?= e($it['name']) ?> (<?= e($it['size']) ?>) × <?= $it['qty'] ?></span><span><?= money($it['price'] * $it['qty']) ?></span></div>
      <?php endforeach; ?>
      <?php if ($o['discount'] > 0): ?><div class="d-flex justify-content-between small price"><span>คูปอง <?= e($o['coupon_code']) ?></span><span>-<?= money($o['discount']) ?></span></div><?php endif; ?>
      <?php if (($o['vat'] ?? 0) > 0): ?><div class="d-flex justify-content-between small"><span>ภาษีมูลค่าเพิ่ม <?= round(VAT_RATE * 100) ?>%</span><span><?= money($o['vat']) ?></span></div><?php endif; ?>
      <div class="text-end mt-2">ยอดรวม <b class="price"><?= money($o['total']) ?></b> (<?= PAY[$o['payment_method']] ?>)</div>
      <?php if ($o['charge_id']): ?><div class="text-end small text-muted">Omise: <?= e($o['charge_id']) ?></div><?php endif; ?>
    </div>
    <?php if ($o['slip']): ?>
    <div class="card-box mb-3 text-center"><h6>สลิปการโอน</h6>
      <a href="<?= img($o['slip']) ?>" target="_blank"><img src="<?= img($o['slip']) ?>" alt="สลิปการโอนเงินคำสั่งซื้อ #<?= $o['id'] ?>" class="img-fluid rounded" style="max-height:300px"></a></div>
    <?php endif; ?>
    <div class="card-box">
      <h6>ประวัติสถานะ</h6>
      <ul class="timeline mt-3">
        <?php foreach (array_reverse($tracks) as $t): ?>
          <li><b><?= STATUS[$t['status']] ?? e($t['status']) ?></b> <small class="text-muted"><?= $t['created_at'] ?></small><br>
            <small><?= e($t['note']) ?><?= $t['lat'] ? ' <i class="bi bi-geo-alt-fill price"></i>' : '' ?></small></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-7"><div class="card-box">
    <h5 class="price">อัปเดตสถานะ / ตำแหน่งพัสดุ</h5>
    <form method="post">
      <div class="row g-2 mb-2">
        <div class="col-md-6"><select name="status" class="form-select">
          <?php foreach (STATUS as $k => $v): ?><option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-md-6"><input name="tracking_no" class="form-control" placeholder="เลขพัสดุ (ถ้ามี)" value="<?= e($o['tracking_no']) ?>"></div>
      </div>
      <select name="rider_id" class="form-select mb-2">
        <option value="">-- มอบหมายไรเดอร์ (ไม่บังคับ) --</option>
        <?php foreach ($riders as $r): ?><option value="<?= $r['id'] ?>" <?= $o['rider_id'] == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
      </select>
      <input name="note" class="form-control mb-2" placeholder="หมายเหตุ เช่น พัสดุถึงศูนย์กระจายสินค้าบางนา">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">คลิกแผนที่เพื่อระบุตำแหน่งพัสดุปัจจุบัน (ไม่บังคับ)</small>
        <button type="button" id="gpsBtn" class="btn btn-sm btn-outline-pink"><i class="bi bi-crosshair"></i> ใช้ GPS เครื่องนี้</button>
      </div>
      <div id="map" class="map mb-3"></div>
      <input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng">
      <button class="btn btn-pink w-100">บันทึกการอัปเดต</button>
    </form>
  </div></div>
</div>
<script>
const shop = [<?= SHOP_LAT ?>, <?= SHOP_LNG ?>];
const dest = <?= $o['lat'] ? json_encode([(float)$o['lat'], (float)$o['lng']]) : 'null' ?>;
const pts  = <?= json_encode($points) ?>;
const map = L.map('map');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '© OpenStreetMap'}).addTo(map);
const pin = (c, i) => L.divIcon({className: '', html: `<div class="map-pin" style="background:${c}"><i class="bi bi-${i}"></i></div>`, iconSize: [34, 34], iconAnchor: [17, 17]});
L.marker(shop, {icon: pin('#ff6b9d', 'shop')}).addTo(map);
if (dest) L.marker(dest, {icon: pin('#28a745', 'house-heart')}).addTo(map).bindPopup('ปลายทาง');
if (pts.length) L.polyline([shop, ...pts], {color: '#e84a7f', weight: 4}).addTo(map);
const all = [shop, ...pts]; if (dest) all.push(dest);
map.fitBounds(all, {padding: [40, 40], maxZoom: 14});
let cur;
function setCur(la, ln) {
  if (cur) cur.setLatLng([la, ln]); else cur = L.marker([la, ln], {icon: pin('#e84a7f', 'truck')}).addTo(map);
  lat.value = la.toFixed(7); lng.value = ln.toFixed(7);
}
map.on('click', e => setCur(e.latlng.lat, e.latlng.lng));
gpsBtn.onclick = () => navigator.geolocation.getCurrentPosition(p => { setCur(p.coords.latitude, p.coords.longitude); map.setView([p.coords.latitude, p.coords.longitude], 15); });
</script>
<?php include '../includes/footer.php'; ?>