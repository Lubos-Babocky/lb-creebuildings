<?php

/*
  Plugin Name: LB CreeAPI Cron
  Description: Temporal replacement for cronjob
  Version: 1.0
  Author: Luboš Babocký <babocky@gmail.com> | ATOMICBOY werbeagentur <office@atomicboy.tv>
 */

defined('ABSPATH') || die('ABSPATH not defined');

require_once plugin_dir_path(__FILE__) . 'autoload.php';

use LB\CreeBuildings\Controller\AdminPageController;

if (class_exists(AdminPageController::class, true)) {
    AdminPageController::RegisterPlugin('index', 'CreeAPI manager', 'dashicons-admin-generic', 100);

    function lb_creebuildings_enqueue_admin_styles($hookSuffix)
    {
        if (strpos($hookSuffix, 'lb-creebuildings') !== false) {
            wp_enqueue_style('lb-creebuildings-admin-grid', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
        }
    }

    add_action('admin_enqueue_scripts', 'lb_creebuildings_enqueue_admin_styles');
}

add_filter('term_link', function ($url, $term, $taxonomy) {
    if ($taxonomy === 'post_tag') {
        return home_url("/projects/?e-filter-a2d9951-post_tag=" . $term->slug);
    }
    return $url;
}, 10, 3);
