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

// Hantera AJAX-förfrågan och spara postnumret i en PHP-session
function handle_search_postcode() {
    $postcode = sanitize_text_field($_POST['postcode']);
    $term_name = search_postcode($postcode);

    $_SESSION['user_postcode'] = $postcode;

    if ($term_name) {
        $_SESSION['user_city'] = $term_name;
        echo $term_name;
    } else {
        echo 'false';
    }

    wp_die();
}

// Funktion för att hämta det sparade postnumret
function get_saved_postcode() {
    if (isset($_SESSION['user_postcode'])) {
        return $_SESSION['user_postcode'];
    } else {
        return '';
    }
}

// Funktion för att hämta den sparade staden
function get_saved_city() {
    if (isset($_SESSION['user_city'])) {
        return $_SESSION['user_city'];
    } else {
        return '';
    }
}

// Funktion för att kontrollera om en produkt är relaterad till användarens stad
function is_product_related_to_user_city($product_id) {
    $user_city = get_saved_city();
    if (!$user_city) {
        return false;
    }

    $term = get_term_by('name', $user_city, 'product-city');
    if (!$term) {
        return false;
    }

    $related_products = get_field('products', $term);
    if (is_array($related_products)) {
        foreach ($related_products as $related_product) {
            if ($related_product->ID == $product_id) {
                return true;
            }
        }
    }

    return false;
}

// Shortcode för att visa produktens tillgänglighet baserat på postnummer
function display_product_city_relation() {
    global $post;
    $product_id = $post->ID;

    $saved_postcode = get_saved_postcode();
    if (!$saved_postcode) {
        return "Vänligen ange postnummer för att se tillgänglighet";
    }

    if (is_product_related_to_user_city($product_id)) {
        return "Denna produkt är tillgänglig i ditt område.";
    } else {
        return "Denna produkt är inte tillgänglig i ditt område.";
    }
}
add_shortcode('product_city_relation', 'display_product_city_relation');

// Funktion för att rensa det sparade postnumret
function clear_saved_postcode() {
    if (isset($_SESSION['user_postcode'])) {
        unset($_SESSION['user_postcode']);
    }
    if (isset($_SESSION['user_city'])) {
        unset($_SESSION['user_city']);
    }
}

// Hantera förfrågan om att rensa det sparade postnumret
function handle_clear_postcode_request() {
    if (isset($_GET['clear_postcode'])) {
        clear_saved_postcode();
        wp_redirect(remove_query_arg('clear_postcode'));
        exit;
    }
}
add_action('init', 'handle_clear_postcode_request');

// Shortcode för att visa sökformuläret och det sparade postnumret
function display_postcode_search_form() {
    ob_start();
    ?>
    <div id="postcodeContainer">
        <?php
        $saved_postcode = get_saved_postcode();
        if ($saved_postcode) {
            echo '<a href="?clear_postcode=1">' . esc_html($saved_postcode) . '</a>';
        } else {
            echo '<input type="text" id="postcodeInput" placeholder="Ange postnummer" maxlength="6">';
        }
        ?>
    </div>
    <div id="searchResults"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('postcode_search_form', 'display_postcode_search_form');

// AJAX-hanterare för att uppdatera innehållet baserat på det sparade postnumret
function handle_update_postcode_content() {
    $postcode = sanitize_text_field($_POST['postcode']);
    $_SESSION['user_postcode'] = $postcode;

    $savedPostcodeText = get_saved_postcode_text();
    $productCityRelationText = get_product_city_relation_text(get_the_ID());

    echo json_encode([
        'savedPostcodeText' => $savedPostcodeText,
        'productCityRelationText' => $productCityRelationText
    ]);
    wp_die();
}
add_action('wp_ajax_update_postcode_content', 'handle_update_postcode_content');
add_action('wp_ajax_nopriv_update_postcode_content', 'handle_update_postcode_content');

function get_saved_postcode_text() {
    $saved_postcode = get_saved_postcode();
    if ($saved_postcode) {
        return 'Sparat postnummer: ' . esc_html($saved_postcode);
    } else {
        return 'Vänligen ange postnummer för att se tillgänglighet';
    }
}

function get_product_city_relation_text($product_id) {
    $user_city = get_saved_city();
    if (!$user_city) {
        return "Vänligen ange postnummer för att se tillgänglighet";
    }

    $term = get_term_by('name', $user_city, 'product-city');
    if (!$term) {
        return "Denna produkt är inte tillgänglig i ditt område.";
    }

    $related_products = get_field('products', $term);
    if (is_array($related_products)) {
        foreach ($related_products as $related_product) {
            if ($related_product->ID == $product_id) {
                return "Denna produkt är tillgänglig i ditt område.";
            }
        }
    }

    return "Denna produkt är inte tillgänglig i ditt område.";
}

function display_saved_postcode_text() {
    $saved_postcode = get_saved_postcode();
    $saved_city = get_saved_city();

    if ($saved_city) {
        return 'Some services are available in ' . $saved_city;
    } elseif ($saved_postcode) {
        return 'Vi finns för närvarande inte i angivet postnummer';
    } else {
        return 'Enter your postal code';
    }
}
add_shortcode('saved_postcode_text', 'display_saved_postcode_text');

?>
