<?php
// Asegurarnos que se ha llamado a la desinstalación desde WordPress
if(!defined('WP_UNINSTALL_PLUGIN')) {
exit;
}

// Borrar los valores de configuración del plugin
function skpu_borrar_valores_configuracion(){
delete_option('skpu_id_pagina_acceso');
delete_option('skpu_id_pagina_registro');
delete_option('skpu_id_pagina_registro_finalizado');
delete_option('skpu_id_pagina_mi_perfil');
delete_option('skpu_id_pagina_editar_mi_perfil');
}
skpu_borrar_valores_configuracion();