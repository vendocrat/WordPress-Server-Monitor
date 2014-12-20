<?php
/**
 * Server Monitor
 *
 * @package    vendocrat
 * @subpackage Plugins/Server Monitor
 *
 * @since      2014-09-26
 * @version    2014-12-17
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

	/* Transient */
	var $transient_key;
	var $transient_expiry;

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

		// set transient
		$this->transient_key = 'v_server_monitor';
		$this->transient_expiry = 3600;

		// load text domain
		add_action( 'plugins_loaded', array( &$this, 'load_plugin_textdomain' ) );

		// register widgets
		add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widgets' ) );

		// add styles to admin_head
		add_action( 'admin_head', array( &$this, 'custom_styles' ) );
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
	 * @version 2014-09-29
	 **************************************************/
	static function load_plugin_textdomain() {
		load_plugin_textdomain( 'vendocrat-server-monitor', false, basename( V_SERVER_MONITOR_DIR ) . '/languages/' );
	}

	/**
	 * Add dashboard widget
	 *
	 * @return void
	 *
	 * @since 2014-09-26
	 * @version 2014-09-26
	 **************************************************/
	function add_dashboard_widgets() {
		wp_add_dashboard_widget(
			'wp_widget_server_monitor',
			sprintf( __( '%s: General', 'vendocrat-server-monitor' ), __( 'Server Monitor', 'vendocrat-server-monitor' ) ),
			array( &$this, 'server_monitor' )
		);

		wp_add_dashboard_widget(
			'wp_widget_server_monitor_php',
			sprintf( __( '%s: PHP &amp; Database', 'vendocrat-server-monitor' ), __( 'Server Monitor', 'vendocrat-server-monitor' ) ),
			array( &$this, 'server_monitor_php' )
		);

		wp_add_dashboard_widget(
			'wp_widget_server_monitor_wp',
			__( 'System Status', 'vendocrat-server-monitor' ),
			array( &$this, 'server_monitor_wp' )
		);
	}

	/**
	 * Custom styles
	 *
	 * @return html $output
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	function custom_styles() {
		ob_start();
?>
<style type="text/css">
table.server-monitor {
	width:100%;}

	table.server-monitor > tbody {}

		table.server-monitor > tbody > tr + tr td {
			border-top:1px solid #ddd;}

		table.server-monitor > tbody > tr td:first-child {
			width:140px;}

.server-monitor-credit {
	margin:0 0 0 -5px;
	padding-left:0;
	
	font-size:12px;
	text-align:right;

	list-style:none;}

	.server-monitor-credit > li {
		display:inline-block;
		margin:0;
		padding-right:5px;
		padding-left:5px;}
</style>
<?php
		$output = ob_get_clean();
 
		echo $output;
	}

	/**
	 * Wrap widget content
	 *
	 * @return html $output
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	public function get_widget( $widget ) {
		$output = '<table class="server-monitor">';
		$output.= '<tbody>';
		$output.= $widget;
		$output.= '</tbody>';
		$output.= '</table>';

		$output.= '<ul class="server-monitor-credit">';
		$output.= '<li><a href="https://github.com/vendocrat/WordPress-Server-Monitor" target="_blank">'. __( 'Contribute', 'vendocrat-server-monitor' ) .'</a></li>';
		$output.= '<li><a href="https://twitter.com/vendocrat" target="_blank">'. __( 'Follow', 'vendocrat-server-monitor' ) .'</a></li>';
		$output.= '<li><a href="http://vendocr.at/donate" target="_blank">'. __( 'Donate', 'vendocrat-server-monitor' ) .'</a></li>';
		$output.= '</ul>';

		echo $output;
	}

	/**
	 * Server Monitor
	 *
	 * @return void
	 *
	 * @since 2014-09-26
	 * @version 2014-12-20
	 **************************************************/
	public function server_monitor() {
		// get transient data
		$data = $this->get_data();
		extract($data);

		// don't save uptime in transient
		$uptime = exec('uptime');

		// host name
		$host = '<span title="'. esc_attr($name) .'">'. $this->str_truncate( $name ) .'</span>';

		// server path
		$path = '<span title="'. esc_attr($path) .'">'. $this->str_truncate( $path ) .'</span>';

		// server load
		preg_match( "/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $uptime, $averages );

		// up time
		$uptime = explode( ' up ', $uptime );
		$uptime = ( array_key_exists( 1, $uptime ) ) ? explode( ',', $uptime[1] ) : __( 'N/A', 'vendocrat-server-monitor' );

		// prepare output
		$output = '';

		$output.= ($host) ? $this->get_row( __( 'Host Name', 'vendocrat-server-monitor' ),   $host ) : '';
		$output.= ($ip)   ? $this->get_row( __( 'Server IP', 'vendocrat-server-monitor' ),   $ip )   : '';
		$output.= ($path) ? $this->get_row( __( 'Server Path', 'vendocrat-server-monitor' ), $path ) : '';

		if ( is_array($averages) AND ! empty( $averages[1] ) AND ! empty( $averages[2] ) AND ! empty( $averages[3] ) ) :
		$output.= $this->get_row( __( 'Server Load', 'vendocrat-server-monitor' ), '<strong>'. $averages[1] .' '. $averages[2] .' '. $averages[3] .'</strong>' );
		endif;

		if ( is_array($uptime) AND ! empty( $uptime[0] ) AND ! empty( $uptime[1] ) ) :
		$output.= ($uptime) ? $this->get_row( __( 'Server up since', 'vendocrat-server-monitor' ), $uptime[0] .', '. $uptime[1] ) : '';
		endif;

		$output.= ($server) ? $this->get_row( __( 'Server Info', 'vendocrat-server-monitor' ), $server ) : '';

		echo $this->get_widget( $output );
	}

	/**
	 * Server Monitor: PHP & Database
	 *
	 * @return void
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	public function server_monitor_php() {
		// get transient data
		$data = $this->get_data();
		extract($data);

		// prepare output
		$output = '';

		$output.= ($php) ? $this->get_row( __( 'PHP Version', 'vendocrat-server-monitor' ), $php ) : '';

		$output.= ($php_post_max_size) ? $this->get_row( 'PHP Post Max Size', $php_post_max_size ) : '';
		$output.= ($php_max_execution_time) ? $this->get_row( __( 'PHP Time Limit', 'vendocrat-server-monitor' ), $php_max_execution_time ) : '';
		$output.= ($php_max_input_vars) ? $this->get_row( 'PHP Max Input Vars', $php_max_input_vars ) : '';

		$output.= ($mysql)  ? $this->get_row( __( 'MySQL Version', 'vendocrat-server-monitor' ), $mysql )  : '';
		$output.= ($dbsize) ? $this->get_row( __( 'Database Size', 'vendocrat-server-monitor' ), $dbsize ) : '';

		echo $this->get_widget( $output );
	}

	/**
	 * Server Monitor: WordPress
	 *
	 * @return void
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	public function server_monitor_wp() {
		// get transient data
		$data = $this->get_data();
		extract($data);

		// prepare output
		$output = '';

		$output.= ($version)    ? $this->get_row( __( 'WordPress Version', 'vendocrat-server-monitor' ), $version ) : '';
		$output.= ($multi)      ? $this->get_row( __( 'Multisite?', 'vendocrat-server-monitor' ), $multi ) : '';
		$output.= ($plugins)    ? $this->get_row( __( 'Active Plugins', 'vendocrat-server-monitor' ), $plugins ) : '';
		$output.= ($memory)     ? $this->get_row( 'Memory Limit', $memory ) : '';
		$output.= ($max_upload) ? $this->get_row( 'Max Upload Size', $max_upload ) : '';
		$output.= ($debug)      ? $this->get_row( __( 'Debug Mode', 'vendocrat-server-monitor' ), $debug ) : '';
		$output.= ($lang)       ? $this->get_row( __( 'Language', 'vendocrat-server-monitor' ), $lang ) : '';
		$output.= ($timezone)   ? $this->get_row( __( 'Timezone', 'vendocrat-server-monitor' ), $timezone ) : '';

		echo $this->get_widget( $output );
	}

	/**
	 * Get data
	 *
	 * @return array
	 *
	 * @since 2014-10-03
	 * @version 2014-12-20
	 **************************************************/
	public function get_data() {
		$data = array();

		$transient_key    = $this->transient_key;
		$transient_expiry = $this->transient_expiry;

		delete_transient( $transient_key );

		if ( ( $data = get_transient($transient_key) ) === false ) {
			// server
			$data['name']    = trim(exec('hostname'));
			$data['ip']      = gethostbyname($data['name']);
			$data['path']    = ABSPATH;
			$data['server']  = $_SERVER['SERVER_SOFTWARE'];

			//php
			$data['php'] = (function_exists('phpversion')) ? phpversion() : __( 'N/A', 'vendocrat-server-monitor' );
			$data['php_post_max_size']      = (function_exists('ini_get')) ? size_format( $this->format_ini_size( ini_get('post_max_size') ) ) : __( 'N/A', 'vendocrat-server-monitor' );
			$data['php_max_execution_time'] = (function_exists('ini_get')) ? ini_get('max_execution_time') : __( 'N/A', 'vendocrat-server-monitor' );
			$data['php_max_input_vars']     = (function_exists('ini_get')) ? ini_get('max_input_vars') : __( 'N/A', 'vendocrat-server-monitor' );

			// database
			$data['mysql']   = $this->get_db_version();
			$data['dbsize']  = $this->get_current_db_size();

			// WordPress
			$data['version']    = get_bloginfo('version');;
			$data['multi']      = ( is_multisite() ) ? __( 'Yes', 'vendocrat-server-monitor' ) : __( 'No', 'vendocrat-server-monitor' );
			$data['plugins']    = count( (array) get_option( 'active_plugins' ) );
			$data['memory']     = $this->get_wp_memory();
			$data['max_upload'] = size_format( wp_max_upload_size() );
			$data['debug']      = ( defined('WP_DEBUG') && WP_DEBUG ) ? __( 'Yes', 'vendocrat-server-monitor' ) : __( 'No', 'vendocrat-server-monitor' );
			$data['lang']       = get_locale();
			$data['timezone']   = date_default_timezone_get();

			set_transient( $transient_key, $data, $transient_expiry );
		}

		return $data;
	}

	/**
	 * Get wp memory limit
	 *
	 * @return String $memory WP Memory Limit
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	function get_wp_memory() {
		$memory = $this->format_ini_size( WP_MEMORY_LIMIT );
		$memory = size_format( $memory );

		return ($memory) ? $memory : __( 'N/A', 'vendocrat-server-monitor' );
	}

	/**
	 * Get database version
	 *
	 * @return string Database version
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	function get_db_version() {
		global $wpdb;

		return ($wpdb->db_version()) ? $wpdb->db_version() : __( 'N/A', 'vendocrat-server-monitor' );
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
	function format_size( $size ) {
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

	/**
	 * Format ini sizes
	 *
	 * @return string $size
	 *
	 * @since 2014-12-20
	 * @version 2014-12-20
	 **************************************************/
	function format_ini_size( $size ) {
		$value  = substr( $size, -1 );
		$return = substr( $size, 0, -1 );

		switch ( strtoupper( $value ) ) {
			case 'P' :
				$return*= 1024;
			case 'T' :
				$return*= 1024;
			case 'G' :
				$return*= 1024;
			case 'M' :
				$return*= 1024;
			case 'K' :
				$return*= 1024;
		}

		return $return;
	}

} // END Class

endif;

/*
 * NO MORE LOVE TO GIVE
 */