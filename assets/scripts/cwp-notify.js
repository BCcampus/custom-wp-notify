/**
 * Handles the AJAX request in our Shortcode class.
 * Calls an anonymous function and passes the jQuery object.
 * Now we can use the $ shortcut as if no other libraries were on the page.
 */

(function ($) {

    $(document).ready(function () {

        // Ajax data
        var data = {
            'action': 'cwpOptIn',
            'optin': $('input[name=cwp-opt-in]').val()
        };

        // State of checkbox based on the user meta
        if (data.optin === 0) {
            $('input[name=cwp-opt-in]').prop('checked', true);
        } else {
            $('input[name=cwp-opt-in]').prop('checked', false);
        }

        // Handle the changes
        $('.cwp-notify').on('change', '.notifiable', function (event) {

            // let the user know something is loading
            $('.cwp-loading').show().fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300);

            // temporarily disable to prevent accidental additional clicks
            $('.notifiable').prop("disabled", true);

            // Response
            $.post(settings.ajaxurl, data, function (response) {

                if (response.success === true) {

                    if ($('.notifiable').is(':checked')) {
                        $('input[name=cwp-opt-in]').val('1');
                        console.log('checked');
                    } else {
                        $('input[name=cwp-opt-in]').val('0');
                        console.log('unchecked');
                    }

                    // show the success message
                    $('.cwp-message').slideDown('slow').fadeOut('slow');

                } else {
                    console.log('false');
                }

                // End loading message and re-enable checkbox
                $('.cwp-loading').hide();
                $('.notifiable').prop("disabled", false);
            });
        })
    });

})(jQuery);