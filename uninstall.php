<?php
// Asegurarnos que se ha llamado a la desinstalación desde WordPress
if(!defined('WP_UNINSTALL_PLUGIN')) {
exit;
}

// Borrar los valores de configuración del plugin
function skpu_borrar_valores_configuracion(){
delete_option('wup_page_id_for_login');
delete_option('wup_page_id_for_registration');
delete_option('wup_page_id_for_registration_finished');
delete_option('wup_page_id_for_show_my_profile');
delete_option('wup_page_id_for_edit_my_profile');
}
skpu_borrar_valores_configuracion();