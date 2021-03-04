<?php
$Acceso = new WUP_Login();
$Acceso->mostrar_avisos();
?>

<form method="post">

<input type="hidden" name="key" value="<?php echo esc_attr($_GET['key']); ?>">
<input type="hidden" name="acceso" value="<?php echo isset($_GET['acceso']) ? sanitize_user($_GET['acceso']) : ''; ?>" />
<input type="hidden" name="skpu_paso_recuperacion_clave" value="realizar-cambio-clave">

<label for="skpu-password1"><?php esc_attr_e('New password', 'skpu'); ?></label>
<input autocomplete="off" name="password1" id="skpu-password1" value="" type="password">

<label for="skpu-password2"><?php esc_attr_e('Confirm new password', 'skpu'); ?></label>
<input autocomplete="off" name="password2" id="skpu-password2" value="" type="password">

<?php wp_nonce_field('skpu_accion_recuperar_clave','skpu_nonce_recuperar_clave'); ?>

<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Reset Password', 'skpu'); ?>" />

</form>