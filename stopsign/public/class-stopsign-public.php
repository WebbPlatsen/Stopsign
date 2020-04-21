<?php
/**
 * Public-facing functionality of the plugin.
 *
 * ### This code uses PHP mb_() functions ###
 *
 * @package    Stopsign
 * @subpackage Stopsign/public
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * class-stopsign-public.php
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

define( 'STOPSIGN_FASTTRAIN',     1);
define( 'STOPSIGN_REGIONALTRAIN', 2);
define( 'STOPSIGN_EXPRESS_BUS',   3);
define( 'STOPSIGN_TRAIN',         4);
define( 'STOPSIGN_METRO',         5);
define( 'STOPSIGN_TRAM',          6);
define( 'STOPSIGN_BUS',           7);
define( 'STOPSIGN_FERRY',         8);
define( 'STOPSIGN_TAXI',          9);
define( 'STOPSIGN_DONTKNOW',  32768);

class Stopsign_Public {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_name;
	private $version;

	/**
	 * API related data
	 */
	private $api_key;
	private $api_url;
	private $api_display_truncate_at_bracket;
	private $api_cache_lookups_with_transients;

	/**
	 * Finding Nemo ... ehr ... Stopsign
	 */
	protected $plugin_path;
	protected $plugin_template_path;
	protected $plugin_lookedup;

	/**
	 * Variables to hold time table data
	 */
	private $time_table_array;
	private $time_table_pointer;
	private $time_group_table;
	private $time_group_key;
	private $time_group_time;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_url = 'https://api.resrobot.se/v2/departureBoard?key=';
		$this->api_key = '';
		$this->api_display_truncate_at_bracket = false;
		$this->api_cache_lookups_with_transients = false;

		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_template_path = $this->plugin_path . 'templates/';
		$this->plugin_lookedup = array ();
		$this->time_table_array = null;
		$this->time_table_pointer = 0;
		$this->time_group_table = null;
		$this->time_group_key = '';
		$this->time_group_time = '';
	}

	/**
	 * Look for a specific .php template used for Stopsign output
	 *
	 * @since    1.0.0
	 * @param    string    $template_file  Filename of template, e.g. "stopsign_default.php"
	 */
	protected function find_template( $template_file ) {
		if ( ! empty( $this->plugin_lookedup[$template_file] ) ) {
			// We did this before, we know how to handle it
			return( $this->plugin_lookedup[$template_file] );
		}
		$look_for = get_template_directory() . '/stopsign/templates/' . $template_file;
		if ( file_exists( $look_for ) ) {
			// Found overriding template in theme directory
			$this->plugin_lookedup[$template_file] = $look_for;
			return( $look_for );
		}
		$look_for = $this->plugin_template_path . $template_file;
		if ( file_exists( $look_for ) ) {
			// Found our own template
			$this->plugin_lookedup[$template_file] = $look_for;
			// error_log ($look_for);
			return( $look_for );
		}
		return ( false );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/stopsign-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stopsign-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Possibly clean up display name of stop/direction
	 */
	protected function cleanup_commute_str( $commute_str ) {
		if ( $this->api_display_truncate_at_bracket ) {
			if ( function_exists( 'mb_strpos' ) ) {
				$bracket = mb_strpos ( $commute_str, ' (' );
				if ( $bracket !== false ) {
					$commute_str = mb_substr( $commute_str, 0, $bracket );
					$bracket = mb_strpos ( $commute_str, ' [' );
					if ( $bracket !== false ) {
						$commute_str = mb_substr( $commute_str, 0, $bracket );
					}
				}
			} else {
				// no mb_strpos, try to do the best we can anyway
				$bracket = strpos ( $commute_str, ' (' );
				if ( $bracket !== false ) {
					$commute_str = substr( $commute_str, 0, $bracket );
					$bracket = strpos ( $commute_str, ' [' );
					if ( $bracket !== false ) {
						$commute_str = substr( $commute_str, 0, $bracket );
					}
				}
			}
		}
		return( $commute_str );
	}

	/**
	 * Do header replacement for list output header template (callback)
	 *
	 * @access   protected
	 * @var      string    $matches String matching RE
	 * @since    1.0.0
	 */
	protected function template_default_header_replace( $matches ) {
		$replacement = '';
		if ( is_array( $matches ) && ! empty( $matches[0] ) ) {
			switch( $matches[0] ) {
				case '{stopsign_hdr_type}':
					$replacement = esc_html__( 'Type', 'stopsign' );
					break;
				case '{stopsign_hdr_number}':
					$replacement = esc_html__( 'Number', 'stopsign' );
					break;
				case '{stopsign_hdr_time}':
					$replacement = esc_html__( 'Time', 'stopsign' );
					break;
				case '{stopsign_hdr_direction}':
					$replacement = esc_html__( 'Direction', 'stopsign' );
					break;
			}// switch
		}
		return ($replacement);
	}

	/**
	 * Do header replacement for list output row template (callback)
	 *
	 * This is ugly. It uses class variables to figure out which row of the
	 * time table is actually being processed, and it will access another
	 * class variable for content. But it works.
	 *
	 * @access   protected
	 * @var      string    $matches String matching RE
	 * @since    1.0.0
	 */
	protected function template_default_row_replace( $matches ) {
		if ( ! is_array( $this->time_table_array ) ) {
			return( '' );
		}
		$replacement = '';
		if ( is_array( $matches ) && ! empty( $matches[0] ) ) {
			switch( $matches[0] ) {
				case '{stopsign_type}':
					switch( $this->time_table_array[$this->time_table_pointer]['category'] ) {
						case STOPSIGN_FASTTRAIN:     $replacement = esc_html__( 'Express train', 'stopsign' );  break;
						case STOPSIGN_REGIONALTRAIN: $replacement = esc_html__( 'Regional train', 'stopsign' ); break;
						case STOPSIGN_EXPRESS_BUS:   $replacement = esc_html__( 'Express bus', 'stopsign' );    break;
						case STOPSIGN_TRAIN:         $replacement = esc_html__( 'Train', 'stopsign' );          break;
						case STOPSIGN_METRO:         $replacement = esc_html__( 'Metro', 'stopsign' );          break;
						case STOPSIGN_TRAM:          $replacement = esc_html__( 'Tram', 'stopsign' );           break;
						case STOPSIGN_BUS:           $replacement = esc_html__( 'Bus', 'stopsign' );            break;
						case STOPSIGN_FERRY:         $replacement = esc_html__( 'Ferry', 'stopsign' );          break;
						case STOPSIGN_TAXI:          $replacement = esc_html__( 'Taxi', 'stopsign' );           break;
						default:					 $replacement = esc_html__( '?', 'stopsign' );              break;
					}// switch
					break;
				case '{stopsign_type_icon}':
					switch( $this->time_table_array[$this->time_table_pointer]['category'] ) {
						case STOPSIGN_FASTTRAIN:     $replacement = '&#x1f684;'; break;
						case STOPSIGN_REGIONALTRAIN: $replacement = '&#x1f688;'; break;
						case STOPSIGN_EXPRESS_BUS:   $replacement = '&#x1f690;'; break;
						case STOPSIGN_TRAIN:         $replacement = '&#x1f686;'; break;
						case STOPSIGN_METRO:         $replacement = '&#x1f687;'; break;
						case STOPSIGN_TRAM:          $replacement = '&#x1f68b;'; break;
						case STOPSIGN_BUS:           $replacement = '&#x1f68c;'; break;
						case STOPSIGN_FERRY:         $replacement = '&#x26f4;';  break;
						case STOPSIGN_TAXI:          $replacement = '&#x1f695;'; break;
						default:					 $replacement = esc_html__( '?', 'stopsign' );              break;
					}// switch
					break;
				case '{stopsign_number}':
					$replacement = esc_html( $this->time_table_array[$this->time_table_pointer]['number'] );
					break;
				case '{stopsign_time}':
					$replacement = esc_html( $this->time_table_array[$this->time_table_pointer]['time'] );
					break;
				case '{stopsign_direction}':
					$replacement = esc_html( $this->cleanup_commute_str( $this->time_table_array[$this->time_table_pointer]['direction'] ) );
					break;
			}// switch
		}
		return ($replacement);
	}

	/**
	 * Do header replacement for grouped output header template (callback)
	 *
	 * @access   protected
	 * @var      string    $matches String matching RE
	 * @since    1.0.0
	 */
	protected function template_grouped_header_replace( $matches ) {
		$replacement = '';
		if ( is_array( $matches ) && ! empty( $matches[0] ) ) {
			switch( $matches[0] ) {
				case '{stopsign_group_number}':
					$replacement = esc_html( $this->time_group_table[$this->time_group_key]['number'] );
					break;
				case '{stopsign_group_direction}':
					$replacement = esc_html( $this->cleanup_commute_str( $this->time_group_table[$this->time_group_key]['direction'] ) );
					break;
			}// switch
		}
		return ($replacement);
	}

	/**
	 * Do header replacement for grouped output row template (callback)
	 *
	 * This is ugly. It uses class variables to figure out which key of the
	 * grouped time table is actually being processed, and it will access
	 * another class variable for content. But it works.
	 *
	 * @access   protected
	 * @var      string    $matches String matching RE
	 * @since    1.0.0
	 */
	protected function template_grouped_row_replace( $matches ) {
		if ( ! is_array( $this->time_group_table ) ) {
			return( '' );
		}
		$replacement = '';
		if ( is_array( $matches ) && ! empty( $matches[0] ) ) {
			switch( $matches[0] ) {
				case '{stopsign_group_time}':
					$replacement = esc_html( $this->time_group_time );
					break;
			}// switch
		}
		return ($replacement);
	}

	/**
	 * Register the shortcodes for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function shortcode_init() {
		// error_log( '>> ' . __FUNCTION__ );
		$config_api_key = get_option( 'stopsign-trafiklab-commutestop-apikey', '' );
		if ( ! empty( $config_api_key ) ) {
			$this->api_key = $config_api_key;
			add_shortcode( 'stopsign', [ $this, 'stopsign_shortcode' ] );
			add_shortcode( 'stopsign_widget', [ $this, 'stopsign_shortcode_widget' ] );
			add_filter( 'widget_text', 'do_shortcode' );
		}
		$value = get_option( 'stopsign-clean-at-bracket', '!' );
		if ( ! empty( $value ) || ( $value == '!' ) ) {
			$this->api_display_truncate_at_bracket = true;
		}
		$value = get_option( 'stopsign-use-transients', '!' );
		if ( ! empty( $value ) || ( $value == '!' ) ) {
			$this->api_cache_lookups_with_transients = true;
		}
		// error_log( '<< ' . __FUNCTION__ );
	}

	/**
	 * Create actual output for shortcode(s)
	 *
	 * If $widget_shortcode is true, we apply *-widget CSS classes instead of
	 * the "regular" classes.
	 *
	 * @access   protected
	 * @var      boolean    $widget_shortcode If output should be suitable for widget
	 * @since    1.0.0
	 */
	protected function stopsign_output_time_table( $args, $widget_shortcode = false) {
		if ( empty( $this->api_key ) ) {
			error_log( 'stopsign: No API key for commutestop lookup configured' );
			return( esc_html__( 'stopsign: No API key for commutestop lookup configured' ) );
		}
		if ( empty( $args['id'] ) ) {
			// id=nnn
			return( 'stopsign: ' . esc_html__( 'Shortcode needs ID', 'stopsign' ) );
		}

		// setup CSS suffix
		if ( $widget_shortcode ) {
			$css_suffix = '-widget';
			$template_suffix = '_widget';
		} else {
			$css_suffix = '';
			$template_suffix = '';
		}

		// setup parameters
		$num_group = false;
		if ( ! empty( $args['numgroup'] ) ) {
			// numgroup=true or numgroup=1
			if ( $args['numgroup'] == 'true' || $args['numgroup'] == '1' ) {
				$num_group = true;
			}
		}
		$max_group = 3;
		if ( ! empty( $args['maxgroup'] ) ) {
			// maxgroup=1..9 (default 3)
			if ( (int)$args['maxgroup'] >= 1 && (int)$args['maxgroup'] <= 9 ) {
				$max_group = $args['maxgroup'];
			}
		}
		// Initialize comuute stop information
		$time_table = array();
		$time_table_count = 0;
		$time_table_is_transient = false;

		// We attempt to load previous result from transients store first
		if ( $this->api_cache_lookups_with_transients ) {
			$table_from_disk = get_transient( 'stopsign_tt_' . (int)$args['id'] );
			if ( $table_from_disk !== false ) {
				$time_table = @ unserialize( $table_from_disk );
				if ( $time_table !== false ) {
					$time_table_count = count( $time_table );
					if ( $time_table_count > 0 ) {
						$time_table_is_transient = true;
						// error_log( '>> ' . __FUNCTION__ . ' Transient fetch');
					} else {
						$time_table = array();
					}
				}
			}
		}// cache lookups with transients

		// Try file_get_contents first, if possible
		if ( $time_table_count == 0 ) {
			$allow_url_fopen = @ ini_get( 'allow_url_fopen' );
			if ( $allow_url_fopen === '1' ) {
				$request_url = $this->api_url . $this->api_key . '&id=' . (int)$args['id'] . '&maxJourneys=10&format=json';
				// error_log( '-- ' . __FUNCTION__ . ' ' . $request_url );
				$request = @ file_get_contents( $request_url );
				if ( $request === false && ! empty( $http_response_header[0] ) ) {
					error_log( 'stopsign-shortcode: ' . $http_response_header[0] );
				} elseif ( ! empty( $request ) ) {
					$json_data = json_decode ( $request, true );
					if ( ! empty( $json_data['Departure'] ) ) {
						$p_count = count( $json_data['Departure'] );
						for ($p = 0; $p < $p_count; $p++) {
							// Loop through entries under 'Departure' and extract only what we want
							$header_out = false;
							$time_table[$time_table_count]['category'] = '';
							$time_table[$time_table_count]['number'] = '';
							$time_table[$time_table_count]['time'] = '';
							$time_table[$time_table_count]['date'] = '';
							$time_table[$time_table_count]['direction'] = '';
							foreach ($json_data['Departure'][$p] as $k => $v) {
								switch( $k ) {
									case 'Product':
										if ( ! empty ( $v['catCode'] ) ) {
											$time_table[$time_table_count]['category'] = (int)$v['catCode'];
										} else {
											$time_table[$time_table_count]['category'] = '';
										}
										if ( ! empty ( $v['num'] ) ) {
											$time_table[$time_table_count]['number'] = (int)$v['num'];
										} else {
											$time_table[$time_table_count]['number'] = '';
										}
										break;
									case 'time':
										$time_table[$time_table_count]['time'] = substr( $v, 0, 5 );
										break;
									case 'date':
										$time_table[$time_table_count]['date'] = $v;
										break;
									case 'direction':
										$time_table[$time_table_count]['direction'] = $v;
										break;
								} // switch
							} // foreach
							$time_table_count++;
						}//

						// Possibly update transient "cache"
						$table_from_disk = @ serialize( $time_table );
						if ( $table_from_disk !== false ) {
							set_transient( 'stopsign_tt_' . (int)$args['id'], $table_from_disk, 30 );
						}

					}// Departure is the outermost container we want
				}
			}
		}// fetch from API

		// Sort, process, render
		if ( $num_group ) {
			if ( $time_table_count > 0 ) {
				// output should be grouped
				$group_table = array( );
				for ( $tc = 0; $tc < $time_table_count; $tc++ ) {
					$group_key = md5( $time_table[$tc]['category'] .
									  $time_table[$tc]['number'] .
									  $time_table[$tc]['direction'] );
					if ( ! isset($group_table[$group_key] ) ) {
						$group_array = array( );
						for ( $gc = 0; $gc < $time_table_count; $gc++ ) {
							$array_key = md5( $time_table[$gc]['category'] .
											  $time_table[$gc]['number'] .
											  $time_table[$gc]['direction'] );
							if ( $array_key == $group_key ) {
								// add this entry to this group
								$group_array[] = $time_table[$gc]['time'];
								if ( count( $group_array ) >= $max_group ) {
									// limit reached for group, bail
									break;
								}
							}
						}// group loop
						$group_table[$group_key]['time']      = $group_array;
						$group_table[$group_key]['number']    = (int)$time_table[$tc]['number'];
						$group_table[$group_key]['category']  = $time_table[$tc]['category'];
						$group_table[$group_key]['direction'] = $time_table[$tc]['direction'];
					}// new group
				}// table loop
				// create a reasonable default
				$stopsign_grouped_header = '<div class="stopsign-grouped-header' . $css_suffix . '">' .
										   '<span>{stopsign_group_number}</span> &middot; {stopsign_group_direction}' .
										   '</div>';
				$stopsign_grouped_row = '<div class="stopsign-grouped-time' . $css_suffix . '">{stopsign_group_time}</div>';
				// attempt to load template
				$template_name = 'stopsign_default' . $template_suffix . '.php';
				$to_load = $this->find_template( $template_name );
				if (! empty( $to_load )) {
					if ( ( @include( $to_load ) ) === false ) {
						error_log( 'Unable to load Stopsign template (' . $template_name . ')' );
					}
				} else {
					error_log( 'Unable to locate Stopsign template (' . $template_name . ')' );
				}
				$html = '<div class="stopsign-grouped-stop' . $css_suffix . '">';
				$this->time_group_table = &$group_table;
				foreach ( $group_table as $k => $v ) {
					$this->time_group_key = $k;
					$html .= mb_ereg_replace_callback( '\{[^\}]*\}', [ $this, 'template_grouped_header_replace'], $stopsign_grouped_header);
					foreach ( $v['time'] as $t ) {
						$this->time_group_time = $t;
						$html .= mb_ereg_replace_callback( '\{[^\}]*\}', [ $this, 'template_grouped_row_replace'], $stopsign_grouped_row);
					}
				}
				$html .= '</div>';
			} else {
				$html = '<div class="stopsign-grouped-stop' . $css_suffix . '">';
				$html .= esc_html__( 'No departures found', 'stopsign' );
				$html .= '</div>';
			}
		} else {
			// output is not grouped

			// create a reasonable default
			$stopsign_table_header = '<tr><th>{stopsign_hdr_type}</th>' .
									 '<th>{stopsign_hdr_number}</th>' .
									 '<th>{stopsign_hdr_time}</th>' .
									 '<th>{stopsign_hdr_direction}</th>' .
									 '</tr>';
			$stopsign_table_row = '<tr><td>{stopsign_type}</th>' .
								  '<td>{stopsign_number}</th>' .
								  '<td>{stopsign_time}</th>' .
								  '<td>{stopsign_direction}</th>' .
								  '</tr>';
			// attempt to load template
			$template_name = 'stopsign_default' . $template_suffix . '.php';
			$to_load = $this->find_template( $template_name );
			if (! empty( $to_load )) {
				if ( ( @include( $to_load ) ) === false ) {
					error_log( 'Unable to load Stopsign template (' . $template_name . ')' );
				}
			} else {
				error_log( 'Unable to locate Stopsign template (' . $template_name . ')' );
			}
			// perform variable substitutions
			$var_header = mb_ereg_replace_callback( '\{[^\}]*\}', [ $this, 'template_default_header_replace'], $stopsign_table_header);
			$html = '<div class="stopsign-stop' . $css_suffix . '">';
			$html .= '<table class="stopsign-stop' . $css_suffix . '">';
			$html .= $var_header;
			if ( $time_table_count > 0 ) {
				$this->time_table_array = &$time_table;
				for ( $tc = 0; $tc < $time_table_count; $tc++ ) {
					$this->time_table_pointer = $tc;
					$html .= mb_ereg_replace_callback( '\{[^\}]*\}', [ $this, 'template_default_row_replace'], $stopsign_table_row);
				}
			} else {
				$html .= '<tr>';
				$html .= '<td colspan="4" style="text-align:center">';
				$html .= esc_html__( 'No departures found', 'stopsign' );
				$html .= '</td>';
				$html .= '</tr>';
			}
			// close stuff
			$html .= '</table>';
			$html .= '</div>';
		}
		// error_log( '<< ' . __FUNCTION__ );
		return( $html );
	}


	/**
	 * Shortcode handler for [stopsign id="n"][/shortcode]
	 */
	public function stopsign_shortcode( $args ) {
		return( $this->stopsign_output_time_table( $args, false ) );
	}

	/**
	 * Shortcode handler for [stopsign_widget id="n"][/shortcode_widget]
	 */
	public function stopsign_shortcode_widget( $args ) {
		return( $this->stopsign_output_time_table( $args, true ) );
	}
}
// Stopsign_Public
