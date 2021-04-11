<?php
if(isset($_GET['a'])){
	echo '<div class="wup-notice success">'.esc_html(__('Your account has been activated', 'wup')).'</div>';
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
echo $Login->generate_password_recovery_url();
?>