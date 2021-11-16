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

add_action('init', 'create_posttype');

function getProductVariations($id)
{
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'variations',
        'suppress_filters' => false,
        'meta_key'			=> 'position',
        'orderby'			=> 'meta_value',
        'order'				=> 'ASC',
        'meta_query' => array(
            array(
                'key' => 'parent_product_id',
                'value' => $id, 
            )
        )
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
    if(count($models)) {
        $output .= '<div><p class="tech-data">' . __('TECHNICAL DATA:') . '</p></div>';
        $output .= '<div class="models-wrapper">';
        $active = '';
        $sortedModels = [];
        foreach ($models as $model) {
            if ($model->indooroutdoor_units == 0) {
                $output .= renderBlock($model);
            } else {
                $sortedModels[$model->indooroutdoor_units][] = $model;
            }
        }
        if (count($sortedModels[1])) {
            $output .= '<div>';
            $output .= __('Indors:');
            foreach ($sortedModels[1] as $model) {
                $output .= renderBlock($model);
            }
            $output .= '</div>';
        }
        if (count($sortedModels[2])) {
            $output .= '<div>';
            $output .= __('Outdoors:');
            foreach ($sortedModels[2] as $model) {
                $output .= renderBlock($model);
            }
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    echo $output;
    //echo do_shortcode('[models_calculator]');
}

function renderBlock($model)
{
    $output = '';
    if (isset($GET['model']) && $GET['model'] == $model->post_name) {
        $active = 'active';
    }
    $output .= '<div data-slug="' . $model->post_name . '" data-modelid="' . $model->ID . '"';
    $output .= 'class="model-box ' . $active . '">';
    $output .= $model->post_title . '</div>';
    return $output;
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
//    if ( is_admin() ) {

//    }
    wp_enqueue_script('model-boxes-js', plugin_dir_url(__FILE__) . 'js/model-boxes.js', array('jquery'));
    wp_enqueue_style('model-box-styles', plugin_dir_url(__FILE__) . 'css/model-boxes.css');
    wp_localize_script('model-boxes-js', 'models_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

}

add_action('wp_ajax_get_models', 'get_models');
add_action('wp_ajax_nopriv_get_models', 'get_models');


function kabal_calc_shortcode()
{
    $output = '';
    $output .= '<div class="calc-form-wrapper">';
        $output .= '<div class="top">';
            $output .= '<div class="box">';
                $output .= '<label for="area">' . __('Heated area:') . '</label>';
                $output .= '<input type="number" placeholder="m2" name="area" id="area">';
            $output .= '</div>';
            $output .= '<div class="box">';
                $output .= '<label for="kw">' . __('Enter the heat demand for heating:') . '</label>';
                $output .= '<input type="number" placeholder="W/m2" name="kw" id="kw">';
            $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="mid">';
            $output .= '<span>' . __('Or choose the energy class of your home') . '</span>';
            $output .= '<div class="box">';
                $output .= '<input type="radio" value="20" name="energy_class" id="energy_class_app">';
                $output .= '<label for="energy_class_app">A++</label>';
                $output .= '<input type="radio" value="25" name="energy_class" id="energy_class_ap">';
                $output .= '<label for="energy_class_ap">A+</label>';
                $output .= '<input type="radio" value="30" name="energy_class" id="energy_class_a">';
                $output .= '<label for="energy_class_a">A</label>';
                $output .= '<input type="radio" value="60" name="energy_class" id="energy_class_b">';
                $output .= '<label for="energy_class_b">B</label>';
                $output .= '<input type="radio" value="80" name="energy_class" id="energy_class_c">';
                $output .= '<label for="energy_class_c">C</label>';
                $output .= '<input type="radio" value="100" name="energy_class" id="energy_class_d">';
                $output .= '<label for="energy_class_d">D</label>';
            $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="mid-bottom">';
            $output .= '<span>'. __('Will the heat pump prepare domestic hot water?').'</span>';
            $output .= '<div class="box">';
                $output .= '<input type="radio" value="1" name="hot_water" id="hot_water_yes"> ';
                $output .= '<label for="hot_water_yes">Yes</label>';
                $output .= '<input type="radio" value="0" name="hot_water" id="hot_water_no"> ';
                $output .= '<label for="hot_water_yes">No</label>';
            $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="calc-form-wrapper-bottom">';
            $output .= '<div class="box">';
                $output .= '<label for="water">' . __('Hot water consumption m3 / month:') . '</label>';
                $output .= '<input type="number" placeholder="m3" name="water" id="water">';
            $output .= '</div>';
            $output .= '<div class="box">';
                $output .= '<span>'.__('Is the hot water tank integrated?').'</span>';
                $output .= '<input type="radio" value="1" name="hot_water_tank" id="hot_water_tank_yes"> ';
                $output .= '<label for="hot_water_tank_yes">Yes</label>';
                $output .= '<input type="radio" value="0" name="hot_water_tank" id="hot_water_tank_no"> ';
                $output .= '<label for="hot_water_tank_no">No</label>';
            $output .= '</div>';
        $output .= '</div>';
        $output .= '<input type="submit" value="calculate" name="calculate" id="calculate">';
    $output .= '</div>';
    $output .= '<div class="results-wrapper">';
    $output .= __('According to the data provided by you, the device is offered: ');
    $output .= '<div class="results"></div>';
    $output .= '<div class="tank-sugest tank-200">'.__('According to your information, the domestic hot water tank "SWP-200" is offered').'</div>';
    $output .= '<div class="tank-sugest tank-300">'.__('According to your information, the domestic hot water tank "SWP-300" is offered').'</div>';
    $output .= '</div>';
    return $output;

}

function get_models()
{
    $power = (int)$_POST['power'];
    $tank = (int)$_POST['tank'];

    if ($power < 7) {
        $powerMax = 6;
    } elseif ($power >= 7 && $power < 11) {
        $powerMax = 10;
    } elseif ($power >= 11 && $power <= 16) {
        $powerMax = 16;
    }
    $filters = [];
    $filters[] = [
        'key' => 'power',
        'value' => $powerMax,
        'type' => 'NUMERIC',
        'compare' => '='
    ];
    $filters[] = [
        'key' => 'water_tank',
        'value' => $tank,
        'type' => 'NUMERIC',
        'compare' => '='
    ];

    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type' => 'variations',
        'meta_query' => [
            'relation' => 'AND',
            $filters
        ]
    ));

    if (count($posts)) {
        $output = [];
        foreach ($posts as $post) {
            $product = wc_get_product($post->parent_product_id);
            $link = $product->get_permalink() . '?model=' . $post->post_name;
            //$output .= '<a href="' . $link . '">' . $post->post_title . '</a>';
            $output[] = [
                'link' => $link,
                'title' => $post->post_title
            ];
        }
        echo json_encode($output);
    } else {
        echo 0;
    }
    wp_die();
}


add_shortcode('models_calculator', 'kabal_calc_shortcode');





