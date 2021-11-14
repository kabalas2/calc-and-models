<?php
/**
 * Plugin Name: Filter and Calculator
 * Description:
 * Version: 1.0
 * Author: Your mom's "favorite" Developer
 * Author URI: http://developer.xyz
 */

register_activation_hook(__FILE__, 'child_plugin_activate');
function child_plugin_activate()
{
    if (!is_plugin_active('advanced-custom-fields/acf.php') and current_user_can('activate_plugins')) {
        wp_die('Sorry, but this plugin requires the ACF to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }
}

function create_posttype()
{
    register_post_type('variations',
        array(
            'labels' => array(
                'name' => __('Variations'),
                'singular_name' => __('Variations')
            ),
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => 'variations'),
        )
    );
}

// Hooking up our function to theme setup
add_action('init', 'create_posttype');

function getProductVariations($id)
{
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'variations',
        'meta_key' => 'parent_product_id',
        'meta_value' => $id
    ));

    return $posts;
}

add_action('woocommerce_after_single_product_summary', 'kabal_add_models_info');
add_action('woocommerce_product_thumbnails', 'kabal_add_models', 100);

function kabal_add_models()
{

    global $product;
    $id = $product->get_id();
    $models = getProductVariations($id);
    $output = '';
    // $output .= '<div><p class="tech-data">TECHNICAL DATA:</p></div>';
    $output .= '<div class="models-wrapper">';
    $active = '';
    foreach ($models as $model) {
        if (isset($GET['model']) && $GET['model'] == $model->post_name) {
            $active = 'active';
        }
        $output .= '<div data-slug="' . $model->post_name . '" data-modelid="' . $model->ID . '"';
        $output .= 'class="model-box ' . $active . '">';
        $output .= $model->post_title . '</div>';
    }
    $output .= '</div>';
    echo $output;
}

function kabal_add_models_info()
{
    global $product;
    $id = $product->get_id();
    $models = getProductVariations($id);
    $output = '<div class="models-info-clear"></div>';
    $output .= '<div class="models-info">';
    foreach ($models as $model) {
        $output .= '<div id="info-box-' . $model->ID . '" style="display:none"  class="model-info-box">' . $model->post_content . '</div>';
    }
    $output .= '</div>';
    echo $output;

}

add_action('wp_enqueue_scripts', 'kabal_adding_js');
function kabal_adding_js()
{
    wp_enqueue_script('model-boxes-js', plugin_dir_url(__FILE__) . 'js/model-boxes.js', array('jquery'));
    wp_enqueue_style('model-box-styles', plugin_dir_url(__FILE__) . 'css/model-boxes.css');
}





