<?php

// This class handles the User Show Profile and Edit profile functionality
class WUP_Profile{

// Message array
private $messages = [];

// Constructor
public function __construct(){}

// Activate class: Setup shortcodes and add some actions and filters
public static function activar_clase(){

    $esta_clase = new self();

    add_shortcode('wup_show_my_profile', [$esta_clase, 'shortcode_show_my_profile']);
    add_shortcode('wup_edit_my_profile', [$esta_clase, 'shortcode_edit_my_profile']);

    add_action('skpu_hook_antes_de_mostrar_secciones', [$esta_clase, 'save_editable_fields'], 5, 2);
    add_action('skpu_hook_antes_de_mostrar_secciones', [$esta_clase, 'save_password'], 10, 2);

    add_filter('wup_editable_sections', [$esta_clase, 'add_profile_section'], 10);
    add_filter('wup_editable_sections', [$esta_clase, 'add_avatar_section'], 10);
    add_filter('wup_editable_sections', [$esta_clase, 'add_password_seccion'], 20);

    add_filter('wup_editable_fields_profile',  [$esta_clase, 'default_editable_fields_profile'], 10);
    add_filter('wup_editable_fields_avatar',   [$esta_clase, 'default_editable_fields_avatar'], 10);
    add_filter('wup_editable_fields_password', [$esta_clase, 'default_editable_fields_password'], 10);

}

// Add the perfil seccion to the perfil output
public function add_profile_section($secciones){

    $secciones[] = [
        'id'            => 'profile',
        'label'         => __('Profile', 'skpu'),
        'seccion_class' => 'perfil-seccion',
        'content_class' => 'perfil-content'
    ];

    return $secciones;
}

// Add the picture seccion to the perfil output
public function add_avatar_section($secciones){

    $secciones[] = [
        'id'            => 'avatar',
        'label'         => __('Avatar', 'skpu'),
        'seccion_class' => 'avatar-seccion',
        'content_class' => 'avatar-content'
    ];

    return $secciones;
}


// Adds the password seccion to the perfil output
public function add_password_seccion($secciones){

    $secciones[] = [
        'id'            => 'password',
        'label'         => __('Password', 'skpu'),
        'seccion_class' => 'password-seccion',
        'content_class' => 'password-content'
    ];

    return $secciones;
}

// Default perfil seccion campos
function default_editable_fields_profile($campos){
    $campos[] = [
        'id'      => 'user_email',
        'label'   => __('Email Address', 'skpu'),
        'desc'    => __('Edit your email address - used for resetting your password etc.', 'skpu'),
        'type'    => 'email',
        'classes' => 'user_email',
    ];

    $campos[] = [
        'id'      => 'first_name',
        'label'   => __('First Name', 'skpu'),
        'desc'    => __('Edit your first name.', 'skpu'),
        'type'    => 'text',
        'classes' => 'first_name',
    ];

    $campos[] = [
        'id'      => 'last_name',
        'label'   => __('Last Name', 'skpu'),
        'desc'    => __('Edit your last name.', 'skpu'),
        'type'    => 'text',
        'classes' => 'last_name',
    ];

    $campos[] = [
        'id'      => 'user_url',
        'label'   => __('URL', 'skpu'),
        'desc'    => __('Edit your perfil associated URL.', 'skpu'),
        'type'    => 'text',
        'classes' => 'user_url',
    ];

    return $campos;
}

// Default avatar seccion campos
function default_editable_fields_avatar($campos){
    $campos[] = [
        'id'      => 'avatar',
        'label'   => __('Avatar', 'skpu'),
        'desc'    => __('User profile image', 'skpu'),
        'type'    => 'image',
        'classes' => 'avatar',
    ];

    return $campos;
}

// Default password seccion campos
function default_editable_fields_password($campos){
    $campos[] = [
        'id'      => 'user_pass',
        'label'   => __('Password', 'skpu'),
        'desc'    => __('New Password', 'skpu'),
        'type'    => 'password',
        'classes' => 'user_pass',
    ];

    return $campos;
}


// Mostrar la vista de Mi Perfil
public function shortcode_show_my_profile(){

    // Si el usuario no ha accedido, lo llevamos a la página de acceso
    $this->redirigir_usuario_no_identificado();

    $perfil_page = get_option('wup_page_id_for_show_my_profile');

    ob_start();

    wup_load_view('mi-perfil.php');

    return ob_get_clean();
}

// Mostrar la vista de Editar Mi Perfil
public function shortcode_edit_my_profile(){

    // Si el usuario no ha accedido, lo llevamos a la página de acceso
    $this->redirigir_usuario_no_identificado();

    ob_start();

    wup_load_view('editar-mi-perfil.php');

    return ob_get_clean();
}

public function redirigir_usuario_no_identificado(){

    // Si está logueado, ignoramos el resto de la función
    if(is_user_logged_in()){
        return;
    }

    $id_pagina_acceso = get_option('wup_page_id_for_login');

    if($id_pagina_acceso){
        $url_redirigir_usuario_no_identificado = get_permalink($id_pagina_acceso);
    }else{
        $url_redirigir_usuario_no_identificado = site_url();
    }

    wp_redirect($url_redirigir_usuario_no_identificado);
    exit;
}

// Obtener la URL de la página de mi perfil
public function url_de_mi_perfil(){
    $id_pagina_mi_perfil = get_option('wup_page_id_for_show_my_profile');
    if(!$id_pagina_mi_perfil){
        return false;
    }
    return get_permalink($id_pagina_mi_perfil);
}

// Obtener la URL de la página de editar mi perfil
public function url_de_editar_mi_perfil(){
    $id_pagina_editar_mi_perfil = get_option('wup_page_id_for_edit_my_profile');
    if(!$id_pagina_editar_mi_perfil){
        return false;
    }
    return get_permalink($id_pagina_editar_mi_perfil);
}


// Mostrar seccion de campos editables
public function mostrar_seccion_editable($seccion){

    // Build an array of campos to output
    $campos = apply_filters(
        'wup_editable_fields_'.$seccion['id'],
        [],
        get_current_user_ID()
    );

    // Check we have some campos
    if (!empty($campos)) {

        /* output a wrapper div and form opener */ ?>

			<div class="skpu-campos">

				<?php

                    /* start a counter */
                    $counter = 1;

        /* get the total number of campos in the array */
        $total_campos = count($campos);

        /* lets loop through our campos array */
        foreach ($campos as $campo) {

                        /* set a base counting class */
            $count_class = ' skpu-'.$campo['type'].'-campo skpu-campo-'.$counter;

            /* build our counter class - check if the counter is 1 */
            if (1 === $counter) {

                            /* this is the first campo element */
                $counting_class = $count_class.' first';

            /* is the counter equal to the total number of campos */
            } elseif ($counter === $total_campos) {

                            /* this is the last campo element */
                $counting_class = $count_class.' last';

            /* if not first or last */
            } else {

                            /* set to base count class only */
                $counting_class = $count_class;
            }

            /* build a var for classes to add to the wrapper */
            $classes = (empty($campo['classes'])) ? '' : ' '.$campo['classes'];

            /* build ful classes array */
            $classes = $counting_class.$classes;

            /* output the campo */
            $this->mostrar_campo_editable($campo, $classes, $seccion['id'], get_current_user_id());

            /* increment the counter */
            $counter++;
        } // end for each campo

                    /* output a closing wrapper div */
                ?>

			</div>

		<?php
    } // end if have campos.

}

// Mostrar campo editable
public function mostrar_campo_editable($campo, $classes, $seccion_id, $user_id){
    ?>

	<div class="skpu-campo<?php echo esc_attr($classes); ?>" id="skpu-campo-<?php echo esc_attr($campo['id']); ?>">

    <?php

    // the reserved meta ids
    $fields_handled_with_update_user = apply_filters(
        'wup_fields_handled_with_update_user',
        [
            'user_email',
            'user_url',
        ]
    );

    // if the current campo id is in the reserved list
    if(in_array($campo['id'], $fields_handled_with_update_user)){
        $userdata = get_userdata($user_id);
        $current_campo_value = $userdata->{$campo['id']};

    // not a reserved id - treat normally
    }else{
        // get the current value
        $current_campo_value = get_user_meta(get_current_user_id(), $campo['id'], true);
    }

    // Output the input label
    ?>
		<label for="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]"><?php echo esc_html($campo['label']); ?></label>
        <?php
        switch($campo['type']){
        case 'image':
        ?>

        <input name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" type="file" accept="image/*">

        <br><b>Valor actual:</b> <?php echo $current_campo_value; ?>

        <?php
        break;
        case 'textarea':
        ?>

            <textarea name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>"><?php echo esc_textarea($current_campo_value); ?></textarea>

        <?php
        break;
        case 'checkbox':
        ?>

            <input type="hidden" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="0" <?php checked($current_campo_value, '0'); ?> />
            <input type="checkbox" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="1" <?php checked($current_campo_value, '1'); ?> />

        <?php
        break;
        case 'email':
        ?>

            <input type="email" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="<?php echo esc_attr($current_campo_value); ?>" />

        <?php
        break;
        case 'password':
        ?>

            <input type="password" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="" placeholder="New Password" />

            <input type="password" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>_check]" id="<?php echo esc_attr($campo['id']); ?>_check" value="" placeholder="Repeat New Password" />

        <?php
        break;
        default:
        ?>

            <input type="text" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="<?php echo esc_attr($current_campo_value); ?>" />

        <?php
        }

    // Show description if set
    if(isset($campo['desc'])){
    ?>
		<p class="description"><?php echo esc_html($campo['desc']); ?></p>
	<?php
    }
    ?>
	</div>

<?php
}

// Save editable fields (attached to the skpu_hook_antes_de_mostrar_secciones hook)
public function save_editable_fields($all_editable_sections, $user_id){

    // Create finfo object to safely check for file extension
    $FINFO = new finfo(FILEINFO_MIME_TYPE);

    // Check the nonce
    if(!isset($_POST['skpu_nonce_name']) || !wp_verify_nonce($_POST['skpu_nonce_name'], 'skpu_nonce_action')){
        return;
    }

    // Array to store messages
    $messages = [];

    // The $_POST data
    $posted_data = $_POST;

    // Attach the $_FILES data to the posted datas keeping the field_id -> value structure
    if(isset($_FILES)){
        foreach($_FILES as $file_row_key => $file_row_content){
            foreach($_FILES[$file_row_key]['name'] as $field_id => $field_file_name){
                $posted_data[$file_row_key][$field_id] = $field_file_name;
            }
        }
    }

    winni_log($posted_data);

    // Lets check we have some data to save
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

            winni_log('Processing '.$field_id);

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

            // Standard user meta - handle with update_user_meta
            }else{

                // Lookup campo options by key
                $registered_field_array_key = array_search($field_id, array_column($all_editable_fields, 'id'));
                
                // Sanitize user input based on campo type
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
                                $messages['update_failed'] = '<p class="error">Ha fallado la subida de la imagen.</p>';
                            }

                        }else{
                            $messages['update_failed'] = '<p class="error">El formato de imagen debe ser jpg, png o gif.</p>';
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

                // Check the update was successful
                if (false == $meta) {

                    // Update failed
                    $messages['update_failed'] = '<p class="error">There was a problem with updating your perfil.</p>';
                }
            }
        } // End seccion loop
    } // End data loop


    // Check if we have an messages to output
    if(empty($messages)){
    ?>
		<div class="skpu-notice error">
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
		</div><!-- // messages -->
	<?php
    }else{
    ?>
		<div class="skpu-notice"><p class="updated"><?php esc_html_e('Your perfil was updated successfully!', 'skpu'); ?></p></div>
	<?php
    }
    ?>
	<?php
}

// Save password
function save_password($secciones, $user_id){

    // Array to store messages
    $messages = [];

    // Check the nonce
    if(!isset($_POST['skpu_nonce_name']) || !wp_verify_nonce($_POST['skpu_nonce_name'], 'skpu_nonce_action')){
        return;
    }

    $data = (isset($_POST['password'])) ? $_POST['password'] : '';

    // Make sure the password is not empty
    if(empty($data)){
        return;
    }

    // Check that the password match
    if($data['user_pass'] != $data['user_pass_check']){
        $messages['password_mismatch'] = '<p class="error">'.sprintf(__('Please make sure the passwords match', 'skpu')).'.</p>';
    }

    // Check we have any messages in the messages array - if we have password failed at some point
    if(empty($messages)){
        
        // The password can now be updated and redirect the user to the acceso page
        wp_set_password($data['user_pass'], $user_id);
        
        // Translators: %s: acceso link
        $successfully_msg = '<div class="messages"><p class="updated">'.sprintf(__('You\'re password was successfully changed and you have been logged out. Please <a href="%s">acceso again here</a>.', 'skpu'), esc_url(wp_acceso_url())).'</p></div>';
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
		<div class="skpu-notice error">
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
public function mostrar_avisos(){

    $avisos = wp_cache_get('skpu_avisos_perfil');

    if($avisos){ 
        echo '<div class="skpu-message">'.$avisos.'</div>';
    }

}

}