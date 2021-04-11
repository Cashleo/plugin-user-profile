<?php

// ACTIONS AND FILTERS

// Add custom fields to the edit user screen
add_action('show_user_profile', 'wup_custom_fields_edit_user_screen'); // My user
add_action('edit_user_profile', 'wup_custom_fields_edit_user_screen'); // Other user

// Handle saving the fields 
add_action('personal_options_update', 'wup_handle_user_custom_fields_save'); // My user
add_action('edit_user_profile_update', 'wup_handle_user_custom_fields_save'); // Other user

// Add custom columns to the user table
add_filter('manage_users_columns', 'wup_add_users_table_columns'); // Create columns
add_filter('manage_users_custom_column', 'wup_populate_users_table_columns', 10, 3); // Populate columns

// ACTIONS AND FILTERS CALLBACKS

function wup_custom_fields_edit_user_screen($user){
?>
<table class="form-table">
    <tr>
        <th>
            <label for="wup_user_activation_status"><?php _e('Status', 'wup'); ?></label>
        </th>
        <td>
            <?php
            $user_activation_status = get_user_meta($user->ID, 'wup_user_activation_status', true);
            ?>
            <select name="wup_user_activation_status">
                <option value="pending_activation" <?php echo ($user_activation_status=='pending_activation')?'selected':'';?>><?php _e('Pending', 'wup'); ?></option>
                <option value="is_active" <?php echo ($user_activation_status=='is_active')?'selected':'';?>><?php _e('Active', 'wup'); ?></option>
            </select>
        </td>
    </tr>
    
</table>
<?php
}


function wup_handle_user_custom_fields_save($user_id){
    if(current_user_can('edit_user', $user_id)){
        update_user_meta($user_id, 'wup_user_activation_status', sanitize_text_field($_POST['wup_user_activation_status']));
    }
}


function wup_add_users_table_columns($columns){
    $columns['wup_user_activation_status'] = __('Status', 'wup');
    return $columns;
}


function wup_populate_users_table_columns($column_content, $column_name, $user_id){
    $status='';
    switch($column_name){
        case 'wup_user_activation_status':
            $user_activation_status = get_user_meta($user_id, 'wup_user_activation_status', true);
            if('is_active' == $user_activation_status) {
                $status = __('Active', 'wup');
            }elseif('pending_activation' == $user_activation_status) {
                $status = __('Pending', 'wup');
            }else{
                $status = __('Other', 'wup');
            }
            return $status;
        break;
    }

    return $column_content;
}