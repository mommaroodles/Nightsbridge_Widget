<?php

defined('ABSPATH') || exit;

class Nightsbridge_Admin
{
    /**
     * Constructor for the admin class. Calls init_hooks to set up actions and filters.
     *
     * @return void
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Set up hooks and filters for the admin interface.
     *
     * Actions:
     * - admin_enqueue_scripts: Enqueue scripts and styles for the admin interface.
     * - admin_menu: Add a menu item for the plugin settings page.
     * - admin_init: Initialize the plugin settings page.
     * - admin_notices: Handle any admin notices that may be displayed.
     *
     * @return void
     */
    private function init_hooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'nb_enqueue_admin_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'nb_enqueue_color_picker')); // Add this
        add_action('admin_menu', array($this, 'nb_add_admin_menu'));
        add_action('admin_init', array($this, 'nb_settings_init'));
        add_action('admin_notices', array($this, 'nb_admin_notices'));
    }


    /**
     * Enqueue scripts and styles for the admin interface, only if the hook suffix
     * matches the plugin settings page.
     *
     * @param string $hook_suffix The suffix of the current hook.
     *
     * @return void
     */
    public function nb_enqueue_admin_scripts($hook_suffix)
    {
        //error_log('Hook suffix: ' . $hook_suffix);
        if ($hook_suffix === 'settings_page_nb_settings') {
            //error_log('Enqueuing for settings_page_nb_settings');

            //$base_url = NIGHTSBRIDGE_PLUGIN_URL;
            //$color_picker_url = $base_url . 'assets/js/nb-color-picker.js';
            //$copy_url = $base_url . 'assets/js/nb-copy-to-clipboard.js';
            //$style_url = $base_url . 'assets/css/admin-style.css';

            //error_log('Color picker URL: ' . $color_picker_url);
            //error_log('Copy URL: ' . $copy_url);
            //error_log('Style URL: ' . $style_url);
            
            //wp_script_is() prevents multiple enqueues within this method. If duplicates persist, they’re from elsewhere

            if (!wp_script_is('nb-copy-to-clipboard', 'enqueued')) {
                wp_enqueue_script('nb-copy-to-clipboard', $copy_url, array('jquery'), NIGHTSBRIDGE_VERSION, true);
            }
            if (!wp_script_is('nb-color-picker', 'enqueued')) {
                wp_enqueue_script('nb-color-picker', $color_picker_url, array('wp-color-picker'), NIGHTSBRIDGE_VERSION, true);
            }
            if (!wp_style_is('wp-color-picker', 'enqueued')) {
                wp_enqueue_style('wp-color-picker');
            }
            if (!wp_style_is('nb-admin-style', 'enqueued')) {
                wp_enqueue_style('nb-admin-style', $style_url, array(), NIGHTSBRIDGE_VERSION);
            }

            //error_log('Scripts and styles enqueued successfully');
        } //else {
        //error_log('Not enqueuing - wrong page: ' . $hook_suffix);
        //}
    }

    /**
     * Add a menu item for the plugin settings page, under the "Settings" menu.
     *
     * @return void
     */
    public function nb_add_admin_menu()
    {
        if (current_user_can('manage_options')) {
            add_options_page(
                __('NightsBridge Booking Widget Settings', 'nightsbridge'),
                __('NightsBridge', 'nightsbridge'),
                'manage_options',
                'nb_settings',
                array($this, 'options_page')
            );
        }
    }

    /**
     * Initialize the plugin settings by registering a setting and adding settings
     * fields and sections to the plugin settings page.
     *
     * @return void
     */

    public function nb_settings_init()
    {
        register_setting('nb_settings_group', 'nb_settings', array(
            'sanitize_callback' => array($this, 'nb_sanitize_settings')
        ));

        add_settings_section(
            'nb_settings_section',
            __('NightsBridge Widget Settings', 'nightsbridge'),
            array($this, 'nb_settings_section_callback'),
            'nb_settings_group'
        );

        add_settings_field(
            'nb_bbid',
            __('NightsBridge ID (BBID)', 'nightsbridge'),
            array($this, 'nb_bbid_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Connect your NightsBridge account (required)', 'nightsbridge'))
        );

        add_settings_field(
            'nb_custom_format',
            __('Date Format', 'nightsbridge'),
            array($this, 'nb_custom_format_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Custom date format (e.g., d-M-Y)', 'nightsbridge'))
        );

        add_settings_field(
            'nb_language',
            __('Language', 'nightsbridge'),
            array($this, 'nb_language_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Select widget language', 'nightsbridge'))
        );

        add_settings_field(
            'nb_page_slug',
            __('Widget Page', 'nightsbridge'),
            array($this, 'nb_page_slug_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Select the page where the widget will appear', 'nightsbridge'))
        );

        add_settings_field(
            'nb_primary_color',
            __('Primary Color', 'nightsbridge'),
            array($this, 'nb_primary_color_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Select the primary color for the widget', 'nightsbridge'))
        );

        add_settings_field(
            'nb_button_text_color',
            __('Button Text Color', 'nightsbridge'),
            array($this, 'nb_button_text_color_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Select the text color for the button', 'nightsbridge'))
        );

        add_settings_field(
            'nb_button_hover_color',
            __('Button Hover Color', 'nightsbridge'),
            array($this, 'nb_button_hover_color_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Select the hover background color for the button', 'nightsbridge'))
        );

        add_settings_field(
            'nb_button_border_radius',
            __('Button Border Radius', 'nightsbridge'),
            array($this, 'nb_button_border_radius_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Specify the border radius for the button in px or % (e.g., 4px, 2%)', 'nightsbridge'))
        );

        add_settings_field(
            'nb_button_text',
            __('Button Text', 'nightsbridge'),
            array($this, 'nb_button_text_render'),
            'nb_settings_group',
            'nb_settings_section',
            array('description' => __('Specify the text for the button', 'nightsbridge'))
        );
    }

    /**
     * Display warning notices in the WordPress admin area if the Booking ID (BBID)
     * or page slug for the widget is missing or null.
     *
     * This function hooks into the `admin_notices` action hook and only displays
     * the notices if the current admin screen is the settings page for the plugin.
     *
     * @return void
     */
    public function nb_admin_notices()
    {
        $options = get_option('nb_settings', array()); // Default to empty array if option doesn't exist
        $screen = get_current_screen();

        // Only show notices on the plugin's settings page
        if (! $screen || $screen->id !== 'settings_page_nb_settings') {
            return;
        }

        // Define notices with conditions
        $notices = array(
            array(
                'condition' => isset($options['nb_bbid']) && empty($options['nb_bbid']),
                'message'   => __('NightsBridge Widget: Please enter your NightsBridge Booking ID (BBID).', 'nightsbridge'),
                'type'      => 'warning',
            ),
            array(
                'condition' => isset($options['nb_page_slug']) && empty($options['nb_page_slug']),
                'message'   => __('NightsBridge Widget: Please select a page for the widget.', 'nightsbridge'),
                'type'      => 'warning',
            ),
            array(
                'condition' => isset($options['nb_page_slug']) && $options['nb_page_slug'] === null,
                'message'   => __('NightsBridge Widget: The page slug for the widget is null.', 'nightsbridge'),
                'type'      => 'error',
            ),
        );

        // Output notices
        foreach ($notices as $notice) {
            if ($notice['condition']) {
                printf(
                    '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                    esc_attr($notice['type']),
                    esc_html($notice['message'])
                );
            }
        }
    }

    /**
     * Displays the options page for the NightsBridge Widget plugin.
     *
     * This function hooks into the `admin_menu` action hook and adds a new
     * settings page for the plugin under the "Settings" menu. The options page
     * displays a form with settings fields for the Booking ID (BBID), page slug
     * for the widget, and button text color. The form is submitted to the
     * WordPress options API when the user clicks the "Save Changes" button.
     *
     * @return void
     */
    public function options_page()
    {
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('nb_settings_group');
                do_settings_sections('nb_settings_group');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    // Settings field render methods
    public function nb_bbid_render($args)
    {
        $options = get_option('nb_settings');
        $value = isset($options['nb_bbid']) ? esc_attr($options['nb_bbid']) : '';
    ?>
        <input type="text" name="nb_settings[nb_bbid]" value="<?php echo $value; ?>">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Render the input field for customizing the date format.
     *
     * This method outputs a text input field that allows users to specify a custom
     * date format. It retrieves the current value from the plugin settings and
     * uses 'd-M-Y' as the default format if none is set.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    public function nb_custom_format_render($args)
    {
        $options = get_option('nb_settings');
        $value = isset($options['nb_custom_format']) ? esc_attr($options['nb_custom_format']) : 'd-M-Y';
    ?>
        <input type="text" name="nb_settings[nb_custom_format]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Render the dropdown for selecting the language.
     *
     * This method outputs a select field with language options for the plugin.
     * It retrieves the current language setting from the plugin options and
     * defaults to 'en-GB' if none is set. The available options include
     * 'English (UK)', 'English (US)', and 'Afrikaans'.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    public function nb_language_render($args)
    {
        $options = get_option('nb_settings');
        $langs = array(
            'en-GB' => 'English (UK)',
            'en-US' => 'English (US)',
            'af-ZA' => 'Afrikaans'
        );
        $selected = isset($options['nb_language']) ? $options['nb_language'] : 'en-GB';
    ?>
        <select name="nb_settings[nb_language]">
            <?php foreach ($langs as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($selected, $code); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Render the dropdown for selecting the page slug.
     *
     * This method outputs a select field with page options for the plugin.
     * It retrieves the current page slug setting from the plugin options and
     * defaults to an empty string if none is set. The available options include
     * all published pages on the site.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    public function nb_page_slug_render($args)
    {
        $options = get_option('nb_settings');
        $selected = isset($options['nb_page_slug']) ? sanitize_title($options['nb_page_slug']) : '';
        $pages = get_pages();
    ?>
        <select name="nb_settings[nb_page_slug]">
            <option value=""><?php echo esc_html__('Select a page', 'nightsbridge'); ?></option>
            <?php foreach ($pages as $page) : ?>
                <option value="<?php echo esc_attr($page->post_name); ?>" <?php selected($selected, $page->post_name); ?>>
                    <?php echo esc_html($page->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <p class="description"><?php echo esc_html__('The NightsBridge Plugin uses two shortcodes to render the widget on the front-end:', 'nightsbridge'); ?></p>
        <h3><?php echo esc_html__('Shortcodes', 'nightsbridge'); ?></h3>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span><?php echo esc_html__('[nb_availability_check] - The shortcode for the Check Availability Widget', 'nightsbridge'); ?></span>
            <button type="button" class="button nb-copy-button" data-shortcode="[nb_availability_check]"><?php echo esc_html__('Copy Shortcode', 'nightsbridge'); ?></button>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span><?php echo esc_html__('[nb_availability_search] - The shortcode and shows accommodation, dates available in calendar form', 'nightsbridge'); ?></span>
            <button type="button" class="button nb-copy-button" data-shortcode="[nb_availability_search]"><?php echo esc_html__('Copy Shortcode', 'nightsbridge'); ?></button>
        </div>
        <h3><?php echo esc_html__('Information', 'nightsbridge'); ?></h3>
        <p><?php echo esc_html__('To add the Widgets copy and paste the shortcodes into the selected page. This is in order to load the Javascript and CSS for the widget only on the page selected as opposed to all WordPress pages. This improves page load speeds and avoids any conflict with other plugins.', 'nightsbridge'); ?></p>
    <?php
    }

    /**
     * Renders the primary color input field.
     *
     * @param array $args The arguments passed to the field.
     *
     * @return void
     */
    public function nb_primary_color_render($args)
    {
        $options = get_option('nb_settings');
        $value = isset($options['nb_primary_color']) ? sanitize_hex_color($options['nb_primary_color']) : '#000000';
    ?>
        <input type="text" name="nb_settings[nb_primary_color]" value="<?php echo esc_attr($value); ?>" class="nbw-color-picker">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Render the input field for customizing the button text color.
     *
     * This method outputs a text input field that allows users to specify a custom
     * color for the button text. It retrieves the current value from the plugin 
     * settings and uses '#ffffff' as the default color if none is set.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    public function nb_button_text_color_render($args)
    {
        $options = get_option('nb_settings');
        $value = isset($options['nb_button_text_color']) ? sanitize_hex_color($options['nb_button_text_color']) : '#ffffff';
    ?>
        <input type="text" name="nb_settings[nb_button_text_color]" value="<?php echo esc_attr($value); ?>" class="nbw-color-picker">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Renders the button hover color input field.
     *
     * This function outputs a text input field that allows users to specify a custom
     * hover color for the button. It retrieves the current hover color from the plugin
     * settings and uses '#b6cc6a' as the default color if none is set.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    public function nb_button_hover_color_render($args)
    {
        $options = get_option('nb_settings');
        $value = isset($options['nb_button_hover_color']) ? sanitize_hex_color($options['nb_button_hover_color']) : '#b6cc6a';
    ?>
        <input type="text" name="nb_settings[nb_button_hover_color]" value="<?php echo esc_attr($value); ?>" class="nbw-color-picker">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Renders the button border radius input field.
     *
     * This function outputs a text input field that allows users to specify a custom
     * border radius for the button. It retrieves the current border radius from the
     * plugin settings and uses '3px' as the default radius if none is set.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    public function nb_button_border_radius_render($args)
    {
        $options = get_option('nb_settings');
        $value = isset($options['nb_button_border_radius']) ? esc_attr($options['nb_button_border_radius']) : '3px';
    ?>
        <input type="text" name="nb_settings[nb_button_border_radius]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
    }

    /**
     * Renders the input field for customizing the button text.
     *
     * This method outputs a text input field that allows users to specify custom
     * text for the button. It retrieves the current value from the plugin settings
     * and uses 'Check Availability' as the default text if none is set.
     *
     * @param array $args {
     *     An array of arguments passed to the settings field's callback function.
     *
     *     @type string $description The description of the field.
     * }
     *
     * @return void
     */
    function nb_button_text_render($args)
    {
        $options = get_option('nb_settings', array());
        $value = !empty($options['nb_button_text']) ? esc_attr($options['nb_button_text']) : 'Check Availability';
    ?>
        <input type="text" name="nb_settings[nb_button_text]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
<?php
    }

    /**
     * Enqueues the color picker script and style.
     *
     * @return void
     */
    public function nb_enqueue_color_picker()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('nb-color-picker', plugin_dir_url(__FILE__) . 'assets/js/nb-color-picker.js', array('wp-color-picker'), false, true);
    }

    /**
     * Outputs the content for the settings section.
     *
     * This method is a callback for the 'settings_section' argument in the
     * {@see add_settings_section()} function. It outputs a paragraph describing
     * the purpose of the settings section.
     *
     * @return void
     */
    public function nb_settings_section_callback()
    {
        echo '<p>' . esc_html__('Configure your NightsBridge widget settings below.', 'nightsbridge') . '</p>';
    }

    /**
     * Sanitizes the settings input array.
     *
     * This method is called by WordPress when saving the plugin's settings.
     * It takes the input array from the settings page and sanitizes each
     * value before returning the sanitized array.
     *
     * @param array $input The input array to sanitize.
     *
     * @return array The sanitized array.
     */
    public function nb_sanitize_settings($input)
    {
        // Define sanitization rules and defaults
        $rules = array(
            'nb_bbid'                 => array('sanitize' => 'sanitize_text_field', 'default' => ''),
            'nb_custom_format'        => array('sanitize' => 'sanitize_text_field', 'default' => 'd-M-Y'),
            'nb_language'             => array('sanitize' => 'sanitize_text_field', 'default' => 'en-GB'),
            'nb_page_slug'            => array('sanitize' => 'sanitize_title', 'default' => ''),
            'nb_primary_color'        => array('sanitize' => 'sanitize_hex_color', 'default' => '#000000'),
            'nb_button_text_color'    => array('sanitize' => 'sanitize_hex_color', 'default' => '#ffffff'),
            'nb_button_hover_color'   => array('sanitize' => 'sanitize_hex_color', 'default' => '#b6cc6a'),
            'nb_button_border_radius' => array('sanitize' => 'sanitize_text_field', 'default' => '4px'),
            'nb_button_text'          => array('sanitize' => 'sanitize_text_field', 'default' => 'Check Availability'),
        );

        $new_input = array();

        foreach ($rules as $key => $rule) {
            $new_input[$key] = isset($input[$key]) ? call_user_func($rule['sanitize'], $input[$key]) : $rule['default'];
        }

        return $new_input;
    }
}