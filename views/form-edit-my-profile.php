<?php
$Profile = new WUP_Profile();
$user = wp_get_current_user();

// Load all editable sections
$sections = apply_filters(
    'wup_editable_sections',
    []
);

// Action hook before displaying sections
do_action('wup_hook_before_displaying_sections', $sections, get_current_user_id());

$Profile->maybe_display_notice();
?>

<ul>
<?php
foreach($sections as $section){
echo '<li><a href="#'.$section['id'].'">'.$section['label'].'</a></li>';
}
?>
</ul>

<?php
// Loop through each item
foreach($sections as $section){

// Build the content class
$content_class = '';

// If we have a class provided
if('' != $section['content_class']){
$content_class .= ' '.$section['content_class'];
}
?>

<div class="wup-section<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($section['id']); ?>">

    <form method="post" action="<?php echo esc_url($Profile->edit_my_profile_url()); ?>" enctype='multipart/form-data'>
        <?php
        // Check if this section has a custom callback function
        if(isset($section['callback']) && function_exists($section['callback'])) {
            // Use custom callback function
            $section['callback']($section);
        }else{
            // Use default callback function
            $Profile->display_editable_section($section);
        }
        
        wp_nonce_field(
            'wup_edit_profile_action',
            'edit-profile-nonce'
        );
        ?>
        <input type="submit" value="<?php _e('Save', 'wup'); ?>">
    </form>
</div>
<?php
}
?>