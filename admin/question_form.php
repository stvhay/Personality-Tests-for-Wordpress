<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

require('functions.php'); 
$answer_count = 2;

// Retreive necessary data
$all_dimensions = $wpdb->get_results($wpdb->prepare("SELECT ID,pdimension,explanation,ndimension FROM {$wpdb->prefix}ptest_dimension WHERE test_id=%d ORDER BY ID", $_REQUEST['test']));

if($_REQUEST['action'] == 'edit') { 
	$action = 'edit';
	$form_title = __('Edit Question','ptest');
	$question = $wpdb->get_row($wpdb->prepare("SELECT question, explanation FROM {$wpdb->prefix}ptest_question WHERE ID=%d", $_REQUEST['question']));
	$all_answers = $wpdb->get_results($wpdb->prepare("SELECT answer,dimension FROM {$wpdb->prefix}ptest_answer WHERE question_id=%d ORDER BY sort_order", $_REQUEST['question']));	
	if($answer_count < count($all_answers)) $answer_count = count($all_answers) ;
} elseif ($_REQUEST['action'] == 'new' OR $_REQUEST['action']=="") {
	$action = 'new';
	$form_title = __('Add New Question','ptest');
}

?>

<div class="wrap">
	<h2><?php echo $form_title.' : '.$_REQUEST['question']; ?></h2>
	<p><a href="edit.php?page=<?php echo PTEST_PLUGIN_NAME; ?>/admin/question_manage.php&amp;test=<?php echo $_REQUEST['test']?>"><?php _e('Go to Questions Page','ptest') ?></a></p>
	<div id="titlediv">
	<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />
	</div>

	<?php ptest_add_editor_js(); ?>
	
	<style type="text/css">
	.qtrans_title, .qtrans_title_wrap {display:none;}
	</style>

	<script type="text/javascript">
	var answer_count = <?php echo $answer_count?>;
	// This function adds a new input field to the form when an additional answer is required
	function newAnswer() {
		answer_count++;
		var para = document.createElement("p");
		var textarea = document.createElement("textarea");
		textarea.setAttribute("name", "answer[]");
		textarea.setAttribute("rows", "3");
		textarea.setAttribute("cols", "50");
		para.appendChild(textarea);
		var label = document.createElement("label");
		label.setAttribute("for", "dimension_" + answer_count);
		label.appendChild(document.createTextNode("<?php _e('Pole:','ptest'); ?>"));
		para.appendChild(label);
		var select = document.createElement("select");
		select.setAttribute("name", "dimension[]");
		select.setAttribute("id", "dimension_" + answer_count);
		select.className = "dimension";
		var option = document.createElement("option");	
		option.setAttribute("value", "");
		option.appendChild(document.createTextNode("<?php echo "--"; ?>"));
		select.appendChild(option);
		<?php  foreach($all_dimensions as $dimensions) { ?>
		var option = document.createElement("option");		
		option.setAttribute("value", "<?php echo $dimensions->pdimension; ?>");
		option.appendChild(document.createTextNode("<?php echo $dimensions->pdimension; ?>"));
		select.appendChild(option);
		<?php } ?>
		para.appendChild(select);	
		document.getElementById("extra-answers").appendChild(para);
	}
	</script>
	<!-- Answer entry form starts  -->
	<form name="post" action="<?php echo PTEST_PLUGIN_URL; ?>/admin/actions.php" method="post" id="post">
		<div id="poststuff">
			<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
				<div class="postbox">
					<h3 class="hndle"><?php _e('Question','ptest') ?></span></h3>
					<div class="inside">
						<?php the_editor(stripslashes($question->question)); ?>
					</div>
				</div>						
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Answers', 'ptest') ?></span></h3>
					<div class="inside">			
						<?php
						for($i=1; $i<=$answer_count; $i++) { ?>
							<p>
							<textarea name="answer[]" class="answer" rows="3" cols="50"><?php if($action == 'edit') echo stripslashes($all_answers[$i-1]->answer); ?></textarea>
							<label for="dimension_<?php echo $i?>"><?php _e('Pole:','ptest'); ?></label>
							<select name="dimension[]" id="dimension_<?php echo answer_count; ?>" class="dimension">
							<option value="" <?php if($all_answers[$i-1]->dimension =="") echo "selected=\"selected\""; ?>>--</option>
							<?php foreach($all_dimensions as $dimensions) {	
								echo "<option value=\"".$dimensions->pdimension."\""; 
								if(($action == 'edit') && ($all_answers[$i-1]->dimension == $dimensions->pdimension)) { echo "selected=\"selected\""; }
								echo "\">".$dimensions->pdimension."</option>";
							}
							?>
							</select>							
							</p>
						<?php } ?>			
						<div id="extra-answers"></div>
						<a href="javascript:newAnswer();"><?php _e("Add New Answer"); ?></a>		
					</div><!-- inside ends -->
				</div><!-- postbox ends -->
				
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Explanation') ?></span></h3>
					<div class="inside">		
						<textarea name="explanation" rows="5" cols="50"><?php echo stripslashes($question->explanation)?></textarea>
						<br />
						<p><?php _e('You can use this field to make an explanation.', 'ptest') ?></p>
					</div><!-- inside ends -->
				</div><!-- postbox ends -->
				
			</div><!-- postarea ends -->	
			
			<p class="submit">
				<?php wp_nonce_field('ptest_create_edit_question'); ?>
				<input type="hidden" name="test" value="<?php echo $_REQUEST['test']?>" />
				<input type="hidden" name="question" value="<?php echo stripslashes($_REQUEST['question'])?>" />
				<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
				<input type="hidden" name="action" value="<?php echo $action ?>" /> 
				<span id="autosave"></span>
				<input type="submit" name="submit-question" value="<?php _e('Save','ptest') ?>" style="font-weight: bold;" />
			</p>
		</div><!-- poststuff ends -->
	</form>

</div><!-- wrap ends -->