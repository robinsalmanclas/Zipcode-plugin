jQuery(document).ready(function ($) {
    function formatAndSearchPostcode(postcode) {
        var formattedPostcode = postcode.replace(/\D/g, '').substring(0, 5);
        if (formattedPostcode.length > 3) {
            formattedPostcode = formattedPostcode.substring(0, 3) + " " + formattedPostcode.substring(3);
        }

        $('#postcodeInput').val(formattedPostcode);

        if (formattedPostcode.length === 6) {
            searchPostcode(formattedPostcode.replace(/\s/g, ''));
        }
    }

    function searchPostcode(postcode) {
        $.ajax({
            type: 'POST',
            url: postalcode_clas_fixare_ajax.ajax_url,
            data: {
                action: 'search_postcode',
                postcode: postcode,
            },
            success: function (response) {
                if (response !== 'false') {
                    // Om postnumret finns, uppdatera sidan
                    location.reload();
                } else {
                    $('#searchResults').html('Postnumret finns inte.');
                }
            },
            error: function () {
                $('#searchResults').html('Ett fel inträffade vid sökningen.');
            },
        });
    }

    $('#postcodeInput').on('input', function () {
        var postcode = $(this).val();
        formatAndSearchPostcode(postcode);
    });
});
