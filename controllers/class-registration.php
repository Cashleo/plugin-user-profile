<?php

// Clase para gestionar el proceso de registro de los usuarios
class WUP_Registration{

public function __construct(){}

// Activar la clase: Definir shortcode, configurar acciones y filtros
public static function activar_clase(){

    $esta_clase = new self();

    // Definir el shortcode para cargar el formulario de registro
    add_shortcode('wup_registration', [$esta_clase, 'mostrar_formulario_registro']);

    // Agregar acciones al hook de 'init'
    add_action('init', [$esta_clase, 'procesar_registro']);

}

// Función asociada al shortcode [wup_registration]
public function mostrar_formulario_registro($atts){

    $atts = shortcode_atts(
        [
            'rol_especifico' => '',    // Forzar un rol fijo
            'rol_elegir' => ''  // Permitir al usuario seleccionar su rol
        ],
        $atts
    );

    ob_start();

    if(is_user_logged_in()){

        wup_load_view(
            'aviso-usuario-identificado.php',
            [
                'user' => wp_get_current_user(),
            ]
        );

    }else{

        $args = [
            'rol_especifico'    => $atts['rol_especifico'],
            'rol_elegir' => $atts['rol_elegir']
        ];
        wup_load_view('formulario-registro.php', $args);

    }

    return ob_get_clean();
}

// Obtener la URL de la página de registro
public function url_de_registro(){

    $id_pagina_registro = get_option('wup_page_id_for_registration');

    if(!$id_pagina_registro){
        $id_pagina_registro = site_url();
    }

    return get_permalink($id_pagina_registro);

}

// Obtener la URL de la página de registro finalizado
public function url_de_registro_finalizado(){

    $id_pagina_registro_finalizado = get_option('wup_page_id_for_registration_finished');

    if(!$id_pagina_registro_finalizado){
        $id_pagina_registro_finalizado = site_url();
    }

    return get_permalink($id_pagina_registro_finalizado);

}

// Procesar los datos enviados en el formulario de registro
public function procesar_registro(){

    // Solo procesar si existe nonce (si no existe es que no hay nada que procesar)
    if(!isset($_POST['nonce_para_registro'])){
        return;
    }

    // Existe nonce, vamos a verificarlo
    if(!wp_verify_nonce($_POST['nonce_para_registro'], 'skpu_accion_registrarse')){
        wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('Ha habido un problema, vuelve a intentarlo por favor.', 'skpu'));
        return;
    }

    // Array para ir guardando los datos del nuevo usuario para insertarlo en WP
    $datos_nuevo_usuario = [];

