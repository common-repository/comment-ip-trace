<?php
/**
 * Plugin Name: Comment Location Tracker
 * Description: Traces the IP of comment leavers in Wordpress
 * Author: Jason Grim
 * Author URI: http://jgwebdevelopment.com
 * Plugin URI: http://jgwebdevelopment.com/plugins/comment-ip-trace
 * Version: 1.1.1
 * Revision Date: June 30, 2013
 * Requires at least: WP 3.0
 * Tested up to: WP 3.0
 */

// $$ Mind is Money $$ //

// Set plugin defines
define ( 'CIT_DB_VERSION', 1.0 );
define ( 'CIT_DIR', dirname( __FILE__ ) );
define ( 'CIT_URL', plugin_dir_url( __FILE__ ) );
define ( 'CIT_FNS_DIR', CIT_DIR . '/fn' );
define ( 'CIT_FNS_URL', CIT_URL . '/fn' );
//define ( 'CIT_IMG_DIR', CIT_DIR . '/img' );
//define ( 'CIT_IMG_URL', CIT_URL . '/img' );
//define ( 'CIT_JS_DIR', CIT_DIR . '/js' );
//define ( 'CIT_JS_URL', CIT_URL . '/js' );
//define ( 'CIT_CSS_DIR', CIT_DIR . '/css' );
//define ( 'CIT_CSS_URL', CIT_URL . '/css' );

// Load core functionality
require_once CIT_FNS_DIR . '/core.php';

// Direction
if ( is_admin() ) {
	include CIT_FNS_DIR . '/admin.php';
} else {
	include CIT_FNS_DIR . '/front-end.php'; // @todo: add short codes
	//include CIT_FNS_DIR . '/template-tags.php'; @todo: add custom template tags
}
