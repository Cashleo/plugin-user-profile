<?php
$Registro = new WUP_Registration();
$Registro->mostrar_avisos();
?>

<form method="post">
	<ol>
		<li>
			<input type="text" name="user_first_name" placeholder="<?php _e('Nombre', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('user_first_name')); ?>">
		</li>
		<li>
			<input type="text" name="user_last_name" placeholder="<?php _e('Apellidos', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('user_last_name')); ?>">
		</li>
		<li>
			<input type="email" required name="user_email" placeholder="<?php _e('E-mail', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('user_email')); ?>">
		</li>
		<li>
			<input type="text" name="usuario_apodo" placeholder="<?php _e('Apodo', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('usuario_apodo')); ?>">
		</li>
		<li>
			<input type="password" name="password1" placeholder="<?php _e('Clave', 'skpu'); ?>" value="">
		</li>
		<li>
			<input type="password" name="password2" placeholder="<?php _e('Repetir clave', 'skpu'); ?>" value="">
		</li>
		<li>
			<?php wp_nonce_field('skpu_accion_registrarse', 'nonce_para_registro'); ?>
			<input type="submit" value="<?php _e('Crear cuenta', 'skpu'); ?>">
		</li>
	</ol>
</form>