    // Verificar email
    if(!filter_var($_POST['usuario_email'], FILTER_VALIDATE_EMAIL)){
        wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('Debe especificar un email válido.', 'skpu'));
        return;
    }else{

        // Comprobar si el email ya existe
        $email_limpio = sanitize_email(wp_unslash($_POST['usuario_email']));
        if(email_exists($email_limpio)){
            wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('Ya existe una cuenta con este correo electrónico.', 'skpu'));
            return;
        }else{
            $datos_nuevo_usuario['user_email'] = $email_limpio;
        }

    }

    // Verificar el login (nombre alfanumerico sin espacios)
    if(empty($_POST['usuario_login'])){ // Empty detecta si está vacío o no existe
        // Usar el email como login si no se ha especificado ninguno
        $datos_nuevo_usuario['user_login'] = $email_limpio;
    }else{

        // Comprobar si el usuario ya existe
        $usuario_login_limpio = sanitize_user(wp_unslash($_POST['usuario_login']));
        if(username_exists($usuario_login)){
            wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('Ya existe una cuenta con este nombre de usuario.', 'skpu'));
            return;
        }else{
            $datos_nuevo_usuario['user_login'] = $usuario_login;
        }

    }

    // Comprobar si se ha seleccionado un apodo
    if(!empty($_POST['usuario_apodo'])){ // Empty detecta si está vacío o no existe
        // Comprobar si el usuario ya existe
        $usuario_apodo = sanitize_text_field(wp_unslash($_POST['usuario_apodo']));
        $datos_nuevo_usuario['display_name'] = $usuario_apodo;
    }

    // Verificar que la clave 1 no está vacía
    if(empty($_POST['clave1'])){
        wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('Debe escribir una contraseña.', 'skpu'));
        return;
    }

    // Verificar que la clave 2 no está vacía
    if(empty($_POST['clave2'])){
        wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('Debe repetir la contraseña para confirmarla.', 'skpu'));
        return;
    }

    // Verify that the 2 passwords match
    if($_POST['clave1'] != $_POST['clave2']){
        wp_cache_set('skpu_avisos_registro', '<strong>'.__('Error', 'skpu').':</strong> '.__('las contraseñas no coinciden.', 'skpu'));
        return;
    }else{
        $datos_nuevo_usuario['user_pass'] = $_POST['clave1']; // No lo limpiamos, se aplicará un hash
    }

    // Asignar rol (dejamos el filtro ahí por si alguien quiere modificar el rol)
    $datos_nuevo_usuario['role'] = apply_filters('skpu_fitro_para_cambiar_rol', 'subscriber');
    
    // Nombre de pila
    if(isset($_POST['usuario_nombre_pila'])){
        $datos_nuevo_usuario['first_name'] = sanitize_text_field(wp_unslash($_POST['usuario_nombre_pila']));
    }

    // Apellidos
    if(isset($_POST['usuario_apellidos'])){
        $datos_nuevo_usuario['last_name'] = sanitize_text_field(wp_unslash($_POST['usuario_apellidos']));
    }

    // Insertar usuario en WordPress
    $id_del_usuario_creado = wp_insert_user($datos_nuevo_usuario);
    
    if(is_wp_error($id_del_usuario_creado)){
        wp_cache_set('skpu_avisos_registro', $id_del_usuario_creado->get_error_message());
        return;
    }else{

        // Hacer accion cuando el usuario se registre
        do_action('skpu_accion_cuando_se_registra_usuario', $id_del_usuario_creado);

        // Automáticamente meter los metadatos extra si hay
        foreach($_POST as $input_nombre => $input_valor){
            // Compruebo si el nombre del campo empieza por "meta-loquesea"
            if(substr($input_name,0,5)=='meta-'){
                $nombre_metadato = substr($input_name,5);
                $valor_metadato = sanitize_text_field($_POST[$input_nombre]);
                add_user_meta($id_del_usuario_creado, $nombre_metadato, $valor_metadato);
            }
        }

        // Establecer estado como pendiente de activación
        add_user_meta($id_del_usuario_creado, 'skpu_estado_activacion_usuario', 'activacion_pendiente');
 
        // Enviamos el email de activación
        $this->enviar_email_activacion($id_del_usuario_creado);

        // Guardar el sexo si se ha establecido
        if(!empty($_POST['usuario_sexo'])){ // Empty detecta si está vacío o no existe
            // Comprobar si el usuario ya existe
            $usuario_sexo = sanitize_text_field(wp_unslash($_POST['usuario_sexo']));
            add_user_meta($id_del_usuario_creado, 'skpu_usuario_sexo', $usuario_sexo, true);
        }

        // Redirigir a una página específica si se ha indicado en el form de registro
        if(isset($_POST['redireccion_post_registro'])){

            // Si estamos redirigiendo es porque ya lo queremos también con sesión iniciada
            wp_set_current_user($id_del_usuario_creado);
            wp_set_auth_cookie($id_del_usuario_creado);

            wp_safe_redirect($_POST['redireccion_post_registro']);
            exit();
        }
        
        // Redirigir a la página de registro finalizado sin loguin
        wp_redirect($this->url_de_registro_finalizado());
        exit;

    }
}


// Enviar email de activación
public function enviar_email_activacion($id_usuario){

    $usuario = get_userdata($id_usuario);

    if($usuario && !is_wp_error($usuario)){
        
        // Generar código de activación
        $codigo_activacion = sha1(time());

        // Guardar el código junto a los metadatos del usuario
        update_user_meta($id_usuario, 'skpu_codigo_activacion_usuario', $codigo_activacion, true);

        $url_de_acceso = get_permalink(get_option('wup_page_id_for_login'));

        $nombre_del_sitio = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $enlace_activacion = add_query_arg(
            [
                'codigo'  => $codigo_activacion,
                'id_usuario' => $id_usuario,
            ],
            $url_de_acceso
        );

        $mensaje_activacion = __('Haz clic en el enlace para activar tu cuente:').'<br><br>';
        $mensaje_activacion .= '<a href="'.$enlace_activacion.'">'.$enlace_activacion.'</a>';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($usuario->user_email, __('Activa tu cuenta'), $mensaje_activacion, $headers);

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
public function mostrar_avisos(){

    $avisos = wp_cache_get( 'skpu_avisos_registro' );

    if($avisos){    
        echo '<div class="skpu-message">'.$avisos.'</div>';
    }

}

}