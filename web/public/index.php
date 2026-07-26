<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$adRepo = new AdRepository($pdo);

$locations = $pdo->query("SELECT location_id, name FROM locations WHERE type = 'district' ORDER BY name")->fetchAll();

$filters = [
    'q'             => trim($_GET['q'] ?? ''),
    'location_id'   => (int)($_GET['location_id'] ?? 0),
    'poster_type'   => in_array($_GET['poster_type'] ?? '', ['non_member', 'member', 'authorized_agent'], true) ? $_GET['poster_type'] : '',
    'min_price'     => $_GET['min_price'] ?? '',
    'max_price'     => $_GET['max_price'] ?? '',
    'sort'          => $_GET['sort'] ?? '',
    'promoted_only' => !empty($_GET['promoted']),
];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = $adRepo->search($filters, $page, ADS_PER_PAGE);
$totalPages = max(1, (int)ceil($result['total'] / ADS_PER_PAGE));

$locationName = 'All of Sri Lanka';
foreach ($locations as $loc) {
    if ((int)$loc['location_id'] === $filters['location_id']) {
        $locationName = $loc['name'];
    }
}

$pageTitle = 'Vehicles for sale in ' . $locationName;
require dirname(__DIR__) . '/includes/header.php';

function keepQuery(array $overrides): string
{
    return '?' . http_build_query(array_filter(array_merge($_GET, $overrides), fn($v) => $v !== '' && $v !== null));
}
?>
<div class="container page">
  <aside class="sidebar">
    <div class="side-card">
      <h3><i class="fa-solid fa-car"></i> Vehicles</h3>
      <div class="side-list">
        <a href="/index.php" class="<?= $filters['location_id'] ? '' : 'active' ?>">All of Sri Lanka</a>
        <?php foreach ($locations as $loc): ?>
          <a href="<?= e(keepQuery(['location_id' => $loc['location_id'], 'page' => 1])) ?>"
             class="<?= (int)$loc['location_id'] === $filters['location_id'] ? 'active' : '' ?>"><?= e($loc['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="side-card safety-card">
      <h3><i class="fa-solid fa-shield-halved"></i> Stay Alert</h3>
      <ul>
        <li>Always inspect the vehicle before paying.</li>
        <li>AutoValue never asks for payments in chat.</li>
        <li>Meet sellers in safe public places.</li>
      </ul>
    </div>
  </aside>

  <section class="content">
    <form class="filter-bar" method="get" action="/index.php">
      <?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= e($filters['q']) ?>"><?php endif; ?>
      <select name="location_id" onchange="this.form.submit()">
        <option value="">All of Sri Lanka</option>
        <?php foreach ($locations as $loc): ?>
          <option value="<?= (int)$loc['location_id'] ?>" <?= (int)$loc['location_id'] === $filters['location_id'] ? 'selected' : '' ?>>
            <?= e($loc['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="poster_type" onchange="this.form.submit()">
        <option value="">Type of poster</option>
        <option value="member" <?= $filters['poster_type'] === 'member' ? 'selected' : '' ?>>Members</option>
        <option value="authorized_agent" <?= $filters['poster_type'] === 'authorized_agent' ? 'selected' : '' ?>>Authorized Agents</option>
        <option value="non_member" <?= $filters['poster_type'] === 'non_member' ? 'selected' : '' ?>>Non-members</option>
      </select>
      <select name="promoted" onchange="this.form.submit()">
        <option value="">All listings</option>
        <option value="1" <?= $filters['promoted_only'] ? 'selected' : '' ?>>Promoted listings</option>
      </select>
      <select name="sort" onchange="this.form.submit()">
        <option value="">Sort: Newest first</option>
        <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
        <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
      </select>
      <input type="number" name="min_price" placeholder="Min Rs" value="<?= e((string)$filters['min_price']) ?>">
      <input type="number" name="max_price" placeholder="Max Rs" value="<?= e((string)$filters['max_price']) ?>">
      <button type="submit">Apply</button>
    </form>

    <div class="result-meta">
      <?= number_format($result['total']) ?> ads in <strong><?= e($locationName) ?></strong>
      <?= $filters['q'] !== '' ? ' for "' . e($filters['q']) . '"' : '' ?>
    </div>

    <?php if (!$result['rows']): ?>
      <div class="card">No vehicles match your search yet. Try removing some filters.</div>
    <?php endif; ?>

    <?php foreach ($result['rows'] as $ad): ?>
      <article class="ad-card <?= $ad['promo_type'] ? 'is-featured' : '' ?>">
        <?= promoTag($ad['promo_type']) ?>
        <a class="ad-thumb" href="/ad.php?id=<?= (int)$ad['ad_id'] ?>">
          <?php if ($ad['thumb']): ?>
            <img src="/<?= e(UPLOAD_URL . '/' . $ad['thumb']) ?>" alt="<?= e($ad['title']) ?>">
          <?php else: ?>
            <span class="no-img"><i class="fa-solid fa-car"></i></span>
          <?php endif; ?>
        </a>
        <div class="ad-body">
          <a class="ad-title" href="/ad.php?id=<?= (int)$ad['ad_id'] ?>"><?= e($ad['title']) ?></a>
          <div class="ad-specline">
            <?= number_format((int)$ad['mileage_km']) ?> km
            <span class="sep">|</span><?= e($ad['body_type'] ?: 'Vehicle') ?>
            <span class="sep">|</span>Used
          </div>
          <div class="ad-locline"><?= e($ad['district']) ?>, Cars</div>
          <?= posterBadge($ad['poster_type']) ?>
          <div class="ad-price"><?= money((float)$ad['price']) ?></div>
        </div>
        <span class="ad-time"><?= e(timeAgo($ad['published_at'] ?? $ad['created_at'])) ?></span>
      </article>
    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= e(keepQuery(['page' => $p])) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
