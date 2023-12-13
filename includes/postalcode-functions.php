<?php

// Funktion för att söka efter ett postnummer i 'product-city' taxonomin
function search_postcode($postcode) {
    $terms = get_terms(array(
        'taxonomy' => 'product-city',
        'hide_empty' => false,
    ));

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

// AJAX-handlers för inloggade och icke-inloggade användare
add_action('wp_ajax_search_postcode', 'handle_search_postcode');
add_action('wp_ajax_nopriv_search_postcode', 'handle_search_postcode');

// Hantera AJAX-förfrågan och spara postnumret i en transient
function handle_search_postcode() {
    $postcode = sanitize_text_field($_POST['postcode']);
    $term_name = search_postcode($postcode);

    // Spara postnumret i en PHP-session oavsett om det finns en matchning eller inte
    $_SESSION['user_postcode'] = $postcode;

    if ($term_name) {
        $_SESSION['user_city'] = $term_name;
        echo $term_name;
    } else {
        echo 'false';
    }

    wp_die(); // Avsluta AJAX-anropet korrekt
}

// Funktion för att logga när postnumret hämtas
function log_retrieved_postcode() {
    $saved_postcode = get_transient('user_postcode');
    if ($saved_postcode !== false) {
        error_log('Retrieved saved postcode: ' . $saved_postcode);
    } else {
        error_log('No saved postcode found');
    }
}

// Kör loggningsfunktionen vid varje sidbelastning
add_action('init', 'log_retrieved_postcode');

add_action('wp_ajax_get_saved_postcode', 'handle_get_saved_postcode');
add_action('wp_ajax_nopriv_get_saved_postcode', 'handle_get_saved_postcode');

function handle_get_saved_postcode() {
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

    // Hämta termen för den sparade user_city
    $term = get_term_by('name', $user_city, 'product-city');
    if (!$term) {
        return false;
    }

    // Hämta de relaterade produkterna för termen med ACF
    $related_products = get_field('products', $term);

    // Kontrollera om produkt-ID:t finns i listan över relaterade produkter
    if (is_array($related_products)) {
        foreach ($related_products as $related_product) {
            if ($related_product->ID == $product_id) {
                return true;
            }
        }
    }

    return false;
}

//rensa postnummer
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
        // Omdirigera till samma sida utan query-parametern för att undvika oavsiktlig återrensning
        wp_redirect(remove_query_arg('clear_postcode'));
        exit;
    }
}
add_action('init', 'handle_clear_postcode_request');


function handle_update_postcode_content() {
    $postcode = sanitize_text_field($_POST['postcode']);
    $_SESSION['user_postcode'] = $postcode; // Spara postnumret i sessionen

    $savedPostcodeText = get_saved_postcode_text(); // Funktion som returnerar text baserat på det sparade postnumret
    $productCityRelationText = get_product_city_relation_text(get_the_ID()); // Funktion som returnerar produktens tillgänglighetstext

    echo json_encode([
        'savedPostcodeText' => $savedPostcodeText,
        'productCityRelationText' => $productCityRelationText
    ]);
    wp_die();
}
add_action('wp_ajax_update_postcode_content', 'handle_update_postcode_content');
add_action('wp_ajax_nopriv_update_postcode_content', 'handle_update_postcode_content');