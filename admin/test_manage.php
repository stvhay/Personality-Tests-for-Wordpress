<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

require('functions.php');

?>

<div class="wrap">
	<h2><?php _e('Edit Tests','ptest'); ?></h2>
	<br />
	<?php
	wp_enqueue_script( 'listman' );
	wp_print_scripts();
	if (isset($_REQUEST['message'])) {
	?>
	<div id="message" class="updated fade"><p>
		<?php 
		if($_REQUEST['message'] == 'test_deleted') _e('Test is deleted!','ptest');
		if($_REQUEST['message'] == 'test_edited') _e('Test is updated!','ptest');
		if($_REQUEST['message'] == 'test_added') _e('New test is added!','ptest');
		?>
	</p></div><!-- updated fade ends -->
	<?php } ?>
	<p><a href="edit.php?page=<?php echo PTEST_PLUGIN_NAME; ?>/admin/test_form.php&amp;action=new"><?php _e('Add a New Test','ptest')?></a></p>
	<table class="widefat">
		<thead>
		<tr>
			<th scope="col"><div style="text-align: center;"><?php _e('ID','ptest') ?></div></th>
			<th scope="col"><?php _e('Test Title','ptest') ?></th>
			<th scope="col"><?php _e('Test Explanation','ptest') ?></th>
			<th scope="col" colspan="3"><?php _e('Actions','ptest') ?></th>
		</tr>
		</thead>		
		<tbody id="the-list">
		<?php
		// Retrieve the tests
		$all_test = $wpdb->get_results("SELECT Q.ID,Q.name,Q.description,Q.added_on,(SELECT COUNT(*) FROM {$wpdb->prefix}ptest_question WHERE test_id=Q.ID) AS question_count
											FROM `{$wpdb->prefix}ptest_main` AS Q ");			
		if (count($all_test)) {
			foreach($all_test as $test) {
				$class = ('alternate' == $class) ? '' : 'alternate';
				
				print "<tr id='test-{$test->ID}' class='$class'>\n";
				?>
				<th scope="row" style="text-align: center;"><?php echo $test->ID ?></th>
				<td><?php echo stripslashes($test->name)?></td>
				<td><?php echo stripslashes($test->description)?></td>
				<td><a href='edit.php?page=<?php echo PTEST_PLUGIN_NAME; ?>/admin/test_form.php&amp;action=edit&amp;test=<?php echo $test->ID?>' class='edit'><?php _e('Edit Test Properties','ptest'); ?></a></td>
				<td><a href='edit.php?page=<?php echo PTEST_PLUGIN_NAME; ?>/admin/question_manage.php&amp;test=<?php echo $test->ID?>' class='edit'><?php _e('Add/Edit/Delete Questions','ptest')?></a> [ <?php echo $test->question_count ?> ]</td>
				<td>
				<form name="post" action="<?php echo PTEST_PLUGIN_URL; ?>/admin/actions.php" method="post" id="post">
				<?php wp_nonce_field('ptest_delete_test'); ?>
				<input type="hidden" name="action" value="delete" />
				<input type="hidden" name="test" value="<?php echo $test->ID; ?>" />
				<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
				<input type="submit" name="submit-test" value="<?php _e('Delete','ptest') ?>" style="font-weight: bold;" tabindex="4" />
				</form>
				</td>
				</tr>
		<?php
				}
			} else {
		?>
			<tr>
				<td colspan="5"><?php _e('No Tests found.','ptest') ?></td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
</div><!-- wrap ends -->
