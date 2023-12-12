jQuery(document).ready(function ($) {
    // Hantera sökning av postnummer
    $('#postcodeSearchForm').submit(function (e) {
        e.preventDefault(); // Förhindra standardformulärskickning

        var postcode = $('#postcodeInput').val();
        var data = {
            action: 'search_postcode',
            postcode: postcode,
        };

        $.ajax({
            type: 'POST',
            url: postalcode_clas_fixare_ajax.ajax_url,
            data: data,
            success: function (response) {
                if (response !== 'false') {
                    $('#searchResults').html('Postnumret finns i: ' + response);
                } else {
                    $('#searchResults').html('Postnumret finns inte.');
                }
            },
            error: function () {
                $('#searchResults').html('Ett fel inträffade vid sökningen.');
            },
        });
    });

    // Hämta sparad postkoddata och visa den
    var savedPostcode = $('#savedPostcode').data('saved-postcode');
    if (savedPostcode) {
        $('#savedPostcode').val(savedPostcode);
    }
});
