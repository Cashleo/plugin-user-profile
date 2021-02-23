<?php
$Perfil = new WUP_Profile();
$usuario_actual = wp_get_current_user();
?>

<a href="<?php echo wp_logout_url(); ?>">Salir</a>
<hr>


<p>
<strong><?php _e('Email', 'skpu'); ?>: </strong>
<?php echo esc_html($usuario_actual->user_email); ?></p>


<a class="btn" href="<?php echo esc_url($Perfil->url_de_editar_mi_perfil()); ?>"><?php _e('Editar mi perfil', 'skpu'); ?></a>