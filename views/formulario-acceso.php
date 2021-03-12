<?php
if(isset($_GET['a'])){
	echo '<div class="skpu-mensaje-ok">'.esc_html(esc_attr__('Tu cuenta se ha activado correctamente.', 'skpu')).'</div>';
}

$Acceso = new WUP_Login();
$Acceso->mostrar_avisos();
?>

<form class="skpu-form" method="post">
	<ol>
		<li>
			<input type="text" name="mail-or-user" class="skpu-input" placeholder="<?php _e('Email o Usuario', 'skpu'); ?>" value="<?php echo esc_attr($Acceso->get_post_value('mail-or-user')); ?>">
		</li>
		<li>
			<input type="password" name="password" class="skpu-input" placeholder="<?php _e('Clave', 'skpu'); ?>">
		</li>
		<li>
			<input name="remember" id="remember" type="checkbox" value="forever">
			<label for="remember"><?php _e('Recordar', 'skpu'); ?></label>
		</li>
		<li>
			<?php wp_nonce_field('skpu_accion_acceder', 'nonce_para_acceso'); ?>
			<input type="submit" value="<?php _e('Acceder', 'skpu'); ?>" />
		</li>
	</ol>
</form>

<?php
echo $Acceso->generar_url_clave_perdida();
?>