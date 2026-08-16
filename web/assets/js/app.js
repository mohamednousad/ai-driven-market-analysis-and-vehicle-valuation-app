var AV = (function ($) {
    var base = $('meta[name="base-url"]').attr('content') || '';
    var csrf = $('meta[name="csrf-token"]').attr('content') || '';

    var toastIcons = {
        success: 'fa-circle-check',
        error: 'fa-circle-xmark',
        warning: 'fa-triangle-exclamation',
        info: 'fa-circle-info'
    };

    function toast(type, message, duration) {
        var $stack = $('#toastStack');
        if (!$stack.length) return;
        var $t = $('<div class="toast ' + type + '"><i class="fa-solid ' + toastIcons[type] + '"></i><span></span><button class="toast-close" type="button"><i class="fa-solid fa-xmark"></i></button></div>');
        $t.find('span').text(message);
        $stack.append($t);
        requestAnimationFrame(function () { $t.addClass('show'); });
        var timer = setTimeout(remove, duration || 4200);
        function remove() {
            clearTimeout(timer);
            $t.removeClass('show');
            setTimeout(function () { $t.remove(); }, 320);
        }
        $t.find('.toast-close').on('click', remove);
    }

    function modal(opts) {
        var $ov = $('#appModal');
        var icons = { success: ['green', 'fa-circle-check'], error: ['red', 'fa-circle-xmark'], warning: ['gold', 'fa-triangle-exclamation'], info: ['blue', 'fa-circle-info'], ai: ['gold', 'fa-wand-magic-sparkles'] };
        var pair = icons[opts.type || 'info'];
        var html = '';
        if (opts.loader) {
            html += '<div class="ai-loader"><div class="ring"></div></div>';
        } else {
            html += '<div class="modal-icon ' + pair[0] + '"><i class="fa-solid ' + pair[1] + '"></i></div>';
        }
        html += '<h3></h3><p></p><div class="modal-actions"></div>';
        $ov.find('.modal').html(html);
        $ov.find('h3').text(opts.title || '');
        $ov.find('p').html(opts.html || '');
        if (opts.text) $ov.find('p').text(opts.text);
        var $actions = $ov.find('.modal-actions');
        (opts.buttons || []).forEach(function (b) {
            var $btn = $('<button type="button" class="btn ' + (b.cls || 'btn-dark') + '"></button>').text(b.label);
            $btn.on('click', function () {
                if (b.keepOpen !== true) close();
                if (b.onClick) b.onClick();
            });
            $actions.append($btn);
        });
        if (!opts.loader && !(opts.buttons || []).length) {
            var $ok = $('<button type="button" class="btn btn-dark">Okay</button>');
            $ok.on('click', close);
            $actions.append($ok);
        }
        $ov.addClass('open');
        function close() { $ov.removeClass('open'); }
        return { close: close };
    }

    function confirmAction(title, text, onYes, yesLabel, yesCls) {
        modal({
            type: 'warning',
            title: title,
            text: text,
            buttons: [
                { label: 'Cancel', cls: 'btn-outline-dark' },
                { label: yesLabel || 'Yes, continue', cls: yesCls || 'btn-gold', onClick: onYes }
            ]
        });
    }

    function ajax(url, data, done, fail) {
        return $.ajax({
            url: base + '/' + url,
            method: 'POST',
            data: data,
            dataType: 'json',
            headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            if (done) done(res);
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Request failed. Check your connection and try again.';
            if (fail) { fail(msg, xhr); } else { toast('error', msg); }
        });
    }

    function gatewaySubmit(gateway) {
        var $form = $('<form method="post"></form>').attr('action', gateway.action).css('display', 'none');
        $.each(gateway.fields || {}, function (name, value) {
            $form.append($('<input type="hidden">').attr('name', name).val(value));
        });
        $('body').append($form);
        $form.trigger('submit');
    }

    function setLoading($btn, on) {
        if (on) {
            $btn.addClass('loading').prop('disabled', true);
            if (!$btn.find('.spinner').length) $btn.prepend('<span class="spinner"></span>');
        } else {
            $btn.removeClass('loading').prop('disabled', false);
            $btn.find('.spinner').remove();
        }
    }

    function showFieldErrors($form, errors) {
        $form.find('.form-group').removeClass('has-error');
        $.each(errors || {}, function (field, msg) {
            var $input = $form.find('[name="' + field + '"]');
            var $group = $input.closest('.form-group');
            $group.addClass('has-error');
            $group.find('.field-error').text(msg);
        });
        var $first = $form.find('.has-error').first();
        if ($first.length) $('html,body').animate({ scrollTop: $first.offset().top - 120 }, 250);
    }

    $(function () {
        $('#navToggle').on('click', function () {
            $('#navLinks').toggleClass('open');
            $('#sidebarOverlay').toggleClass('open');
        });
        $('#sidebarOverlay').on('click', function () {
            $('#navLinks').removeClass('open');
            $(this).removeClass('open');
        });

        $('#notifBtn').on('click', function (e) {
            e.stopPropagation();
            var $drop = $('#notifDrop');
            $drop.toggleClass('open');
            if ($drop.hasClass('open')) {
                ajax('api/notifications.php', { action: 'list' }, function (res) {
                    var $list = $drop.find('.nd-list').empty();
                    if (!res.items.length) {
                        $list.append('<div class="notif-item"><p>No notifications yet.</p></div>');
                        return;
                    }
                    res.items.forEach(function (n) {
                        var icons = { system: 'fa-gear', ad: 'fa-car', payment: 'fa-credit-card', message: 'fa-comment' };
                        var $item = $('<div class="notif-item ' + (n.is_read == 0 ? 'unread' : '') + '"><div class="ni-icon"><i class="fa-solid ' + (icons[n.type] || 'fa-bell') + '"></i></div><div><b></b><p></p><span class="ni-time"></span></div></div>');
                        $item.find('b').text(n.title);
                        $item.find('p').text(n.message);
                        $item.find('.ni-time').text(n.time_ago);
                        $list.append($item);
                    });
                    ajax('api/notifications.php', { action: 'mark_read' }, function () {
                        $('#notifBadge').remove();
                    });
                });
            }
        });
        $(document).on('click', function () { $('#notifDrop').removeClass('open'); });
        $('#notifDrop').on('click', function (e) { e.stopPropagation(); });

        (window.AV_FLASH || []).forEach(function (f) { toast(f.type, f.message); });

        $(document).on('click', '.js-fav', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            ajax('api/favourite.php', { ad_id: $btn.data('ad') }, function (res) {
                $btn.toggleClass('active', res.favourited);
                $btn.find('i').attr('class', res.favourited ? 'fa-solid fa-heart' : 'fa-regular fa-heart');
                toast(res.favourited ? 'success' : 'info', res.favourited ? 'Added to your favourites.' : 'Removed from your favourites.');
            });
        });

        $(document).on('click', '.js-confirm', function (e) {
            e.preventDefault();
            var $el = $(this);
            confirmAction($el.data('title') || 'Are you sure?', $el.data('text') || 'This action cannot be undone.', function () {
                if ($el.data('href')) { window.location = $el.data('href'); return; }
                var $form = $el.closest('form');
                if ($form.length) $form.trigger('submit');
            }, $el.data('yes') || 'Yes, continue', $el.data('danger') ? 'btn-danger' : 'btn-gold');
        });

        $('.gallery-thumbs img').on('click', function () {
            $('.gallery-thumbs img').removeClass('active');
            $(this).addClass('active');
            $('#galleryMain').attr('src', $(this).data('full'));
        });

        $('.rating-stars i').on('mouseenter click', function (e) {
            var $stars = $(this).parent().find('i');
            var idx = $stars.index(this);
            $stars.each(function (i) { $(this).toggleClass('filled', i <= idx); });
            if (e.type === 'click') $(this).parent().data('value', idx + 1).attr('data-value', idx + 1);
        });
        $('.rating-stars').on('mouseleave', function () {
            var value = parseInt($(this).attr('data-value') || '0', 10);
            $(this).find('i').each(function (i) { $(this).toggleClass('filled', i < value); });
        });
    });

    return { toast: toast, modal: modal, confirm: confirmAction, ajax: ajax, setLoading: setLoading, showFieldErrors: showFieldErrors, gatewaySubmit: gatewaySubmit, base: base, csrf: csrf };
})(jQuery);
