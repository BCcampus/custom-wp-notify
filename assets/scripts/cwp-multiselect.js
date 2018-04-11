/**
 *  MultiSelect JS
 */

    jQuery(document).ready(function($) {
        $('#multiselect').multiselect({
            search: {
                left: '<input type="text" name="q" class="form-control" placeholder="Filter by email or username..." />',
                right: '<input type="text" name="q" class="form-control" placeholder="Filter by email or username..." />',
            },
            fireSearch: function(value) {
                return value.length > 3;
            }
        });
    });
