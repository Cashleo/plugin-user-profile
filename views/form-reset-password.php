<?php
$Login = new WUP_Login();
$Login->maybe_display_notice();
?>

<form method="post">
    <input type="hidden" name="key" value="<?php echo esc_attr($_GET['key']); ?>">
    <input type="hidden" name="login" value="<?php echo isset($_GET['login']) ? sanitize_user($_GET['login']) : ''; ?>" />
    <input type="hidden" name="wup-password-recovery-step" value="execute-password-reset">

    <label for="skpu-password1"><?php esc_attr_e('New password', 'wup'); ?></label>
    <input autocomplete="off" name="password1" id="skpu-password1" value="" type="password">

    <label for="skpu-password2"><?php esc_attr_e('Confirm new password', 'wup'); ?></label>
    <input autocomplete="off" name="password2" id="skpu-password2" value="" type="password">

    <?php wp_nonce_field('wup_password_recovery_action','nonce-password-recovery'); ?>
    <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Update password', 'wup'); ?>" />
</form>