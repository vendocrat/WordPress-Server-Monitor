<?php
/**
 * Server Monitor
 *
 * @package    vendocrat
 * @subpackage Plugins/Server Monitor
 *
 * @since      2014-09-26
 * @version    2014-09-26
 *
 * @author     Poellmann Alexander Manfred <alex@vendocr.at>
 * @copyright  Copyright 2014 vendocrat. All Rights Reserved.
 * @link       http://vendocr.at/
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'vendocrat_Server_Monitor' ) ) :

class vendocrat_Server_Monitor {

	/* File var */
	private $file;

	/* Basic vars */
	public $version;
	public $plugin_url;
	public $plugin_dir;

	/* Memory */
	var $memory = array();

	/**
	 * Constructor
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function __construct( $file ) {
		// setup dir/uri
		$this->file = $file;
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		$this->plugin_dir = trailingslashit( dirname( $file ) );

		// definitions
		$this->defines();

		// load functions and classes
		$this->load_functions();
		$this->load_classes();

		// load text domain
		add_action( 'plugins_loaded', array( &$this, 'load_plugin_textdomain' ) );

		// register widgets
		add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widget' ) );
	}

	/**
	 * Definitions
	 *
	 * @return void
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function defines() {
		// Plugin
		define( 'V_SERVER_MONITOR_DIR', $this->plugin_dir );
		define( 'V_SERVER_MONITOR_URI', $this->plugin_url );
	}

	/**
	 * Load functions
	 *
	 * @return void
	 *
	 * @since 2014-09-07
	 * @version 2014-09-07
	 **************************************************/
	function load_functions() {}

	/**
	 * Load classes
	 *
	 * @return void
	 *
	 * @since 2014-09-07
	 * @version 2014-09-07
	 **************************************************/
	function load_classes() {}

	/**
	 * Load theme textdomain
	 *
	 * @return void
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	static function load_plugin_textdomain() {
		load_plugin_textdomain( 'vendocrat-server-monitor', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add dashboard widget
	 *
	 * @return void
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'wp_server_load_widget',
			__( 'Server Monitor', 'vendocrat-server-monitor' ),
			array( &$this, 'server_monitor' )
		);
	}

	/**
	 * Server Monitor
	 *
	 * @return html $output
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function server_monitor() {
		// get vars
		$name   = trim(exec('hostname'));
		$ip     = gethostbyname($name);
		$path   = ABSPATH;
		$uptime = exec('uptime');
		$server = $_SERVER['SERVER_SOFTWARE'];
		$php    = (function_exists('phpversion')) ? phpversion() : __( 'N/A', 'vendocrat-server-monitor' );
		$mysql  = (function_exists('mysql_get_server_info')) ? mysql_get_server_info() : __( 'N/A', 'vendocrat-server-monitor' );
		$mysql  = (function_exists('mysql_get_server_info')) ? mysql_get_server_info() : __( 'N/A', 'vendocrat-server-monitor' );
		$dbsize = $this->get_current_db_size();

		// host name
		$host = '<span title="'. esc_attr($name) .'">'. $this->str_truncate( $name ) .'</span>';

		// server path
		$path = '<span title="'. esc_attr($path) .'">'. $this->str_truncate( $path ) .'</span>';

		// server load
		preg_match( "/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $uptime, $averages );

		// up time
		$uptime = explode( ' up ', $uptime );
		$uptime = explode( ',', $uptime[1] );

		// prepare output
		$output = '<table>';

		$output.= ($host) ? $this->get_row( __( 'Host Name', 'vendocrat-server-monitor' ), $host ) : '';
		$output.= ($ip)   ? $this->get_row( __( 'Server IP', 'vendocrat-server-monitor' ), $ip ) : '';
		$output.= ($path) ? $this->get_row( __( 'Server Path', 'vendocrat-server-monitor' ), $path ) : '';

		if ( is_array($averages) AND ! empty( $averages[1] ) AND ! empty( $averages[2] ) AND ! empty( $averages[3] ) ) :
		$output.= $this->get_row( __( 'Server Load', 'vendocrat-server-monitor' ), '<h3>'. $averages[1] .' '. $averages[2] .' '. $averages[3] .'</h3>' );
		endif;

		if ( is_array($uptime) AND ! empty( $uptime[0] ) AND ! empty( $uptime[1] ) ) :
		$output.= ($uptime) ? $this->get_row( __( 'Server up since', 'vendocrat-server-monitor' ), $uptime[0] .', '. $uptime[1] ) : '';
		endif;

		$output.= ($server) ? $this->get_row( __( 'Server Info', 'vendocrat-server-monitor' ), $server ) : '';
		$output.= ($php)    ? $this->get_row( __( 'PHP Version', 'vendocrat-server-monitor' ), $php ) : '';
		$output.= ($mysql)  ? $this->get_row( __( 'MySQL Version', 'vendocrat-server-monitor' ), $mysql ) : '';
		$output.= ($dbsize) ? $this->get_row( __( 'Database Size', 'vendocrat-server-monitor' ), $dbsize ) : '';

		$output.= '</table>';
			
		echo $output;
	}

	/**
	 * Get table row
	 *
	 * @return html $output
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function get_row( $left = false, $right = false ) {
		if ( ! $left AND ! $right )
			return;

		if ( ( $left AND ! $right ) OR ( ! $left AND $right ) ) {
			$td = '<td colspan="2">';
		} else {
			$td = '<td>';
		}

		// emphasis left cell
		if ( $left )
			$left = '<strong>'. $left .'</strong>';

		// prepare output
		$output = '<tr>';
		$output.= ($left)  ? $td . $left .'</td>'  : '';
		$output.= ($right) ? $td . $right .'</td>' : '';
		$output.= '</tr>';

		return $output;
	}

	/**
	 * Truncate string and append hellip
	 *
	 * @return string $string
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function str_truncate( $string ) {
		$string = trim($string);

		if ( strlen($string) > 30 ) {
			$string = substr( $string, 0, 30 ) . '&hellip;';
		}

		return $string;
	}

	/**
	 * Get database size
	 *
	 * @return string $total_size
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function get_current_db_size(){
		global $wpdb;

		$total_size  = 0;
		$usage_row   = 0;
		$usage_data  = 0;
		$usage_index = 0;

		$tables_status = $wpdb->get_results("SHOW TABLE STATUS");

		foreach ( $tables_status as $table_status ) {
			$usage_row   += $table_status->Rows;
			$usage_data  += $table_status->Data_length;
			$usage_index += $table_status->Index_length;
		}

		$total_size = $usage_data + $usage_index;

		return $this->format_size($total_size);
	}

	/**
	 * Format given size
	 *
	 * @return string $size
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function format_size($size) {
		if( $size / 1073741824 > 1 ) {
			return number_format_i18n( $size/1073741824, 2 ) .' GB';
		} elseif ( $size / 1048576 > 1 ) {
			return number_format_i18n( $size/1048576, 1 ) .' MB';
		} elseif ( $size / 1024 > 1 ) {
			return number_format_i18n( $size/1024, 1 ) .' KB';
		} else {
			return number_format_i18n( $size, 0 ) .' bytes';
		}
	}

} // END Class

endif;

/*
 * NO MORE LOVE TO GIVE
 */