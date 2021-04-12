<?php
$Login = new WUP_Login();
$Login->maybe_display_notice();
?>

<form method="post" action="">
	<label for="mail-or-user"><?php esc_attr_e('E-mail or username:', 'wup'); ?></label>
	<input type="text" name="mail-or-user">
	<input type="hidden" name="wup-password-recovery-step" value="send-recovery-link">
	<?php wp_nonce_field('wup_password_recovery_action','nonce-password-recovery'); ?>
	<input type="submit" value="<?php esc_attr_e('Reset password', 'wup'); ?>" />
</form>