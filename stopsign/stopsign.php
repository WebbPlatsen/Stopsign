<?php
/**
 * Plugin bootstrap file
 *
 * @link              https://github.com/joho1968/Stopsign
 * @since             1.0.0
 * @package           Stopsign
 *
 * @wordpress-plugin
 * Plugin Name:       Stopsign
 * Plugin URI:        https://github.com/joho1968/Stopsign
 * Description:       Display depature times of public transport at specific stop using data from Trafiklab.se
 * Version:           1.0.0
 * Author:            Joaquim Homrighausen <joho@webbplatsen.se>
 * Author URI:        https://github.com/joho1968/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       stopsign
 * Domain Path:       /languages
 *
 * stopsign.php
 * Copyright (C) 2020 Joaquim Homrighausen
 *
 * This file is part of Stopsign. Stopsign is free software.
 *
 * You may redistribute it and/or modify it under the terms of the
 * GNU General Public License version 2, as published by the Free Software
 * Foundation.
 *
 * Stopsign is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the Stopsign package. If not, write to:
 *  The Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor
 *  Boston, MA  02110-1301, USA.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'STOPSIGN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 */
function activate_stopsign() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stopsign-activator.php';
	Stopsign_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_stopsign() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stopsign-deactivator.php';
	Stopsign_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_stopsign' );
register_deactivation_hook( __FILE__, 'deactivate_stopsign' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-stopsign.php';

/**
 * Begins execution of the plugin.
 */
function run_stopsign() {

	$plugin = new Stopsign();
	$plugin->run();

}

run_stopsign();
