<?php
/*
Plugin Name: Cloud Nine Web Accessibility
Version: 1.0.0
Author: Cloud Nine Web (Shusanto)
Author URI: https://cloudnineweb.co/
Description: This plugin adds accessibility widget for people with disabiliuties
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


// Function to enqueue the scripts
function cnw_accessibility_enqueue_scripts() {

    // Enqueue the 'sienna' script
    wp_enqueue_script(
        'sienna', // Handle for the script
        plugin_dir_url(__FILE__) . 'js/sienna.min.js', // Path to the script file
        array(), // No dependencies
        '1.0', // Version number
        true // Load in footer
    );
}

// Hook the function to wp_enqueue_scripts action hook
add_action('wp_enqueue_scripts', 'cnw_accessibility_enqueue_scripts');


//Add html attribute to menu
function start_output_buffer() {
    ob_start('replace_brx_submenu_toggle');
}
add_action('wp_head', 'start_output_buffer');

function replace_brx_submenu_toggle($content) {
    return str_replace('<div class="brx-submenu-toggle">', '<div class="brx-submenu-toggle" aria-haspopup="true" aria-expanded="false">', $content);
}


function end_output_buffer() {
    ob_end_flush();
}
add_action('wp_footer', 'end_output_buffer');

//Menu dropdown hover
function add_hover_script() {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var elements = document.querySelectorAll('.brx-submenu-toggle');
            
            elements.forEach(function(element) {
                element.setAttribute('aria-expanded', 'false');
                
                element.addEventListener('mouseover', function() {
                    element.setAttribute('aria-expanded', 'true');
                });
                
                element.addEventListener('mouseout', function() {
                    element.setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
    <?php
}
add_action('wp_head', 'add_hover_script');