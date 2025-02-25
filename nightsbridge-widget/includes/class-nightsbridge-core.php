<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://www.nightsbridge.co.za
 * @since      1.0.0
 *
 * @package    Nightsbridge
 * @subpackage Nightsbridge/includes
 */
defined('ABSPATH') || exit;

class Nightsbridge_Core {
/**
 * Constructor for the Nightsbridge_Core class.
 *
 * Initializes the core plugin by loading dependencies and setting up hooks.
 *
 * @return void
 */
    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load plugin dependencies.
     *
     * This method is called by the constructor to load the plugin's dependencies
     * into memory. It requires the admin and frontend class files.
     *
     * @return void
     */
    private function load_dependencies() {
        require_once NIGHTSBRIDGE_PLUGIN_DIR . 'includes/class-nightsbridge-admin.php';
        require_once NIGHTSBRIDGE_PLUGIN_DIR . 'includes/class-nightsbridge-frontend.php';
    }
    
    /**
     * Initialize the core plugin hooks.
     *
     * This method sets up the necessary actions for the plugin's operation. It
     * hooks into the 'plugins_loaded' action to load the plugin's text domain for
     * translations and initializes the admin and frontend components of the
     * Nightsbridge plugin.
     *
     * @return void
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        new Nightsbridge_Admin();
        new Nightsbridge_Frontend();
    }
    
    /**
     * Load the plugin's translation file.
     *
     * This method hooks into the 'plugins_loaded' action to load the plugin's
     * translation file. The domain is set to 'nightsbridge' and the path is
     * relative to the plugin's root directory.
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain('nightsbridge', false, dirname(plugin_basename(NIGHTSBRIDGE_PLUGIN_FILE)) . '/languages/');
    }
    
    /**
     * Delete the plugin settings from the options table when the plugin is uninstalled.
     *
     * This method is registered as the uninstall hook for the plugin and is called
     * when the plugin is uninstalled. It deletes the plugin's settings from the
     * options table to prevent any data from being left behind.
     *
     * @return void
     */
    public static function uninstall() {
        delete_option('nb_settings');
    }
}