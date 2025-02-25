<?php
/**
 * Plugin Name: Nightsbridge Widget
 * Plugin URI: https://wpdevs.co.za
 * Description: Allow visitors to check NightsBridge room availability and make bookings directly on your WordPress website, by querying the NightsBridge API.
 * Version: 2.0.0
 * Author: Melanie Shepherd
 * Author URI: https://wpdevs.co.za
 * Text Domain: nightsbridge
 * License URI: https://mit-license.org/
 */

defined('ABSPATH') || exit;

// Define constants
if (!defined('NIGHTSBRIDGE_VERSION')) {
    define('NIGHTSBRIDGE_VERSION', '2.0.0');
}

if (!defined('NIGHTSBRIDGE_BASENAME')) {
    define('NIGHTSBRIDGE_BASENAME', plugin_basename(__FILE__));
}

if (!defined('NIGHTSBRIDGE_PLUGIN_DIR')) {
    define('NIGHTSBRIDGE_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('NIGHTSBRIDGE_PLUGIN_URL')) {
    define('NIGHTSBRIDGE_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('NIGHTSBRIDGE_PLUGIN_FILE')) {
    define('NIGHTSBRIDGE_PLUGIN_FILE', __FILE__);
}

// Include the core class
require_once NIGHTSBRIDGE_PLUGIN_DIR . 'includes/class-nightsbridge-core.php';

// Initialize the plugin
function nightsbridge_widget_init() {
    $core = new Nightsbridge_Core();
}
add_action('plugins_loaded', 'nightsbridge_widget_init');


/**
 * for debugging purposes
 */
/* function nb_debug_settings() {
    $options = get_option( 'nb_settings', array() );
    error_log( 'Current nb_settings: ' . print_r( $options, true ) );
    if ( is_admin() ) {
        echo '<pre>Current Settings: ' . esc_html( print_r( $options, true ) ) . '</pre>';
    }
}
add_action( 'admin_init', 'nb_debug_settings' );
add_action( 'wp_head', 'nb_debug_settings' ); */  // Logs on frontend too


// Cleanup on uninstall
register_uninstall_hook(__FILE__, 'nb_uninstall');
function nb_uninstall() {
    delete_option('nb_settings');
}