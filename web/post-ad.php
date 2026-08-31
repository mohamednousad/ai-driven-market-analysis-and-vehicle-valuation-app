<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();
$pageTitle = 'Post Your Ad';
$locations = (new LocationModel())->all();
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
  <div class="container">
    <div class="breadcrumb"><a href="<?= Config::baseUrl('index.php') ?>">Home</a> / Post Ad</div>
    <h1>Post Your Vehicle</h1>
  </div>
</div>
<div class="container split-layout">
  <form id="postAdForm" enctype="multipart/form-data" novalidate>
    <div class="form-card">
      <h3>Listing Details</h3>
      <p class="hint">Give buyers a clear, honest headline and description.</p>
      <div class="form-group">
        <label>Ad title</label>
        <input type="text" name="title" placeholder="e.g. Toyota Aqua 2018 - Excellent Condition">
        <span class="field-error"></span>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Condition, history, reason for selling..."></textarea>
        <span class="field-error"></span>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Asking price (LKR)</label>
          <input type="number" name="price" placeholder="5850000">
          <span class="field-error"></span>
        </div>
        <div class="form-group">
          <label>Location</label>
          <select name="location_id">
            <option value="">Select location</option>
            <?php foreach ($locations as $loc): ?><option value="<?= (int)$loc['id'] ?>"><?= Helpers::e($loc['city'] . ', ' . $loc['district']) ?></option><?php endforeach; ?>
          </select>
          <span class="field-error"></span>
        </div>
      </div>
      <label class="chk-row"><input type="checkbox" name="is_negotiable" value="1"> Price is negotiable</label>
    </div>
    <div class="form-card">
      <h3>Vehicle Information</h3>
      <p class="hint">These exact details are what the AI engine uses to judge your price.</p>
      <div class="form-row">
        <div class="form-group"><label>Make</label><input type="text" name="make" placeholder="Toyota"><span class="field-error"></span></div>
        <div class="form-group"><label>Model</label><input type="text" name="model" placeholder="Aqua"><span class="field-error"></span></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Manufacture year</label><input type="number" name="manufacture_year" placeholder="2018"><span class="field-error"></span></div>
        <div class="form-group"><label>Registration year</label><input type="number" name="registration_year" placeholder="2019"><span class="field-error"></span></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Engine (cc)</label><input type="number" name="engine_cc" placeholder="1500"><span class="field-error"></span></div>
        <div class="form-group"><label>Mileage (km)</label><input type="number" name="mileage" placeholder="65000"><span class="field-error"></span></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Transmission</label>
          <select name="transmission"><option value="automatic">Automatic</option><option value="manual">Manual</option></select>
        </div>
        <div class="form-group">
          <label>Fuel type</label>
          <select name="fuel_type"><option value="petrol">Petrol</option><option value="diesel">Diesel</option><option value="hybrid">Hybrid</option><option value="electric">Electric</option></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Body type</label><input type="text" name="body_type" placeholder="Hatchback"></div>
        <div class="form-group"><label>Colour</label><input type="text" name="colour" placeholder="White"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Number of owners</label><input type="number" name="number_of_owners" value="1"></div>
        <div class="form-group">
          <label>Finance status</label>
          <select name="finance_status"><option value="Clear">Clear</option><option value="Under Finance">Under Finance</option></select>
        </div>
      </div>
      <div class="form-group"><label>Finance company (if any)</label><input type="text" name="finance_company" placeholder="Optional"></div>
      <div class="form-row">
        <label class="chk-row"><input type="checkbox" name="accident_history" value="1"> Accident history</label>
        <label class="chk-row"><input type="checkbox" name="service_history" value="1" checked> Full service history</label>
      </div>
      <div class="form-group"><label>Features (separate with |)</label><input type="text" name="features" placeholder="ABS|Airbags|Alloy Wheels"></div>
    </div>
    <div class="form-card">
      <h3>Photos</h3>
      <p class="hint">Upload up to <?= (int)Config::get('MAX_IMAGES_PER_AD') ?> photos. Tap the star to choose your cover photo.</p>
      <div class="upload-zone" id="uploadZone">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <div><b>Click to upload</b> or drag photos here</div>
        <div style="font-size:11.5px;margin-top:4px">JPG, PNG or WEBP up to 5MB each</div>
      </div>
      <input type="file" id="imageInput" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
      <div class="thumb-row" id="thumbRow"></div>
    </div>
    <button class="btn btn-gold" type="submit" style="min-width:230px"><i class="fa-solid fa-wand-magic-sparkles"></i><span class="btn-label"> Submit for AI Review</span></button>
  </form>
  <aside>
    <div class="sidebar-card">
      <h4>What happens next</h4>
      <ul class="progress-list">
        <li class="done"><span class="pdot"><i class="fa-solid fa-check"></i></span><div><b>You submit the ad</b><br>Details and photos are saved securely.</div></li>
        <li class="done"><span class="pdot"><i class="fa-solid fa-bolt"></i></span><div><b>AI reviews the price instantly</b><br>Your asking price is compared against thousands of market records.</div></li>
        <li><span class="pdot">3</span><div><b>Fair price: goes live immediately</b><br>No waiting for manual review.</div></li>
        <li><span class="pdot">4</span><div><b>Unfair price: clearly rejected</b><br>You get the expected range and can resubmit with a new price.</div></li>
      </ul>
    </div>
    <div class="sidebar-card" style="background:var(--jet-black);color:#fff;border-color:var(--jet-black)">
      <h4 style="color:var(--gold-bright)">Pricing tip</h4>
      <p style="font-size:13px;color:rgba(255,255,255,.7)">Ads priced within the AI fair range get approved instantly and receive up to 3x more buyer chats in the first week.</p>
    </div>
  </aside>
