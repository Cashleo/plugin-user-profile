<?php
$Login = new WUP_Login();
$Login->maybe_display_notice();
?>

<form method="post" action="">
	<label for="login"><?php esc_attr_e('Username or E-mail:', 'wup'); ?></label>
	<input type="text" name="login">
	<input type="hidden" name="wup-password-recovery-step" value="send-recovery-link">
	<?php wp_nonce_field('wup_password_recovery_action','nonce-password-recovery'); ?>
	<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Reset password', 'wup'); ?>" />
</form>