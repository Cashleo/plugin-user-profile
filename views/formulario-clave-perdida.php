<?php
$Acceso = new WUP_Login();
$Acceso->mostrar_avisos();
?>

<form method="post" action="">
	<label for="skpu-user_acceso"><?php esc_attr_e('Username or E-mail:', 'skpu'); ?></label>
	<input type="text" name="acceso">
	<input type="hidden" name="skpu_paso_recuperacion_clave" value="enviar-correo-recuperacion">
	<?php wp_nonce_field('skpu_accion_recuperar_clave','skpu_nonce_recuperar_clave'); ?>
	<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Get New Password', 'skpu'); ?>" />
</form>