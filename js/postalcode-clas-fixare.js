jQuery(document).ready(function ($) {
    // Function to format and search for a postcode
    function formatAndSearchPostcode(postcode) {
        var formattedPostcode = postcode.replace(/\D/g, '').substring(0, 5);
        if (formattedPostcode.length > 3) {
            formattedPostcode = formattedPostcode.substring(0, 3) + " " + formattedPostcode.substring(3);
        }

        $('#postcodeInput').val(formattedPostcode);

        if (formattedPostcode.length === 6) {
            searchPostcode(formattedPostcode.replace(/\s/g, ''));
        } else {
            // Disable the button if the postcode is not complete
            updateAddToCartButton(false, 'Enter postcode for availability');
        }
    }

    // Function to search for a postcode
    function searchPostcode(postcode) {
        $.ajax({
            type: 'POST',
            url: postalcode_clas_fixare_ajax.ajax_url,
            data: {
                action: 'search_postcode',
                postcode: postcode,
                security: postalcode_clas_fixare_ajax.nonce
            },
            success: function (response) {
                if (response !== 'false') {
                    location.reload(); // Reload page if zipcode is available
                    updateAddToCartButton(true, 'Add to cart');
                } else {
                    $('#searchResults').html('The postcode does not exist.');
                    updateAddToCartButton(false, 'Ej tillgängligt i området');
                }
            },
            error: function () {
                $('#searchResults').html('An error occurred during the search.');
                updateAddToCartButton(false, 'Enter postcode for availability');
            },
        });
    }

    // Function to update the Add to Cart button
    function updateAddToCartButton(isEnabled, text) {
        var addToCartButton = $('.single_add_to_cart_button');
        addToCartButton.prop('disabled', !isEnabled).text(text);
    }

    // Function to check the postcode availability for the current product
    function checkProductAvailability() {
        var savedPostcode = $('#postcodeInput').data('saved-postcode');
        var productID = $('#productInfo').data('product-id');

        if (savedPostcode && productID) {
            $.ajax({
                type: 'POST',
                url: postalcode_clas_fixare_ajax.ajax_url,
                data: {
                    action: 'check_product_availability',
                    postcode: savedPostcode,
                    product_id: productID,
                    security: postalcode_clas_fixare_ajax.nonce
                },
                success: function (response) {
                    if (response.isAvailable) {
                        updateAddToCartButton(true, 'Add to cart');
                    } else {
                        updateAddToCartButton(false, 'Ej tillgängligt i området');
                    }
                },
                error: function () {
                    console.error('Ett fel inträffade vid kontroll av produktens tillgänglighet.');
                }
            });
        } else {
            // Om inget postnummer är sparat, inaktivera knappen
            updateAddToCartButton(false, 'Enter postcode for availability');
        }
    }

    // Event listener for postcode input
    $('#postcodeInput').on('input', function () {
        var postcode = $(this).val();
        formatAndSearchPostcode(postcode);
    });

    // Initial check of product availability
    checkProductAvailability();
});
