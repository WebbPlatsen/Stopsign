<?php
/**
 * Plugin is uninstalled.
 *
 * @link       http://example.com
 * @since      1.0.0
 * @package    Stopsign
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * uninstall.php
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

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
// If action is not to uninstall, then exit
if ( empty( $_REQUEST['action'] ) || $_REQUEST['action'] !== 'delete-plugin' ) {
	exit;
}
// If it's not us, then exit
if ( empty( $_REQUEST['slug'] ) || $_REQUEST['slug'] !== 'stopsign' ) {
	exit;
}
// If we shouldn't do this, then exit
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'delete_plugins' ) ) {
	exit;
}


// Figure out if an uninstall should remove plugin settings. We will always
// remove transients and other temporary things.

$remove_settings = get_option( 'stopsign-remove-settings', '0' );

// Remove transients (always)

$GLOBALS['wpdb']->query(
	$GLOBALS['wpdb']->prepare(
		'DELETE FROM '.$GLOBALS['wpdb']->options.' WHERE option_name LIKE %s',
		'_transient_timeout_stopsign_tt_%'
	)
);
$GLOBALS['wpdb']->query(
	$GLOBALS['wpdb']->prepare(
		'DELETE FROM '.$GLOBALS['wpdb']->options.' WHERE option_name LIKE %s',
		'_transient_stopsign_tt_%'
	)
);

if ( $remove_settings == '1' ) {
	// Stopsign plugin options
	delete_option( 'stopsign-trafiklab-commutestop-apikey' );
	delete_option( 'stopsign-trafiklab-commuteplanner-apikey' );
	delete_option( 'stopsign-clean-at-bracket' );
	delete_option( 'stopsign-use-transients' );
	delete_option( 'stopsign-remove-settings' );
}
