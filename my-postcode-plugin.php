<?php
/**
 * Plugin Name: Postalcode Clas Fixare
 * Plugin URI: https://clasfixare.se
 * Description: En WordPress-plugin för att hantera postnummer.
 * Version: 1.0
 * Author: Robin
 * Author URI: https://clasfixare.se
 * License: GPL2
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 8.6
 */

// Starta PHP-session (om den inte redan är startad)
if (!session_id()) {
    session_start();
}

function postalcode_clas_fixare_enqueue_styles() {
    wp_enqueue_style('postalcode-clas-fixare-style', plugins_url('css/postalcode-clas-fixare.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'postalcode_clas_fixare_enqueue_styles');

// Inkludera PHP-fil med pluginens funktioner
include(plugin_dir_path(__FILE__) . 'includes/postalcode-functions.php');

// Enqueue JavaScript-filen
function postalcode_clas_fixare_enqueue_scripts() {
    wp_enqueue_script('postalcode-clas-fixare-script', plugins_url('js/postalcode-clas-fixare.js', __FILE__), array('jquery'), null, true);

    // Lägg till AJAX URL som en lokaliserad variabel
    wp_localize_script('postalcode-clas-fixare-script', 'postalcode_clas_fixare_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'postalcode_clas_fixare_enqueue_scripts');

// Lägg till shortcodes för att visa sökformuläret och det sparade postnumret
function display_postcode_search_form() {
    ob_start();
    ?>
    <div id="postcodeContainer">
        <?php
        $saved_postcode = get_saved_postcode(); // Funktionen som hämtar det sparade postnumret
        if ($saved_postcode) {
            // Visa postnumret som en länk om det är sparat
            echo '<a href="?clear_postcode=1">' . esc_html($saved_postcode) . '</a>';
        } else {
            // Visa inputfältet om inget postnummer är sparat
            echo '<input type="text" id="postcodeInput" placeholder="Ange postnummer" maxlength="6">';
        }
        ?>
    </div>
    <div id="searchResults"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('postcode_search_form', 'display_postcode_search_form');

function display_saved_city() {
    $saved_city = get_saved_city();
    $saved_postcode = get_saved_postcode();

    if ($saved_city) {
        return 'Some services are available in ' . $saved_city;
    } elseif ($saved_postcode) {
        return 'Vi finns för närvarande inte i angivet postnummer';
    } else {
        return 'Enter your postal code';
    }
}
add_shortcode('saved_postcode_text', 'display_saved_city');

function get_svg_icon_content() {
    $svg_path = plugin_dir_path(__FILE__) . 'includes/assets/PinDropOutlined.svg';
    return file_exists($svg_path) ? file_get_contents($svg_path) : '';
}

function display_product_city_relation() {
    global $post;
    $product_id = $post->ID;
    $saved_postcode = get_saved_postcode();

    // Hämta SVG-ikonen
    $svg_icon = get_svg_icon_content();

    if (!$saved_postcode) {
        return '<div class="postcode-status postcode-status-gray">
                    <div class="postcode-status-icon">
                    <div class="postcode-status-icon-black">' . $svg_icon . '</div></div>
                    <div class="postcode-status-text">Please enter your postal code</div>
                </div>';
    }

    if (is_product_related_to_user_city($product_id)) {
        return '<div class="postcode-status postcode-status-black">
                    <div class="postcode-status-icon">
                    <div class="postcode-status-icon-white">' . $svg_icon . '</div></div>
                    <div class="postcode-status-text">Available in your area</div>
                </div>';
    } else {
        return '<div class="postcode-status postcode-status-red">
                    <div class="postcode-status-icon">
                    <div class="postcode-status-icon-black">' . $svg_icon . '</div></div>
                    <div class="postcode-status-text">Not available in your area</div>
                </div>';
    }
}
add_shortcode('product_city_relation', 'display_product_city_relation');

?>
