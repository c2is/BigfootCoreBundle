$(function(){

    var options = {
        success: successResponse,
    };

    $('.modal-trigger')
        .on('click', function (event) {
            var modal = $(this).data('target');

            $(modal + ' .modal-body').html('<div class="progress progress-striped active"><div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>');
        });

    $('.modal-save')
        .on('click', function (event) {
            $(this)
                .closest('.modal')
                    .find('form')
                        .ajaxSubmit(options);
        });

    function successResponse(responseText, statusText, xhr, $form) {
        if (responseText.status === true) {
            var modal = $('#' + responseText.modal);

            modal
                .find('.modal-body')
                    .empty()
                    .prepend("<div class='alert alert-block alert-success'>" + responseText.message + '</div>');

            modal
                .find('.modal-footer')
                    .remove();

            window.location.reload();
        } else {
            $('#' + responseText.modal)
                .find('.modal-body')
                    .empty()
                    .prepend("<div class='alert alert-block alert-danger'>" + responseText.message + '</div>')
                    .append(responseText.content);
        }
    }

});
