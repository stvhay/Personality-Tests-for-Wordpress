<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
	global $wpdb;
	$wpdb->show_errors;
if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
			$ptest_options['access_to_tests'] =	$_REQUEST['access_to_tests'];
			update_option('ptest_options', $ptest_options);
			$message = "Options updated";
}
$ptest_options = get_option('ptest_options');

?>
<div class="wrap">
	<h2><?php _e('PTest Settings','ptest'); ?></h2>
	<?php
	if (isset($message)) {
	?>
	<div id="message" class="updated fade"><p>
		<?php 
		echo $message;
		?>
	</p></div><!-- updated fade ends -->
	<?php 
	}
	?>	
	<div id="poststuff">	
		<div id="postdiv" class="postarea">			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('Plugin Info','ptest') ?></span></h3>
				<div class="inside">
				<?php echo "<strong>".__('PTest Version: ','ptest')."</strong>".PTEST_VERSION; ?><br />
				<?php echo "<strong>".__('PTest Database Version: ','ptest')."</strong>".get_option('ptest_db_version'); ?>
				</div><!-- inside ends -->
			</div><!-- postbox ends -->		
		</div>
	</div>
	<form name="post" action="" method="post" id="post">
		<div id="poststuff">
			<div id="postdiv" class="postarea">			
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Access to the tests','ptest') ?></span></h3>
					<div class="inside">
						<input type="radio" name="access_to_tests" <?php if($ptest_options['access_to_tests'] == 'public') echo 'checked="checked"'; ?> value="public" id="access_to_tests_public" /> 
						<label for="access_to_tests_public"><?php _e("Everyone can access to tests.") ?></label><br />
						<input type="radio" name="access_to_tests" <?php if($ptest_options['access_to_tests'] == 'members') echo 'checked="checked"'; ?> value="members" id="access_to_tests_members" /> 
						<label for="access_to_tests_public"><?php _e("Only registered users can access to tests.") ?></label><br />
					</div><!-- inside ends -->
				</div><!-- postbox ends -->									
				<p class="submit">
					<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
					<span id="autosave"></span>
					<input type="submit" name="submit" value="<?php _e('Save Options','ptest') ?>" style="font-weight: bold;" />
				</p>				
			</div><!-- postdiv ends -->
		</div><!-- poststuff ends -->
	</form>

</div><!-- wrap ends -->