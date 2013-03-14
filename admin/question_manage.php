<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

require('functions.php');

$test_name = stripslashes($wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}ptest_main WHERE ID=%d", $_REQUEST['test'])));
?>

<div class="wrap">
	<h2><?php echo __('Add/Edit/Delete Questions in Test : ','ptest') . $test_name; ?></h2><br />
	<?php
	wp_enqueue_script( 'listman' );
	wp_print_scripts();
	if (isset($_REQUEST['message'])) {
	?>
	<div id="message" class="updated fade"><p>
		<?php 
		if($_REQUEST['message'] == 'question_deleted') _e('Question is deleted!','ptest');
		if($_REQUEST['message'] == 'question_edited') _e('Question is updated!','ptest');
		if($_REQUEST['message'] == 'question_added') _e('New question is added!','ptest');
		?>
	</p></div><!-- updated fade ends -->
	<?php }?>
	<p><a href="edit.php?page=<?php echo PTEST_PLUGIN_NAME; ?>/admin/question_form.php&amp;action=new&amp;test=<?php echo $_REQUEST['test'] ?>"><?php _e('Add a New Question','ptest')?></a></p>
	<table class="widefat">
		<thead>
		<tr>
			<th scope="col"><div style="text-align: center;">#</div></th>
			<th scope="col"><?php _e('Question','ptest') ?></th>
			<th scope="col"><?php _e('Answers','ptest') ?></th>
			<th scope="col" colspan="2"><?php _e('Actions','ptest') ?></th>
						
		</tr>
		</thead>
		<tbody id="the-list">
	<?php
	// Retrieve the questions
	$all_question = $wpdb->get_results("SELECT Q.ID,Q.question,(SELECT COUNT(*) FROM {$wpdb->prefix}ptest_answer WHERE question_id=Q.ID) AS answer_count
											FROM `{$wpdb->prefix}ptest_question` AS Q
											WHERE Q.test_id=$_REQUEST[test] ORDER BY Q.ID");
	if (count($all_question)) {
		$bgcolor = '';
		$class = ('alternate' == $class) ? '' : 'alternate';
		$question_count = 0;
		foreach($all_question as $question) {
			$question_count++;
			$all_answers = $wpdb->get_results("SELECT ID,answer,dimension FROM {$wpdb->prefix}ptest_answer WHERE question_id={$question->ID} ORDER BY sort_order");
			print "<tr id='question-{$question->ID}' class='$class'>\n";
			?>
			<th scope="row" style="text-align: center;"><?php echo $question_count ?></th>
			<td><?php echo stripslashes($question->question) ?></td>
			<td><?php echo $question->answer_count. " [ "; 
			foreach($all_answers as $answer) {
				echo $answer->dimension." ";
			}
			echo " ]";
			?>
			 
			</td>
			<td><a href='edit.php?page=<?php echo PTEST_PLUGIN_NAME; ?>/admin/question_form.php&amp;question=<?php echo $question->ID?>&amp;action=edit&amp;test=<?php echo $_REQUEST['test']?>' class='edit'><?php _e('Edit This Question','ptest'); ?></a></td>
			<td>
			<form name="post" action="<?php echo PTEST_PLUGIN_URL; ?>/admin/actions.php" method="post" id="post">
				<?php wp_nonce_field('ptest_delete_question'); ?>
				<input type="hidden" name="test" value="<?php echo $_REQUEST['test']?>" />
				<input type="hidden" name="question" value="<?php echo stripslashes($question->ID)?>" />
				<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
				<input type="hidden" name="action" value="delete" /> 
				<input type="submit" name="submit-question" value="<?php _e('Delete','ptest') ?>" style="font-weight: bold;" />
			</form>
			</td>
			</tr>
	<?php
			}
		} else {
	?>
		<tr style='background-color: <?php echo $bgcolor; ?>;'>
			<td colspan="4"><?php _e('No questiones found.','ptest') ?></td>
		</tr>
	<?php
	}
	?>
		</tbody>
	</table>	
</div><!-- wrap ends -->
