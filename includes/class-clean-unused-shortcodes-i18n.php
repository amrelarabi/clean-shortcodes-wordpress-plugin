<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://amrelarabi.com
 * @since      1.0.0
 *
 * @package    Clean_Unused_Shortcodes
 * @subpackage Clean_Unused_Shortcodes/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Clean_Unused_Shortcodes
 * @subpackage Clean_Unused_Shortcodes/includes
 * @author     Amr Elarabi <contact@amrelarabi.com>
 */
class Clean_Unused_Shortcodes_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'clean-unused-shortcodes',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
