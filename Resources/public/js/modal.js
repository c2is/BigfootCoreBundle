$(function() {

    /*
     * Ajax modal
     */
    var $modal = $('#ajax-modal');

    $('body').on('click', '.ajax-modal', function() {
        var
            url   = $(this).data('url'),
            title = $(this).data('title');

        $('body').modalmanager('loading');

        setTimeout(function() {
            $modal.load(url, '', function() {
                $modal.modal();
                $('.modal-header h3').html(title);
            });
        }, 1000);
    });

    /*
     * Spinner modal
     */
    $.fn.modal.defaults.spinner = $.fn.modalmanager.defaults.spinner =
        '<div class="loading-spinner" style="width: 200px; margin-left: -100px;">' +
            '<div class="progress progress-striped active">' +
                '<div class="progress-bar" style="width: 100%;"></div>' +
            '</div>' +
        '</div>';

    $.fn.modalmanager.defaults.resize = true;

    $('[data-source]').each(function() {
        var
            $this   = $(this),
            $source = $($this.data('source')),
            text    = [];

        $source.each(function() {
            var $s = $(this);

            if ($s.attr('type') == 'text/javascript'){
                text.push($s.html().replace(/(\n)*/, ''));
            } else {
                text.push($s.clone().wrap('<div>').parent().html());
            }
        });

        $this.text(text.join('\n\n').replace(/\t/g, '    '));
    });

});
