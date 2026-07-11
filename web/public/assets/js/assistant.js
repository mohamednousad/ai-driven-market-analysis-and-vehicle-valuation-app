(function ($) {
    'use strict';

    var history = [];

    function appendMessage(text, role) {
        var $msg = $('<div class="chat-msg ' + role + '"></div>').text(text);
        $('#chatLog').append($msg);
        var log = document.getElementById('chatLog');
        log.scrollTop = log.scrollHeight;
        return $msg;
    }

    function send() {
        var text = $.trim($('#chatText').val());
        if (!text) { return; }
        appendMessage(text, 'user');
        history.push({ role: 'user', text: text });
        $('#chatText').val('');
        $('#chatSend').prop('disabled', true);

        var $typing = appendMessage('Assistant is typing...', 'assistant typing');

        AutoValue.postJson('../api/chat.php', { message: text, history: history.slice(0, -1) })
            .done(function (data) {
                $typing.remove();
                if (data.success) {
                    appendMessage(data.reply, 'assistant');
                    history.push({ role: 'assistant', text: data.reply });
                } else {
                    appendMessage(data.message || 'Something went wrong.', 'assistant');
                }
            })
            .fail(function (xhr) {
                $typing.remove();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to reach the assistant.';
                appendMessage(msg, 'assistant');
            })
            .always(function () {
                $('#chatSend').prop('disabled', false);
                $('#chatText').focus();
            });
    }

    $('#chatSend').on('click', send);
    $('#chatText').on('keypress', function (e) {
        if (e.which === 13) { send(); }
    });
})(jQuery);
