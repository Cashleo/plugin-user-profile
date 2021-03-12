<?php

// ACCIONES Y FILTROS

// Agregar campos personalizados a la zona de admin de editar usuario
add_action('show_user_profile', 'skpu_agregar_campos_personalizados_zona_usuario'); // Mi usuario
add_action('edit_user_profile', 'skpu_agregar_campos_personalizados_zona_usuario'); // Otro usuario

// Guardar los campos personalizados al darle al botón de Guardar 
add_action('personal_options_update', 'skpu_guardar_campos_personalizados_zona_usuario'); // Mi usuario
add_action('edit_user_profile_update', 'skpu_guardar_campos_personalizados_zona_usuario'); // Otro usuario

// Agregar columnas personalizadas a la tabla de Usuarios en la zona admin
add_filter('manage_users_columns', 'agregar_columnas_tabla_usuarios'); // Nombre columna
add_filter('manage_users_custom_column', 'contenido_columnas_tabla_usuarios', 10, 3); // Contenido columna

// FUNCIONES ASOCIADAS A LAS ACCIONES Y FILTROS

function skpu_agregar_campos_personalizados_zona_usuario($usuario){
?>
<table class="form-table">
    <tr>
        <th>
            <label for="wup_user_activation_status"><?php _e('Estado', 'skpu'); ?></label>
        </th>
        <td>
            <?php
            $estado_activacion_usuario = esc_attr(get_user_meta($usuario->ID, 'wup_user_activation_status', true));
            ?>
            <select name="wup_user_activation_status">
                <option value="activacion_pendiente" <?php echo ($estado_activacion_usuario=='activacion_pendiente')?'selected':'';?>><?php _e('Pendiente', 'skpu'); ?></option>
                <option value="usuario_activado" <?php echo ($estado_activacion_usuario=='usuario_activado')?'selected':'';?>><?php _e('Activado', 'skpu'); ?></option>
            </select>
        </td>
    </tr>
    
</table>
<?php
}


function skpu_guardar_campos_personalizados_zona_usuario($id_usuario){
    if(current_user_can('edit_user', $id_usuario)){
        update_user_meta($id_usuario, 'wup_user_activation_status', sanitize_text_field($_POST['wup_user_activation_status']));
    }
}



function agregar_columnas_tabla_usuarios($columnas){
    $columnas['wup_user_activation_status'] = __('Estado', 'skpu');
    return $columnas;
}


function contenido_columnas_tabla_usuarios($valor_columna, $nombre_columna, $id_usuario){
    $status='';
    switch($nombre_columna){
        case 'wup_user_activation_status':
            $estado_activacion_usuario = get_user_meta($id_usuario, 'wup_user_activation_status', true);
            if('usuario_activado' == $estado_activacion_usuario) {
                $status = __('Activado', 'skpu');
            }elseif('activacion_pendiente' == $estado_activacion_usuario) {
                $status = __('Pendiente', 'skpu');
            }else{
                $status = __('Registro via WP', 'skpu');
            }
            return $status;
        break;
    }

    return $valor_columna;
}