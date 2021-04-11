<?php
$Login = new WUP_Login();
$Login->maybe_display_notice();
?>

<form method="post">
    <input type="hidden" name="key" value="<?php echo esc_attr($_GET['key']); ?>">
    <input type="hidden" name="login" value="<?php echo esc_attr($_GET['login']); ?>" />
    <input type="hidden" name="wup-password-recovery-step" value="execute-password-reset">

    <label for="new-password"><?php esc_attr_e('New password', 'wup'); ?></label>
    <input autocomplete="off" name="new-password" value="" type="password">

    <?php wp_nonce_field('wup_password_recovery_action','nonce-password-recovery'); ?>
    <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Update password', 'wup'); ?>" />
</form>