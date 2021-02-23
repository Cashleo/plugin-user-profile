<?php
/**
 * Plugin Name: Winni User Profile
 * Description: WP user profile functionality
 * Version:     1
 * Author:      WinniPress
 * Text Domain: wup
*/

// Some helper functions
require_once plugin_dir_path(__FILE__).'controllers/functions.php';

// Load Login class and fire hooks
require_once plugin_dir_path(__FILE__).'controllers/class-login.php';
WUP_Login::activar_clase();

// Load Registration class and fire hooks
require_once plugin_dir_path(__FILE__).'controllers/class-registration.php';
WUP_Registration::activar_clase();

// Load Profile class and fire hooks
require_once plugin_dir_path(__FILE__).'controllers/class-profile.php';
WUP_Profile::activar_clase();

// Load customizations for the admin area
if(is_admin()){
    require_once plugin_dir_path(__FILE__).'admin/settings-page.php';
    require_once plugin_dir_path(__FILE__).'admin/users-area-customizations.php';
}

// Hide admin bar
add_filter('show_admin_bar', '__return_false');