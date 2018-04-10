<script src="../script/jquery/jquery-3.2.1.js"></script>
<script src="../script/validation/jquery.validate.js"></script>
<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Grading Policy");
    $PAGE->set_heading("Add Grading Policy Items");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/grading_policy.php');
    
	require_login();
	if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
	}
	echo $OUTPUT->header();
?>
<style>
	input[type='number'] {
		-moz-appearance:textfield;
	}
	input::-webkit-outer-spin-button,
	input::-webkit-inner-spin-button {
		-webkit-appearance: none;
	}
</style>
<?php

	if(!empty($_GET['course'])){
		$course_id=$_GET['course'];
		$coursecontext = context_course::instance($course_id);
		is_enrolled($coursecontext, $USER->id) || die('<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
		
		if(isset($_POST['save'])){
			$sum = 0;
			$rec=$DB->get_records_sql('SELECT percentage FROM mdl_grading_policy WHERE courseid=?',array($course_id));
			foreach ($rec as $records){
                $percentage=$records->percentage;
                $sum+=$percentage;
            }
           
			for ($i=0; $i < count($_POST["activity"]); $i++) {
				$sum+=trim($_POST["percentage"][$i]);
			}
			//echo $sum;
			if($sum > 100){
				$msgP = "<font color = red>Total percentage of all evaluation methods should be 100%</font><br />";
			}
			else{
				for ($i=0; $i < count($_POST["activity"]); $i++) {
					# code...
					$activity=trim($_POST["activity"][$i]);
					$percentage=trim($_POST["percentage"][$i]);
					//echo $activity;
					//echo $percentage;
					if($percentage != '') 
					{
						$sql="INSERT INTO mdl_grading_policy (courseid,name,percentage) VALUES ('$course_id','$activity','$percentage')";
						$DB->execute($sql);
					}
					else 
					{
						// percentage not entered for activity
					}
				}
				$msgP = "<font color = green>Grading Policy saved successfully!</font><br />";
			}
		}
		elseif(isset($_POST['return'])) {
			$sum = 0;
			$rec=$DB->get_records_sql('SELECT percentage FROM mdl_grading_policy WHERE courseid=?',array($course_id));
			foreach ($rec as $records){
                $percentage=$records->percentage;
                $sum+=$percentage;
            }
			for ($i=0; $i < count($_POST["activity"]); $i++) {
				$sum+=trim($_POST["percentage"][$i]);
			}
			//echo $sum;
			if($sum > 100){
				$msgP = "<font color = red>Total percentage of all evaluation methods should be 100%</font><br />";
			}
			else{
				for ($i=0; $i < count($_POST["activity"]); $i++) {
					# code...
					$activity=trim($_POST["activity"][$i]);
					$percentage=trim($_POST["percentage"][$i]);
					//echo $activity;
					//echo $percentage;
					if($percentage != '') 
					{
						$sql="INSERT INTO mdl_grading_policy (courseid,name,percentage) VALUES ('$course_id','$activity','$percentage')";
						$DB->execute($sql);
					}
					else 
					{
						// percentage not entered for activity
					}
				}
				$msgP = "<font color = green>Grading Policy saved successfully!</font><br />";
				$redirect_page="./report_teacher.php?course=$course_id";
				redirect($redirect_page);
			}
		}
		if(isset($msgP)){
			echo $msgP;
		}
		$gps=$DB->get_records_sql('SELECT SUM(percentage) AS sum FROM `mdl_grading_policy` WHERE courseid = ?', array($course_id));
		foreach($gps as $gp){
			$sum = $gp->sum;
		}

		$rec=$DB->get_records_sql('SELECT id, name, percentage FROM mdl_grading_policy WHERE courseid=? ',array($course_id));
		
		if($rec){
            $serial=0;
            $sum=0;
            $table = new html_table();
            $table->head = array('S. No.', 'Activity', 'Percentage');
            foreach ($rec as $records) {
                $serial++;
                $id=$records->id;
                $name=$records->name;
                $percentage=$records->percentage;
                $sum+=$percentage;
                if($name == "mid term" | $name == "final exam"){
                    $table->data[] = array($serial,strtoupper($name), $percentage.'%', "Predefined");
                }
                else{
                    $table->data[] = array($serial,strtoupper($name), $percentage.'%', "<a href='edit_grading_policy.php?course=$course_id&edit=$id' title='Edit'><img src='../img/icons/edit.png' /></a> <a href='display_grading_policy.php?course=$course_id&delete=$id' onClick=\"return confirm('Delete grading policy of $name?')\" title='Delete'><img src='../img/icons/delete.png' /></a>");
                }
            }
            $table->data[] = array("<b>Total:</b>", "", $sum.'%', "");
            if($serial){
                if($sum != 100){
					echo "<h5>Grading Policy is not 100%<h5><br />";
					echo html_writer::table($table);
					echo "<h5>Remaining ".(100-$sum)."% <h5><br />";
				}
                
            }
        }

		if($sum < 100){
		?>
		<h3>Select an Activity to choose Grading Policy for:</h3>
		<form method='post' action="" class="mform" id="gpForm">
		<div id="dynamicInput">
			<div class="form-group row fitem" id="div0">
				<div class="col-md-4 form-inline felement">
					<select
						id="activity0" class="select custom-select" name="activity[]" required>
						<option value="">Choose</option>
						<option value="quiz">Quiz</option>
						<option value="assignment">Assignment</option>
						<option value="project">Project</option>
						<!--<option value="mid term">Mid Term</option>-->
					   	<!--<option value="final exam">Final Exam</option>-->
						<option value="other">Other</option>
					</select>
				</div>
				<div class="col-md-4 form-inline felement" data-fieldtype="number">
					<span class="input-group-addon" style="display: inline;"><i class="fa fa-percent"></i></span>
					<input type="number"
							class="form-control"
							name="percentage[]"
							id="percent0"
							size=""
							maxlength="10"
							step="0.001"
							min="0" max="100"
							required>
				</div>
				<div class="form-control-feedback" id="id_error_shortname">
				</div>
				<div class="col-md-4">
					<i id="cross0" class="fa fa-times" style="font-size:28px;color:red;cursor:pointer" title="Remove"></i>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<input class="btn btn-success" type="button" value="Add another" onClick="addInput('dynamicInput');">
			</div>
		</div>
		<br />
		<input class="btn btn-info" type="submit" name="save" value="Save and continue"/>
		<input class="btn btn-info" type="submit" name="return" value="Save and return"/>
		<a class="btn btn-default" type="submit" <?php echo "href='./report_teacher.php?course=$course_id'" ?>>Cancel</a>
		</form>

		<script>
			// script to remove first activity and percent fields from form
			$(document).ready(function(){
				$("#cross0").click(function(){
					$("#div0").remove();
				});
			});
		</script>
		<script>
			// script to add more activity and percent fields to form
			var counter = 1;
			function addInput(divName){
				var newdiv = document.createElement('div');
				newdiv.innerHTML = '<div class="form-group row fitem" id="div'+counter+'"><div class="col-md-4 form-inline felement"><select id="activity'+counter+'" class="select custom-select" name="activity[]"><option value="">Choose</option><option value="quiz">Quiz</option><option value="assignment">Assignment</option><option value="project">Project</option><option value="other">Other</option></select></div><div class="col-md-4 form-inline felement" data-fieldtype="number"><span class="input-group-addon" style="display: inline;"><i class="fa fa-percent"></i></span><input type="number" class="form-control" name="percentage[]" id="percent'+counter+'" size="" maxlength="10" step="0.001" min="0" max="100" required></div><div class="form-control-feedback" id="id_error_shortname"></div><div class="col-md-4"><i id="cross'+counter+'" class="fa fa-times" style="font-size:28px;color:red;cursor:pointer" title="Remove"></i></div></div>';
				document.getElementById(divName).appendChild(newdiv);
				var idname = "#cross" + counter;
				var divname = "#div" + counter;
				$(idname).click(function(){
					$(divname).remove();
				});
				counter++;
			}
		</script>
		<script>
			//form validation
			$(document).ready(function () {
				$('#gpForm').validate({ // initialize the plugin
					rules: {
						"activity[]": {
							required: true
						},
						"percentage[]": {
							number: true,
							required: true,
							step: 0.001,
							range: [0, 100],
							min: 0,
							max: 100,
							minlength: 1,
							maxlength: 7
						}
					},
					messages: {
						"activity[]": {
							required: "Please select activity."
						},
						"percentage[]": {
							number: "Only numeric values are allowed.",
							required: "Please enter percentage.",
							step: "Please enter nearest percentage value.",
							range: "Please enter percentage between 0 and 100%.",
							min: "Please enter percentage greater than or equal to 0%.",
							max: "Please enter percentage less than or equal to 100%.",
							minlength: "Please enter more than 1 numbers.",
							maxlength: "Please enter no more than 6 numbers (including decimal part)."
						}
					}
				});
			});
		</script>
		<?php
		}
		else{
			echo "<font color = green>Grading Policy is already 100%<br />Cannot add another evaluation method.<br />Either <a href=display_grading_policy.php?course=$course_id>delete</a> or <a href=display_grading_policy.php?course=$course_id>edit</a> grading policy.</font>";
		}
	}
	else{
		?>
		<h3 style="color:red;"> Invalid Selection </h3>
    	<a href="./teacher_courses.php">Back</a>
    	<?php
	}

	echo $OUTPUT->footer();

?>
