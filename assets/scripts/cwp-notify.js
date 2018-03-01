/**
 * Handles the AJAX request in our Shortcode class.
 * Calls an anonymous function and passes the jQuery object.
 * Now we can use the $ shortcut as if no other libraries were on the page.
 */

(function ($) {

    $(document).ready(function () {


        // Handle the changes
        $('.cwp-notify').on('change', '.notifiable', function (event) {

            // todo: set the checkbox state based on the settings.is_checked
            $(console.log(settings.checkstate));

            // let the user know something is loading
            $('.cwp-loading').show().fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300);

            // temporarily disable to prevent accidental or additional clicks
            $('.notifiable').prop("disabled", true);

            // set the value depending on if it's checked or not
            if ($('.notifiable').is(':checked')) {
                $new_value = 1;
            } else {
                $new_value = 0;
            }

            // Ajax data
            var data = {
                'action': 'cwpOptIn',
                'new_value': $new_value
            };
            // Response
            $.post(settings.ajaxurl, data, function (response) {

                if (response.success === true) {

                    // show the success message
                    $('.cwp-message').slideDown('slow').fadeOut('slow');

                } else {

                }

                // End loading message and re-enable checkbox
                $('.cwp-loading').hide();
                $('.notifiable').prop("disabled", false);
            });
        })
    });

})(jQuery);