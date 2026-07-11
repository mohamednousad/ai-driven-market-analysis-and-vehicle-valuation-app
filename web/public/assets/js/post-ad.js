(function ($) {
    'use strict';

    function collectInputs() {
        return {
            brand: $('#brand').val(),
            model_year: Number($('#model_year').val()),
            mileage: Number($('#mileage').val()),
            engine_capacity: Number($('#engine_capacity').val()),
            fuel_type: $('#fuel_type').val(),
            transmission: $('#transmission').val(),
            condition: $('#condition_grade').val()
        };
    }

    function renderValuation(data) {
        var symbol = data.currency_symbol || 'Rs';
        var verdictClass = data.fair ? 'fair' : data.verdict;
        var verdictIcon = data.fair ? 'fa-circle-check'
            : (data.verdict === 'overpriced' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down');
        var verdictText = data.fair ? 'Fair price'
            : (data.verdict === 'overpriced' ? 'Price is too high' : 'Price is too low');

        var html = '<div class="valuation-panel">'
            + '<span class="muted" style="font-weight:600;font-size:13px">AI predicted price</span>'
            + '<div class="valuation-price">' + AutoValue.money(data.predicted_price) + '</div>'
            + '<div class="valuation-band">'
            + '<div><span class="muted">Fair range low</span><strong>' + AutoValue.money(data.lower_bound) + '</strong></div>'
            + '<div><span class="muted">Fair range high</span><strong>' + AutoValue.money(data.upper_bound) + '</strong></div>'
            + '</div>'
            + '<div class="verdict ' + verdictClass + '"><i class="fa-solid ' + verdictIcon + '"></i> ' + verdictText + '</div>'
            + '</div>';
        $('#valuationResult').removeClass('hidden').html(html);
    }

    $('#valuateBtn').on('click', function () {
        var asking = Number($('#asking_price').val());
        if (!asking || asking <= 0) {
            AutoValue.toast('Enter an asking price first.', 'error');
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Checking...');
        $('#valuationResult').removeClass('hidden').html('<p class="muted">Running the AI valuation model...</p>');

        AutoValue.postJson('../api/valuate.php', { inputs: collectInputs(), asking_price: asking })
            .done(function (data) {
                if (data.success) {
                    renderValuation(data);
                    AutoValue.toast(data.fair ? 'Price looks fair!' : 'Price is outside the fair range.', data.fair ? 'success' : 'error');
                } else {
                    $('#valuationResult').html('<div class="alert error"><i class="fa-solid fa-circle-exclamation"></i><span>' + AutoValue.escapeHtml(data.message) + '</span></div>');
                }
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to reach the valuation service.';
                $('#valuationResult').html('<div class="alert error"><i class="fa-solid fa-circle-exclamation"></i><span>' + AutoValue.escapeHtml(msg) + '</span></div>');
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-wand-magic-sparkles"></i> Run AI valuation');
            });
    });
})(jQuery);
