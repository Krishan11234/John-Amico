jQuery(document).ready(function ($) {
    function toggleZip(value) {
        if (value) {
            $('.field-postcode').hide()
            $('.field-zip_from.field-zip_to').show()
        } else {
            $('.field-postcode').show()
            $('.field-zip_from.field-zip_to').hide()
        }
    }

    var zip_is_range = $('[name="zip_is_range"]');

    zip_is_range.change(function () {
        toggleZip($(this).is(':checked'))
    })

    toggleZip(zip_is_range.is(':checked'))
});
