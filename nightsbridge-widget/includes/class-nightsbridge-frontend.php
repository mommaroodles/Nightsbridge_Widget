<?php
/**
 * Nightsbridge Widget Frontend Class
 *
 * Handles frontend functionality for the Nightsbridge Widget plugin, including
 * script/style enqueuing, shortcodes, and custom styles.
 *
 * @package Nightsbridge_Widget
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

class Nightsbridge_Frontend {
    
    /**
     * Constructor method for the Nightsbridge_Frontend class.
     *
     * Calls the register_hooks method to set up the various hooks and filters
     * for the frontend functionality of the plugin.
     *
     * @return void
     */
    public function __construct() {
        $this->register_hooks();
    }
    
    /**
     * Registers the hooks and filters for the frontend functionality of the
     * Nightsbridge Widget plugin.
     *
     * Hooks:
     * - wp_enqueue_scripts: Enqueues the script and style for the Nightsbridge
     *   widget.
     * - wp_head: Applies custom styles to the frontend of the plugin.
     *
     * Shortcodes:
     * - nb_availability_search: Displays a grid widget which displays the
     *   accommodation and dates available in calendar form.
     * - nb_availability_check: Displays a single input field for selecting a
     *   check-in date, and a button to check availability.
     *
     * @return void
     */
    private function register_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'nb_enqueue_widget_scripts' ) );
        add_action( 'wp_head', array( $this, 'nb_apply_custom_styles' ) );
        add_shortcode( 'nb_availability_search', array( $this, 'nb_availability_search_shortcode' ) );
        add_shortcode( 'nb_availability_check', array( $this, 'nb_availability_check_shortcode' ) );
    }
    
    /**
     * Enqueues the required scripts and styles for the Nightsbridge Widget frontend.
     *
     * The widget scripts and styles are only loaded if the page slug set in the
     * plugin settings matches the current page, or if the page slug is empty.
     *
     * @return void
     */
    public function nb_enqueue_widget_scripts() {
        $options = get_option( 'nb_settings' );
        $selected_page = !empty( $options['nb_page_slug'] ) ? sanitize_title( $options['nb_page_slug'] ) : '';
        
        if ( empty( $selected_page ) || ! is_page( $selected_page ) ) {
            return;
        }
        
        // Enqueue CSS files
        wp_enqueue_style( 'nightsbridge-flatpickr-material-blue', 'https://cdn.nightsbridge.com/flatpickr/material_blue-1.0.css', array(), '1.0' );
        wp_enqueue_style( 'nightsbridge-flatpickr', plugin_dir_url( NIGHTSBRIDGE_PLUGIN_FILE ) . 'assets/css/flatpickr.css', array(), NIGHTSBRIDGE_VERSION );
        wp_enqueue_style( 'nightsbridge-widget-style', plugin_dir_url( NIGHTSBRIDGE_PLUGIN_FILE ) . 'assets/css/nb_DateWidgetStyle.css', array(), NIGHTSBRIDGE_VERSION );
        wp_enqueue_style( 'nightsbridge-style', plugin_dir_url( NIGHTSBRIDGE_PLUGIN_FILE ) . 'assets/css/style.css', array(), NIGHTSBRIDGE_VERSION );
        
        // Enqueue JS files
        wp_enqueue_script( 'nightsbridge-flatpickr', 'https://cdn.nightsbridge.com/flatpickr/flatpickr.min-1.0.js', array(), '1.0', true );
        wp_enqueue_script( 'nightsbridge-widget-script', plugin_dir_url( NIGHTSBRIDGE_PLUGIN_FILE ) . 'assets/js/nightsbridge-widget-v2.js', array( 'nightsbridge-flatpickr' ), NIGHTSBRIDGE_VERSION, true );
        
        // Localize script with settings
        $bbid = isset( $options['nb_bbid'] ) ? sanitize_text_field( $options['nb_bbid'] ) : '';
        $custom_format = isset( $options['nb_custom_format'] ) ? sanitize_text_field( $options['nb_custom_format'] ) : 'd-M-Y';
        $language = isset( $options['nb_language'] ) ? sanitize_text_field( $options['nb_language'] ) : 'en-GB';
        
        wp_localize_script( 'nightsbridge-widget-script', 'nbConfig', array(
                'bbid'         => $bbid,
                'customFormat' => $custom_format,
                'language'     => $language,
        ) );
    }
    
    /**
     * Shortcode to display the availability search grid view.
     *
     * @return string HTML output for the shortcode.
     */
    public function nb_availability_search_shortcode() {
        $options = get_option( 'nb_settings' );
        $bbid = !empty( $options['nb_bbid'] ) ? esc_attr( $options['nb_bbid'] ) : '';
        
        if ( empty( $bbid ) ) {
            return '<p class="nb-error">' . esc_html__( 'Please set a Nightsbridge Booking ID in the plugin settings.', 'nightsbridge' ) . '</p>';
        }
        
        ob_start();
        ?>
        <div id="availability_search">
            <div id="nb_gridwidget"></div>
            <script type="text/javascript" src="https://www.nightsbridge.co.za/bridge/view?gridwidget=1&bbid=<?php echo esc_attr( $bbid ); ?>&height=720&width=1000"></script>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Apply custom styles to the frontend based on plugin settings.
     */
    public function nb_apply_custom_styles() {
        $options = get_option( 'nb_settings' );
        if ( $options === false ) {
            // Log the error and provide a user-friendly message
            error_log( 'Failed to retrieve plugin settings.' );
            echo '<p class="nb-error">' . esc_html__( 'Failed to retrieve plugin settings. Please check the plugin configuration.', 'nightsbridge' ) . '</p>';
            return;
        }
        
        $primary_color = !empty( $options['nb_primary_color'] ) ? sanitize_hex_color( $options['nb_primary_color'] ) : '#000000';
        $button_text_color = !empty( $options['nb_button_text_color'] ) ? sanitize_hex_color( $options['nb_button_text_color'] ) : '#ffffff';
        $button_hover_color = !empty( $options['nb_button_hover_color'] ) ? sanitize_hex_color( $options['nb_button_hover_color'] ) : '#b6cc6a';
        $button_border_radius = !empty( $options['nb_button_border_radius'] ) ? sanitize_text_field( $options['nb_button_border_radius'] ) : '';
        $button_text = !empty( $options['nb_button_text'] ) ? sanitize_text_field( $options['nb_button_text'] ) : 'Check Availability';
        ?>
            <style type="text/css">
                :root {
                    --nb-primary-color: <?php echo esc_attr( $primary_color ); ?>;
                    --nb-button-text-color: <?php echo esc_attr( $button_text_color ); ?>;
                    --nb-button-hover-color: <?php echo esc_attr( $button_hover_color ); ?>;
                    --nb-button-border-radius: <?php echo esc_attr( $button_border_radius ); ?>;
                }
                .nb_btn {
                    background-color: var(--nb-primary-color);
                    color: var(--nb-button-text-color) !important;
                    border-radius: var(--nb-button-border-radius);
                }
                .nb_btn:hover {
                    background-color: var(--nb-button-hover-color);
                    color: var(--nb-button-text-color) !important;
                }
            </style>
           <?php
    }

    /**
     * Shortcode to display the availability check widget.
     *
     * @return string HTML output for the shortcode.
     */
public function nb_availability_check_shortcode() {
    $options = get_option( 'nb_settings', [] );
    if ( empty( $options ) ) {
        //error_log( 'NightsBridge Widget: Settings are empty or not initialized in availability check.' );
        return '<p class="nb-error">' . esc_html__( 'Settings are not configured. Please check the NightsBridge settings.', 'nightsbridge' ) . '</p>';
    }

    $bbid = !empty( $options['nb_bbid'] ) ? esc_attr( $options['nb_bbid'] ) : '';
    $button_text = !empty( $options['nb_button_text'] ) ? sanitize_text_field( $options['nb_button_text'] ) : 'Check Availability';

    //error_log( "NightsBridge: nb_availability_check - BBID: " . ( $bbid ?: 'empty' ) );
    
    // Debug output
    //error_log( 'Button text in shortcode: ' . $button_text );

    ob_start();
    if ( empty( $bbid ) ) {
        //error_log( "NightsBridge: nb_availability_check - No BBID, showing error." );
        echo '<p class="nb-error">' . esc_html__( 'Please set a NightsBridge Booking ID in the plugin settings.', 'nightsbridge' ) . '</p>';
    } else {
        //error_log( "NightsBridge: nb_availability_check - Rendering HTML for Post ID: " . ( $post ? $post->ID : 'none' ) );
        ?>
        <div class="nightsbridge-widget">
            <br><br>
            <div class="nb_grid-container">
                <div class="nb_grid-header" data-title="Arrival">
                    <label class="nb_header" id="nb_checkInHeader"><?php echo esc_html__( 'Arrival', 'nightsbridge' ); ?></label>
                </div>
                <div class="nb_grid-header" data-title="Departure">
                    <label class="nb_header" id="nb_checkOutHeader"><?php echo esc_html__( 'Departure', 'nightsbridge' ); ?></label>
                </div>
                <div></div>
                <div data-title="Arrival">
                    <label id="check_in" class="arriv_depart"><?php echo esc_html__( 'Arrival', 'nightsbridge' ); ?></label>
                    <div class="nb_datePicker">
                        <input class="form-control" type="text" id="nb_CheckInDate" placeholder="DD-MM-YYYY">
                    </div>
                </div>
                <div data-title="Departure">
                    <label id="check_out" class="arriv_depart"><?php echo esc_html__( 'Departure', 'nightsbridge' ); ?></label>
                    <div class="nb_datePicker">
                        <input class="form-control" type="text" id="nb_CheckOutDate" placeholder="DD-MM-YYYY">
                    </div>
                </div>
                <div>
                    <button id="nb_checkAvailabilityBtn" class="nb_btn" type="button" value="<?php echo esc_attr( $button_text ); ?>">
                        <span class="nb_buttonText"><?php echo esc_html( $button_text ); ?></span>
                    </button>
                </div>
            </div>
            <br>
            <div id="availabilityModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn">X</span>
                    <iframe id="availabilityIframe" src="" style="width: 100%; height: 800px; border: none;"></iframe>
                </div>
            </div>
        </div>
        <?php
    }
    $output = ob_get_clean();
    //error_log( "NightsBridge: nb_availability_check - Output length: " . strlen( $output ) );
    return $output;
}

/*
 * barebones shortcode for debugging
 *
 * add_shortcode( 'nb_test_button', function() {
 $options = get_option( 'nb_settings', array() );
 $button_text = !empty( $options['nb_button_text'] ) ? sanitize_text_field( $options['nb_button_text'] ) : 'Check Availability';
 return '<button>' . esc_html( $button_text ) . '</button>';
 } );
 */


}