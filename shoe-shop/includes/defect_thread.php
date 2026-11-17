<?php /* แสดงแชทของรายงานสินค้าชำรุด : ต้องมี $msgs และ $viewer ('customer' | 'admin') */ ?>
<style>
.chat{max-height:460px;overflow-y:auto;background:#fff8fb;border-radius:12px;padding:1rem}
.bub{max-width:80%;padding:.6rem .9rem;border-radius:14px;margin-bottom:.8rem;word-break:break-word}
.bub.me{background:#e84a7f;color:#fff;margin-left:auto;border-bottom-right-radius:4px}
.bub.them{background:#fff;border:1px solid #ffd1dc;border-bottom-left-radius:4px}
.bub img{width:120px;height:120px;border-radius:8px;margin:.4rem .4rem 0 0;object-fit:cover}
.bub small.t{opacity:.75;display:block;margin-top:.3rem;font-size:.7rem}
</style>
<div class="chat" id="chat">
<?php foreach ($msgs as $m):
    $mine = $m['sender'] === $viewer;
    $who = $m['sender'] === 'admin' ? 'ร้านค้า' : ($viewer === 'admin' ? 'ลูกค้า' : 'คุณ'); ?>
  <div class="bub <?= $mine ? 'me' : 'them' ?>">
    <b class="small"><?= e($who) ?></b><br>
    <?php if ($m['message'] !== null && $m['message'] !== ''): ?><?= nl2br(e($m['message'])) ?><?php endif; ?>
    <?php if (!empty($m['images'])): ?><div>
      <?php foreach (explode(',', $m['images']) as $im): ?>
        <a href="<?= e(img($im)) ?>" target="_blank"><img src="<?= e(img($im)) ?>" alt="รูปแนบ"></a>
      <?php endforeach; ?></div>
    <?php endif; ?>
    <small class="t"><?= e($m['created_at']) ?></small>
  </div>
<?php endforeach; ?>
</div>
<script>const chat = document.getElementById('chat'); chat.scrollTop = chat.scrollHeight;</script>