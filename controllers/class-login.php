<?php

// Clase para gestionar el acceso de los usuarios

class WUP_Login{

public function __construct(){}

// Activar la clase: Definir shortcode, configurar acciones y filtros
public static function activar_clase(){

    $esta_clase = new self();

    // Definir el shortcode para cargar el formulario de acceso
    add_shortcode('wup_login', [$esta_clase, 'mostrar_formulario_acceso']);

    // Agregar acciones al hook de 'init'
    add_action('init', [$esta_clase, 'procesar_acceso']);
    add_action('init', [$esta_clase, 'procesar_cierre_sesion']);
    add_action('init', [$esta_clase, 'procesar_recuperacion_clave']);
    add_action('init', [$esta_clase, 'procesar_activacion_usuario']);

    // Añadir filtros
    add_filter('login_url', [$esta_clase, 'filtrar_url_acceso'], 10, 2);
    add_filter('logout_url', [$esta_clase, 'filtrar_url_cerrar_sesion'], 10, 2);
    add_filter('lostpassword_url', [$esta_clase, 'filtrar_url_recuperar_clave'], 10, 2);

}


// Obtener URL de acción según qué acción vamos a hacer
public function url_de_accion($accion = 'acceso', $redirigir_hacia = ''){

    // Usamos como base la URL de la página de acceso
    $url_acceso = $this->url_de_acceso();

    switch($accion){
        case 'clave-perdida':
            return add_query_arg(['accion' => 'clave-perdida'], $url_acceso);
        break;
        case 'resetear-clave':
            return add_query_arg(['accion' => 'resetear-clave'], $url_acceso);
        break;
        case 'salir':
            return wp_nonce_url(add_query_arg(['accion' => 'salir'], $url_acceso), 'log-out');
        break;
        default:
            return $url_acceso;
    }
}

// Obtener la URL de la página de acceso
public function url_de_acceso(){

    $id_pagina_acceso = get_option('skpu_id_pagina_acceso');

    if(!$id_pagina_acceso){
        return false;
    }

    return get_permalink($id_pagina_acceso);

}

// Filtrar la URL de acceso de WP para poner la nuestra
public function filtrar_url_acceso($url, $redirect){
    return $this->url_de_accion('acceso', $redirect);
}

// Filtrar la URL de cerrar sesión de WP para poner la nuestra
public function filtrar_url_cerrar_sesion($url, $redirect){
    return $this->url_de_accion('salir', $redirect);
}

// Filtrar la URL de recuperar clave de WP para poner la nuestra
public function filtrar_url_recuperar_clave($url, $redirect){
    return $this->url_de_accion('clave-perdida', $redirect);
}

// Generar la URL de clave perdida
public function generar_url_clave_perdida(){
    return sprintf('<a href="%s">%s</a>', $this->url_de_accion('clave-perdida'), __('He perdido mi clave', 'skpu'));
}

// Función asociada al shortcode [wup_login]
public function mostrar_formulario_acceso(){

    ob_start();

    // Si ya ha iniciado sesión, mostrar la plantilla de usuario ya identificado
    if(is_user_logged_in()){

        skpu_cargar_vista(
            'aviso-usuario-identificado.php',
            [
                'user' => wp_get_current_user(),
            ]
        );

    // Si no, mostrar la plantilla correspondiente dependiendo de la acción actual
    }else{

        $url_pagina_acceso = $this->url_de_acceso();

        $accion = isset($_GET['accion']) ? $_GET['accion'] : 'acceso';
        $args = [
            'accion_url' => $url_pagina_acceso,
        ];

        // Mostrar contenido según la acción que vayamos a realizar
        switch($accion){

        // Clave perdida. Pedimos usuario para poder enviarle un correo de recuperación
        case 'clave-perdida':
            wp_cache_set( 'skpu_avisos_acceso', __('No te preocupes, escribe aquí tu usuario o email y recibirás un enlace para poder recuperar tu clave.', 'skpu') );
            skpu_cargar_vista('formulario-clave-perdida.php', $args);
        break;

        // Resetear clave. El usuario ya ha pinchado el enlace de recuperación
        case 'resetear-clave':
            if(isset($_GET['clave-cambiada'])){
                wp_cache_set('skpu_avisos_acceso', __('Tu clave ha sido cambiada correctamente.', 'skpu'));
                skpu_cargar_vista('formulario-acceso.php', $args);
            }else{
                wp_cache_set('skpu_avisos_acceso', __('Escribe tu nueva clave de acceso', 'skpu'));
                skpu_cargar_vista('formulario-resetear-clave.php', $args);
            }
        break;

        // Por defecto mostramos el formulario de acceso
        default:
            if(isset($_GET['enviado-correo-recuperacion'])){
                wp_cache_set('skpu_avisos_acceso', __('Check your e-mail for the confirmation link.', 'skpu'));
            }

            if(isset($_GET['loggedout'])){
                wp_cache_set('skpu_avisos_acceso', __('You are now logged out.', 'skpu'));
            }

            skpu_cargar_vista('formulario-acceso.php', $args);
        }
    }

    return ob_get_clean();
}

// Procesar el formulario de acceso
public function procesar_acceso(){

    // Solo procesar si existe nonce (si no existe es que no hay nada que procesar)
    if(!isset($_POST['nonce_para_acceso'])){
        return;
    }

    // Existe nonce, vamos a verificarlo
    if(!wp_verify_nonce($_POST['nonce_para_acceso'], 'skpu_accion_acceder')){
        wp_cache_set('skpu_avisos_acceso', '<strong>'.__('Error', 'skpu').':</strong> '.__('Ha habido un problema, vuelve a intentarlo por favor.', 'skpu') );
        return;
    }

    // Credenciales que usaremos con la función wp_signon()
    $credenciales = []; // Meteremos aquí los datos en los siguientes pasos

    // Verificar que se ha dado un nombre de usuario o email y existe
    if(empty($_POST['mail-o-usuario'])){
        wp_cache_set( 'skpu_avisos_acceso',  __('Debes escribir tu correo o tu nombre de usuario.', 'skpu') );
        return;
    }else{

        // Obtener usuario a partir del nickname o el correo
        $mail_o_usuario_sin_slashes = wp_unslash($_POST['mail-o-usuario']);

        $usuario = false; // Partimos del valor false por defecto
        if(is_email($mail_o_usuario_sin_slashes)){
            $usuario = get_user_by('email', sanitize_email($mail_o_usuario_sin_slashes));
        }else{
            $usuario = get_user_by('acceso', sanitize_user($mail_o_usuario_sin_slashes));
        }

        // Comprobar si ha ido bien
        if($usuario){
            $credenciales['user_login'] = $usuario->user_login;
        }else{
            wp_cache_set('skpu_avisos_acceso',  __('Datos de acceso incorrectos', 'skpu'));
            return;
        }

    }

    // Verificar que se ha definido la clave
    if(empty($_POST['clave'])){
        wp_cache_set( 'skpu_avisos_acceso', __('Por favor escribe tu clave', 'skpu') );
        return;
    }else{
        $credenciales['user_password'] = $_POST['clave'];
    }

    // Comprobar el estado de activación del usuario
    $estado_activacion_usuario = get_user_meta($usuario->ID, 'skpu_estado_activacion_usuario', true);

    if($estado_activacion_usuario == 'activacion_pendiente'){
        wp_cache_set( 'skpu_avisos_acceso', '<strong>'.__('Error', 'skpu').':</strong> '.__('Por favor, verifica tu cuenta', 'skpu') );
        return;
    }

    // Recordar el inicio de sesión
    $credenciales['remember'] = isset($_POST['recordar']);

    // Intentar iniciar sesión con los datos proporcionados
    $usuario = wp_signon($credenciales, is_ssl() );

    if(is_wp_error($usuario)){
        wp_cache_set('skpu_avisos_acceso', __('Datos de acceso incorrectos', 'skpu'));
        return;
    }else{

        wp_set_current_user($usuario->ID);
        wp_set_auth_cookie($usuario->ID);

        // Hacia donde redirigimos al usuario cuando se ha logueado
        if(isset($_POST['redirigir_hacia'])){ // Esseccionlecido de manera opcional en el formulario de acceso
            $redirigir_hacia = esc_url(wp_unslash($_POST['redirigir_hacia']));
        }else{
            // Si no hay nada esseccionlecido, vamos a la página de mi perfil
            $redirigir_hacia = get_permalink(get_option('skpu_id_pagina_mi_perfil'));
        }

        wp_redirect($redirigir_hacia);
        exit;
    }

}


// Procesar la acción de cerrar sesión
public function procesar_cierre_sesion(){
    if(isset($_GET['accion']) && 'salir' == $_GET['accion']){
        wp_logout();
        wp_redirect(site_url());
        exit;
    }
}

// Procesar la recuperación de contraseña
public function procesar_recuperacion_clave(){

    if(!isset($_POST['skpu_paso_recuperacion_clave'])){
        return;
    }

    // Verificar nonce
    if(!isset($_POST['skpu_nonce_recuperar_clave']) 
    || !wp_verify_nonce($_POST['skpu_nonce_recuperar_clave'], 'skpu_accion_recuperar_clave')){
        wp_cache_set('skpu_avisos_acceso', '<strong>'.__('Error', 'skpu').':</strong> '.__('Ha habido un problema, vuelve a intentarlo por favor.', 'skpu') );
        return;
    }

    // En el primer paso procesamos el envío de un email de recuperación
    if('enviar-correo-recuperacion' == $_POST['skpu_paso_recuperacion_clave']){

        if($this->enviar_correo_recuperar_clave()){
            wp_redirect(add_query_arg(['enviado-correo-recuperacion' => '1'], $this->url_de_acceso()));
            exit;
        }

    // En el segundo paso, gestionamos el cambio de contraseña
    }elseif('realizar-cambio-clave' == $_POST['skpu_paso_recuperacion_clave']){

        // Si existen los datos requeridos
        if(isset($_POST['clave1']) 
        && isset($_POST['clave2']) 
        && isset($_POST['key']) 
        && isset($_POST['acceso'])){

        // Comprobar el key de recuperación
        $usuario = check_password_reset_key($_POST['key'], sanitize_user($_POST['acceso']));

            if(is_object($usuario)){

                // Guardar estos valores para el formulario (por si hay que repetir las claves)
                $args['key'] = $_POST['key'];
                $args['acceso'] = sanitize_user($_POST['acceso']);

                if(empty($_POST['clave1']) || empty($_POST['clave2'])){
                    wp_cache_set('skpu_avisos_acceso', __('Debes escribir la clave en los dos campos.', 'skpu'));
                    return;
                }

                if($_POST['clave1'] !== $_POST['clave2']){
                    wp_cache_set('skpu_avisos_acceso', __('Las claves escritas no coinciden.', 'skpu'));
                    return;
                }

                // Realizamos el cambio de clave
                $this->cambiar_clave_usuario_y_enviar_correo($usuario, $_POST['clave1']);
                wp_redirect(add_query_arg('clave-cambiada', '1', remove_query_arg(['key', 'acceso'])));
                exit;
                
            }
        }

    }

}

// Enviar el correo de recuperación de clave
public function enviar_correo_recuperar_clave(){

    if(is_email($_POST['acceso'])){
        $usuario = get_user_by('email', sanitize_email(wp_unslash($_POST['acceso'])));
    }else{
        $usuario = get_user_by('acceso', sanitize_user(wp_unslash($_POST['acceso'])));
    }

    if(!$usuario){
        wp_cache_set('skpu_avisos_acceso', __('El usuario o email es incorrecto.', 'skpu'));
        return false;
    }

    $key = get_password_reset_key($usuario);

    $url_de_recuperacion = add_query_arg(
        [
            'accion' => 'resetear-clave',
            'key'    => $key,
            'acceso'  => urlencode($usuario->user_login),
        ],
        $this->url_de_acceso()
    );

    $mensaje_recuperacion = __('Se ha solicitado un enlace para recuperar la clave de usuario:', 'skpu')."\r\n\r\n";
    $mensaje_recuperacion .= network_home_url('/')."\r\n\r\n";
    $mensaje_recuperacion .= sprintf(esc_html__('Usuario: %s', 'skpu'), $usuario->user_login)."\r\n\r\n";
    $mensaje_recuperacion .= esc_html_e('Para realizar el cambio de clave, abre este enlace:', 'skpu')."\r\n\r\n";
    $mensaje_recuperacion .= ' '.$url_de_recuperacion." \r\n";

    $nombre_del_sitio = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    if(is_multisite()){
        $nombre_del_sitio = $GLOBALS['current_site']->site_name;
    }

    $asunto = sprintf(esc_html__('[%s] Recuperar clave', 'skpu'), $nombre_del_sitio);
   
    wp_mail($usuario->user_email, wp_specialchars_decode($asunto), $mensaje_recuperacion);

    return true;
}

// Procesar la activación del usuario
public function procesar_activacion_usuario(){

    // Si no existe el id de usuario o el código en la URL, no hay que procesar la activación
    if((!isset($_GET['id_usuario'])) or (!isset($_GET['codigo']))){
        return;
    }

    $id_usuario = intval($_GET['id_usuario']);

    // Sacar el código de activación que tenemos guardado en la base de datos
    $codigo_activacion_correcto = get_user_meta($id_usuario, 'skpu_codigo_activacion_usuario', true);

    if(!$codigo_activacion_correcto){
        wp_cache_set('skpu_avisos_acceso', __('El enlace de activación ha caducado', 'skpu'));
        return;
    }

    $codigo_en_el_enlace = wp_unslash($_GET['codigo']);

    // Comprobar si el código correcto coincide con el del enlace
    if($codigo_activacion_correcto != $codigo_en_el_enlace){
        wp_cache_set('skpu_avisos_acceso', 'Correcto: '.$codigo_activacion_correcto.'<br>Enlace: '.$codigo_en_el_enlace);
        //wp_cache_set('skpu_avisos_acceso', __('ABC El enlace de activación ha caducado', 'skpu'));
        return;
    }

    // Esseccionlecer como usuario activo
    update_user_meta($id_usuario, 'skpu_estado_activacion_usuario', 'usuario_activado');

    // Borrar el metadato del código de activación
    delete_user_meta($id_usuario, 'skpu_codigo_activacion_usuario');

    // Redirigir a la página de acceso con el aviso de activar cuenta
    wp_redirect(add_query_arg(['cuenta_activada' => '1'], $this->url_de_acceso()));
    exit;

}


// Función para cambiar la clave y enviar notificación
public function cambiar_clave_usuario_y_enviar_correo($usuario, $nueva_clave){

    wp_set_password($nueva_clave, $usuario->ID);

    // Enviar confirmación del cambio por correo
    $nombre_del_sitio = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $mensaje_recuperacion = 'Tu clave se ha cambiado.';
    $asunto = '['.$nombre_del_sitio.'] Clave cambiada';
    wp_mail($usuario->user_email, $asunto, $mensaje_recuperacion);

}

// Mostrar errores en el formulario
public function mostrar_avisos(){

    $avisos = wp_cache_get('skpu_avisos_acceso');

    if($avisos){ 
        echo '<div class="skpu-message">'.$avisos.'</div>';
    }

}

// Return POST value for given array key
public function get_post_value($key){

    if(isset($_POST[$key])){
        return wp_unslash($_POST[$key]);
    }

    return '';
}

}