document.addEventListener('DOMContentLoaded', function () {
  var mainImg = document.getElementById('gallery-main-img');
  document.querySelectorAll('.gallery-thumbs img').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      if (mainImg) mainImg.src = thumb.dataset.full;
      document.querySelectorAll('.gallery-thumbs img').forEach(function (t) { t.classList.remove('active'); });
      thumb.classList.add('active');
    });
  });

  var revealBtn = document.getElementById('reveal-phone');
  if (revealBtn) {
    revealBtn.addEventListener('click', function () {
      revealBtn.outerHTML = '<a class="btn btn-green btn-block" href="tel:' + revealBtn.dataset.phone + '">' +
        '<i class="fa-solid fa-phone"></i> ' + revealBtn.dataset.phone + '</a>';
    });
  }

  document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modal = document.getElementById(btn.dataset.modalOpen);
      if (modal) modal.classList.add('open');
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('.modal-back').classList.remove('open');
    });
  });
  document.querySelectorAll('.modal-back').forEach(function (back) {
    back.addEventListener('click', function (e) {
      if (e.target === back) back.classList.remove('open');
    });
  });

  var fileInput = document.getElementById('images-input');
  if (fileInput) {
    fileInput.addEventListener('change', function () {
      var preview = document.getElementById('img-preview');
      preview.innerHTML = '';
      Array.from(fileInput.files).slice(0, 6).forEach(function (file) {
        var img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
      });
    });
  }

  var checkBtn = document.getElementById('check-price-btn');
  if (checkBtn) {
    checkBtn.addEventListener('click', function () {
      var out = document.getElementById('price-check-result');
      var payload = {
        brand: val('make'), model: val('model'),
        manufacture_year: val('manufacture_year'),
        transmission: val('transmission'), fuel_type: val('fuel_type'),
        engine_cc: val('engine_cc'), mileage_km: val('mileage_km'),
        feature_count: document.querySelectorAll('input[name="features[]"]:checked').length,
        price: val('price')
      };
      if (!payload.brand || !payload.model || !payload.manufacture_year || !payload.price) {
        out.innerHTML = '<div class="ai-verdict ai-bad">Fill in make, model, year and your price first.</div>';
        return;
      }
      out.innerHTML = '<div class="ai-verdict" style="background:#f4f4f4">Checking with the AI valuation model…</div>';
      fetch('/api/valuate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) {
            out.innerHTML = '<div class="ai-verdict ai-bad">' + data.message + '</div>';
            return;
          }
          var p = data.prediction;
          var cls = data.result === 'fair' ? 'ai-fair' : 'ai-bad';
          out.innerHTML =
            '<div class="ai-verdict ' + cls + '"><strong>' + data.headline + '</strong>' + data.message +
            '<div class="ai-range">' +
            '<span>Predicted: <b>' + p.predicted_display + '</b></span>' +
            '<span>Fair range: <b>' + p.range_display + '</b></span>' +
            '<span>Confidence: <b>' + Math.round(p.confidence_score * 100) + '%</b></span>' +
            '</div></div>';
        })
        .catch(function () {
          out.innerHTML = '<div class="ai-verdict ai-bad">Could not reach the valuation service.</div>';
        });
    });
  }

  function val(name) {
    var el = document.querySelector('[name="' + name + '"]');
    return el ? el.value.trim() : '';
  }

  var chatScroll = document.querySelector('.chat-messages');
  if (chatScroll) chatScroll.scrollTop = chatScroll.scrollHeight;
});
