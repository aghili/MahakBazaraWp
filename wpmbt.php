<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.mostafa.aghili.ir
 * @since             1.0.0
 * @package           Wpmbt
 *
 * @wordpress-plugin
 * Plugin Name:       WPMahakBazaraTest
 * Plugin URI:        https://gitlab.com/aghili.mostafa/MahakBazaraTest.git
 * Description:       پلاگین تست برای سامانه بازارا در محک
 * Version:           0.0.1
 * Author:            mostafa aghili
 * Author URI:        http://www.mostafa.aghili.ir
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpmbt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpmbt-activator.php
 */
function activate_wpmbt() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmbt-activator.php';
	Wpmbt_Activator::activate();
}

function update_wpmbt()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-wpmbt-activator.php';
	Wpmbt_Activator::update_db_check();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpmbt-deactivator.php
 */
function deactivate_wpmbt() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmbt-deactivator.php';
	Wpmbt_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpmbt' );
register_deactivation_hook( __FILE__, 'deactivate_wpmbt' );
add_action('plugins_loaded', 'update_wpmbt');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpmbt.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpmbt() {

	$plugin = new Wpmbt();
	$plugin->run();

}


run_wpmbt();