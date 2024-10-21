<?php

/*
  Plugin Name: LB CreeAPI Cron
  Description: Temporal replacement for cronjob
  Version: 1.0
  Author: Luboš Babocký | ATOMICBOY werbeagentur <office@atomicboy.tv>
 */

defined('ABSPATH') || die('ABSPATH not defined');

require_once plugin_dir_path(__FILE__).'autoload.php';

function create_custom_table() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    if (file_exists($sqlFile = plugin_dir_path(__FILE__) . 'tables.sql') && !empty($sqlContent = file_get_contents($sqlFile))) {
        //dbDelta($sqlContent);
    }
}

register_activation_hook(__FILE__, 'create_custom_table');

function lb_creebuildings_admin_menu() {
    add_menu_page(
            'CreeAPI data update',
            '[LB] CreeBuildings',
            'manage_options',
            'lb-creebuildings',
            'lb_creebuildings_render_admin_page',
            'dashicons-admin-generic',
            6
    );
}

add_action('admin_menu', 'lb_creebuildings_admin_menu');

function lb_creebuildings_render_admin_page() {
    (new \LB\CreeBuildings\Controller\AdminPageController())->serve();
}

function lb_creebuildings_enqueue_admin_styles( $hookSuffix ) {
    if ( 'toplevel_page_lb-creebuildings' === $hookSuffix ) {
        wp_enqueue_style( 'lb-creebuildings-admin-grid', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'lb_creebuildings_enqueue_admin_styles' );