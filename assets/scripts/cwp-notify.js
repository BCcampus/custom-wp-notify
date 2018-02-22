( function ($) {

    $(document).ready(function () {

        $('.cwp-notify').on('click', '.save-preference', function (event) {

            var $button = $(this);

            $button.width($button.width()).text('...');

            // set ajax data
            var data = {
                'action': 'save_preference',
                'report': $('.confirm-change').val()
            };

            $.post(settings.ajaxurl, data, function (response) {

                console.log('ok');

            });

        });

    })(jQuery);
} )

