<?php

/*
  Plugin Name: LB CreeAPI Cron
  Description: Temporal replacement for cronjob
  Version: 1.0
  Author: Luboš Babocký | ATOMICBOY werbeagentur <office@atomicboy.tv>
 */

defined('ABSPATH') || die('ABSPATH not defined');

// <editor-fold defaultstate="collapsed" desc="Class loading">
/*
spl_autoload_register(function ($className) {
	$classNameParts = explode('\\', $className);
	if ($classNameParts[0] !== 'LB') {
		return;
	}
	$classPath = sprintf('%swp-content/plugins/%s-%s/Classes/', ABSPATH, strtolower($classNameParts[0]), strtolower($classNameParts[1]));
	array_splice($classNameParts, 0, 2);
	if (file_exists($file = $classPath . implode('/', $classNameParts) . '.php')) {
		include $file;
	} else {
		die(sprintf('Class <b>[%s]</b> not found in [%s]', $className, $file));
	}
});
/**/
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Init DB tables">
function create_custom_table() {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	if (file_exists($sqlFile = plugin_dir_path(__FILE__) . 'tables.sql') && !empty($sqlContent = file_get_contents($sqlFile))) {
		dbDelta($sqlContent);
	}
}

register_activation_hook(__FILE__, 'create_custom_table');

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="BE Module (temp)">
function lb_creebuildings_cron_menu() {
	add_menu_page(
			'[LB] CreeBuildings',
			'[LB] CreeBuildings',
			'manage_options',
			'lb-creebuildings',
			'lb_creebuildings_module_page',
			'dashicons-admin-generic',
			6
	);
}

add_action('admin_menu', 'lb_creebuildings_cron_menu');

function lb_creebuildings_module_page() {
	\LB\CreeBuildings\ProjectImport::RenderProjectTable();
}
// </editor-fold>