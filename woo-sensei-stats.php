<?php
/*
* Plugin Name: Woo Sensei Analytics
* Version: 1.2.0
* Plugin URI: http://www.wpexperts.io/
* Description: Display Sensei Analytics
* Author: wpexpertsio
* Author URI: https://www.wpexperts.io
*/

include 'includes/sensei_get_results.php';

// Check Sensei Is Active or not
function wsa_sensei_deactive_error() {

    if( !class_exists( 'Sensei_Main' ) ) {

        deactivate_plugins( plugin_basename( __FILE__ ) );

        $class = 'notice notice-error';
        $message = __( 'Error! Woothemes Sensei not Active or installed. Please installed Woothemes Sensei <b> - `Woo Sensei Analytics` Deactivated </b>', 'wsa' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
    }

}

add_action( 'admin_notices', 'wsa_sensei_deactive_error', 20 );

function wsa_scripts() {
    wp_enqueue_script( 'wss-script-sensei', plugin_dir_url( __FILE__ ) . '/js/script.js');
    wp_enqueue_script( 'wss-script-chart', plugin_dir_url( __FILE__ ) . '/js/google-charts.js');
}
add_action( 'admin_enqueue_scripts', 'wsa_scripts' );


function wsa_loader_script() {
    ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {'packages':['line', 'corechart']});
    </script>
<?php
}

add_action('admin_enqueue_scripts','wsa_loader_script');

/*
 * Sensei Graphs
 */
function wsa_sensei_dashboard_widgets() {

    add_meta_box(
        'wsa_sensei_graphs'
        ,'Sensei Analytics <span class="dashicons dashicons-chart-pie"></span>'
        ,'wsa_dashboard_sensei_charts'
        ,get_current_screen() // Take a look at the output of `get_current_screen()` on your dashboard page
        ,'normal' // Valid: 'side', 'advanced'
        ,'high' // Valid: 'default', 'high', 'low'
    );
}

function wsa_dashboard_sensei_charts() {

    do_action('wsa_before_graphs');

    $settings = get_option('woothemes-sensei-settings');

    if($settings['hide_info_labels'] == 0)
        $legend = 'labeled';
    else
        $legend = '';

    if($settings['show_3d_charts'] == 1)
        $is_3d = true;
    else
        $is_3d = false;

    if($settings['active_course'] == 1) {
        $sensei_stuff = new wsa_sensei_stuff();
        echo $sensei_stuff->sensei_active_courses('active_courses', 'Active Courses',"['orange', 'green','brown']", $legend, $is_3d); ?>

        <div id="active_courses" style="width: auto;padding: 0px; height: 200px;float:left"></div> <?php

    }

    if($settings['complete_course'] == 1) {
        $sensei_stuff = new wsa_sensei_stuff();
        echo $sensei_stuff->sensei_complete_courses('complete_courses', 'Complete Courses', "['#EF018D', '#00ADF1','brown']", $legend, $is_3d); ?>

        <div id="complete_courses" style="width: auto;padding: 0px; height: 200px;float:left"></div> <?php
    }

    if($settings['graded_ungraded'] == 1) {
        $sensei_stuff = new wsa_sensei_stuff();
        echo $sensei_stuff->sensei_lesson_graded('graded_ungraded','Graded / Ungraded Lessons',"['red', 'green']", $legend, $is_3d); ?>

        <div id="graded_ungraded" style="width: auto;padding: 0px; height: 200px;;float:left"></div> <?php
    }

    if($settings['failure_courses'] == 1) {
        $sensei_stuff = new wsa_sensei_stuff();
        echo $sensei_stuff->sensei_failed_courses('failure_Lessons','Failures Per Lesson',"['#F00000','#A80000','#C80000']", $legend, $is_3d); ?>

        <div id="failure_Lessons" style="width: auto;padding: 0px; height: 200px;float:left"></div> <?php
    }

    if($settings['purchased_courses'] == 1) {
        $sensei_stuff = new wsa_sensei_stuff();
        echo $sensei_stuff->sensei_most_sell_courses('purchased_courses','Purchased Courses',"['orange', 'green','brown']", $legend, $is_3d); ?>

        <div id="purchased_courses" style="width: auto;padding: 0px; height: 200px;float:left"></div> <?php
    }

    if($settings['enrolled_months'] == 1) {
        $sensei_stuff = new wsa_sensei_stuff();
        echo $sensei_stuff->sensei_enrolled_user_by_month('monthly_enrolled','Sensei Lesson Graded',"['orange', 'pink','lightgreen']"); ?>

        <div id="monthly_enrolled" style="width: 90%;padding: 0px; height: 200px;float:left"></div> <?php
    }

    do_action('wsa_after_graphs');
}

add_action( 'wp_dashboard_setup', 'wsa_sensei_dashboard_widgets' );

add_action( 'admin_head-index.php', 'wsa_sensei_graph_layout' );

function wsa_sensei_graph_layout() {
    add_screen_option(
        'layout_columns',
        array(
            'max'     => 2,
            'default' => 1
        )
    );
}

add_action( 'admin_head-index.php', 'wsa_style_dashboard');

function wsa_style_dashboard()
{
    ?>
    <style>
        div#wsa_sensei_graphs {
            height: 800px;
            width: 100%;
        }
    </style>
<?php
}

add_filter('sensei_settings_tabs', 'wsa_settings_graph');

function wsa_settings_graph( $sections ) {

    $sections['graph-settings'] = array(
        'name' 			=> __( 'Graph', 'woothemes-sensei' ),
        'description'	=> __( 'Settings that apply to the graph.', 'woothemes-sensei' )
    );

    return $sections;
}

add_filter('sensei_settings_fields', 'wsa_graph_fields');

function wsa_graph_fields( $fields ) {

    $fields['active_course'] = array(
        'name' => __( 'Active Courses', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Enable', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
        //'required' => 1
    );

    $fields['complete_course'] = array(
        'name' => __( 'Complete Courses', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Enable', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
    );


    $fields['graded_ungraded'] = array(
        'name' => __( 'Graded / Ungraded', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Enable', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
        //'required' => 1
    );

    $fields['failure_courses'] = array(
        'name' => __( 'Failure Courses', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Enable', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
        //'required' => 1
    );

    $fields['purchased_courses'] = array(
        'name' => __( 'Purchased Courses', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Enable', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
        //'required' => 1
    );

    $fields['enrolled_months'] = array(
        'name' => __( 'Enroll By Monthly', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Enable', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
        //'required' => 1
    );

    $fields['hide_info_labels'] = array(
        'name' => __( 'Hide Labels', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Yes', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => true,
        'section' => 'graph-settings',
        //'required' => 1
    );

    $fields['show_3d_charts'] = array(
        'name' => __( 'Show 3d Charts', 'woothemes-sensei' ),
        'description' => sprintf( __( 'Yes', 'woothemes-sensei' ), '<code>', '</code>' ),
        'type' => 'checkbox',
        'default' => false,
        'section' => 'graph-settings',
        //'required' => 1
    );

    return $fields;
}