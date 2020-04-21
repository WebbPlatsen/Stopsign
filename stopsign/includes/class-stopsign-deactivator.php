<?php
/**
 * Plugin deactivation.
 *
 * @since      1.0.0
 * @package    Stopsign
 * @subpackage Stopsign/includes
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * class-stopsign-deactivator.php
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
class Stopsign_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

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

	}

}
