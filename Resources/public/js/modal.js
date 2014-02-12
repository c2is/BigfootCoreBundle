$(function(){

    $('.m-trigger')
        .on('click', function (event) {
            var modal = $(this).data('target');

            $(modal + ' .modal-body').html('<div class="progress progress-striped active"><div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>');
        });

});
