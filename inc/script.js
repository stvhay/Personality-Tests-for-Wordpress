var current_question = 1;
var total_questions = 0;
var mode = "show";

// Check if the question is answered or not, give warning
function checkAnswer(e) {
	var answered = false;
	
	jQuery("#question-" + current_question + " .answer").each(function(i) {
		if(this.checked) {
			answered = true;
			return true;
		}
	});
	if(!answered) {
		if(!confirm(ptest_js_translate.no_answer)) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	}
	return true;
}

// Decide which button to show  

function nextQuestion(e) {
	if(!checkAnswer(e)) return;
	
	jQuery("#question-" + current_question).hide();
	current_question++;
	jQuery("#question-" + current_question).show();
	
	if(total_questions <= current_question) {
		jQuery("#next-question").hide();
		jQuery("#action-button").show();
	}
}

function ptestInit() {
	jQuery("#question-1").show();
	total_questions = jQuery(".ptest-question").length;
	
	if(total_questions == 1) {
		jQuery("#action-button").show();
		jQuery("#next-question").hide();
	
	} else {
		jQuery("#next-question").click(nextQuestion);
	}
}

jQuery(document).ready(ptestInit);
