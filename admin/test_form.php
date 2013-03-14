<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

require('functions.php');
$dimension_count = 4;
$all_dimensions = array(); 


// Populate form fields according to the action
if($_REQUEST['action']== 'edit') { 
	$action = 'edit';
	$form_title = __('Edit Test','ptest');
	$dtest = array();
	$dtest = $wpdb->get_row($wpdb->prepare("SELECT name,description,final_screen FROM {$wpdb->prefix}ptest_main WHERE ID=%d", $_REQUEST['test']));
	$final_screen = stripslashes($dtest->final_screen);
	$all_dimensions = $wpdb->get_results($wpdb->prepare("SELECT ID, pdimension, explanation, ndimension FROM {$wpdb->prefix}ptest_dimension WHERE test_id=%d", $_REQUEST['test'])); 
	if($dimension_count < count($all_dimensions)) $dimension_count = count($all_dimensions) ;
} elseif ($_REQUEST['action'] == 'new' OR $_REQUEST['action']=="") {
	$action = 'new';
	$form_title = __('Add New Test','ptest');
	$final_screen = __('<p>Congratulations - you have completed %%TEST_NAME%%.</p>\n\n<p>Your personality profile %%PROFILE_CODE%%. </p>','ptest');
}

?>

<div class="wrap">
	<h2><?php echo $form_title.' : '.$_REQUEST['test']; ?></h2>
	<?php ptest_add_editor_js();	
	// Below script adds a new input field to the form when an additional dimension is required
	?>
	<script type="text/javascript">
	var dimension_count = <?php echo $dimension_count?>;
	function newDimension() {
		dimension_count++;
		var para = document.createElement("p");
		var label = document.createElement("label");
		label.setAttribute("for", "pdimension_" + dimension_count);
		label.appendChild(document.createTextNode("<?php _e('One Pole:','ptest'); ?>"));
		para.appendChild(label);
		var input = document.createElement("input");
		input.setAttribute("type", "text");
		input.setAttribute("name", "pdimension[]");
		input.className = "pdimension";
		input.setAttribute("value", "");
		input.setAttribute("id", "pdimension_" + dimension_count);
		input.setAttribute("maxlength", "2");
		para.appendChild(input);
		var label = document.createElement("label");
		label.setAttribute("for", "pdimension_desc_" + dimension_count);
		label.appendChild(document.createTextNode("<?php _e('Description:','ptest'); ?>"));
		para.appendChild(label);
		var textarea = document.createElement("textarea");
		textarea.setAttribute("name", "pdimension_desc[]");
		textarea.setAttribute("rows", "2");
		textarea.setAttribute("cols", "30");
		para.appendChild(textarea);
		var label = document.createElement("label");
		label.setAttribute("for", "ndimension_" + dimension_count);
		label.appendChild(document.createTextNode("<?php _e('Opposite Pole:','ptest'); ?>"));
		para.appendChild(label);
		var input = document.createElement("input");
		input.setAttribute("type", "text");
		input.setAttribute("name", "ndimension[]");
		input.className = "ndimension";
		input.setAttribute("value", "");
		input.setAttribute("id", "ndimension_" + dimension_count);
		input.setAttribute("maxlength", "2");
		para.appendChild(input);		
		document.getElementById("extra-dimensions").appendChild(para);
	}
	</script>
	
	<form name="post" action="<?php echo PTEST_PLUGIN_URL; ?>/admin/actions.php" method="post" id="post">
		<div id="poststuff">	
			<div class="postbox" id="titlediv">
				<h3 class="hndle"><span><?php _e('Test Name','ptest') ?></span></h3>
				<div class="inside">
					<input type='text' name='name' id="title" value='<?php echo stripslashes($dtest->name); ?>' />
				</div><!-- inside ends -->
			</div><!-- hndle ends -->			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('Test Description','ptest') ?></span></h3>
				<div class="inside">
					<textarea name='description' rows='5' cols='50' style='width:100%'><?php echo stripslashes($dtest->description); ?></textarea>
				</div><!-- inside ends -->
			</div><!-- postbox ends -->
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('Personality Dimensions (Write each pole of all dimensions below)','ptest') ?></span></h3>
				<div class="inside">
					<?php
					for($i=1; $i<=$dimension_count; $i++) {
					 ?>
						<p>
						<label for="pdimension_<?php echo $i?>"><?php _e('One Pole:','ptest'); ?></label>
						<input type="text" class="pdimension" id="pdimension" name="pdimension[]" value="<?php if($action == 'edit') echo stripslashes($all_dimensions[$i-1]->pdimension); ?>" maxlength="2" />
						<label for="dimension_desc_<?php echo $i?>"><?php _e('Description:','ptest'); ?></label>
						<textarea name="pdimension_desc[]" class="pdimension_desc" rows="2" cols="30"><?php if($action == 'edit') echo stripslashes($all_dimensions[$i-1]->explanation); ?></textarea>
						<label for="ndimension_<?php echo $i?>"><?php _e('Opposite Pole:','ptest'); ?></label>
						<input type="text" class="ndimension" id="ndimension" name="ndimension[]" value="<?php if($action == 'edit') echo stripslashes($all_dimensions[$i-1]->ndimension); ?>" maxlength="2" />
						</p>
					<?php 
					 } ?>			
					<div id="extra-dimensions"></div>
					<br /><br />
					<a href="javascript:newDimension();"><?php _e('Add New Dimension','ptest'); ?></a>	
					<br /><br />				
				</div><!-- inside ends -->
			</div><!-- postbox ends -->
			
			<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea postbox">
				<h3 class="hndle"><span><?php _e('Report Screen','ptest') ?></span></h3>
				<div class="inside">
					<?php the_editor($final_screen); ?>				
					<p><strong><?php _e('Usable Variables...','ptest') ?></strong></p>
					<table>
						<tr><th style="text-align:left;"><?php _e('Variable','ptest') ?></th><th style="text-align:left;"><?php _e('Value','ptest') ?></th></tr>
						<tr><td>%%PROFILE_CODE%%</td><td><?php _e('A code compiled from shortcodes of dominant personality dimensions.','ptest') ?></td></tr>
						<tr><td>%%TEST_NAME%%</td><td><?php _e('The name of the test','ptest') ?></td></tr>
						<tr><td>%%DESCRIPTION%%</td><td><?php _e('The text entered in the description field.','ptest') ?></td></tr>
					</table>
				</div><!-- inside ends -->
			</div><!-- postarea postbox ends -->
			<p class="submit">
				<?php wp_nonce_field('ptest_create_edit_test'); ?>
				<input type="hidden" name="action" value="<?php echo $action; ?>" />
				<input type="hidden" name="test" value="<?php echo $_REQUEST['test']; ?>" />
				<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
				<span id="autosave"></span>
				<input type="submit" name="submit-test" value="<?php _e('Save','ptest') ?>" style="font-weight: bold;" tabindex="4" />
			</p>		
		</div><!-- poststuff ends -->
	</form>

</div><!-- wrap ends -->
