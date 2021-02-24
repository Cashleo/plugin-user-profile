<?php

// ACCIONES Y FILTROS

// Crear la página de ajustes
add_action('admin_menu', 'skpu_pagina_de_ajustes');

// Crear las secciones y campos editables
add_action('admin_init', 'skpu_crear_secciones_y_campos');

// FUNCIONES ASOCIADAS A LAS ACCIONES Y FILTROS

function skpu_pagina_de_ajustes(){
    add_options_page(
    'Ajustes de perfiles',        /* Título de la página de ajustes */
    'Perfiles',                   /* Nombre que aparece en el menú */
    'manage_options',             /* Permiso necesario para ver este menú */
    'pagina_ajustes_perfiles',    /* Identificador único para esta página*/
    'mostrar_ajustes_perfiles',   /* Nombre de la función que muestra el contenido */
    3                             /* Posición de este menú en el panel lateral */
    );
}

function skpu_crear_secciones_y_campos(){

    // Crear una sección de ajustes
    add_settings_section(
    'seccion_ajustes_paginas',                     /* Identificador de la sección */
    'Páginas',                                     /* Título de la sección */
    'mostrar_encabezado_seccion_ajustes_paginas',  /* Función para mostrar contenido de cabecera */
    'pagina_ajustes_perfiles'                      /* Página de ajustes a la que va asociada */
    );

    // Opción para página de acceso
    register_setting(
    'skpu_ajustes',          /* Nombre del grupo de opciones */
    'wup_page_id_for_login'  /* Nombre de la opción */
    );

    add_settings_field(
    'wup_page_id_for_login',             /* Identificador único de este campo */
    'Id de la página Acceder',           /* Título que se muestra junto a este campo */
    'mostrar_input_pagina_de_acceso',    /* Función que imprime el input para el formulario */
    'pagina_ajustes_perfiles',           /* Página de ajustes a la que va asociado */
    'seccion_ajustes_paginas'            /* Sección de ajustes a la que va asociado */
    );

    // Opción para página de registro
    register_setting(
    'skpu_ajustes',            /* Nombre del grupo de opciones */
    'wup_page_id_for_registration'  /* Nombre de la opción */
    );

    add_settings_field(
    'wup_page_id_for_registration',           /* Identificador único de este campo */
    'Id de la página Registrarse',       /* Título que se muestra junto a este campo */
    'mostrar_input_pagina_de_registro',  /* Función que imprime el input para el formulario */
    'pagina_ajustes_perfiles',           /* Página de ajustes a la que va asociado */
    'seccion_ajustes_paginas'            /* Sección de ajustes a la que va asociado */
    );

    // Opción para página de registro finalizado
    register_setting(
    'skpu_ajustes',                       /* Nombre del grupo de opciones */
    'wup_page_id_for_registration_finished'  /* Nombre de la opción */
    );

    add_settings_field(
    'wup_page_id_for_registration_finished',           /* Identificador único de este campo */
    'Id de la página de Registro Finalizado',       /* Título que se muestra junto a este campo */
    'mostrar_input_pagina_de_registro_finalizado',  /* Función que imprime el input para el formulario */
    'pagina_ajustes_perfiles',                      /* Página de ajustes a la que va asociado */
    'seccion_ajustes_paginas'                       /* Sección de ajustes a la que va asociado */
    );

    // Opción para página mi perfil
    register_setting(
    'skpu_ajustes',             /* Nombre del grupo de opciones */
    'wup_page_id_for_show_my_profile'  /* Nombre de la opción */
    );

    add_settings_field(
    'wup_page_id_for_show_my_profile',          /* Identificador único de este campo */
    'Id de la página Mi Perfil',         /* Título que se muestra junto a este campo */
    'mostrar_input_pagina_mi_perfil',    /* Función que imprime el input para el formulario */
    'pagina_ajustes_perfiles',           /* Página de ajustes a la que va asociado */
    'seccion_ajustes_paginas'            /* Sección de ajustes a la que va asociado */
    );

    // Opción para página editar mi perfil
    register_setting(
    'skpu_ajustes',                    /* Nombre del grupo de opciones */
    'wup_page_id_for_edit_my_profile'  /* Nombre de la opción */
    );

    add_settings_field(
    'wup_page_id_for_edit_my_profile',       /* Identificador único de este campo */
    'Id de la página Editar Perfil',         /* Título que se muestra junto a este campo */
    'mostrar_input_pagina_editar_mi_perfil', /* Función que imprime el input para el formulario */
    'pagina_ajustes_perfiles',               /* Página de ajustes a la que va asociado */
    'seccion_ajustes_paginas'                /* Sección de ajustes a la que va asociado */
    );

}

// Función que muestra el texto de encabezado de la sección
function mostrar_encabezado_seccion_ajustes_paginas(){
    echo '<p>En esta sección especificamos los id de las páginas de acceso, registro, perfil, etc...</p>';
}

// Función que muestra el input de página de acceso
function mostrar_input_pagina_de_acceso(){
    echo '<input
    type="number"
    name="wup_page_id_for_login"
    value="'.esc_attr(get_option('wup_page_id_for_login')).'" />';
}

// Función que muestra el input de página de registro
function mostrar_input_pagina_de_registro(){
    echo '<input
    type="number"
    name="wup_page_id_for_registration"
    value="'.esc_attr(get_option('wup_page_id_for_registration')).'" />';
}

// Función que muestra el input de página de registro finalizado
function mostrar_input_pagina_de_registro_finalizado(){
    echo '<input
    type="number"
    name="wup_page_id_for_registration_finished"
    value="'.esc_attr(get_option('wup_page_id_for_registration_finished')).'" />';
}

// Función que muestra el input de página mi perfil
function mostrar_input_pagina_mi_perfil(){
    echo '<input
    type="number"
    name="wup_page_id_for_show_my_profile"
    value="'.esc_attr(get_option('wup_page_id_for_show_my_profile')).'" />';
}

// Función que muestra el input de página editar mi perfil
function mostrar_input_pagina_editar_mi_perfil(){
    echo '<input
    type="number"
    name="wup_page_id_for_edit_my_profile"
    value="'.esc_attr(get_option('wup_page_id_for_edit_my_profile')).'" />';
}

// Función que muestra el contenido de la página de ajustes
function mostrar_ajustes_perfiles(){
    echo '
    <h1>Ajustes de Perfiles</h1>
    <p>Aquí puedes poner lo que te dé la gana.</p>
    ';

    // Formulario de opciones
    echo '<form action="options.php" method="post">';

    // Decirle a WordPress que genere los campos
    settings_fields('skpu_ajustes');                  /* Id del grupo de opciones */
    do_settings_sections('pagina_ajustes_perfiles');  /* Id de la página de ajustes */

    // Mostrar el botón de Guardar y cerrar el formulario
    echo '
    <input
      type="submit"
      name="submit"
      class="button button-primary"
      value="Guardar"
    />
    </form>';
}