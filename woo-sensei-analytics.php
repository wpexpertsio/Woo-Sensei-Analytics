<?php
/**
 * Plugin Name: Woo Sensei Analytics
 * Version: 1.3.0
 * Plugin URI: http://www.wpexperts.io/
 * Description: Display Sensei Analytics
 * Author: wpexpertsio
 * Author URI: https://www.wpexperts.io
 */

require_once 'includes/class-wsa-charts.php';

function wsa_load_plugin_textdomain() {
	$domain = 'woo-sensei-analytics';

	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );

	wsa_load_localisation();
}

wsa_load_plugin_textdomain();

add_action( 'init', 'wsa_load_localisation' );

function wsa_load_localisation() {

	load_plugin_textdomain( 'woo-sensei-analytics', false, plugin_dir_path( __FILE__ ) . '/lang/' );
}


// Check Sensei is Active or not.
function wsa_sensei_deactive_error() {

	if ( ! class_exists( 'Sensei_Main' ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		$class = 'notice notice-error';
		$message = __( 'Sensei plugin not activated or installed. Please install the Sensei plugin <b> - `Woo Sensei Analytics` deactivated </b>', 'woo-sensei-analytics' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
}

add_action( 'admin_notices', 'wsa_sensei_deactive_error', 20 );

function wsa_scripts() {
	wp_enqueue_script( 'wss-script-sensei', plugin_dir_url( __FILE__ ) . '/js/script.js' );
	wp_enqueue_script( 'wss-script-chart', plugin_dir_url( __FILE__ ) . '/js/google-charts.js' );
}

add_action( 'admin_enqueue_scripts', 'wsa_scripts' );


function wsa_loader_script() {
	?>
	<script src="https://www.gstatic.com/charts/loader.js"></script>
	<script>
		google.charts.load('current', {'packages':['line', 'corechart']});
	</script>
	<?php
}

add_action( 'admin_enqueue_scripts','wsa_loader_script' );

/**
 * Sensei Graphs
 */
function wsa_sensei_dashboard_widgets() {

	add_meta_box(
		'wsa_sensei_graphs'
		,'<span class="dashicons dashicons-chart-pie"></span>' . __( 'Woo Sensei Analytics', 'woo-sensei-analytics' )
		,'wsa_dashboard_sensei_charts'
		,get_current_screen() // Take a look at the output of `get_current_screen()` on your dashboard page.
		,'normal' // Valid: 'side', 'advanced'.
		,'high' // Valid: 'default', 'high', 'low'.
	);
}

function wsa_dashboard_sensei_charts() {

	do_action('wsa_before_graphs' );

	$default_settings = array(
		'hide_info_labels' => 0,
		'show_3d_charts' => 0,
		'active_course' => 1,
		'completed_course' => 1,
		'graded_ungraded' => 1,
		'failed_lessons' => 1,
		'purchased_courses' => 1,
		'enrolled_months' => 1,
	);

	$settings = get_option( 'woothemes-sensei-settings' );

	$settings = array_merge( $default_settings, $settings );

	$legend = '';

	if ( $settings['hide_info_labels'] == 0 ) {
		$legend = 'labeled';
	}

	$is_3d = false;

	if ( $settings['show_3d_charts'] == 1 ) {
		$is_3d = true;
	}

	$wsa_charts = new WSA_Charts();

	if ( $settings['active_course'] == 1 ) {

		echo $wsa_charts->sensei_active_courses(
			'active_courses',
			__( 'Active Courses', 'woo-sensei-analytics' ),
			"['orange', 'green','brown']",
			$legend,
			$is_3d
		); ?>

		<div id="active_courses" style="float:left"></div> <?php
	}

	if ( $settings['completed_course'] == 1 ) {

		echo $wsa_charts->sensei_completed_courses(
			'completed_courses',
			__( 'Completed Courses', 'woo-sensei-analytics' ),
			"['#EF018D', '#00ADF1','brown']",
			$legend,
			$is_3d
		); ?>

		<div id="completed_courses" style="float:left"></div> <?php
	}

	if ( $settings['graded_ungraded'] == 1 ) {

		echo $wsa_charts->sensei_lesson_graded(
			'graded_ungraded',
			__( 'Graded / Ungraded Lessons', 'woo-sensei-analytics' ),
			"['red', 'green']",
			$legend,
			$is_3d
		); ?>

		<div id="graded_ungraded" style="float:left"></div> <?php
	}

	if ( $settings['failed_lessons'] == 1 ) {

		echo $wsa_charts->sensei_failed_lessons(
			'failure_Lessons',
			__( 'Failed Lessons', 'woo-sensei-analytics' ),
			"['#F00000','#A80000','#C80000']",
			$legend,
			$is_3d
		); ?>

		<div id="failure_Lessons" style="float:left"></div> <?php
	}

	if ( $settings['purchased_courses'] == 1 ) {

		echo $wsa_charts->sensei_most_sell_courses(
			'purchased_courses',
			__( 'Purchased Courses', 'woo-sensei-analytics' ),
			"['orange', 'green','brown']",
			$legend,
			$is_3d
		); ?>

		<div id="purchased_courses" style="float:left"></div> <?php
	}

	if ( $settings['enrolled_months'] == 1 ) {

		echo $wsa_charts->sensei_enrolled_user_by_month(
			'monthly_enrolled',
			__( 'Enrolled Students by Month', 'woo-sensei-analytics' ),
			"['orange', 'pink','lightgreen']"
		); ?>

		<div id="monthly_enrolled" style="float:left"></div> <?php
	}

	?>
	<div style="clear: left;"></div>
	<?php

	do_action( 'wsa_after_graphs' );
}

add_action( 'wp_dashboard_setup', 'wsa_sensei_dashboard_widgets' );

add_action( 'admin_head-index.php', 'wsa_sensei_graph_layout' );

function wsa_sensei_graph_layout() {
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 1,
		)
	);
}

add_filter( 'sensei_settings_tabs', 'wsa_settings_graph' );

function wsa_settings_graph( $sections ) {

	$sections['graph-settings'] = array(
		'name' 			=> __( 'Analytics', 'woo-sensei-analytics' ),
		'description'	=> __( 'Woo Sensei Analytics plugin charts settings.', 'woo-sensei-analytics' )
	);

	return $sections;
}

add_filter( 'sensei_settings_fields', 'wsa_graph_fields' );

function wsa_graph_fields( $fields ) {

	$fields['active_course'] = array(
		'name' => __( 'Active Courses', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Enable', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
		// 'required' => 1,
	);

	$fields['completed_course'] = array(
		'name' => __( 'Completed Courses', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Enable', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
	);


	$fields['graded_ungraded'] = array(
		'name' => __( 'Graded / Ungraded Lessons', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Enable', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
		// 'required' => 1,
	);

	$fields['failed_lessons'] = array(
		'name' => __( 'Failed Lessons', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Enable', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
		// 'required' => 1,
	);

	$fields['purchased_courses'] = array(
		'name' => __( 'Purchased Courses', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Enable', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
		// 'required' => 1,
	);

	$fields['enrolled_months'] = array(
		'name' => __( 'Enrolled Students by Month', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Enable', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
		//'required' => 1
	);

	$fields['hide_info_labels'] = array(
		'name' => __( 'Hide Labels', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Yes', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => true,
		'section' => 'graph-settings',
		// 'required' => 1,
	);

	$fields['show_3d_charts'] = array(
		'name' => __( 'Show 3D Charts', 'woo-sensei-analytics' ),
		'description' => sprintf( __( 'Yes', 'woo-sensei-analytics' ), '<code>', '</code>' ),
		'type' => 'checkbox',
		'default' => false,
		'section' => 'graph-settings',
		// 'required' => 1,
	);

	return $fields;
}
