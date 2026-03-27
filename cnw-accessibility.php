<?php
/*
Plugin Name: Cloud Nine Web Accessibility
Version: 1.0.0
Author: Cloud Nine Web (Shusanto)
Author URI: https://cloudnineweb.co/
Description: This plugin adds accessibility widget for people with disabilities
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


// Function to enqueue the scripts
function cnw_accessibility_enqueue_scripts() {

	wp_enqueue_script('jquery');

    // Enqueue the 'sienna' script
    wp_enqueue_script(
        'sienna', // Handle for the script
        plugin_dir_url(__FILE__) . 'js/sienna.min.js', // Path to the script file
        array(), // No dependencies
        '1.1', // Version number
        true // Load in footer
    );
}

// Hook the function to wp_enqueue_scripts action hook
add_action('wp_enqueue_scripts', 'cnw_accessibility_enqueue_scripts');


// ==========================================
// Admin Settings Page - Widget Color Settings
// ==========================================

function cnw_accessibility_admin_menu() {
    add_submenu_page(
        'tools.php',
        'Cnw Accessibility',
        'Cnw Accessibility',
        'manage_options',
        'cnw-accessibility-colors',
        'cnw_accessibility_colors_page'
    );
}
add_action('admin_menu', 'cnw_accessibility_admin_menu');

function cnw_accessibility_register_settings() {
    register_setting('cnw_accessibility_colors', 'cnw_accessibility_colors', 'cnw_accessibility_sanitize_colors');
}
add_action('admin_init', 'cnw_accessibility_register_settings');

function cnw_accessibility_sanitize_colors($input) {
    $sanitized = array();
    if (isset($input['theme_color']) && $input['theme_color'] !== '') {
        $sanitized['theme_color'] = sanitize_hex_color($input['theme_color']);
    }
    return $sanitized;
}

function cnw_accessibility_colors_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $colors = get_option('cnw_accessibility_colors', array());
    $theme_color = isset($colors['theme_color']) ? $colors['theme_color'] : '';
    ?>
    <div class="wrap">
        <h1>Cnw Accessibility</h1>
        <p>Set a theme color for the accessibility widget. This replaces all blue accent colors in the widget. Leave empty to use the default blue.</p>
        <form method="post" action="options.php">
            <?php settings_fields('cnw_accessibility_colors'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="cnw_color_theme">Theme Color</label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="cnw_color_theme"
                            name="cnw_accessibility_colors[theme_color]"
                            value="<?php echo esc_attr($theme_color); ?>"
                            class="cnw-color-picker"
                            data-default-color="#0848ca"
                        />
                        <p class="description">Applies to the floating button, menu header, selected/hover states, and checkmarks. (Default: #0848ca)</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Color'); ?>
        </form>
    </div>
    <?php
}

function cnw_accessibility_admin_scripts($hook) {
    if ($hook !== 'tools_page_cnw-accessibility-colors') {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script(
        'cnw-admin-color-picker',
        plugin_dir_url(__FILE__) . 'js/cnw-admin.js',
        array('wp-color-picker'),
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'cnw_accessibility_admin_scripts');


// ==========================================
// Frontend - Inject CSS color overrides
// ==========================================

function cnw_accessibility_custom_colors_css() {
    $colors = get_option('cnw_accessibility_colors', array());
    $theme  = isset($colors['theme_color']) ? $colors['theme_color'] : '';

    if (!$theme) {
        return;
    }

    $css  = '';
    // Floating button
    $css .= "body .asw-widget .asw-menu-btn{background:{$theme}!important;background:linear-gradient(96deg,{$theme} 0,{$theme} 100%)!important;outline-color:{$theme}!important;}";
    // Menu header
    $css .= "body .asw-menu .asw-menu-header{background-color:{$theme}!important;}";
    $css .= "body .asw-menu .asw-menu-header svg{fill:{$theme}!important;}";
    // Selected/hover states and checkmarks
    $css .= "body .asw-menu .asw-btn.asw-selected,body .asw-menu .asw-btn:hover{border-color:{$theme}!important;}";
    $css .= "body .asw-menu .asw-btn.asw-selected span,body .asw-menu .asw-btn.asw-selected svg{fill:{$theme}!important;color:{$theme}!important;}";
    $css .= "body .asw-menu .asw-btn.asw-selected:after{background-color:{$theme}!important;}";
    $css .= "body .asw-menu .asw-minus:hover,body .asw-menu .asw-plus:hover{border-color:{$theme}!important;}";
    // Footer hover
    $css .= "body .asw-menu .asw-footer a:hover,body .asw-menu .asw-footer a:hover span{color:{$theme}!important;}";

    echo '<style id="cnw-accessibility-custom-colors">' . $css . '</style>';
}
add_action('wp_head', 'cnw_accessibility_custom_colors_css');


//Add html attribute to menu
function start_output_buffer() {
    ob_start('replace_brx_submenu_toggle');
}
add_action('wp_head', 'start_output_buffer');


function replace_brx_submenu_toggle($content) {
    return str_replace('<div class="brx-submenu-toggle">', '<div class="brx-submenu-toggle" aria-haspopup="true">', $content);
}


function end_output_buffer() {
    ob_end_flush();
}
add_action('wp_footer', 'end_output_buffer');


//Menu dropdown hover
function add_hover_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
			$('.brx-submenu-toggle').each(function(){
				$labelText = $(this).find('a').text() + 'Submenu toggle';
				$(this).find('button').attr('aria-label', $labelText);
			});
		});
    </script>
    <?php
}
add_action('wp_head', 'add_hover_script');