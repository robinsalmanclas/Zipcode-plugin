jQuery(document).ready(function ($) {
    function formatAndSearchPostcode(postcode) {
        var formattedPostcode = postcode.replace(/\D/g, '').substring(0, 5);
        if (formattedPostcode.length > 3) {
            formattedPostcode = formattedPostcode.substring(0, 3) + " " + formattedPostcode.substring(3);
        }

        $('#postcodeInput').val(formattedPostcode);

        if (formattedPostcode.length === 6) {
            updatePostcodeContent(formattedPostcode.replace(/\s/g, ''));
        }
    }

    function updatePostcodeContent(postcode) {
        $.ajax({
            type: 'POST',
            url: postalcode_clas_fixare_ajax.ajax_url,
            data: {
                action: 'update_postcode_content',
                postcode: postcode
            },
            success: function (response) {
                var data = JSON.parse(response);
                $('#savedPostcodeContainer').html(data.savedPostcodeText);
                $('#productCityRelationContainer').html(data.productCityRelationText);
            },
            error: function () {
                console.log('Ett fel inträffade vid uppdatering av innehållet.');
            }
        });
    }

    $('#postcodeInput').on('input', function () {
        var postcode = $(this).val();
        formatAndSearchPostcode(postcode);
    });
});