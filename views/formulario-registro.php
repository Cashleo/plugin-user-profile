<?php
$Registro = new WUP_Registration();
$Registro->mostrar_avisos();
?>

<form method="post">
	<ol>
		<li>
			<input type="text" name="usuario_nombre_pila" placeholder="<?php _e('Nombre', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('usuario_nombre_pila')); ?>">
		</li>
		<li>
			<input type="text" name="usuario_apellidos" placeholder="<?php _e('Apellidos', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('usuario_apellidos')); ?>">
		</li>
		<li>
			<input type="email" required name="usuario_email" placeholder="<?php _e('E-mail', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('usuario_email')); ?>">
		</li>
		<li>
			<input type="text" name="usuario_apodo" placeholder="<?php _e('Apodo', 'skpu'); ?>" value="<?php echo esc_attr($Registro->get_post_value('usuario_apodo')); ?>">
		</li>
		<li>
			<input type="password" name="clave1" placeholder="<?php _e('Clave', 'skpu'); ?>" value="">
		</li>
		<li>
			<input type="password" name="clave2" placeholder="<?php _e('Repetir clave', 'skpu'); ?>" value="">
		</li>
		<li>
			<?php wp_nonce_field('skpu_accion_registrarse', 'nonce_para_registro'); ?>
			<input type="submit" value="<?php _e('Crear cuenta', 'skpu'); ?>">
		</li>
	</ol>
</form>