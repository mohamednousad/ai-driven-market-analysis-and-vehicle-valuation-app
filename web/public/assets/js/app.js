var AutoValue = (function ($) {
    'use strict';

    function money(value) {
        var num = Number(value) || 0;
        return 'Rs ' + num.toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function toast(message, type) {
        type = type || 'info';
        var icon = type === 'success' ? 'fa-circle-check'
            : (type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info');
        var $t = $('<div class="app-toast ' + type + '"><i class="fa-solid ' + icon + '"></i><span>' + escapeHtml(message) + '</span></div>');
        if (!$('#toastHost').length) {
            $('body').append('<div id="toastHost" style="position:fixed;top:20px;right:20px;z-index:9999;display:grid;gap:10px"></div>');
        }
        $t.css({
            display: 'flex', alignItems: 'center', gap: '10px', padding: '12px 16px',
            borderRadius: '14px', fontWeight: 600, fontSize: '14px', boxShadow: '0 16px 40px rgba(31,36,48,.16)',
            background: type === 'success' ? '#e7f7ee' : (type === 'error' ? '#fdecef' : '#fff1e6'),
            color: type === 'success' ? '#1f9d55' : (type === 'error' ? '#e23b52' : '#e8620a'),
            opacity: 0, transform: 'translateY(-8px)', transition: '.25s'
        });
        $('#toastHost').append($t);
        requestAnimationFrame(function () { $t.css({ opacity: 1, transform: 'translateY(0)' }); });
        setTimeout(function () {
            $t.css({ opacity: 0, transform: 'translateY(-8px)' });
            setTimeout(function () { $t.remove(); }, 300);
        }, 3200);
    }

    function postJson(url, payload) {
        return $.ajax({
            url: url, method: 'POST', contentType: 'application/json',
            data: JSON.stringify(payload), dataType: 'json'
        });
    }

    function getJson(url) {
        return $.ajax({ url: url, method: 'GET', dataType: 'json' });
    }

    function initNav() {
        $('#navToggle').on('click', function () {
            $('#navLinks').toggleClass('open');
        });
    }

    function initSlider() {
        var $slider = $('.slider');
        if (!$slider.length) { return; }
        var $slides = $slider.find('.slide');
        var $dots = $slider.find('.slider-dots button');
        var index = 0;
        var timer = null;

        function show(next) {
            index = (next + $slides.length) % $slides.length;
            $slides.removeClass('active').eq(index).addClass('active');
            $dots.removeClass('active').eq(index).addClass('active');
        }
        function start() { timer = setInterval(function () { show(index + 1); }, 5000); }
        function stop() { clearInterval(timer); }

        $slider.find('.slider-arrow.next').on('click', function () { show(index + 1); stop(); start(); });
        $slider.find('.slider-arrow.prev').on('click', function () { show(index - 1); stop(); start(); });
        $dots.on('click', function () { show($(this).index()); stop(); start(); });
        $slider.on('mouseenter', stop).on('mouseleave', start);
        show(0);
        start();
    }

    function initFavorites() {
        $(document).on('click', '.vehicle-fav', function (e) {
            e.preventDefault();
            var $icon = $(this).find('i');
            $icon.toggleClass('fa-regular fa-solid');
            $(this).css('color', $icon.hasClass('fa-solid') ? '#ff7a18' : '#7a8496');
        });
    }

    function initFilterReset() {
        $('#resetFilters').on('click', function () {
            var $form = $(this).closest('form');
            $form.find('input, select').each(function () {
                if ($(this).attr('type') !== 'submit') { $(this).val(''); }
            });
            $form.submit();
        });
    }

    $(function () {
        initNav();
        initSlider();
        initFavorites();
        initFilterReset();
    });

    return {
        money: money,
        escapeHtml: escapeHtml,
        toast: toast,
        postJson: postJson,
        getJson: getJson
    };
})(jQuery);
