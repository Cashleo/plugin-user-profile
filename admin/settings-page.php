<?php

// ACTIONS AND FILTERS

// Create the settings page
add_action('admin_menu', 'wup_settings_page');

// Create sections and fields
add_action('admin_init', 'wup_settings_page_sections_and_fields');


// ACTIONS AND FILTERS CALLBACKS

function wup_settings_page(){
    add_options_page(
    __('User Profile Settings', 'wup'),     // Page title
    __('User Profile', 'wup'),              // Menu label
    'manage_options',                       // Required cap to see this page
    'wup_settings_page',                    // ID for this page
    'wup_render_settings_page_content',     // Callback that renders the page content
    3                                       // Menu position
    );
}

function wup_settings_page_sections_and_fields(){

    // The section for pages
    add_settings_section(
    'wup_pages_settings_section',           // Section ID
    __('Pages', 'wup'),                     // Section title
    'wup_pages_settings_section_heading',   // Callback to display section heading
    'wup_settings_page'                     // The settings page this section belongs to
    );

    // The section for options
    add_settings_section(
    'wup_options_settings_section',
    __('Options', 'wup'),
    'wup_options_settings_section_heading',
    'wup_settings_page'
    );

    // For the "pages" section

    // Add Setting: Login page ID
    register_setting(
    'wup_settings',                         // Options group name
    'wup_page_id_for_login'                 // Option name
    );

    add_settings_field(
    'wup_page_id_for_login',                // Field ID
    __('Login page ID', 'wup'),             // Field title
    'display_login_page_id_field',          // Callback
    'wup_settings_page',                    // The settings page this field belongs to
    'wup_pages_settings_section'            // The settings section this section belongs to
    );

    // Add Setting: Registration page ID
    register_setting(
    'wup_settings',
    'wup_page_id_for_registration'
    );

    add_settings_field(
    'wup_page_id_for_registration',
    __('Registration page ID', 'wup'),
    'display_registration_page_id_field',
    'wup_settings_page',
    'wup_pages_settings_section'
    );

    // Add Setting: Registraion finished page ID
    register_setting(
    'wup_settings',
    'wup_page_id_for_registration_finished'
    );

    add_settings_field(
    'wup_page_id_for_registration_finished',
    __('Registration finished page ID', 'wup'),
    'display_registration_finished_page_id_field',
    'wup_settings_page',
    'wup_pages_settings_section'
    );

    // Add Setting: Show my profile page ID
    register_setting(
    'wup_settings',
    'wup_page_id_for_show_my_profile'
    );

    add_settings_field(
    'wup_page_id_for_show_my_profile',
    __('Show my profile page ID', 'wup'),
    'display_show_my_profile_page_id_field',
    'wup_settings_page',
    'wup_pages_settings_section'
    );

    // Add Setting: Edit my profile page ID
    register_setting(
    'wup_settings',
    'wup_page_id_for_edit_my_profile'
    );

    add_settings_field(
    'wup_page_id_for_edit_my_profile',
    __('Edit my profile page ID', 'wup'),
    'display_edit_my_profile_page_id_field',
    'wup_settings_page',
    'wup_pages_settings_section'
    );

    // For the "options" section

    // Add Setting: Disable activation email
    register_setting(
    'wup_settings',
    'wup_disable_activation_email'
    );

    add_settings_field(
    'wup_disable_activation_email',
    __('Disable activation email (auto activate account)', 'wup'),
    'display_disable_activation_email_field',
    'wup_settings_page',
    'wup_options_settings_section'
    );

}

// This function renders the heading for the Pages section
function wup_pages_settings_section_heading(){
    echo '<p>'.__('In this section you may set the pages ids for each view', 'wup').'</p>';
}

// Render login page ID form input
function display_login_page_id_field(){
    echo '<input
    type="number"
    name="wup_page_id_for_login"
    value="'.esc_attr(get_option('wup_page_id_for_login')).'" />';
}

// Render registration page ID form input
function display_registration_page_id_field(){
    echo '<input
    type="number"
    name="wup_page_id_for_registration"
    value="'.esc_attr(get_option('wup_page_id_for_registration')).'" />';
}

// Render registration finished page ID form input
function display_registration_finished_page_id_field(){
    echo '<input
    type="number"
    name="wup_page_id_for_registration_finished"
    value="'.esc_attr(get_option('wup_page_id_for_registration_finished')).'" />';
}

// Render show my profile page ID form input
function display_show_my_profile_page_id_field(){
    echo '<input
    type="number"
    name="wup_page_id_for_show_my_profile"
    value="'.esc_attr(get_option('wup_page_id_for_show_my_profile')).'" />';
}

// Render edit my profile page ID form input
function display_edit_my_profile_page_id_field(){
    echo '<input
    type="number"
    name="wup_page_id_for_edit_my_profile"
    value="'.esc_attr(get_option('wup_page_id_for_edit_my_profile')).'" />';
}

// Render the checkbox to disable the activation email
function display_disable_activation_email_field(){
    $checked = (get_option('wup_disable_activation_email'))?' checked="checked"':'';
    echo '<input '.$checked.'
    type="checkbox"
    name="wup_disable_activation_email"
    value="1" />';
}

// Function that displays the settings page in the admin area
function wup_render_settings_page_content(){
    echo '
    <h1>'.__('User Profile Settings').'</h1>
    <p>'.__('Settings for the Winni User Profile plugin', 'wup').'</p>
    ';

    // Start the form
    echo '<form action="options.php" method="post">';

    // Generate fields
    settings_fields('wup_settings');               // Options group
    do_settings_sections('wup_settings_page');     // Settings page ID

    // Display save button and close form
    echo '
    <input
      type="submit"
      name="submit"
      class="button button-primary"
      value="Save"
    />
    </form>';
}