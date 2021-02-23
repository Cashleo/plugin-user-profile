<?php

// Clase para gestionar Mi Perfil y Editar Perfil
class WUP_Profile{

// Message array
private $messages = [];

// Constructor
public function __construct(){}

// Activar la clase: Definir shortcode, configurar acciones y filtros
public static function activar_clase(){

    $esta_clase = new self();

    add_shortcode('wup_show_my_profile', [$esta_clase, 'mostrar_pagina_mi_perfil']);
    add_shortcode('wup_edit_my_profile', [$esta_clase, 'mostrar_pagina_editar_mi_perfil']);

    add_action('skpu_hook_antes_de_mostrar_secciones', [$esta_clase, 'save_campos'], 5, 2);
    add_action('skpu_hook_antes_de_mostrar_secciones', [$esta_clase, 'save_password'], 10, 2);

    add_filter('skpu_secciones', [$esta_clase, 'add_perfil_seccion'], 10);
    add_filter('skpu_secciones', [$esta_clase, 'add_password_seccion'], 20);
    add_filter('skpu_campos_perfil', [$esta_clase, 'default_perfil_seccion_campos'], 10);
    add_filter('skpu_campos_password', [$esta_clase, 'default_password_seccion_campos'], 10);

}

// Add the perfil seccion to the perfil output
public function add_perfil_seccion($secciones){

    // Add our seccion to the secciones array
    $secciones[] = [
        'id'            => 'perfil',
        'label'         => __('Profile', 'skpu'),
        'seccion_class'     => 'perfil-seccion',
        'content_class' => 'perfil-content',
        'callback'      => 'skpu_perfil_seccion_content',
    ];

    return $secciones;
}

// Adds the password seccion to the perfil output
public function add_password_seccion($secciones){

    // Add our seccion to the secciones array
    $secciones[] = [
        'id'            => 'password',
        'label'         => __('Password', 'skpu'),
        'seccion_class'     => 'password-seccion',
        'content_class' => 'password-content',
    ];

    return $secciones;
}

// Default perfil seccion campos
function default_perfil_seccion_campos($campos){
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

    $campos[] = [
        'id'      => 'description',
        'label'   => __('Description/Bio', 'skpu'),
        'desc'    => __('Edit your description/bio.', 'skpu'),
        'type'    => 'wysiwyg',
        'classes' => 'description',
    ];

    return $campos;
}

// Default password seccion campos
function default_password_seccion_campos($campos){
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
public function mostrar_pagina_mi_perfil(){

    // Si el usuario no ha accedido, lo llevamos a la página de acceso
    $this->redirigir_usuario_no_identificado();

    $perfil_page = get_option('skpu_id_pagina_mi_perfil');

    ob_start();

    skpu_cargar_vista('mi-perfil.php');

    return ob_get_clean();
}

// Mostrar la vista de Editar Mi Perfil
public function mostrar_pagina_editar_mi_perfil(){

    // Si el usuario no ha accedido, lo llevamos a la página de acceso
    $this->redirigir_usuario_no_identificado();

    ob_start();

    skpu_cargar_vista('editar-mi-perfil.php');

    return ob_get_clean();
}

public function redirigir_usuario_no_identificado(){

    // Si está logueado, ignoramos el resto de la función
    if(is_user_logged_in()){
        return;
    }

    $id_pagina_acceso = get_option('skpu_id_pagina_acceso');

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
    $id_pagina_mi_perfil = get_option('skpu_id_pagina_mi_perfil');
    if(!$id_pagina_mi_perfil){
        return false;
    }
    return get_permalink($id_pagina_mi_perfil);
}

// Obtener la URL de la página de editar mi perfil
public function url_de_editar_mi_perfil(){
    $id_pagina_editar_mi_perfil = get_option('skpu_id_pagina_editar_mi_perfil');
    if(!$id_pagina_editar_mi_perfil){
        return false;
    }
    return get_permalink($id_pagina_editar_mi_perfil);
}


// Mostrar seccion de campos editables
public function mostrar_seccion_editable($seccion){

    // Build an array of campos to output
    $campos = apply_filters(
        'skpu_campos_'.$seccion['id'],
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
    $reserved_ids = apply_filters(
        'skpu_reserved_ids',
        [
            'user_email',
            'user_url',
        ]
    );

    // if the current campo id is in the reserved list
    if(in_array($campo['id'], $reserved_ids)){
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

            // Switch to alter the output deactivacion_pendiente on type
            switch ($campo['type']) {

                /* if this is a wysiwyg setting */
                case 'wysiwyg':
                    /* set some settings args for the editor */
                    $editor_settings = [
                        'textarea_rows' => apply_filters('skpu_wysiwyg_textarea_rows', '5', $campo['id']),
                        'media_buttons' => apply_filters('skpu_wysiwyg_media_buttons', false, $campo['id']),
                    ];

                    /* build campo name. */
                    $wysiwyg_name = $campo['id'];

                    /* display the wysiwyg editor */
                    wp_editor(
                        $current_campo_value, // default content.
                        $wysiwyg_name, // id to give the editor element.
                        $editor_settings // edit settings from above.
                    );

                    break;

                

              

            

                /* if the type is set to a textarea input */
                case 'textarea':
                    ?>

					<textarea name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" rows="<?php echo absint(apply_filters('skpu_textarea_rows', '5', $campo['id'])); ?>" cols="50" id="<?php echo esc_attr($campo['id']); ?>" class="regular-text"><?php echo esc_textarea($current_campo_value); ?></textarea>

					<?php

                    /* break out of the switch statement */
                    break;

                /* if the type is set to a checkbox */
                case 'checkbox':
                    ?>
					<input type="hidden" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="0" <?php checked($current_campo_value, '0'); ?> />
					<input type="checkbox" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" value="1" <?php checked($current_campo_value, '1'); ?> />
					<?php

                    /* break out of the switch statement */
                    break;

                

                /* if the type is set to an email input */
                case 'email':
                    ?>
					<input type="email" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" class="regular-text" value="<?php echo esc_attr($current_campo_value); ?>" />
					<?php
                    /* break out of the switch statement */
                    break;

                /* if the type is set to a password input */
                case 'password':
                    ?>
					<input type="password" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" class="regular-text" value="" placeholder="New Password" />

					<input type="password" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>_check]" id="<?php echo esc_attr($campo['id']); ?>_check" class="regular-text" value="" placeholder="Repeat New Password" />

					<?php

                    /* break out of the switch statement */
                    break;
                /* any other type of input - treat as text input */
                default:
                    ?>
					<input type="text" name="<?php echo esc_attr($seccion_id); ?>[<?php echo esc_attr($campo['id']); ?>]" id="<?php echo esc_attr($campo['id']); ?>" class="regular-text" value="<?php echo esc_attr($current_campo_value); ?>" />
					<?php

            }

    /* if we have a description lets output it */
    if (isset($campo['desc'])) {
        ?>
				<p class="description"><?php echo esc_html($campo['desc']); ?></p>
				<?php
    } // end if have description

            ?>
	</div>

	<?php
}

// Save campos
public function save_campos($secciones, $user_id){

    // Check the nonce
    if(!isset($_POST['skpu_nonce_name']) || !wp_verify_nonce($_POST['skpu_nonce_name'], 'skpu_nonce_action')){
        return;
    }

    // Array to store messages
    $messages = [];

    // The POST data
    $secciones_data = $_POST;

    /**
    * Remove the following array elements from the data
    * password
    * nonce name
    * wp refer - sent with nonce.
    */
    unset($secciones_data['password']);
    unset($secciones_data['skpu_nonce_name']);
    unset($secciones_data['_wp_http_referer']);
    unset($secciones_data['description']);

    // Lets check we have some data to save
    if(empty($secciones_data)){
        return;
    }

    /**
    * Setup an array of reserved meta keys
    * to process in a different way
    * they are not meta data in WordPress
    * reserved names are user_url and user_email as they are stored in the users seccionle not user meta.
    */
    $reserved_ids = apply_filters(
        'skpu_reserved_ids',
        [
            'user_email',
            'user_url',
        ]
    );

    // Array of registered campos
    $registered_campos = [];
    foreach ($secciones as $seccion) {
        $seccion_campos = apply_filters(
            'skpu_campos_'.$seccion['id'],
            [],
            $user_id
        );
        $registered_campos = array_merge($registered_campos, $seccion_campos);
    }

    // Set an array of registered keys
    $registered_keys = wp_list_pluck($registered_campos, 'id');

    // Loop through the data array - each element of this will be a secciones data
    foreach($secciones_data as $seccion_data){
        
        /**
        * Loop through this secciones array
        * the ket here is the meta key to save to
        * the value is the value we want to actually save.
        */
        foreach($seccion_data as $key => $value){

            // If the key is the save submit - move to next in array
            if('skpu_save' == $key || 'skpu_nonce_action' == $key){
                continue;
            }

            // If the key is not in our list of registered keys - move to next in array
            if(!in_array($key, $registered_keys)){
                continue;
            }

            // Check whether the key is reserved - handled with wp_update_user
            if(in_array($key, $reserved_ids)){
                
                $user_id = wp_update_user(
                    [
                        'ID' => $user_id,
                        $key => $value,
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
                $registered_campo_key = array_search($key, array_column($registered_campos, 'id'));
                
                // Sanitize user input based on campo type
                switch($registered_campos[$registered_campo_key]['type']){
                    
                    case 'wysiwyg':
                        $value = wp_filter_post_kses($value);
                    break;
                    case 'textarea':
                        $value = wp_filter_nohtml_kses($value);
                    break;
                    case 'checkbox':
                        $value = isset($value) && '1' === $value ? true : false;
                    break;
                    
                    case 'email':
                        $value = sanitize_email($value);
                    break;
                    default:
                        $value = sanitize_text_field($value);
                }

                // Update the user meta data
                if(isset($registered_campos[$registered_campo_key]['taxonomy'])){
                    $meta = wp_set_object_terms($user_id, $value, $registered_campos[$registered_campo_key]['taxonomy'], false);
                }else{
                    $meta = update_user_meta($user_id, $key, $value);
                }

                // Check the update was successful
                if (false == $meta) {

                    // Update failed
                    $messages['update_failed'] = '<p class="error">There was a problem with updating your perfil.</p>';
                }
            }
        } // End seccion loop
    } // End data loop

    // Update user bio
    if(isset($_POST['description'])){
        wp_update_user(
            [
                'ID'          => $user_id,
                'description' => sanitize_text_field(wp_unslash($_POST['description'])),
            ]
        );
    }

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