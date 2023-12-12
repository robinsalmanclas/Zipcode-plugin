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

// Inkludera PHP-fil med pluginens funktioner
include(plugin_dir_path(__FILE__) . 'includes/postalcode-functions.php');

// Enqueue JavaScript-filen
function postalcode_clas_fixare_enqueue_scripts() {
    wp_enqueue_script('postalcode-clas-fixare-script', plugins_url('js/postalcode-clas-fixare.js', __FILE__), array('jquery'), null, true);

    // Lägg till AJAX URL som en lokaliserad variabel
    wp_localize_script('postalcode-clas-fixare-script', 'postalcode_clas_fixare_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'postalcode_clas_fixare_enqueue_scripts');

