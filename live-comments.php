<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.i3studioz.com/wp-dialogue
 * @since             1.0.0
 * @package           WP_Dialogue
 *
 * @wordpress-plugin
 * Plugin Name:       WP Dialogue
 * Plugin URI:        http://www.i3studioz.com/wp-dialogue/
 * Description:       WP Dialogue is a WordPress plugin which converts the WordPress native commenting system into a live discussion system.  It uses all the native features of WordPress so that there is minimal chances of breaking with updates. The plugin retains the ease of usability of the WordPress's commenting and provides multiple options to make it compatible with your current theme's look.
 * Version:           1.0.0
 * Author:            WP Team @ i3studioz
 * Author URI:        http://www.i3studioz.com/
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-dialogue
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-live-comments-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-live-comments-deactivator.php';

/** This action is documented in includes/class-live-comments-activator.php */
register_activation_hook( __FILE__, array( 'Live_Comments_Activator', 'activate' ) );

/** This action is documented in includes/class-live-comments-deactivator.php */
register_deactivation_hook( __FILE__, array( 'Live_Comments_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-live-comments.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_live_comments() {

	$plugin = new Live_Comments();
	$plugin->run();

}
run_live_comments();
