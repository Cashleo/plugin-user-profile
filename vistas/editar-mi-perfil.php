<?php
$Perfil = new WUP_Profile();
$user = wp_get_current_user();

// Cargar las secciones editables
$skpu_secciones = apply_filters(
    'skpu_secciones',
    []
);

// Hook para añadir acciones justo antes de mostrar los tabs (como por ejemplo guardar los cambios)
do_action('skpu_hook_antes_de_mostrar_secciones', $skpu_secciones, get_current_user_id());
?>

<ul>
<?php
foreach($skpu_secciones as $skpu_seccion){

echo '<li><a href="#'.$skpu_seccion['id'].'">'.$skpu_seccion['label'].'</a></li>';

}
?>
</ul>

<?php

// Loop through each item
foreach ($skpu_secciones as $skpu_seccion) {

// Build the content class
$content_class = '';

// If we have a class provided
if ('' != $skpu_seccion['content_class']){
$content_class .= ' '.$skpu_seccion['content_class'];
}

?>

<div class="skpu-seccion<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($skpu_seccion['id']); ?>">

    <form method="post" action="<?php echo esc_url($Perfil->url_de_editar_mi_perfil()).'#'.esc_attr($skpu_seccion['id']); ?>">
        <?php
            /* check if callback function exists */
        if (isset($skpu_seccion['callback']) && function_exists($skpu_seccion['callback'])) {
            /* use custom callback function */
            $skpu_seccion['callback']($skpu_seccion);
        } else {
            /* use default callback function */
            $Perfil->mostrar_seccion_editable($skpu_seccion);
        } ?>

        <?php
            wp_nonce_field(
                'skpu_nonce_action',
                'skpu_nonce_name'
            ); ?>
        <input type="submit" value="Guardar">
    </form>
</div>
<?php
} // end secciones loop
?>