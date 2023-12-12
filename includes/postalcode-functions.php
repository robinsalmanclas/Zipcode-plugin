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

    if ($term_name) {
        // Spara postnumret i en PHP-session
        $_SESSION['user_postcode'] = $postcode;
        $_SESSION['user_city'] = $term_name;
        error_log('Postcode saved: ' . $postcode); // Debug-utskrift
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

