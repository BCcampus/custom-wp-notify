/**
 * Handles the AJAX request in our Shortcode class.
 * Calls an anonymous function and passes the jQuery object.
 * Now we can use the $ shortcut as if no other libraries were on the page.
 */

(function ($) {

    $(document).ready(function () {

        // Handles the select all functionality
        $('#select_all').click(function() {
            $('.notifiable-categories').prop('checked', this.checked);
        });

        // Handle the changes
        $('button.notifiable-categories').on('click', function (event) {
            // prevent jumping to the top of page on form submit
            event.preventDefault();
            
            // let the user know something is loading
            $('.cwp-cat-loading').show().fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300);

            // temporarily disable to prevent accidental or additional clicks
            $('.notifiable-categories').prop("disabled", true);

            var prefs = [];
            $('input[name="cwp_notify_categories[]"]:checked').each(function () {
                prefs.push(parseInt($(this).val()));
            });

            // Ajax data
            var data = {
                'action': 'cwpCategoryPrefs',
                'categories': prefs,
                'nonce': category_settings.nonce
            };

            // Response
            $.post(category_settings.ajaxurl, data, function (response) {
                if (response.success === true) {

                    // show the success message
                    $('.cwp-cat-message').slideDown('slow').fadeOut('slow');

                } else {

                    // show the error message
                    $('.cwp-cat-message-error').slideDown('slow').fadeOut('slow');

                }

                // End loading message and re-enable checkbox
                $('.cwp-cat-loading').hide();
                $('.notifiable-categories').prop("disabled", false);
            });
        })
    });

})(jQuery);