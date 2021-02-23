<?php

//Include a template file (Looks up first on the theme directory or load default)
function skpu_cargar_vista($file, $args = []){
    $child_theme_dir = get_stylesheet_directory().'/skpu/';
    $parent_theme_dir = get_template_directory().'/skpu/';
    $skpu_dir = plugin_dir_path(__DIR__).'vistas/';

    if(file_exists($child_theme_dir.$file)){
        include $child_theme_dir.$file;
    }elseif (file_exists($parent_theme_dir.$file)){
        include $parent_theme_dir.$file;
    }else{
        include $skpu_dir.$file;
    }
}