</div>
<?php
$pageScript = <<<'JS'
var selectedFiles = [];
var primaryIndex = 0;
var maxImages = parseInt($('#uploadZone').closest('.form-card').find('.hint').text().match(/\d+/)[0], 10) || 10;

$('#uploadZone').on('click', function () { $('#imageInput').trigger('click'); });
$('#uploadZone').on('dragover', function (e) { e.preventDefault(); $(this).addClass('drag'); });
$('#uploadZone').on('dragleave drop', function (e) { e.preventDefault(); $(this).removeClass('drag'); });
$('#uploadZone').on('drop', function (e) { addFiles(e.originalEvent.dataTransfer.files); });
$('#imageInput').on('change', function () { addFiles(this.files); this.value = ''; });

function addFiles(list) {
  Array.prototype.slice.call(list).forEach(function (f) {
    if (selectedFiles.length >= maxImages) { AV.toast('warning', 'Maximum ' + maxImages + ' photos allowed.'); return; }
    if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { AV.toast('error', f.name + ' is not a supported image type.'); return; }
    if (f.size > 5242880) { AV.toast('error', f.name + ' is larger than 5MB.'); return; }
    selectedFiles.push(f);
  });
  renderThumbs();
}

function renderThumbs() {
  var $row = $('#thumbRow').empty();
  selectedFiles.forEach(function (f, i) {
    var $item = $('<div class="thumb-item ' + (i === primaryIndex ? 'primary' : '') + '"><img><button type="button" class="thumb-x"><i class="fa-solid fa-xmark"></i></button><button type="button" class="thumb-star" title="Set as cover"><i class="fa-solid fa-star"></i></button></div>');
    var reader = new FileReader();
    reader.onload = function (e) { $item.find('img').attr('src', e.target.result); };
    reader.readAsDataURL(f);
    $item.find('.thumb-x').on('click', function () {
      selectedFiles.splice(i, 1);
      if (primaryIndex >= selectedFiles.length) primaryIndex = 0;
      renderThumbs();
    });
    $item.find('.thumb-star').on('click', function () { primaryIndex = i; renderThumbs(); });
    $row.append($item);
  });
}

$('#postAdForm').on('submit', function (e) {
  e.preventDefault();
  var $form = $(this);
  var fd = new FormData(this);
  fd.append('primary_index', primaryIndex);
  selectedFiles.forEach(function (f) { fd.append('images[]', f); });
  var $btn = $form.find('button[type=submit]');
  AV.setLoading($btn, true);
  $.ajax({
    url: AV.base + '/api/post-ad.php',
    method: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json',
    headers: { 'X-CSRF-Token': AV.csrf, 'X-Requested-With': 'XMLHttpRequest' }
  }).done(function (res) {
    if (!res.ok) {
      AV.setLoading($btn, false);
      if (res.errors) { AV.showFieldErrors($form, res.errors); AV.toast('error', 'Please fix the highlighted fields.'); }
      else { AV.toast('error', res.error || 'Could not save your ad.'); }
      return;
    }
    runAiReview(res.ad_id, $btn);
  }).fail(function () {
    AV.setLoading($btn, false);
    AV.toast('error', 'Could not save your ad. Please try again.');
  });
});

function runAiReview(adId, $btn) {
  var loading = AV.modal({
    loader: true,
    title: 'AI is reviewing your price',
    text: 'Comparing your asking price against thousands of real market records. This usually takes a few seconds.',
    buttons: []
  });
  AV.ajax('api/analyze.php', { ad_id: adId }, function (res) {
    loading.close();
    AV.setLoading($btn, false);
    if (res.verdict === 'fair') {
      AV.modal({
        type: 'success',
        title: 'Approved. Your ad is live!',
        text: res.message,
        buttons: [
          { label: 'View My Ad', cls: 'btn-gold', onClick: function () { window.location = AV.base + '/ad.php?id=' + adId; } },
          { label: 'Go to Dashboard', cls: 'btn-outline-dark', onClick: function () { window.location = AV.base + '/dashboard.php'; } }
        ]
      });
    } else {
      AV.modal({
        type: 'error',
        title: 'Ad Rejected: Price Not Fair',
        text: res.message,
        buttons: [
          { label: 'Adjust Price in My Ads', cls: 'btn-gold', onClick: function () { window.location = AV.base + '/my-ads.php'; } },
          { label: 'Close', cls: 'btn-outline-dark' }
        ]
      });
    }
  }, function (msg) {
    loading.close();
    AV.setLoading($btn, false);
    AV.modal({
      type: 'warning',
      title: 'AI service unavailable',
      text: msg + ' Your ad is saved as pending and will not be visible until it is analyzed. You can retry from My Ads.',
      buttons: [{ label: 'Go to My Ads', cls: 'btn-gold', onClick: function () { window.location = AV.base + '/my-ads.php'; } }]
    });
  });
}
JS;
require __DIR__ . '/partials/footer.php';
?>
