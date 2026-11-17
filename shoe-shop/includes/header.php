<?php
$cartCount = array_sum($_SESSION['cart'] ?? []);
// ---- SEO: title / meta description / robots / canonical ----
$self = basename($_SERVER['PHP_SELF']); $dir = basename(dirname($_SERVER['PHP_SELF']));
$noindex = !empty($noindex) || in_array($dir, ['admin', 'rider'])
    || in_array($self, ['cart.php','checkout.php','payment.php','orders.php','track.php','review.php','defect.php','defect_view.php','login.php','register.php','404.php','500.php','newsletter.php']);
$pageTitle = $title ?? 'Sneaks Store';
$fullTitle = mb_stripos($pageTitle, 'Sneaks') !== false ? $pageTitle : $pageTitle . ' | Sneaks Store';
$metaDesc = trim(preg_replace('/\s+/u', ' ', $desc ?? ($pageTitle . ' - Sneaks Store ร้านรองเท้าออนไลน์ รองเท้าผ้าใบ รองเท้าวิ่ง ส้นสูง ส่งไวทั่วไทย ชำระพร้อมเพย์ได้ ติดตามพัสดุบนแผนที่')));
if (mb_strlen($metaDesc) > 160) $metaDesc = mb_substr($metaDesc, 0, 157) . '...';
$canon = str_replace(' ', '%20', SITE_URL) . substr(rawurldecode(strtok($_SERVER['REQUEST_URI'], '?')), strlen(BASE_URL));
if ($self === 'product.php' && !empty($_GET['id'])) $canon .= '?id=' . (int)$_GET['id'];
elseif ($self === 'index.php' && !empty($_GET['cat'])) $canon .= '?cat=' . (int)$_GET['cat'];
$cats = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
$f = flash();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($metaDesc) ?>">
<meta name="robots" content="<?= $noindex ? 'noindex,nofollow' : 'index,follow' ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta name="theme-color" content="#e84a7f">
<meta property="og:type" content="website"><meta property="og:site_name" content="Sneaks Store">
<meta property="og:title" content="<?= e($fullTitle) ?>"><meta property="og:description" content="<?= e($metaDesc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%91%9F%3C/text%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php if (!empty($needMap)): ?><link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet"><?php endif; ?>
<link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
<?php if (!empty($needMap)): ?><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><?php endif; ?>
</head>
<body>
<div class="infobar d-none d-md-block"><div class="container d-flex justify-content-between align-items-center gap-3">
  <div class="d-flex gap-4 flex-wrap">
    <span><i class="bi bi-geo-alt"></i> ติดตามพัสดุบนแผนที่ได้ทุกออเดอร์</span>
    <span><i class="bi bi-shield-check"></i> ชำระเงินปลอดภัย พร้อมเพย์ / บัตร</span>
    <span><i class="bi bi-headset"></i> บริการหลังการขาย โทร <?= e(SHOP_PHONE) ?></span>
  </div>
  <div class="social">
    <?php foreach (['facebook' => SOCIAL_FACEBOOK, 'instagram' => SOCIAL_INSTAGRAM, 'tiktok' => SOCIAL_TIKTOK, 'youtube' => SOCIAL_YOUTUBE, 'line' => SOCIAL_LINE] as $ic => $url): if ($url): ?>
      <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= $ic ?>"><i class="bi bi-<?= $ic ?>"></i></a>
    <?php endif; endforeach; ?>
  </div>
</div></div>
<?php
$onIndex = $self === 'index.php' && $dir !== 'admin';
$isPromo = $onIndex && !empty($_GET['promo']);
$isHome  = $onIndex && !$isPromo && empty($_GET['cat']) && empty($_GET['q']) && empty($_GET['sort']);
$isProd  = ($onIndex && !$isPromo && !$isHome) || $self === 'product.php';
$rank = function ($n) { foreach (['ผู้ชาย', 'ผู้หญิง', 'เด็ก', 'โปรโมชั่น'] as $i => $k) if (mb_strpos($n, $k) !== false) return $i; return 9; };
usort($cats, fn($a, $b) => $rank($a['name']) <=> $rank($b['name']));
?>
<header class="topbar">
  <nav class="navbar navbar-expand-lg navbar-dark"><div class="container">
    <a href="<?= BASE_URL ?>/" class="brand"><i class="bi bi-bag-heart-fill"></i> Sneaks Store</a>
    <a href="<?= BASE_URL ?>/cart.php" class="text-white position-relative order-lg-last ms-auto ms-lg-3 me-3 me-lg-0" aria-label="ตะกร้าสินค้า">
      <i class="bi bi-cart3 fs-4"></i>
      <?php if ($cartCount): ?><span class="badge-count"><?= $cartCount ?></span><?php endif; ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-label="เมนู"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-lg-auto gap-lg-1">
        <li class="nav-item"><a class="nav-link <?= $isHome ? 'active' : '' ?>" href="<?= BASE_URL ?>/">หน้าหลัก</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $isProd ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">สินค้า</a>
          <ul class="dropdown-menu catmenu">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?sort=latest">สินค้าทั้งหมด</a></li>
            <li><hr class="dropdown-divider"></li>
            <?php foreach ($cats as $c): ?>
              <li><a class="dropdown-item <?= ($_GET['cat'] ?? '') == $c['id'] ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?cat=<?= $c['id'] ?>"><?= e($c['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link <?= $isPromo ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php?promo=1">โปรโมชั่น</a></li>
        <li class="nav-item"><a class="nav-link <?= $self === 'about.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/about.php">เกี่ยวกับเรา</a></li>
        <li class="nav-item"><a class="nav-link <?= $self === 'contact.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/contact.php">ติดต่อเรา</a></li>
      </ul>
      <form class="search-pill d-flex align-items-center my-2 my-lg-0" action="<?= BASE_URL ?>/index.php">
        <input name="q" class="form-control" placeholder="ค้นหารองเท้า..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="ค้นหาสินค้า">
        <button aria-label="ค้นหา"><i class="bi bi-search"></i></button>
      </form>
      <ul class="navbar-nav ms-lg-2">
      <?php if (user()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <span class="d-lg-none d-xl-inline"><?= e(user()['name']) ?></span></a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-box-seam"></i> คำสั่งซื้อของฉัน</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/defect.php"><i class="bi bi-exclamation-triangle"></i> แจ้งสินค้าชำรุด</a></li>
            <?php if (in_array(user()['role'], ['rider', 'admin'])): ?>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/rider/"><i class="bi bi-scooter"></i> งานจัดส่ง</a></li>
            <?php endif; ?>
            <?php if (user()['role'] === 'admin'): ?>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/"><i class="bi bi-speedometer2"></i> ระบบหลังบ้าน</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php">ออกจากระบบ</a></li>
          </ul>
        </li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php"><i class="bi bi-person"></i> เข้าสู่ระบบ</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/register.php">สมัครสมาชิก</a></li>
      <?php endif; ?>
      </ul>
    </div>
  </div></nav>
</header>
<main class="container py-4">
<?php if ($f): ?><div class="alert alert-<?= $f[1] ?> alert-dismissible fade show"><?= e($f[0]) ?>
  <button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>