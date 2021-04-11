<?php

// This class handles the User Show Profile and Edit profile functionality
class WUP_Profile{

// Message array
private $messages = [];

// Constructor
public function __construct(){}

// Activate class: Setup shortcodes and add some actions and filters
public static function setup_hooks(){

    $this_class = new self();

    add_shortcode('wup_show_my_profile', [$this_class, 'shortcode_show_my_profile']);
    add_shortcode('wup_edit_my_profile', [$this_class, 'shortcode_edit_my_profile']);

    add_action('wup_hook_before_displaying_sections', [$this_class, 'save_editable_fields'], 5, 2);
    add_action('wup_hook_before_displaying_sections', [$this_class, 'save_password'], 10, 2);

    add_filter('wup_editable_sections', [$this_class, 'add_profile_section'], 10);
    add_filter('wup_editable_sections', [$this_class, 'add_avatar_section'], 10);
    add_filter('wup_editable_sections', [$this_class, 'add_password_section'], 20);

    add_filter('wup_editable_fields_profile',  [$this_class, 'default_editable_fields_profile'], 10);
    add_filter('wup_editable_fields_avatar',   [$this_class, 'default_editable_fields_avatar'], 10);
    add_filter('wup_editable_fields_password', [$this_class, 'default_editable_fields_password'], 10);

}

// Add the profile section
public function add_profile_section($sections){

    $sections[] = [
        'id'            => 'profile',
        'label'         => __('Profile', 'wup'),
        'section_class' => 'profile-section',
        'content_class' => 'profile-content'
    ];

    return $sections;
}

// Add the avatar section
public function add_avatar_section($sections){

    $sections[] = [
        'id'            => 'avatar',
        'label'         => __('Avatar', 'wup'),
        'section_class' => 'avatar-section',
        'content_class' => 'avatar-content'
    ];

    return $sections;
}


// Adds the password section
public function add_password_section($sections){

    $sections[] = [
        'id'            => 'password',
        'label'         => __('Password', 'wup'),
        'section_class' => 'password-section',
        'content_class' => 'password-content'
    ];

    return $sections;
}

// Default perfil section fields
function default_editable_fields_profile($fields){
    $fields[] = [
        'id'      => 'user_email',
        'label'   => __('Email Address', 'wup'),
        'desc'    => __('Edit your email address', 'wup'),
        'type'    => 'email',
        'classes' => 'user_email',
    ];

    $fields[] = [
        'id'      => 'first_name',
        'label'   => __('First Name', 'wup'),
        'desc'    => __('Edit your first name', 'wup'),
        'type'    => 'text',
        'classes' => 'first_name',
    ];

    $fields[] = [
        'id'      => 'last_name',
        'label'   => __('Last Name', 'wup'),
        'desc'    => __('Edit your last name', 'wup'),
        'type'    => 'text',
        'classes' => 'last_name',
    ];

    $fields[] = [
        'id'      => 'user_url',
        'label'   => __('URL', 'wup'),
        'desc'    => __('Edit your profile associated URL', 'wup'),
        'type'    => 'text',
        'classes' => 'user_url',
    ];

    return $fields;
}

// Default avatar section fields
function default_editable_fields_avatar($fields){
    $fields[] = [
        'id'      => 'avatar',
        'label'   => __('Avatar', 'wup'),
        'desc'    => __('User profile image', 'wup'),
        'type'    => 'image',
        'classes' => 'avatar',
    ];

    return $fields;
}

// Default password section fields
function default_editable_fields_password($fields){
    $fields[] = [
        'id'      => 'user_pass',
        'label'   => __('Password', 'wup'),
        'desc'    => __('New password', 'wup'),
        'type'    => 'password',
        'classes' => 'user_pass',
    ];

    return $fields;
}


// [wup_show_my_profile] shortcode callback
public function shortcode_show_my_profile(){

    // If the user is not logged in, show login form
    if(is_user_logged_in()){
        $view = 'my-profile.php';
    }else{
        $view = 'form-login.php';
    }

    ob_start();

    wup_load_view($view);

    return ob_get_clean();
}

// [wup_edit_my_profile] shortcode callback
public function shortcode_edit_my_profile(){

    // If the user is not logged in, show login form
    if(is_user_logged_in()){
        $view = 'form-edit-my-profile.php';
    }else{
        $view = 'form-login.php';
    }

    ob_start();

    wup_load_view($view);

    return ob_get_clean();
}


// Get my profile URL
public function my_profile_url(){
    $my_profile_page_id = get_option('wup_page_id_for_show_my_profile');
    if(!$my_profile_page_id){
        return false;
    }
    return get_permalink($my_profile_page_id);
}

// Get the edit my profile URL
public function edit_my_profile_url(){
    $edit_my_profile_page_id = get_option('wup_page_id_for_edit_my_profile');
    if(!$edit_my_profile_page_id){
        return false;
    }
    return get_permalink($edit_my_profile_page_id);
}

// Display editable section
public function display_editable_section($section){

    // Build an array of fields to output
    $fields = apply_filters(
        'wup_editable_fields_'.$section['id'],
        [],
        get_current_user_ID()
    );

    // Check we have some fields
    if(empty($fields)){
        return;
    }

    // Loop through the fields array
    foreach($fields as $field){
        $classes = (empty($field['classes'])) ? '' : ' '.$field['classes'];
        $this->display_editable_field($field, $classes, $section['id'], get_current_user_id());
    }

}

// Display editable field
public function display_editable_field($field, $classes, $section_id, $user_id){
?>

    <div class="wup-field<?php echo esc_attr($classes); ?>" id="wup-field-<?php echo esc_attr($field['id']); ?>">

    <?php
    // Native user object attribute ids
    $fields_handled_with_update_user = apply_filters(
        'wup_fields_handled_with_update_user',
        [
            'user_email',
            'user_url',
        ]
    );

    // If the current field id is in the special list
    if(in_array($field['id'], $fields_handled_with_update_user)){
        $userdata = get_userdata($user_id);
        $current_field_value = $userdata->{$field['id']};

    // Not a reserved id, handle via user meta
    }else{
        $current_field_value = get_user_meta(get_current_user_id(), $field['id'], true);
    }

    // Output the input label
    ?>
		<label for="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]"><?php echo esc_html($field['label']); ?></label>
        <?php
        switch($field['type']){
        case 'image':
        ?>

            <input name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" type="file" accept="image/*">

            <img src="<?php echo $current_field_value; ?>">

        <?php
        break;
        case 'textarea':
        ?>

            <textarea name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>"><?php echo esc_textarea($current_field_value); ?></textarea>

        <?php
        break;
        case 'checkbox':
        ?>

            <input type="hidden" name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" value="0" <?php checked($current_field_value, '0'); ?> />
            <input type="checkbox" name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" value="1" <?php checked($current_field_value, '1'); ?> />

        <?php
        break;
        case 'email':
        ?>

            <input type="email" name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($current_field_value); ?>" />

        <?php
        break;
        case 'password':
        ?>

            <input type="password" name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" value="" placeholder="<?php _e('New password', 'wup'); ?>" />

            <input type="password" name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>_check]" id="<?php echo esc_attr($field['id']); ?>_check" value="" placeholder="<?php _e('Repeat', 'wup'); ?>" />

        <?php
        break;
        default:
        ?>

            <input type="text" name="<?php echo esc_attr($section_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($current_field_value); ?>" />

        <?php
        }

    // Show description if set
    if(isset($field['desc'])){
    echo '<p class="description">'. esc_html($field['desc']) .'</p>';
    }
    ?>
	</div>

<?php
}

// Save editable fields (attached to the wup_hook_before_displaying_sections hook)
public function save_editable_fields($all_editable_sections, $user_id){

    // Create finfo object to safely check for file extension
    $FINFO = new finfo(FILEINFO_MIME_TYPE);

    // Verify nonce
    if(!isset($_POST['edit-profile-nonce']) || !wp_verify_nonce($_POST['edit-profile-nonce'], 'wup_edit_profile_action')){
        return;
    }

    // Array to store messages
    $messages = [];

    // The $_POST data
    $posted_data = $_POST;

    // Attach the $_FILES data to the posted data keeping the field_id -> value structure
    if(isset($_FILES)){
        foreach($_FILES as $file_row_key => $file_row_content){
            foreach($_FILES[$file_row_key]['name'] as $field_id => $field_file_name){
                $posted_data[$file_row_key][$field_id] = $field_file_name;
            }
        }
    }

    // Check we have some data to save
    if(empty($posted_data)){
        return;
    }

    // Reserved ids are handled in a different way
    $fields_handled_with_update_user = apply_filters(
        'wup_fields_handled_with_update_user',
        [
            'user_email',
            'user_url',
        ]
    );

    // Array of all registered editable fields
    $all_editable_fields = [];
    foreach($all_editable_sections as $section){
        $section_fields = apply_filters(
            'wup_editable_fields_'.$section['id'],
            [],
            $user_id
        );
        $all_editable_fields = array_merge($all_editable_fields, $section_fields);
    }

    // Set an array of registered keys
    $all_editable_fields_ids = wp_list_pluck($all_editable_fields, 'id');

    // Loop through the data array - each element of this will be a section's data
    foreach($posted_data as $posted_data_key=>$posted_data_value){

        // Check if this posted data row is an array ( = section data)
        if(!is_array($posted_data_value)){
            continue;
        }

        // Yes, it is an array of all the section fields (key => value)
        
        // Loop through this sections array
        foreach($posted_data_value as $field_id => $field_value){

            // If the key is not in our list of registered keys - move to next in array
            if(!in_array($field_id, $all_editable_fields_ids)){
                winni_log($field_id.' is not editable');
                continue;
            }

            // Check whether the key is reserved - handled with wp_update_user
            if(in_array($field_id, $fields_handled_with_update_user)){
                
                $user_id = wp_update_user(
                    [
                        'ID' => $user_id,
                        $field_id => $field_value,
                    ]
                );

                // Check for errors
                if(is_wp_error($user_id)){

                    // Update failed
                    $messages['update_failed'] = '<p class="error">There was a problem with updating your perfil.</p>';
                }

            // Standard user meta, handle with update_user_meta
            }else{

                // Lookup field options by key
                $registered_field_array_key = array_search($field_id, array_column($all_editable_fields, 'id'));
                
                // Sanitize user input based on field type
                switch($all_editable_fields[$registered_field_array_key]['type']){
                    case 'textarea':
                        $field_value = wp_filter_nohtml_kses($field_value);
                    break;
                    case 'image':
                        $accepted_mimes = array('image/jpeg','image/png','image/gif');
                        $file_mime = $FINFO->file($_FILES[$posted_data_key]['tmp_name'][$field_id]);
                        if(in_array($file_mime,$accepted_mimes)){

                            $upload_dir = wp_upload_dir();
                            $final_file_name = uniqid().'-'.$field_value;
                            $file_destination_path = $upload_dir['path'].'/'.$final_file_name;
                            
                            if(move_uploaded_file($_FILES[$posted_data_key]['tmp_name'][$field_id], $file_destination_path)){
                                // Save the final destination as image URL
                                $field_value = $upload_dir['url'].'/'.$final_file_name;
                            }else{
                                $messages['update_failed'] = '<p class="error">'.__('Image upload has failed', 'wup').'</p>';
                            }

                        }else{
                            $messages['update_failed'] = '<p class="error">'.__('Image extension must be PNG, GIF or JPG', 'wup').'</p>';
                        }
                    break;
                    case 'checkbox':
                        $field_value = isset($field_value) && '1' === $field_value ? true : false;
                    break;
                    case 'email':
                        $field_value = sanitize_email($field_value);
                    break;
                    default:
                        $field_value = sanitize_text_field($field_value);
                }

                // Update the user meta data
                if(isset($all_editable_fields[$registered_field_array_key]['taxonomy'])){
                    $meta = wp_set_object_terms($user_id, $field_value, $all_editable_fields[$registered_field_array_key]['taxonomy'], false);
                }else{
                    $meta = update_user_meta($user_id, $field_id, $field_value);
                }

                // Check if update failed
                if(false == $meta){
                    $messages['update_failed'] = '<p class="error">'.__('There was a problem, please try again', 'wup').'</p>';
                }
            }
        } // End section loop
    } // End data loop


    // Check if we have an messages to output
    if(empty($messages)){
    ?>
		<div class="wup-notice error">
		<?php
        // Lets loop through the messages stored
        foreach($messages as $message){
            // Output the message
            echo wp_kses(
                $message,
                [
                    'p' => [
                        'class' => [],
                    ],
                ]
            );
        }
        ?>
		</div>
	<?php
    }else{
    ?>
		<div class="wup-notice"><p class="updated"><?php esc_html_e('Your profile was updated successfully!', 'wup'); ?></p></div>
	<?php
    }
    ?>
	<?php
}

// Save password
function save_password($sections, $user_id){

    // Array to store messages
    $messages = [];

    // Check the nonce
    if(!isset($_POST['edit-profile-nonce']) || !wp_verify_nonce($_POST['edit-profile-nonce'], 'wup_edit_profile_action')){
        return;
    }

    $data = (isset($_POST['password'])) ? $_POST['password'] : '';

    // Make sure the password is not empty
    if(empty($data)){
        return;
    }

    // Check that the password match
    if($data['user_pass'] != $data['user_pass_check']){
        $messages['password_mismatch'] = '<p class="error">'.__('Please make sure the passwords match', 'wup').'.</p>';
    }

    // Check we have any messages in the messages array - if we have password failed at some point
    if(empty($messages)){
        
        // The password can now be updated and redirect the user to the acceso page
        wp_set_password($data['user_pass'], $user_id);
        
        // Translators: %s: acceso link
        $successfully_msg = '<div class="messages"><p class="updated">'.sprintf(__('Your password was successfully changed and you have been logged out. Please <a href="%s">login again here</a>', 'wup'), esc_url(wp_acceso_url())).'</p></div>';
        echo wp_kses(
            $successfully_msg,
            [
                'div' => [
                    'class' => [],
                ],
                'p'   => [
                    'class' => [],
                ],
                'a'   => [
                    'href' => [],
                ],
            ]
        );
        
    // Messages not empty therefore password failed
    }else{
    ?>
		<div class="wup-notice error">
		<?php
        foreach ($messages as $message) {
            echo wp_kses(
                $message,
                [

                    'p' => [
                        'class' => [],
                    ],
                ]
            );
        }
        ?>
		</div>
	<?php
    }

}

// Mostrar errores en el formulario
public function maybe_display_notice(){

    $avisos = wp_cache_get('wup_avisos_perfil');

    if($avisos){ 
        echo '<div class="wup-message">'.$avisos.'</div>';
    }

}

}