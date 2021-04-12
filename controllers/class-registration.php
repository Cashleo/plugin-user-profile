<?php

// This class handles the user registration features
class WUP_Registration{

public function __construct(){}

// Activate class: Setup shortcodes and add some actions and filters
public static function setup_hooks(){

    $this_class = new self();

    // Setup the shortcode that displays the registration form
    add_shortcode('wup_registration', [$this_class, 'shortcode_registration']);

    // Add actions to the init hook
    add_action('init', [$this_class, 'process_registration']);

}

// [wup_registration] shortcode callback
public function shortcode_registration(){

    ob_start();

    if(is_user_logged_in()){
        wup_load_view('registration-already-logged-in.php');
    }else{
        wup_load_view('form-registration.php', $args);
    }

    return ob_get_clean();
}

// Get registration URL
public function registration_url(){

    $registration_page_id = get_option('wup_page_id_for_registration');

    if(!$registration_page_id){
        $registration_page_id = site_url();
    }

    return get_permalink($registration_page_id);

}

// Get registration sinished URL
public function registration_finished_url(){

    $registration_finished_page_id = get_option('wup_page_id_for_registration_finished');

    if(!$registration_finished_page_id){
        $registration_finished_page_id = site_url();
    }

    return get_permalink($registration_finished_page_id);

}

// Process registration
public function process_registration(){

    // Return if the nonce is not set
    if(!isset($_POST['registration-nonce'])){
        return;
    }

    // Existe nonce, vamos a verificarlo
    if(!wp_verify_nonce($_POST['registration-nonce'], 'wup_registration_action')){
        wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('Please try again', 'wup'));
        return;
    }

    // Array to store the new user data
    $data_for_new_user = [];

    // Validate email
    if(!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)){
        wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('Please provide a valid email', 'wup'));
        return;
    }else{

        // Check if this email already exists
        $clean_email = sanitize_email(wp_unslash($_POST['user_email']));
        if(email_exists($clean_email)){
            wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('An account already exists with this email address', 'wup'));
            return;
        }else{
            $data_for_new_user['user_email'] = $clean_email;
        }

    }

    // Validate username (if not provided, use the email as username)
    if(empty($_POST['user_username'])){
        $data_for_new_user['user_login'] = $clean_email;
    }else{

        // Check if this username already exists
        $clean_username = sanitize_user(wp_unslash($_POST['user_username']));
        if(username_exists($clean_username)){
            wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('An account already exists with this username', 'wup'));
            return;
        }else{
            $data_for_new_user['user_login'] = $clean_username;
        }

    }

    // Check if a nickname has been provided
    if(!empty($_POST['user_nickname'])){
        $user_nickname = sanitize_text_field(wp_unslash($_POST['user_nickname']));
        $data_for_new_user['display_name'] = $user_nickname;
    }

    // Make sure password 1 is set
    if(empty($_POST['password1'])){
        wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('Please provide a password', 'wup'));
        return;
    }

    // Make sure password 2 is set
    if(empty($_POST['password2'])){
        wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('Please provide a password', 'wup'));
        return;
    }

    // Verify that the 2 passwords match
    if($_POST['password1'] != $_POST['password2']){
        wp_cache_set('wup_registration_notice', '<strong>'.__('Error', 'wup').':</strong> '.__('The provided passwords do not match', 'wup'));
        return;
    }else{
        $data_for_new_user['user_pass'] = $_POST['password1']; // Don't sanitize this, it will be hashed
    }

    // Assign role (allow filter)
    $data_for_new_user['role'] = apply_filters('wup_default_registration_role', 'subscriber');
    
    // User first name
    if(isset($_POST['user_first_name'])){
        $data_for_new_user['first_name'] = sanitize_text_field(wp_unslash($_POST['user_first_name']));
    }

    // User last name
    if(isset($_POST['user_last_name'])){
        $data_for_new_user['last_name'] = sanitize_text_field(wp_unslash($_POST['user_last_name']));
    }

    // Create the user
    $the_created_user_id = wp_insert_user($data_for_new_user);
    
    if(is_wp_error($the_created_user_id)){
        wp_cache_set('wup_registration_notice', $the_created_user_id->get_error_message());
        return;
    }else{

        // Allow action after user registration
        do_action('wup_action_after_user_registration', $the_created_user_id);

        // Save custom user metadata if provided
        foreach($_POST as $input_name => $input_value){
            // Check for POST input keys called "meta-whatever"
            if(substr($input_name,0,5)=='meta-'){
                $metadata_name = substr($input_name,5);
                $metadata_value = sanitize_text_field($input_value);
                add_user_meta($the_created_user_id, $metadata_name, $metadata_value);
            }
        }

        // See if the current setting is to disable the email activation mode
        $is_email_activation_mode_disabled = get_option('wup_disable_activation_email', false);
        
        // If activation mode is disabled, just mark the user as active
        if($is_email_activation_mode_disabled){
            add_user_meta($the_created_user_id, 'wup_user_activation_status', 'is_active');
        }else{
            // Set as pending activation
            add_user_meta($the_created_user_id, 'wup_user_activation_status', 'pending_activation');

            // Send user activation email
            $this->send_activation_email($the_created_user_id);
        }

        // Redirect to a specific page after registration
        if(isset($_POST['redirect_after_registration'])){

            // Si estamos redirigiendo es porque ya lo queremos también con sesión iniciada
            wp_set_current_user($the_created_user_id);
            wp_set_auth_cookie($the_created_user_id);

            wp_safe_redirect($_POST['redirect_after_registration']);
            exit();
        }
        
        // Redirect to registration finished page
        wp_redirect($this->registration_finished_url());
        exit;

    }
}


// Send activation email
public function send_activation_email($user_id){

    $user = get_userdata($user_id);

    if($user && !is_wp_error($user)){
        
        // Generate activation code
        $activation_code = sha1(time());

        // Save the activation code as a user meta
        update_user_meta($user_id, 'wup_user_activation_code', $activation_code, true);

        $nombre_del_sitio = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $activation_link = add_query_arg(
            [
                'c' => $activation_code,
                'u' => $user_id,
            ],
            get_permalink(get_option('wup_page_id_for_login'))
        );

        $activation_message = __('Open the following link to activate your account:', 'wup').'<br><br>';
        $activation_message .= '<a href="'.$activation_link.'">'.$activation_link.'</a>';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($user->user_email, __('Activate your account', 'wup'), $activation_message, $headers);

    }
}


// Return POST value for given array key
public function get_post_value($key){

    if(isset($_POST[$key])){
        return wp_unslash($_POST[$key]);
    }

    return '';
}

// Show errors on the form
public function maybe_display_notice(){

    $notice = wp_cache_get( 'wup_registration_notice' );

    if($notice){    
        echo '<div class="wup-message">'.$notice.'</div>';
    }

}

}