<?php
/*
Plugin Name: Personality Tests for Wordpress
Plugin URI: http://www.e-ucy.com/ptest
Description: PTest enables Wordpress site owners to create personality tests and serve these tests to visitors very easily. 
Version: 1.3
Author: UCY
Author URI: http://www.e-ucy.com 

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


if (!class_exists('ptest_loader')) {
	class ptest_loader {
		static $add_script = false;
		var $version     = '1.3';
		static $dbversion   = '1.0';
		var $minium_WP   = '2.8';

		function ptest_loader () {
			// Stop the plugin if we missed the requirements
			if ( !$this->ptest_required_version() )	return;
			// Define constants
			$this->define_constants();
			$this->plugin_name = plugin_basename(__FILE__);
			// Init options & tables during activation & deregister init option
			register_activation_hook( $this->plugin_name, array(__CLASS__, 'ptest_activate') );
			//add_action('activate_ptest/ptest.php', array(__CLASS__, 'ptest_activate'));	
			// Register a uninstall hook to remove all tables & option automatic
			register_uninstall_hook( $this->plugin_name, array(__CLASS__, 'ptest_uninstall') );
			
			load_plugin_textdomain('ptest', false, dirname( plugin_basename(__FILE__) ) . '/lang' );
			if ( is_admin() ) {	
				add_action( 'admin_menu', array(__CLASS__, 'ptest_add_menu_links' ) );			
			} else {
				add_shortcode( 'PTEST', array(__CLASS__, 'ptest_shortcode' ) );			
				add_action('wp_print_styles', array(__CLASS__, 'ptest_load_styles') );
				add_action('wp_footer', array(__CLASS__, 'ptest_load_scripts' ));
				// Add a version number to the header
				add_action('wp_head', create_function('', 'echo "\n<meta name=\'PTest\' content=\'' . $this->version . '\' />\n";') );
				
			}
		}
		
		// Define global constants
		function define_constants() {
			define( 'PTEST_VERSION', $this->version );
			if ( ! defined( 'PTEST_PLUGIN_BASENAME' ) )
				define( 'PTEST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			if ( ! defined( 'PTEST_PLUGIN_NAME' ) )
				define( 'PTEST_PLUGIN_NAME', trim( dirname( PTEST_PLUGIN_BASENAME ), '/' ) );
			if ( ! defined( 'PTEST_PLUGIN_DIR' ) )
				define( 'PTEST_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . PTEST_PLUGIN_NAME );
			if ( ! defined( 'PTEST_PLUGIN_URL' ) )
				define( 'PTEST_PLUGIN_URL', WP_PLUGIN_URL . '/' . PTEST_PLUGIN_NAME );
		}
		
		// This function scans all the content pages that wordpress outputs for the special code. If the code is found, it will replace the requested test.
		function ptest_shortcode( $attr ) {
			$test_id = $attr[0];
			$contents = '';
			if(is_numeric($test_id)) { 
				ob_start();
				include(ABSPATH . 'wp-content/plugins/'.PTEST_PLUGIN_NAME.'/show_test.php');
				$contents = ob_get_contents();
				ob_end_clean();
			}
			self::$add_script = true;
			return $contents;
		}
		
		// Load scripts	when PTEST shortcode is found in a page
		function ptest_load_scripts() {
			if ( self::$add_script ) {			
				// Load jquery and the plugin's js immediately after jquery		
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'ptest_js', plugins_url(PTEST_PLUGIN_NAME.'/inc/script.js'), array('jquery'), '2.50', true);
				// Translate the strings in js file
				$ptest_js_objects = array(
			    	'no_answer' => __('You did not select any answer. Are you sure you want to continue?', 'ptest'),
					'show_answer' => __('Show Answer', 'ptest'),
					'next' => __('Next >', 'ptest')
					);
				wp_localize_script('ptest_js', 'ptest_js_translate', $ptest_js_objects);
				wp_print_scripts('ptest_js');
			}
		}

		// Load styles when PTEST shortcode is found in a page
		function ptest_load_styles() {
			//if ( self::$add_script ) {	
			wp_register_style('Ptest_Plugin_Style', plugins_url(PTEST_PLUGIN_NAME.'/inc/style.css'));
			wp_enqueue_style('Ptest_Plugin_Style');	
			//} 
		}		
		
		// Check min required WP version
		function ptest_required_version() {
			global $wp_version;
			// Check for WP version installation
			$wp_ok  =  version_compare($wp_version, $this->minium_WP, '>=');
			if ( ($wp_ok == FALSE) ) {
				add_action(
					'admin_notices', 
					create_function(
						'', 
						'global $ptest_plugin; printf (\'<div id="message" class="error"><p><strong>\' . __(\'Sorry, Ptest works only under WordPress %s or higher\', "ptest" ) . \'</strong></p></div>\', $ptest_plugin->minium_WP );'
					)
				);
				return false;
			}
			return true;
		}

		// Populate administration menu of the plugin
		function ptest_add_menu_links() {
			global $wp_version, $_registered_pages;
			add_menu_page( __('PTest Overview', 'ptest'),__('PTest', 'ptest'), 'activate_plugins', 'ptest_main', array(__CLASS__, 'ptest_menu_overview_page' ) );
			add_submenu_page('ptest_main', __('Add New Test', 'ptest'), __('Add New Test', 'ptest'), 'administrator', PTEST_PLUGIN_NAME.'/admin/test_form.php');
			add_submenu_page('ptest_main', __('Edit Tests', 'ptest'), __('Edit Tests', 'ptest'), 'administrator', PTEST_PLUGIN_NAME.'/admin/test_manage.php');
			add_submenu_page('ptest_main', __('Settings', 'ptest'), __('Settings', 'ptest'), 'administrator', PTEST_PLUGIN_NAME.'/admin/main_options.php');
			// Register the pages to WP
			$code_pages = array('test_form.php','test_manage.php', 'question_form.php', 'question_manage.php', 'actions.php');
			foreach($code_pages as $code_page) {
				$hookname = get_plugin_page_hookname(PTEST_PLUGIN_NAME.'/admin/'.$code_page, '' );
				$_registered_pages[$hookname] = true;
			}	 
		}
		
		// Create PTest main page content
		function ptest_menu_overview_page() {
			global $title;
			include_once (dirname (__FILE__) . '/admin/main_overview.php');
		}
	
		// Create tables and register plugin options to wp_options
		function ptest_activate() {
			global $wpdb, $dbversion;
			$db=self::$dbversion;
			include_once (dirname (__FILE__) . '/admin/main_install.php');
			ptest_install_tables($db);
		}
		
		// Uninstall tables and clean plugin options for wp_options
		function ptest_uninstall() {
			global $wpdb;
			include_once (dirname (__FILE__) . '/admin/main_uninstall.php');
			ptest_uninstall_tables();
		}
		
	}
	
	// Start the plugin
	global $ptest_plugin;
	$ptest_plugin = new ptest_loader();
}


?>