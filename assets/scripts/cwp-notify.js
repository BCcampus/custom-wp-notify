/**
 * Handles the AJAX request in our Shortcode class.
 * Calls an anonymous function and passes the jQuery object.
 * Now we can use the $ shortcut as if no other libraries were on the page.
 */

(function ($) {

    $(document).ready(function () {

        $('.cwp-notify').on('change', '.notifiable', function (event) {

            // let the user know something is loading
            $('.cwp-loading').show().fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300);

            // temporarily disable to prevent accidental additional clicks
            $('.notifiable').prop("disabled", true);

            // set ajax data
            var data = {
                'action': 'cwpOptIn',
                'optin': $('.notifiable').val()
            };

            // Response
            $.post(settings.ajaxurl, data, function (response) {
                // remove the loading message

                $('.cwp-loading').hide();

                if (response.success === true) {
                    // value of notifiable
                    console.log(data.optin);

                    // show the success message
                    $('.cwp-message').slideDown('slow').fadeOut('slow');

                    // enable the checkbox
                    $('.notifiable').prop("disabled", false);
                } else {

                }
            });
        })
    });

})(jQuery);