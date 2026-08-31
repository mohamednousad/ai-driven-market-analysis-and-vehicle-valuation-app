<?php
require_once __DIR__ . '/app/bootstrap.php';
$pageTitle = 'Browse Cars';
$adModel = new AdModel();
$filters = [
    'q' => Helpers::get('q'),
    'make' => Helpers::get('make'),
    'district' => Helpers::get('district'),
    'transmission' => Helpers::get('transmission'),
    'fuel_type' => Helpers::get('fuel_type'),
    'min_price' => Helpers::get('min_price'),
    'max_price' => Helpers::get('max_price'),
    'fair_only' => Helpers::get('fair_only'),
    'authorized_only' => Helpers::get('authorized_only'),
];
$perPage = (int)Config::get('ADS_PER_PAGE');
$page = max(1, (int)Helpers::get('page', '1'));
$total = $adModel->approvedCount($filters);
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$ads = $adModel->approvedList($filters, $perPage, ($page - 1) * $perPage);
$makes = $adModel->distinctMakes();
$districts = (new LocationModel())->districts();
require __DIR__ . '/partials/header.php';

function browsePageUrl(int $p): string
{
    $qs = $_GET;
    $qs['page'] = $p;
    return Config::baseUrl('browse.php?' . http_build_query($qs));
}
?>
<div class="page-head">
  <div class="container">
    <div class="breadcrumb"><a href="<?= Config::baseUrl('index.php') ?>">Home</a> / Browse</div>
    <h1>Browse Cars</h1>
  </div>
</div>
<div class="container browse-layout">
  <aside class="filter-panel">
    <form method="get" action="<?= Config::baseUrl('browse.php') ?>">
      <h4>Keyword</h4>
      <input type="text" name="q" value="<?= Helpers::e($filters['q']) ?>" placeholder="Search title or model">
      <h4>Make</h4>
      <select name="make">
        <option value="">Any make</option>
        <?php foreach ($makes as $m): ?><option value="<?= Helpers::e($m['make']) ?>" <?= $filters['make'] === $m['make'] ? 'selected' : '' ?>><?= Helpers::e($m['make']) ?></option><?php endforeach; ?>
      </select>
      <h4>District</h4>
      <select name="district">
        <option value="">All districts</option>
        <?php foreach ($districts as $d): ?><option value="<?= Helpers::e($d['district']) ?>" <?= $filters['district'] === $d['district'] ? 'selected' : '' ?>><?= Helpers::e($d['district']) ?></option><?php endforeach; ?>
      </select>
      <h4>Transmission</h4>
      <select name="transmission">
        <option value="">Any</option>
        <option value="automatic" <?= $filters['transmission'] === 'automatic' ? 'selected' : '' ?>>Automatic</option>
        <option value="manual" <?= $filters['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
      </select>
      <h4>Fuel</h4>
      <select name="fuel_type">
        <option value="">Any</option>
        <?php foreach (['petrol', 'diesel', 'hybrid', 'electric'] as $fuel): ?>
        <option value="<?= $fuel ?>" <?= $filters['fuel_type'] === $fuel ? 'selected' : '' ?>><?= ucfirst($fuel) ?></option>
        <?php endforeach; ?>
      </select>
      <h4>Price Range (LKR)</h4>
      <div class="range-row">
        <input type="number" name="min_price" value="<?= Helpers::e($filters['min_price']) ?>" placeholder="Min">
        <input type="number" name="max_price" value="<?= Helpers::e($filters['max_price']) ?>" placeholder="Max">
      </div>
      <h4>Trust</h4>
      <label class="chk-row"><input type="checkbox" name="fair_only" value="1" <?= $filters['fair_only'] ? 'checked' : '' ?>> AI fair price only</label>
      <label class="chk-row"><input type="checkbox" name="authorized_only" value="1" <?= $filters['authorized_only'] ? 'checked' : '' ?>> Authorized sellers only</label>
      <button class="btn btn-dark btn-block" type="submit" style="margin-top:10px"><i class="fa-solid fa-filter"></i> Apply Filters</button>
      <a class="btn btn-outline-dark btn-block btn-sm" href="<?= Config::baseUrl('browse.php') ?>" style="margin-top:8px">Reset</a>
    </form>
  </aside>
  <div>
    <div class="results-bar">
      <div class="count"><b><?= $total ?></b> vehicles found</div>
    </div>
    <?php if (!$ads): ?>
      <div class="empty-state"><i class="fa-solid fa-car-tunnel"></i><h4>No cars match those filters</h4><p>Try widening your price range or clearing a filter.</p></div>
    <?php else: ?>
      <div class="car-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
        <?php foreach ($ads as $card) { include __DIR__ . '/partials/ad-card.php'; } ?>
      </div>
      <?php if ($pages > 1): ?>
      <div class="pagination">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p === $page): ?><span class="current"><?= $p ?></span>
          <?php else: ?><a href="<?= Helpers::e(browsePageUrl($p)) ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
