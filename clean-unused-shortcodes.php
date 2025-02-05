<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://amrelarabi.com
 * @since             1.0.0
 * @package           Clean_Unused_Shortcodes
 *
 * @wordpress-plugin
 * Plugin Name:       Clean unused shortcodes
 * Plugin URI:        https://amrelarabi.com?ref=clean-unused-shortcodes
 * Description:       Remove unused shortcodes from your posts content.
 * Version:           1.0.4
 * Author:            Amr Elarabi
 * Author URI:        https://amrelarabi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       clean-unused-shortcodes
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CLEAN_UNUSED_SHORTCODES_VERSION', '1.0.4' );
define( 'CUS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CUS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-clean-unused-shortcodes-activator.php
 */
function activate_clean_unused_shortcodes() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clean-unused-shortcodes-activator.php';
	Clean_Unused_Shortcodes_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-clean-unused-shortcodes-deactivator.php
 */
function deactivate_clean_unused_shortcodes() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clean-unused-shortcodes-deactivator.php';
	Clean_Unused_Shortcodes_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_clean_unused_shortcodes' );
register_deactivation_hook( __FILE__, 'deactivate_clean_unused_shortcodes' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-clean-unused-shortcodes.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_clean_unused_shortcodes() {

	$plugin = new Clean_Unused_Shortcodes();
	$plugin->run();

}
run_clean_unused_shortcodes();
