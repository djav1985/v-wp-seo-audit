<?php
/*
Plugin Name: v-wp-seo-audit
Description: Main plugin file for v-wp-seo-audit - WordPress SEO Audit plugin.
Version: 1.0.0
Author: djav1985
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin initialization
function wskellitoon_init() {
    // Add your plugin setup code here
}
add_action('init', 'wskellitoon_init');

// Example: Add admin menu
function wskellitoon_admin_menu() {
    add_menu_page(
        'WSkellitoon SEO Audit',
        'WSkellitoon',
        'manage_options',
        'wskellitoon',
        'wskellitoon_admin_page',
        'dashicons-search',
        80
    );
}
add_action('admin_menu', 'wskellitoon_admin_menu');

function wskellitoon_admin_page() {
    echo '<div class="wrap"><h1>WSkellitoon SEO Audit</h1><p>Welcome to the WSkellitoon plugin!</p></div>';
}
