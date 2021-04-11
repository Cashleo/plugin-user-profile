<?php
$Registration = new WUP_Registration();
$Registration->maybe_display_notice();
?>

<form method="post">
	<ol>
		<li>
			<input type="text" name="user_first_name" placeholder="<?php _e('First name', 'wup'); ?>" value="<?php echo esc_attr($Registration->get_post_value('user_first_name')); ?>">
		</li>
		<li>
			<input type="text" name="user_last_name" placeholder="<?php _e('Last name', 'wup'); ?>" value="<?php echo esc_attr($Registration->get_post_value('user_last_name')); ?>">
		</li>
		<li>
			<input type="email" required name="user_email" placeholder="<?php _e('E-mail', 'wup'); ?>" value="<?php echo esc_attr($Registration->get_post_value('user_email')); ?>">
		</li>
		<li>
			<input type="password" name="password1" placeholder="<?php _e('Password', 'wup'); ?>" value="">
		</li>
		<li>
			<input type="password" name="password2" placeholder="<?php _e('Repeat', 'wup'); ?>" value="">
		</li>
		<li>
			<?php wp_nonce_field('wup_registration_action', 'registration-nonce'); ?>
			<input type="submit" value="<?php _e('Create account', 'wup'); ?>">
		</li>
	</ol>
</form>