<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }



// Uninstall all settings and tables called via Setup and register_unstall hook

function ptest_uninstall_tables() {
	global $wpdb;
	
	// first remove all tables
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ptest_main");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ptest_question");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ptest_dimension");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ptest_answer");
	
	// then remove all options
	delete_option( 'ptest_options' );
	delete_option( 'ptest_db_version' );

}

?>