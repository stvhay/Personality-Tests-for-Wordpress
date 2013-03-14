<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

// creates all tables for the plugin called during register_activation hook

function ptest_install_tables ($dbversion) {	
   	global $wpdb;
	$installed_db = get_option('ptest_db_version');
	
	
	// Check for capability
	if ( !current_user_can('activate_plugins') ) return;
	
	/* add charset & collate like wp core
	$charset_collate = '';

	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}
	*/
	if($dbversion != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
		$sql = "CREATE TABLE {$wpdb->prefix}ptest_main (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					description mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					final_screen mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					added_on datetime NOT NULL,
					PRIMARY KEY  (ID)
				) ;
				CREATE TABLE {$wpdb->prefix}ptest_dimension (
					ID int(11) unsigned NOT NULL auto_increment,
					test_id int(11) unsigned NOT NULL,
					pdimension char(2) NOT NULL default '0',
					explanation mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					ndimension char(2) NOT NULL default '0',
					PRIMARY KEY  (ID)
				) ;
				CREATE TABLE {$wpdb->prefix}ptest_question (
					ID int(11) unsigned NOT NULL auto_increment,
					test_id int(11) unsigned NOT NULL,
					question mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					sort_order int(3) NOT NULL default 0,
					explanation mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					PRIMARY KEY  (ID),
					KEY test_id (test_id)
				) ;
				CREATE TABLE {$wpdb->prefix}ptest_answer (
					ID int(11) unsigned NOT NULL auto_increment,
					question_id int(11) unsigned NOT NULL,
					answer varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					dimension char(2) NOT NULL default '0',
					sort_order int(3) NOT NULL default 0,
					PRIMARY KEY  (ID)
				) ;";
				dbDelta($sql);
				update_option( "ptest_db_version", $dbversion );
	}

	$options = get_option('ptest_options');
	// set the default settings, if we didn't upgrade
	if ( empty( $options ) ) ptest_default_options();


}

/**
 * Setup the default option array for the plugin
 * 
 * @access internal
 * @return void
 */
function ptest_default_options() {

	$ptest_options['access_to_tests']		= "public";  		// public or members 
	$ptest_options['show_test_by']			= "question";		// question or all	
	update_option('ptest_options', $ptest_options);
}

?>