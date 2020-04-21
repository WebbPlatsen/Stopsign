<?php
/**
 * Stopsign Admin
 *
 * @package    Stopsign
 * @subpackage Stopsign/admin
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * class-stopsign-admin.php
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
class Stopsign_Admin {

	private $plugin_name;
	private $version;
	private $api_commute_id_lookup_url;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_commute_id_lookup_url = 'https://api.resrobot.se/v2/location.name?key=';
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/stopsign-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		$commute_planner_api_key = get_option( 'stopsign-trafiklab-commuteplanner-apikey', '' );
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stopsign-admin.js' );
		// Pass ajax_url to stopsign-admin.js
        wp_localize_script( $this->plugin_name,
						    'plugin_ajax_object',
							array(
								'ajax_url' => admin_url( 'admin-ajax.php' ),
								'ajax_api_url' => $this->api_commute_id_lookup_url,
								'ajax_api_key'  => $commute_planner_api_key,
								'txt_commute_stop_id' => esc_html__( 'Commute stop ID', 'stopsign' ),
								'txt_commute_stop_name' => esc_html__( 'Commute stop name', 'stopsign' ),
								'txt_commute_stop_types' => esc_html__( 'Stop types', 'stopsign' ),
								'txt_no_match' => esc_html__( 'No match', 'stopsign' ),
							)
						  );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stopsign-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Output content for settings form
	 */
	public function setup_options_page() {
		if ( ! current_user_can( 'manage_options' ) )  {
			return;
		}
		// error_log ('>> '.__FUNCTION__);
		echo '<div class="wrap">';
		echo '<h1>Stopsign</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'stopsign-settings' );
		do_settings_sections( 'stopsign-settings' );
		submit_button();
		echo '</form>';
		// Ajax "form" for commute stop ID lookup
		echo '<div class="clear"></div>';
		echo '<div id="div_search">';
		echo '<h3>' . esc_html__( 'Commute stop ID lookup', 'stopsign' ) . '</h3>';
		echo '<table class="form-table" role="presentation">';
		echo '<tr class="row">';
		echo '<th scope="row">';
		echo esc_html__( 'Search location', 'stopsign' );
		echo '</th>';
		echo '<td>';
		echo '<input type="text" name="stopsignajax" id="stopsignajax" size="60" maxlenth="60" placeholder="' . esc_html__( 'Enter a location to lookup ID', 'stopsign' ). '" />';
		echo '<p class="description">' . esc_html__( 'This search may return a commuter stop close to what you are looking for', 'stopsign' ) . '</p>';
		echo '</td>';
		echo '</tr>';
		// Where search results go
		echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo '<td><div id="stopsign_search_result"></td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';

		echo '</div>';
		// error_log ('<< '.__FUNCTION__);
	}

	/**
	 * Activate ourselves as a menu option
	 */
	public function setup_stopsign_menu() {
		add_action( 'wp_ajax_commute_lookup_id', [ $this, 'stopsign_commute_lookup_callback' ] );
		add_action( 'wp_ajax_nopriv_commute_lookup_id', [ $this, 'stopsign_commute_lookup_callback' ] );

		if ( ! current_user_can( 'manage_options' ) )  {
			return;
		}
		// error_log ('>> '.__FUNCTION__);
		add_options_page( __( 'Stopsign settings', 'stopsign' ),
						 'Stopsign',
					     'manage_options',
					     'stopsign',
					     [ $this, 'setup_options_page' ]
						);
		// error_log ('<< '.__FUNCTION__);
	}

	/**
	 * Setup the required fields (and sections)
	 */
	public function register_stopsign_settings() {
		// error_log ('>> '.__FUNCTION__);
		// This decides if we can actually perform a lookup in admin
		$commute_planner_key = get_option( 'stopsign-trafiklab-commuteplanner-apikey', '' );
		// Add section
		add_settings_section( 'stopsign_section_1', 'Trafiklab API', false, 'stopsign-settings' );
		// Add fields for API keys
		add_settings_field( 'stopsign-trafiklab-commutestop-apikey',
						    '<label for="stopsign-trafiklab-commutestop-apikey">' . esc_html__( 'Commute stop API key', 'stopsign' ) . '</label>',
							[ $this, 'paint_setting_field' ],
							'stopsign-settings',
							'stopsign_section_1',
							array(
								'name'        => 'stopsign-trafiklab-commutestop-apikey',
								'class'       => 'row',
								'label'       => '',
								'type'        => 'text',
								'placeholder' => __( 'Enter a valid API key', 'stopsign' ),
								'helper'      => '',
								'desc'        => __( 'An API key from the Trafiklab', 'stopsign' ).
								                ' '.
												'ResRobot - Stolptidtabeller (2)'.
												' '.
												__( 'service. Registration may be required.', 'stopsign' ),
								'default'     => '',
								'size'        => 60,
								'maxlength'   => 128,
							)
						   );
		add_settings_field( 'stopsign-trafiklab-commuteplanner-apikey',
						    '<label for="stopsign-trafiklab-commuteplanner-apikey">' . __( 'Commute planner API key', 'stopsign' ) . '</label>',
							[ $this, 'paint_setting_field' ],
							'stopsign-settings',
							'stopsign_section_1',
							array(
								'name'        => 'stopsign-trafiklab-commuteplanner-apikey',
								'class'       => 'row',
								'label'       => '',
								'type'        => 'text',
								'placeholder' => __( 'Enter a valid API key', 'stopsign' ),
								'helper'      => '',
								'desc'        => __( 'An API key from the Trafiklab', 'stopsign' ).
								                ' '.
												'ResRobot - Reseplanerare'.
												' '.
												__( 'service. Registration may be required.', 'stopsign' ),
								'default'     => '',
								'size'        => 60,
								'maxlength'   => 128,
							)
						   );
		// Add section
		add_settings_section( 'stopsign_section_2', __( 'Other settings', 'stopsign' ), false, 'stopsign-settings' );
		add_settings_field( 'stopsign-clean-at-bracket',
						    '<label for="stopsign-clean-at-bracket">' . __( 'Clean strings', 'stopsign' ) . '</label>',
							[ $this, 'paint_setting_field' ],
							'stopsign-settings',
							'stopsign_section_2',
							array(
								'name'        => 'stopsign-clean-at-bracket',
								'class'       => 'row',
								'type'        => 'checkbox',
								'desc'        => __( 'Truncates direction and commute stop name strings at first occurrence of " (" or " [".', 'stopsign' ),
								'default'     => '1',
							)
						   );
		add_settings_field( 'stopsign-use-transients',
						    '<label for="stopsign-use-transients">' . __( 'Use transients', 'stopsign' ) . '</label>',
							[ $this, 'paint_setting_field' ],
							'stopsign-settings',
							'stopsign_section_2',
							array(
								'name'        => 'stopsign-use-transients',
								'class'       => 'row',
								'type'        => 'checkbox',
								'desc'        => __( 'Make use of WordPress\' internal "transients" handler to prevent flooding of the the API.', 'stopsign' ),
								'default'     => '1',
							)
						   );
		add_settings_field( 'stopsign-remove-settings',
						    '<label for="stopsign-remove-settings">' . __( 'Remove settings', 'stopsign' ) . '</label>',
							[ $this, 'paint_setting_field' ],
							'stopsign-settings',
							'stopsign_section_2',
							array(
								'name'        => 'stopsign-remove-settings',
								'class'       => 'row',
								'type'        => 'checkbox',
								'desc'        => __( 'Remove all Stopsign plugin settings and data when plugin is uninstalled.', 'stopsign' ),
								'default'     => '0',
							)
						   );
		register_setting( 'stopsign-settings', 'stopsign-trafiklab-commutestop-apikey');
		register_setting( 'stopsign-settings', 'stopsign-trafiklab-commuteplanner-apikey');
		register_setting( 'stopsign-settings', 'stopsign-clean-at-bracket');
		register_setting( 'stopsign-settings', 'stopsign-use-transients');
		register_setting( 'stopsign-settings', 'stopsign-remove-settings');
		// error_log ('<< '.__FUNCTION__);
	}

	/**
	 * Actual output of input fields HTML, etc.
	 */
	public function paint_setting_field( $args ) {
		// error_log ('>> '.__FUNCTION__);
		if ( empty( $args['name'] ) ) {
			return;
		}
	    $value = get_option( $args['name'], '!' );
		$html = '';
		if ( ! empty( $args ) ) {
			if ( ! empty( $args['type'] ) && $args['type']=='checkbox' ) {
				if ( $value == '!' ) {
					// not set at all
					if ( ! empty( $args['default'] ) ) {
						$value = 1;
					} else {
						$value = 0;
					}
				}
				$html .= ' type="checkbox" value="1"'.checked( 1, ! empty( $value ), false);
				foreach( $args as $k => $v ) {
					switch( $k ) {
						case 'name':
							$html .= ' name="' . esc_attr( $v ) . '"' .
									 ' id="'   . esc_attr( $v ) . '"';
							break;
						case 'class':
							$html .= ' class="' . esc_attr( $v ) . '"';
							break;
					}//switch
				}//foreach
		    echo '<input'.$html.' />';
			} else {
				foreach( $args as $k => $v ) {
					switch( $k ) {
						case 'name':
							$html .= ' name="' . esc_attr( $v ) . '"' .
									 ' id="'   . esc_attr( $v ) . '"';
							break;
						case 'placeholder':
							$html .= ' placeholder="' . esc_attr__( $v ) . '"';
							break;
						case 'class':
							$html .= ' class="' . esc_attr( $v ) . '"';
							break;
						case 'size':
							$html .= ' size="' . esc_attr( $v ) . '"';
							break;
						case 'maxlength':
							$html .= ' maxlength="' . esc_attr( $v ) . '"';
							break;
						case 'type':
							$html .= ' type="' . esc_attr( $v ) . '"';
							break;
						case 'default':
							if ( empty( $value ) ) {
								$value = $v;
							}
							break;
					}//switch
				}//foreach
		    echo '<input value="' . esc_attr( $value ) . '" '.$html.' />';
			}// !checkbox
		}
		// Handle help text
		if ( ! empty( $args['helper'] )) {
			echo '<span class="helper">' . esc_html__( $args['helper'] ) . '</span>';
		}
		// Handle supplemental description
		if ( ! empty( $args['desc'] )) {
			echo '<p class="description">' . esc_html__( $args['desc'] ) . '</p>';
		}
		// error_log ('<< '.__FUNCTION__);
	}

	/**
	 * Output of commute stop ID lookup
	 */
	public function stopsign_commute_lookup_callback() {
		// error_log ('>> '.__FUNCTION__);
		// error_log ('<< '.__FUNCTION__);
		wp_die();
	}

}