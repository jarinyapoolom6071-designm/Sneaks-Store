<?php $needMap = true; require '../config.php'; require_rider();
$st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND rider_id=? AND status IN('confirmed','shipping')");
$st->execute([$_GET['id'] ?? 0, user()['id']]);
$o = $st->fetch();
if (!$o) not_found();

$a = $_POST['action'] ?? '';
$lat = $_POST['lat'] ?? null; $lng = $_POST['lng'] ?? null;
if ($a === 'ping' && $lat) {
    $pdo->prepare("UPDATE orders SET cur_lat=?, cur_lng=?, cur_at=NOW() WHERE id=?")->execute([(float)$lat, (float)$lng, $o['id']]);
    exit('ok');
}
if ($a === 'start' && $o['status'] === 'confirmed') {
    set_status($pdo, $o['id'], 'shipping', 'ไรเดอร์ ' . user()['name'] . ' รับพัสดุแล้ว กำลังนำส่ง', $lat, $lng);
    redirect("run.php?id={$o['id']}");
}
if ($a === 'done' && $o['status'] === 'shipping') {
    $note = 'ส่งถึงผู้รับเรียบร้อย' . ($o['payment_method'] === 'cod' ? ' · เก็บเงินปลายทาง ' . money($o['total']) . ' แล้ว' : '');
    set_status($pdo, $o['id'], 'delivered', $note, $lat, $lng);
    flash('ปิดงาน #' . $o['id'] . ' เรียบร้อย'); redirect('index.php');
}
$title = 'จัดส่ง #' . $o['id']; include '../includes/header.php'; ?>
<div class="card-box mb-3">
  <div class="d-flex justify-content-between"><h5>#<?= $o['id'] ?> · <?= e($o['receiver']) ?></h5><?= badge($o['status']) ?></div>
  <p class="small mb-2"><?= e($o['address']) ?></p>
  <?php if ($o['payment_method'] === 'cod'): ?>
    <div class="alert alert-warning py-2 mb-2"><i class="bi bi-cash-coin"></i> เก็บเงินปลายทาง <b><?= money($o['total']) ?></b></div>
  <?php endif; ?>
  <div class="d-flex gap-2">
    <a href="tel:<?= e($o['phone']) ?>" class="btn btn-outline-pink"><i class="bi bi-telephone"></i> โทรหาลูกค้า</a>
    <?php if ($o['lat']): ?>
      <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $o['lat'] ?>,<?= $o['lng'] ?>" target="_blank" class="btn btn-outline-pink flex-grow-1"><i class="bi bi-sign-turn-right"></i> นำทางด้วย Google Maps</a>
    <?php endif; ?>
  </div>
</div>
<div class="card-box">
  <div id="map" class="map mb-2"></div>
  <p id="gps" class="small text-muted"><i class="bi bi-broadcast"></i> กำลังหาตำแหน่ง GPS...</p>
  <form method="post">
    <input type="hidden" name="lat"><input type="hidden" name="lng">
    <?php if ($o['status'] === 'confirmed'): ?>
      <button name="action" value="start" class="btn btn-pink btn-lg w-100"><i class="bi bi-box-arrow-right"></i> รับพัสดุแล้ว เริ่มจัดส่ง</button>
    <?php else: ?>
      <button name="action" value="done" class="btn btn-success btn-lg w-100" onclick="return confirm('ยืนยันว่าส่งถึงผู้รับแล้ว?')"><i class="bi bi-check2-circle"></i> ส่งสำเร็จ</button>
    <?php endif; ?>
  </form>
  <small class="text-muted d-block mt-2">เปิดหน้านี้ค้างไว้ระหว่างจัดส่ง ลูกค้าจะเห็นตำแหน่งของคุณทุก ~15 วินาที</small>
</div>
<script>
const shop = [<?= SHOP_LAT ?>, <?= SHOP_LNG ?>];
const dest = <?= $o['lat'] ? json_encode([(float)$o['lat'], (float)$o['lng']]) : 'null' ?>;
const shipping = <?= json_encode($o['status'] === 'shipping') ?>;
const csrf = '<?= $_SESSION['csrf'] ?>';
const map = L.map('map').setView(dest || shop, 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '© OpenStreetMap'}).addTo(map);
const pin = (c, i) => L.divIcon({className: '', html: `<div class="map-pin" style="background:${c}"><i class="bi bi-${i}"></i></div>`, iconSize: [34, 34], iconAnchor: [17, 17]});
if (dest) L.marker(dest, {icon: pin('#28a745', 'house-heart')}).addTo(map).bindPopup('ปลายทาง');
let me, last = 0;
navigator.geolocation.watchPosition(p => {
  const ll = [p.coords.latitude, p.coords.longitude];
  if (me) me.setLatLng(ll);
  else { me = L.marker(ll, {icon: pin('#7c4dff', 'scooter')}).addTo(map); map.fitBounds(dest ? [ll, dest] : [ll], {padding: [40, 40], maxZoom: 16}); }
  document.querySelector('[name=lat]').value = ll[0];
  document.querySelector('[name=lng]').value = ll[1];
  gps.innerHTML = '<i class="bi bi-broadcast text-success"></i> GPS พร้อม (แม่นยำ ±' + Math.round(p.coords.accuracy) + ' ม.)';
  if (shipping && Date.now() - last > 15000) {
    last = Date.now();
    fetch(location.href, {method: 'POST', body: new URLSearchParams({action: 'ping', lat: ll[0], lng: ll[1], csrf})});
  }
}, () => gps.textContent = 'ใช้ GPS ไม่ได้ กรุณาอนุญาตการเข้าถึงตำแหน่ง (เว็บต้องเป็น HTTPS)', {enableHighAccuracy: true});
</script>
<?php include '../includes/footer.php'; ?>