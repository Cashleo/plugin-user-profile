<?php
if(isset($_GET['a'])){ // Activated
	echo '<div class="wup-notice success">'.esc_html(__('Your account has been activated', 'wup')).'</div>';
}elseif(isset($_GET['pwu'])){ // Password updated
	echo '<div class="wup-notice success">'.esc_html(__('Your password has been updated, please login again', 'wup')).'</div>';
}

$Login = new WUP_Login();
$Login->maybe_display_notice();
?>

<form class="wup-form" method="post">
	<ol>
		<li>
			<input type="text" name="mail-or-user" placeholder="<?php _e('Email or Username', 'wup'); ?>" value="<?php echo esc_attr($Login->get_post_value('mail-or-user')); ?>">
		</li>
		<li>
			<input type="password" name="password" placeholder="<?php _e('Password', 'wup'); ?>">
		</li>
		<li>
			<input name="remember" id="remember" type="checkbox" value="forever">
			<label for="remember"><?php _e('Remember', 'wup'); ?></label>
		</li>
		<li>
			<?php wp_nonce_field('wup_login_action', 'login-nonce'); ?>
			<input type="submit" value="<?php _e('Login', 'wup'); ?>" />
		</li>
	</ol>
</form>

<?php
echo $Login->print_password_recovery_link();
?>