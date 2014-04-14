(function($) {
    $.fn.confirmModal = function(options)
    {
        $('body').append('<div id="confirmContainer"></div>');
        var confirmContainer = $('#confirmContainer');

        $(this).on('click', function(modalEvent)
        {
            var confirmLink = $(this);
            modalEvent.preventDefault();
            var targetData = $(modalEvent.target).data();
            var modal = Twig.render(confirmModal, targetData);

            confirmContainer.html(modal);
            confirmContainer.children('#confirmModal').modal('show');

            var callback = options.callback;
            if (callback == undefined || typeof callback !== 'function') {
                callback = function(event) {
                    confirmContainer.modal('hide');
                    window.location = confirmLink.attr('href');
                };
            }

            $('button[data-dismiss="ok"]', confirmContainer).on('click', callback);
        });

        return this;
    };
})(jQuery);