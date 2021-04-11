<?php
/**
 * Plugin Name: Winni User Profile
 * Description: Clean WP user profile functionality with no JS/CSS bloat.
 * Version:     1
 * Author:      Álvaro Franz
 * Text Domain: wup
 * Domain Path: /translations/
*/

// Plugin helper functions
require_once plugin_dir_path(__FILE__).'controllers/functions.php';

// Load Login class and setup hooks
require_once plugin_dir_path(__FILE__).'controllers/class-login.php';
WUP_Login::setup_hooks();

// Load Registration class and setup hooks
require_once plugin_dir_path(__FILE__).'controllers/class-registration.php';
WUP_Registration::setup_hooks();

// Load Profile class and setup hooks
require_once plugin_dir_path(__FILE__).'controllers/class-profile.php';
WUP_Profile::setup_hooks();

// Load customizations for the admin area
if(is_admin()){
    require_once plugin_dir_path(__FILE__).'admin/settings-page.php';
    require_once plugin_dir_path(__FILE__).'admin/users-area-customizations.php';
}

// Load text domain
add_action('plugins_loaded', 'wup_load_text_domain');
function wup_load_text_domain(){
    load_plugin_textdomain('wup', false /* Deprecated argument */ , dirname(plugin_basename(__FILE__)) . '/translations/');
}

// Disable admin bar
add_filter('show_admin_bar', '__return_false');