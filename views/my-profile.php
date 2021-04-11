<?php
$Profile = new WUP_Profile();
$current_user = wp_get_current_user();
?>

<!-- Change this to whatever fits your needs -->

<!-- Just some heading -->
<h1><?php _e('My profile', 'wup'); ?></h1>
<h2><?php echo esc_html($current_user->user_email); ?></h2>

<!-- Edit my profile link -->
<a href="<?php echo esc_url($Perfil->edit_my_profile_url()); ?>">
<?php _e('Edit my profile', 'wup'); ?>
</a>

<!-- logout link -->
<a href="<?php echo wp_logout_url(); ?>">
<?php _e('Logout', 'wup'); ?>
</a>