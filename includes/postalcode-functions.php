<?php

// Function to search for a postcode in the 'product-city' taxonomy
function search_postcode($postcode) {
    // Attempt to retrieve cached terms
    $cached_terms = get_transient('cached_product_city_terms');
    if (false === $cached_terms) {
        $terms = get_terms(array(
            'taxonomy' => 'product-city',
            'hide_empty' => false,
        ));
        set_transient('cached_product_city_terms', $terms, 12 * HOUR_IN_SECONDS);
    } else {
        $terms = $cached_terms;
    }

    foreach ($terms as $term) {
        $zipcodes = get_field('zipcodes', $term);
        if (!$zipcodes) {
            continue;
        }

        $zipcode_list = explode("\n", $zipcodes);
        $zipcode_list = array_map('trim', $zipcode_list);

        if (in_array($postcode, $zipcode_list)) {
            return $term->name;
        }
    }

    return false;
}

// AJAX handlers for logged-in and non-logged-in users
add_action('wp_ajax_search_postcode', 'handle_search_postcode');
add_action('wp_ajax_nopriv_search_postcode', 'handle_search_postcode');

// Handle AJAX request and save the postcode in a transient
function handle_search_postcode() {
    // Check nonce for security
    check_ajax_referer('postalcode_clas_fixare_nonce', 'security');

    $postcode = sanitize_text_field($_POST['postcode']);
    $term_name = search_postcode($postcode);

    // Save the postcode in a PHP session whether there is a match or not
    $_SESSION['user_postcode'] = $postcode;

    if ($term_name) {
        $_SESSION['user_city'] = $term_name;
        echo $term_name;
    } else {
        echo 'false';
    }

    wp_die(); // Terminate AJAX request correctly
}

// Function to log when the postcode is retrieved
function log_retrieved_postcode() {
    $saved_postcode = get_transient('user_postcode');
    if ($saved_postcode !== false) {
        error_log('Retrieved saved postcode: ' . $saved_postcode);
    } else {
        error_log('No saved postcode found');
    }
}

// Run the logging function at every page load
add_action('init', 'log_retrieved_postcode');

add_action('wp_ajax_get_saved_postcode', 'handle_get_saved_postcode');
add_action('wp_ajax_nopriv_get_saved_postcode', 'handle_get_saved_postcode');

function handle_get_saved_postcode() {
    // Check nonce for security
    check_ajax_referer('postalcode_clas_fixare_nonce', 'security');

    echo get_transient('user_postcode') ?: 'false';
    wp_die();
}

function get_saved_postcode() {
    if (isset($_SESSION['user_postcode'])) {
        return $_SESSION['user_postcode'];
    } else {
        return '';
    }
}

function get_saved_city() {
    if (isset($_SESSION['user_city'])) {
        return $_SESSION['user_city'];
    } else {
        return '';
    }
}

function get_saved_user_city() {
    return isset($_SESSION['user_city']) ? $_SESSION['user_city'] : null;
}

global $post;
$product_id = $post->ID;

function is_product_related_to_user_city($product_id) {
    $user_city = get_saved_user_city();
    if (!$user_city) {
        return false;
    }

    // Retrieve the term for the saved user_city
    $term = get_term_by('name', $user_city, 'product-city');
    if (!$term) {
        return false;
    }

    // Retrieve the related products for the term with ACF
    $related_products = get_field('products', $term);

    // Check if the product ID is in the list of related products
    if (is_array($related_products)) {
        foreach ($related_products as $related_product) {
            if ($related_product->ID == $product_id) {
                return true;
            }
        }
    }

    return false;
}

// Clear postcode
function clear_saved_postcode() {
    if (isset($_SESSION['user_postcode'])) {
        unset($_SESSION['user_postcode']);
    }
    if (isset($_SESSION['user_city'])) {
        unset($_SESSION['user_city']);
    }
}

function handle_clear_postcode_request() {
    if (isset($_GET['clear_postcode'])) {
        clear_saved_postcode();
        // Redirect to the same page without the query parameter to avoid accidental re-clearing
        wp_redirect(remove_query_arg('clear_postcode'));
        exit;
    }
}
add_action('init', 'handle_clear_postcode_request');

function handle_update_postcode_content() {
    // Check nonce for security
    check_ajax_referer('postalcode_clas_fixare_nonce', 'security');

    $postcode = sanitize_text_field($_POST['postcode']);
    $_SESSION['user_postcode'] = $postcode; // Save the postcode in the session

    $savedPostcodeText = get_saved_postcode_text(); // Function that returns text based on the saved postcode
    $productCityRelationText = get_product_city_relation_text(get_the_ID()); // Function that returns the product's availability text

    echo json_encode([
        'savedPostcodeText' => $savedPostcodeText,
        'productCityRelationText' => $productCityRelationText
    ]);
    wp_die();
}
add_action('wp_ajax_update_postcode_content', 'handle_update_postcode_content');
add_action('wp_ajax_nopriv_update_postcode_content', 'handle_update_postcode_content');

?>
