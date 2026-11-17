</main>
<footer class="sitefoot mt-5">
  <div class="container py-4"><div class="row g-4">
    <div class="col-lg-3">
      <div class="brand-f"><i class="bi bi-bag-heart-fill"></i> Sneaks Store</div>
      <p class="small text-muted mt-2 mb-0">More Than Just Sneakers.<br>ร้านรองเท้าออนไลน์ ส่งไวทั่วไทย</p>
    </div>
    <div class="col-6 col-lg-2">
      <h6>เมนูหลัก</h6>
      <ul class="list-unstyled small footlinks">
        <li><a href="<?= BASE_URL ?>/">หน้าหลัก</a></li>
        <li><a href="<?= BASE_URL ?>/index.php?sort=latest">สินค้า</a></li>
        <li><a href="<?= BASE_URL ?>/index.php?promo=1">โปรโมชั่น</a></li>
        <li><a href="<?= BASE_URL ?>/about.php">เกี่ยวกับเรา</a></li>
        <li><a href="<?= BASE_URL ?>/contact.php">ติดต่อเรา</a></li>
      </ul>
    </div>
    <div class="col-6 col-lg-3">
      <h6>ช่องทางการติดต่อ</h6>
      <ul class="list-unstyled small footlinks mb-2">
        <li><i class="bi bi-telephone"></i> <a href="tel:<?= e(SHOP_PHONE) ?>"><?= e(SHOP_PHONE) ?></a></li>
        <li><i class="bi bi-envelope"></i> <a href="mailto:<?= e(SHOP_EMAIL) ?>"><?= e(SHOP_EMAIL) ?></a></li>
        <li><i class="bi bi-mailbox"></i> รหัสไปรษณีย์ <?= e(SHOP_POSTCODE) ?></li>
      </ul>
      <div class="footsocial">
        <?php foreach (['facebook' => SOCIAL_FACEBOOK, 'instagram' => SOCIAL_INSTAGRAM, 'tiktok' => SOCIAL_TIKTOK, 'youtube' => SOCIAL_YOUTUBE, 'line' => SOCIAL_LINE] as $ic => $url): if ($url): ?>
          <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= $ic ?>"><i class="bi bi-<?= $ic ?>"></i></a>
        <?php endif; endforeach; ?>
      </div>
    </div>
    <div class="col-lg-4">
      <h6>สมัครรับข่าวสาร</h6>
      <p class="small text-muted mb-2">รับโปรโมชั่นและสินค้าใหม่ก่อนใคร</p>
      <form method="post" action="<?= BASE_URL ?>/newsletter.php" class="newsletter">
        <input type="email" name="email" class="form-control" placeholder="กรอกอีเมลของคุณ" required aria-label="อีเมลสำหรับรับข่าวสาร">
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
        <button aria-label="สมัครรับข่าวสาร"><i class="bi bi-arrow-right"></i></button>
      </form>
      <small class="text-muted d-block mt-1" style="font-size:.75rem">การสมัครถือว่ายินยอมให้ใช้อีเมลเพื่อส่งข่าวสาร ตาม<a href="<?= BASE_URL ?>/privacy.php">นโยบาย PDPA</a> ยกเลิกได้ตลอดเวลา</small>
    </div>
  </div></div>
  <div class="subfoot"><div class="container d-flex flex-wrap justify-content-between gap-2 small">
    <span>© <?= date('Y') ?> Sneaks Store. สงวนสิทธิ์ทุกประการ</span>
    <span><a href="<?= BASE_URL ?>/privacy.php">นโยบายความเป็นส่วนตัว (PDPA)</a> &nbsp;|&nbsp; <a href="<?= BASE_URL ?>/defect.php">แจ้งสินค้าชำรุด</a> &nbsp;|&nbsp; <a href="<?= BASE_URL ?>/contact.php">ติดต่อเรา</a></span>
  </div></div>
</footer>
<?php if (GA_ID || FB_PIXEL_ID): ?>
<div id="ckBar" class="position-fixed bottom-0 start-0 end-0 bg-white border-top shadow p-3" style="z-index:2000;display:none">
  <div class="container d-flex flex-wrap align-items-center gap-2 justify-content-between small">
    <span>เว็บไซต์นี้ใช้คุกกี้เพื่อวิเคราะห์การใช้งาน (<?= GA_ID ? 'Google Analytics' : '' ?><?= GA_ID && FB_PIXEL_ID ? ' / ' : '' ?><?= FB_PIXEL_ID ? 'Facebook Pixel' : '' ?>) อ่านรายละเอียดที่ <a href="<?= BASE_URL ?>/privacy.php">นโยบายความเป็นส่วนตัว (PDPA)</a></span>
    <span class="text-nowrap"><button id="ckNo" class="btn btn-sm btn-outline-secondary">ปฏิเสธ</button> <button id="ckYes" class="btn btn-sm btn-pink">ยอมรับ</button></span>
  </div>
</div>
<script>
(function () {
  const GA = <?= json_encode(GA_ID) ?>, FB = <?= json_encode(FB_PIXEL_ID) ?>;
  function load() {
    if (GA) {
      const s = document.createElement('script'); s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA; document.head.appendChild(s);
      window.dataLayer = window.dataLayer || []; window.gtag = function () { dataLayer.push(arguments); };
      gtag('js', new Date()); gtag('config', GA, {anonymize_ip: true});
    }
    if (FB) {
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', FB); fbq('track', 'PageView');
    }
  }
  let c = null; try { c = localStorage.getItem('ck_consent'); } catch (e) {}
  if (c === 'yes') load();
  else if (!c) {
    const bar = document.getElementById('ckBar'); bar.style.display = 'block';
    const set = v => { try { localStorage.setItem('ck_consent', v); } catch (e) {} bar.remove(); if (v === 'yes') load(); };
    document.getElementById('ckYes').onclick = () => set('yes');
    document.getElementById('ckNo').onclick = () => set('no');
  }
})();
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script>
document.querySelectorAll('form[method="post"]').forEach(f => {
  const i = document.createElement('input');
  i.type = 'hidden'; i.name = 'csrf'; i.value = '<?= $_SESSION['csrf'] ?>';
  f.appendChild(i);
});
</script>
</body></html>