<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;
$ptest_options = get_option('ptest_options');
$show_test_by = $ptest_options['show_test_by'];

$show_test_to = 0;
if($ptest_options['access_to_tests'] == "public") {
	$show_test_to = 1;
} else {
	if(is_user_logged_in()) $show_test_to = 1;
}


// Show Test Results

if(isset($_REQUEST['show-result']) ) { 
		
		// Retreive dimensions
		$all_dimensions = $wpdb->get_results($wpdb->prepare("SELECT ID,test_id,pdimension,explanation,ndimension FROM {$wpdb->prefix}ptest_dimension WHERE test_id=%d ORDER BY ID", $_REQUEST['test_id']));
		foreach ($all_dimensions as $dimension) { // For all the dimensions defined within the test master data, count how many answers there are
			$pdimension=$dimension->pdimension;
			$dimension_score[$pdimension] = 0;
			foreach ($_REQUEST['user_answer'] as $answer) {				
				If ($answer == $pdimension) {
				$dimension_score[$pdimension]++;
				}
			}
			// echo " ".$pdimension." : ".$dimension_score[$pdimension]." / ";
		}
		foreach ($all_dimensions as $dimension) { // Compare scores between poles of each dimension
			$pdimension=$dimension->pdimension;
			$ndimension=$dimension->ndimension;
			if ($dimension_score[$pdimension]>$dimension_score[$ndimension]) {
					$profile[$pdimension]= $pdimension;		
			} elseif ($dimension_score[$pdimension]<$dimension_score[$ndimension]) {
					$profile[$ndimension]= $ndimension;	
			} elseif ($dimension_score[$pdimension]==$dimension_score[$ndimension]){
					$n = $pdimension."/".$ndimension;
					$nn = $ndimension."/".$pdimension;
					if (empty($profile[$nn])) {
					$profile[$n]= $n;
					}
			}
			
		}
		$profile_code = implode("-", $profile);
		$test_details = $wpdb->get_row($wpdb->prepare("SELECT name, description, final_screen FROM {$wpdb->prefix}ptest_main WHERE ID=%d", $_REQUEST['test_id']));
		$replace_these	= array('%%PROFILE_CODE%%', '%%TEST_NAME%%', '%%DESCRIPTION%%');
		$with_these		= array($profile_code, stripslashes($test_details->name), stripslashes($test_details->description));
		
		print str_replace($replace_these, $with_these, stripslashes($test_details->final_screen));
		
} elseif($show_test_to) {
	
	// Retreive questions
	$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation FROM {$wpdb->prefix}ptest_question WHERE test_id=%d ORDER BY ID", $test_id));
	
	if($all_question) {		
	?>
		<div class="test-area <?php if($show_test_by=="all") echo 'single-page-test'; ?>">
			<form action="" method="post" class="test-form" id="test-<?php echo $test_id?>">
				<?php
				$question_count = 1;
				
				foreach ($all_question as $ques) {
					echo "<div class='ptest-question' id='question-$question_count'>";
					echo "<div class='question-content'>". stripslashes($ques->question) . "</div><br />";
					echo "<input type='hidden' name='question_id[]' value='{$ques->ID}' />";
					$dans = $wpdb->get_results("SELECT ID,answer,dimension FROM {$wpdb->prefix}ptest_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
					foreach ($dans as $ans) {
						echo "<input type='radio' name='user_answer[{$ques->ID}]' id='answer-id-{$ans->ID}' class='answer answer-$question_count $answer_class' value='{$ans->dimension}' />";
						echo "<label for='answer-id-{$ans->ID}' id='answer-label-{$ans->ID}' class='$answer_class answer label-$question_count'><span>" . stripslashes($ans->answer) . "</span></label><br />";
					}
					
					echo "</div>";
					$question_count++;
				}
				
				?><br />
				<?php // wp_nonce_field('ptest_show_result'); 
				?>
				<input type="button" id="next-question" value="<?php _e('Next Question','ptest') ?> &gt;"  /><br />		
				<input type="submit" name="show-result" id="action-button" value="<?php _e('Show the Results','ptest') ?>"  />
				<input type="hidden" name="test_id" value="<?php echo  $test_id ?>" />
			</form>
		</div><!-- test area -->
		
	<?php
	}
} else {
	_e('You have to register to have the test!','ptest');
}

?>