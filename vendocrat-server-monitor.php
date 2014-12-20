<?php
/**
/* Plugin Name: vendocrat Server Monitor
 * Plugin URI:  http://vendocr.at/
 * Description: <strong>Adds a Server Monitor widget to your WordPress Dashboard.</strong> Handcrafted with &hearts; by <a href='http://vendocr.at/'>vendocrat</a> in Vienna &amp; Rome.
 * Version:     0.2.0
 * Author:      vendocrat
 * Author URI:  http://vendocr.at/
 * License:     GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// check permissions
if ( ! is_admin() )
	return;

// require classes
require_once( 'classes/class-server-monitor.php' );

global $vendocrat_server_monitor;
$vendocrat_server_monitor = new vendocrat_Server_Monitor( __FILE__ );
$vendocrat_server_monitor->version = '0.2.0';

/*
 * E fatto!
 */