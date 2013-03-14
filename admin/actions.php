<?php
require('../../../../wp-blog-header.php');
auth_redirect();

// Queries for test actions
if($_REQUEST['action'] == 'edit' AND isset($_REQUEST['submit-test']) AND check_admin_referer('ptest_create_edit_test')) {
	
	$wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}ptest_main SET name=%s, description=%s,final_screen=%s WHERE ID=%d", $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['content'], $_REQUEST['test']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_dimension WHERE test_id=%d", $_REQUEST['test']));
	$test_id = $_REQUEST['test'];	
	// Insert dimensions
	$counter = 1;
	foreach ($_REQUEST['pdimension'] as $pdimension) {
		if($pdimension) {
			$explanation = $_REQUEST['pdimension_desc'];
			$ndimension = $_REQUEST['ndimension'];
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ptest_dimension(test_id, pdimension, explanation, ndimension) VALUES(%d, %s, %s, %s)", $test_id, $pdimension, $explanation[$counter-1], $ndimension[$counter-1])); 
		}
		$counter++;
	}		
	wp_redirect(get_option('home') . '/wp-admin/admin.php?page='.PTEST_PLUGIN_NAME.'/admin/test_manage.php&message=test_edited&test='.$test_id);
} 
elseif ($_REQUEST['action'] == 'new' AND isset($_REQUEST['submit-test']) AND check_admin_referer('ptest_create_edit_test')){
	$wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}ptest_main(name,description,final_screen,added_on) VALUES(%s,%s,%s,NOW())", $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['content']));
	$test_id = $wpdb->insert_id;
	// Insert dimensions
	$counter = 1;
	foreach ($_REQUEST['pdimension'] as $pdimension) {
		if($pdimension) {
			$explanation = $_REQUEST['pdimension_desc'];
			$ndimension = $_REQUEST['ndimension'];
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ptest_dimension(test_id, pdimension, explanation, ndimension) VALUES(%d, %s, %s, %s)", $test_id, $pdimension, $explanation[$counter-1], $ndimension[$counter-1])); 
		}
		$counter++;
	}
	wp_redirect(get_option('home') . '/wp-admin/admin.php?page='.PTEST_PLUGIN_NAME.'/admin/test_manage.php&message=test_added&test='.$test_id);
}		
elseif($_REQUEST['action'] == 'delete' AND isset($_REQUEST['submit-test']) AND check_admin_referer('ptest_delete_test')) {
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_main WHERE ID=%d", $_REQUEST['test']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_answer WHERE question_id=(SELECT ID FROM {$wpdb->prefix}ptest_question WHERE test_id=%d)", $_REQUEST['test']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_question WHERE test_id=%d", $_REQUEST['test']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_dimension WHERE test_id=%d", $_REQUEST['test']));
	wp_redirect(get_option('home').'/wp-admin/admin.php?page='.PTEST_PLUGIN_NAME.'/admin/test_manage.php&message=test_deleted&test='.$_REQUEST['test']);
}

// Queries for question actions

if ($_REQUEST['action'] == 'edit' AND isset($_REQUEST['submit-question']) AND check_admin_referer('ptest_create_edit_question')){
	$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ptest_question SET question=%s, explanation=%s WHERE ID=%d", $_REQUEST['content'], $_REQUEST['explanation'], $_REQUEST['question']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_answer WHERE question_id=%d", $_REQUEST['question']));
	$question_id = $_REQUEST['question'];	
	// Insert answers. $counter will skip over empty answers - $sort_order_counter won't.
	$counter = 1;
	$sort_order_counter = 1;
	foreach ($_REQUEST['answer'] as $answer_text) {
		if($answer_text) {
			$dimension = $_REQUEST['dimension'];
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ptest_answer(question_id, answer, dimension, sort_order) 
				VALUES(%d, %s, %s, %d)", $question_id, $answer_text, $dimension[$counter-1], $sort_order_counter)); 
			$sort_order_counter++;
		}
		$counter++;
	}	
	wp_redirect(get_option('home') . '/wp-admin/admin.php?page='.PTEST_PLUGIN_NAME.'/admin/question_manage.php&message=question_edited&test='.$_REQUEST['test']);
} 
elseif ($_REQUEST['action'] == 'new' AND isset($_REQUEST['submit-question']) AND check_admin_referer('ptest_create_edit_question')) {
	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ptest_question(test_id, question, explanation) VALUES(%d, %s, %s)", $_REQUEST['test'], $_REQUEST['content'], $_REQUEST['explanation']));
	$_REQUEST['question'] = $wpdb->insert_id;
	$question_id = $_REQUEST['question'];	
	// $counter will skip over empty answers - $sort_order_counter won't.
	$counter = 1;
	$sort_order_counter = 1;
	foreach ($_REQUEST['answer'] as $answer_text) {
		if($answer_text) {
			$dimension = $_REQUEST['dimension'];
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}ptest_answer(question_id, answer, dimension, sort_order) 
				VALUES(%d, %s, %s, %d)", $question_id, $answer_text, $dimension[$counter-1], $sort_order_counter)); 
			$sort_order_counter++;
		}
		$counter++;
	}
	wp_redirect(get_option('home') . '/wp-admin/admin.php?page='.PTEST_PLUGIN_NAME.'/admin/question_manage.php&message=question_added&test='.$_REQUEST['test']);
}
elseif($_REQUEST['action'] == 'delete' AND isset($_REQUEST['submit-question']) AND check_admin_referer('ptest_delete_question')) {
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_answer WHERE question_id=%d", $_REQUEST['question']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ptest_question WHERE ID=%d", $_REQUEST['question']));
	wp_redirect(get_option('home') . '/wp-admin/admin.php?page='.PTEST_PLUGIN_NAME.'/admin/question_manage.php&message=question_deleted&test='.$_REQUEST['test']);
}
exit;
?>