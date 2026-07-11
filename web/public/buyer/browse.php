<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../lib/AdRepository.php';
require_once __DIR__ . '/../../lib/SettingsService.php';

require_role(ROLE_BUYER);
$assetBase = '../';

$settings = new SettingsService($pdo);
$perPage = (int)$settings->get('ads_per_page', 9);

$filters = [
    'keyword' => trim(query('keyword')),
    'brand' => query('brand'),
    'fuel_type' => query('fuel_type'),
    'transmission' => query('transmission'),
    'min_price' => query('min_price'),
    'max_price' => query('max_price'),
    'min_year' => query('min_year'),
    'min_rating' => query('min_rating'),
    'sort' => query('sort'),
];
$page = max(1, (int)query('page', 1));

$adRepo = new AdRepository($pdo);
$result = $adRepo->search($filters, $page, $perPage);

$brands = ['Toyota', 'Honda', 'Nissan', 'Suzuki', 'Mitsubishi', 'Mazda', 'BMW', 'Mercedes-Benz', 'Audi', 'Other'];
$fuels = ['Petrol', 'Diesel', 'Hybrid', 'Electric'];
$transmissions = ['Automatic', 'Manual'];

function query_string_without_page(): string
{
    $params = $_GET;
    unset($params['page']);
    return http_build_query($params);
}

$pageTitle = 'Browse vehicles';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="section">
    <span class="eyebrow">Marketplace</span>
    <h1 class="mb-0">Browse vehicles</h1>
    <p>Every listing here has passed the AI fair-price check. Use the filters to narrow your search.</p>
</section>

<div class="grid grid-sidebar">
    <aside class="card" style="position:sticky;top:90px">
        <h3>Filters</h3>
        <form method="GET" class="form-grid">
            <div class="field">
                <label>Keyword</label>
                <div class="input-icon"><i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="keyword" value="<?php echo e($filters['keyword']); ?>" placeholder="Brand or model">
                </div>
            </div>
            <div class="field">
                <label>Brand</label>
                <select name="brand">
                    <option value="">Any brand</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo e($b); ?>" <?php echo $filters['brand'] === $b ? 'selected' : ''; ?>><?php echo e($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Fuel type</label>
                <select name="fuel_type">
                    <option value="">Any fuel</option>
                    <?php foreach ($fuels as $f): ?>
                        <option value="<?php echo e($f); ?>" <?php echo $filters['fuel_type'] === $f ? 'selected' : ''; ?>><?php echo e($f); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="">Any</option>
                    <?php foreach ($transmissions as $t): ?>
                        <option value="<?php echo e($t); ?>" <?php echo $filters['transmission'] === $t ? 'selected' : ''; ?>><?php echo e($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Price range (Rs)</label>
                <div class="flex">
                    <input type="number" name="min_price" value="<?php echo e($filters['min_price']); ?>" placeholder="Min" step="100000">
                    <input type="number" name="max_price" value="<?php echo e($filters['max_price']); ?>" placeholder="Max" step="100000">
                </div>
            </div>
            <div class="field">
                <label>Minimum year</label>
                <input type="number" name="min_year" value="<?php echo e($filters['min_year']); ?>" placeholder="e.g. 2015" min="1990" max="2026">
            </div>
            <div class="field">
                <label>Minimum seller rating</label>
                <select name="min_rating">
                    <option value="">Any rating</option>
                    <option value="3" <?php echo $filters['min_rating'] === '3' ? 'selected' : ''; ?>>3+ stars</option>
                    <option value="4" <?php echo $filters['min_rating'] === '4' ? 'selected' : ''; ?>>4+ stars</option>
                    <option value="4.5" <?php echo $filters['min_rating'] === '4.5' ? 'selected' : ''; ?>>4.5+ stars</option>
                </select>
            </div>
            <div class="field">
                <label>Sort by</label>
                <select name="sort">
                    <option value="">Newest first</option>
                    <option value="price_low" <?php echo $filters['sort'] === 'price_low' ? 'selected' : ''; ?>>Price: low to high</option>
                    <option value="price_high" <?php echo $filters['sort'] === 'price_high' ? 'selected' : ''; ?>>Price: high to low</option>
                    <option value="rating" <?php echo $filters['sort'] === 'rating' ? 'selected' : ''; ?>>Top-rated sellers</option>
                </select>
            </div>
            <button type="submit" class="btn primary full"><i class="fa-solid fa-filter"></i> Apply filters</button>
            <button type="button" class="btn secondary full" id="resetFilters">Reset</button>
        </form>
    </aside>

    <div>
        <div class="flex-between" style="margin-bottom:18px">
            <p class="muted mb-0"><strong><?php echo $result['total']; ?></strong> vehicles found</p>
        </div>
        <?php if (!$result['items']): ?>
            <div class="card empty-state">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p>No vehicles match your filters. Try widening your search.</p>
            </div>
        <?php else: ?>
            <div class="vehicle-grid">
                <?php foreach ($result['items'] as $ad): ?>
                    <?php include __DIR__ . '/_vehicle_card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php if ($result['pages'] > 1): ?>
                <nav class="pagination">
                    <?php $qs = query_string_without_page(); ?>
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo $qs; ?>&page=<?php echo $page - 1; ?>"><i class="fa-solid fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo $qs; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $result['pages']): ?>
                        <a href="?<?php echo $qs; ?>&page=<?php echo $page + 1; ?>"><i class="fa-solid fa-chevron-right"></i></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
