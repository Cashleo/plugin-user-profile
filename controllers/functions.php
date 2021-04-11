<?php

// Include a template file (Looks up first on the theme directory or load default)
function wup_load_view($file, $args = []){
    $child_theme_dir = get_stylesheet_directory().'/user-profile/';
    $parent_theme_dir = get_template_directory().'/user-profile/';

    if(file_exists($child_theme_dir.$file)){
        include $child_theme_dir.$file;
    }elseif (file_exists($parent_theme_dir.$file)){
        include $parent_theme_dir.$file;
    }else{
        include plugin_dir_path(__DIR__).'views/'.$file;
    }
}