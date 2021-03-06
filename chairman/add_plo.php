<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Add OBE PLOs");
    $PAGE->set_heading("Add Program Learning Outcome (PLO)");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/chairman/add_plo.php');
    
    echo $OUTPUT->header();
	require_login();
	$rec1=$DB->get_records_sql('SELECT us.username FROM mdl_user us, mdl_role r,mdl_role_assignments ra   WHERE us.id=ra.userid AND r.id=ra.roleid AND  r.shortname=? AND us.id=? ',array('chairman',$USER->id));
    $rec1 || die('<h2>This page is for Chairperson only!</h2>'.$OUTPUT->footer());
?>
<script src="../script/sweet-alert/sweetalert.min.js"></script>
<script src="../script/jquery/jquery-3.2.1.js"></script>
<script src="../script/validation/jquery.validate.js"></script>
<script src="../script/validation/additional-methods.min.js"></script>
<style>
	input[type='number'] {
		-moz-appearance:textfield;
	}
	input::-webkit-outer-spin-button,
	input::-webkit-inner-spin-button {
		-webkit-appearance: none;
	}
	label.error {
		color: red;
	}
</style>
<?php
	if((isset($_POST['submit']) && isset( $_POST['frameworkid'])) || (isset($SESSION->fid2) && $SESSION->fid2 != "xyz") || isset($_POST['save']) || isset($_POST['return']) || isset($_GET['fwid']))
	{
		if(isset($_POST['submit']) || (isset($SESSION->fid2) && $SESSION->fid2 != "xyz") || isset($_GET['fwid']) )
		{
			if(isset($SESSION->fid2) && $SESSION->fid2 != "xyz")
			{
				$frameworkid=$SESSION->fid2;
				//echo "$frameworkid";
				
				$SESSION->fid2 = "xyz";
			}
			elseif(isset( $_POST['frameworkid']))
			{
				$frameworkid=$_POST['frameworkid'];
				//echo "$frameworkid";
			}
			else
			{
				$frameworkid=$_GET['fwid'];
				//echo "$frameworkid";
			}
			
			$rec=$DB->get_records_sql('SELECT shortname from mdl_competency_framework WHERE id=?', array($frameworkid));
		 	if($rec){
				foreach ($rec as $records){
					$framework_shortname = $records->shortname;
				}
			}
		}
	
		if(isset($_POST['save']) || isset($_POST['return'])){
			$shortname=trim($_POST['shortname']);
			$description=trim($_POST['description']);
			$idnumber=trim($_POST['idnumber']); $idnumber=strtoupper($idnumber);
			$frameworkid=$_POST['frameworkid'];
			$framework_shortname=$_POST['framework_shortname'];
			$cpkpi=$_POST["kpi_cohort_programme"];
			//$cckpi=$_POST["kpi_cohort_course"];
			$iskpi=$_POST["kpi_individual_student"];
			$peo=$_POST['peo'];
			$time = time();
			//echo "peo = $peo";
			if(empty($shortname) || empty($idnumber) || strlen($shortname)> '30' || strlen($idnumber)>'10' || empty($cpkpi) || empty($iskpi) || is_null($peo) || $peo === NULL || empty($peo))
			{
				if(empty($shortname))
				{
					$msg1="<font color='red'>-Please enter PLO name</font>";
				}
				if(empty($idnumber))
				{
					$msg2="<font color='red'>-Please enter ID number</font>";
				}
				if(empty($peo) || is_null($peo))
				{
					$msg4="<font color='red'>-Please select PEO</font>";
				}
				if(strlen($shortname)> '30')
				{
					$msg1="<font color='red'>-Length of the Name should be less than 30</font>";
				}
				if(strlen($idnumber)>'10' )
				{
					$msg2="<font color='red'>-Length of the ID Number should be less than 10</font>";
				}
				if(empty($cpkpi))
				{
					$msg5="<font color='red'>-Please enter PLO Cohort Programme KPI</font>";
				}
				
				if(empty($iskpi))
				{
					$msg7="<font color='red'>-Please enter PLO Individual Student KPI</font>";
				}
			}
			elseif(substr($idnumber,0,4) != 'PLO-')
			{
				$msg2="<font color='red'>-The ID number must start with PLO-</font>";
			}
			else{
				$check=$DB->get_records_sql('SELECT * from mdl_competency WHERE idnumber=? AND competencyframeworkid=?', array($idnumber, $frameworkid));
				if(count($check)){
					$msg2="<font color='red'>-Please enter UNIQUE ID number</font>";
				}
				else{
					try {
						$transaction = $DB->start_delegated_transaction();
						$record = new stdClass();
						$record->shortname = $shortname;
						$record->description = $description;
						$record->descriptionformat = 1;
						$record->idnumber = $idnumber;
						$record->competencyframeworkid = $frameworkid;
						$record->parentid = $peo;
						$record->path = '/0/'.$peo.'/';
						$record->sortorder = 0;
						$record->timecreated = $time;
						$record->timemodified = $time;
						$record->usermodified = $USER->id;
						
						$ploid = $DB->insert_record('competency', $record);
						
						//echo "PLO ID: $ploid";
						/*$sql="INSERT INTO mdl_competency (shortname, description, descriptionformat, idnumber,competencyframeworkid, parentid, path, sortorder, timecreated, timemodified, usermodified) VALUES ('$shortname', '$description', 1, '$idnumber',$frameworkid , $peo, '/0/$peo/', 0, '$time', '$time', $USER->id)";
						$DB->execute($sql);*/
						
						if($ploid){
							//kpi_cohort_programme
							$record = new stdClass();
							$record->ploid = $ploid;
							$record->kpi = $cpkpi;
							$DB->insert_record('plo_kpi_cohort_programme', $record);
							//kpi_cohort_course
							//$record = new stdClass();
							////$record->ploid = $ploid;
							//$record->kpi = $cckpi;
							//$DB->insert_record('plo_kpi_cohort_course', $record);
							//kpi_individual_student
							$record = new stdClass();
							$record->ploid = $ploid;
							$record->kpi = $iskpi;
							$DB->insert_record('plo_kpi_individual_student', $record);
						}
						$msg3 = "<font color='green'><b>PLO successfully defined!</b></font><br /><p><b>Add another below.</b></p>";
						$transaction->allow_commit();
						if (isset($_POST['return'])){
							$redirect_page1='./report_chairman.php';
							redirect($redirect_page1);
						}
					} catch(Exception $e) {
						$transaction->rollback($e);
						$msg3 = "<font color='red'>PLO failed to define!</font>";
					}
				}
			}
		}
        
		/* delete code */
		elseif(isset($_GET['delete']))
		{
			$id_d=$_GET['delete'];
			$fw_id=$_GET['fwid'];
			$check=$DB->get_records_sql('SELECT * from mdl_competency where parentid=? and competencyframeworkid=?',array($id_d,$fw_id));
			if($check){
				$delmsg = "<font color='red'><b>The PLO cannot be deleted! Remove the mapping before PLO deletion.</b></font><br />";
				?>
				<script>
				swal("Alert", "The PLO cannot be deleted! Remove the mapping before PLO deletion.", "info");
				</script>
				<?php
			}
			else
			{
				$sql_delete="DELETE from mdl_competency where id=$id_d";
				$DB->execute($sql_delete);
				$delmsg = "<font color='green'><b>PLO has been deleted!</b></font><br />";
				?>
				<script>
				swal("PLO has been deleted!", {
						icon: "success",
						});
				</script>
				<?php
			}
		}
		/* /delete code */

		$peos=$DB->get_records_sql('SELECT * FROM `mdl_competency` 
		WHERE competencyframeworkid = ?
		AND parentid = 0 ',
		array($frameworkid));

		$peoNameArray=array();
		$peoIdArray=array();

		foreach ($peos as $p) {
			$id =  $p->id;
			$name = $p->shortname;
			//$idnumpeo =  $p->idnumber;
			array_push($peoNameArray,$name);
			array_push($peoIdArray,$id);
		}

		$plos=$DB->get_records_sql('SELECT id, shortname, idnumber FROM  `mdl_competency`
		WHERE competencyframeworkid = ? 
		AND idnumber LIKE "plo%" ORDER BY id',
			array($frameworkid));
			
		if($plos){
			$i = 1;
			echo "<h3>Already Present PLOs In Framework</h3>";
			foreach ($plos as $records){
				$shortname1 = $records->shortname;
				$idnumber1 = $records->idnumber;
				$id=$records->id;
				echo "<div class='row'>
						<div class='col-md-4 col-sm-4 col-xs-8'>$i. $shortname1 ($idnumber1)</div>
						<div class='col-md-8 col-sm-8 col-xs-4'>
							<a href='edit_plo.php?edit=$id&fwid=$frameworkid' title='Edit'><i class='icon fa fa-pencil text-info' aria-hidden='true' title='Edit' aria-label='Edit'></i></a>
							<a href='add_plo.php?delete=$id&fwid=$frameworkid' onClick=\"return confirm('Delete PLO?')\" title='Delete'><i class='icon fa fa-trash text-danger' aria-hidden='true' title='Delete' aria-label='Delete'></i></a>
						</div>
					  </div>";//link to edit_plo.php and delete
				$i++;			
			}
		}
		
		if(isset($msg3)){
			echo $msg3;
		}
		/*
		if(isset($delmsg)){
		echo $delmsg;
		}
		*/
		?>
		<br />
		<h3>Add New PLO</h3>
		<form method='post' action="" class="mform" id="ploForm">
			
			<div class="form-group row fitem ">
				<div class="col-md-3">
					<label class="col-form-label d-inline" for="id_plo">
						OBE framework
					</label>
				</div>
				<div class="col-md-9 form-inline felement">
					<?php echo $framework_shortname; ?>
				</div>
			</div>
			
			<div class="form-group row fitem">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
					</span>
					<label class="col-form-label d-inline" for="id_idnumber">
						ID number
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="text">
					<input type="text"
							class="form-control "
							name="idnumber"
							id="id_idnumber"
							size=""
							pattern="[p/P][l/L][o/O]-[0-9]{1,}"
							title="eg. PLO-12"
							required
							placeholder="eg. PLO-12"
							maxlength="20" type="text" > (eg. PLO-12)
					<div class="form-control-feedback" id="id_error_idnumber">
					<?php
					if(isset($msg2)){
						echo $msg2;
					}
					?>
					</div>
				</div>
			</div>
			
			<div class="form-group row fitem ">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
					</span>
					<label class="col-form-label d-inline" for="id_shortname">
						Name
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="text">
					<input type="text"
							class="form-control "
							name="shortname"
							id="id_shortname"
							size=""
							required
							maxlength="30" type="text" >
					<div class="form-control-feedback" id="id_error_shortname">
					<?php
					if(isset($msg1)){
						echo $msg1;
					}
					
					?>
					</div>
				</div>
			</div>
			
			<div class="form-group row fitem">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
					</span>
					<label class="col-form-label d-inline" for="id_description">
						Description
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="editor">
					<div>
						<div>
							<textarea id="id_description" name="description" class="form-control" rows="4" cols="80" spellcheck="true" ></textarea>
						</div>
					</div>
					<div class="form-control-feedback" id="id_error_description"  style="display: none;">
					</div>
				</div>
			</div>

			<div class="form-group row fitem ">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
						<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right"
                        data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;Cohort (mapping of PLOs to Programme) – At least 50% of the mapped courses should be attaining PLO &lt;/p&gt;&lt;/div&gt; "
                        data-html="true" tabindex="0" data-trigger="focus">
                        <i class="icon fa fa-question-circle text-info fa-fw " aria-hidden="true" title="Help with Passing Percentage" aria-label="Help with Passing Percentage"></i>
                        </a>
					</span>
					<label class="col-form-label d-inline" for="id_kpi_cohort_programme">
						Passing Percentage Cohort (mapping of PLOs to Programme)
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="number">
					<span class="input-group-addon" style="display: inline;"><i class="fa fa-percent"></i></span>
					<input type="number"
							class="form-control"
							name="kpi_cohort_programme"
							id="id_kpi_cohort_programme"
							size=""
							required
							placeholder="eg. 50"
							maxlength="10"
							step="0.001"
							min="0" max="100"
							value="50">
					<div class="form-control-feedback" id="id_error_kpi_cohort_programme">
					<?php
					if(isset($msg5)){
						echo $msg5;
					}
					?>
					</div>
				</div>
			</div>
<!--
			<div class="form-group row fitem ">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
						<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right"
                        data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;Cohort (mapping of a PLO to a Course) – At least 50% of the students in a mapped course should attain PLO &lt;/p&gt;&lt;/div&gt; "
                        data-html="true" tabindex="0" data-trigger="focus">
                        <i class="icon fa fa-question-circle text-info fa-fw " aria-hidden="true" title="Help with Passing Percentage" aria-label="Help with Passing Percentage"></i>
                        </a>
					</span>
					<label class="col-form-label d-inline" for="id_kpi_cohort_course">
						Passing Percentage Cohort (mapping of a PLO to a Course)
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="number">
					<span class="input-group-addon" style="display: inline;"><i class="fa fa-percent"></i></span>
					<input type="number"
							class="form-control"
							name="kpi_cohort_course"
							id="id_kpi_cohort_course"
							size=""
							required
							placeholder="eg. 50"
							maxlength="10"
							step="0.001"
							min="0" max="100"
							value="50">
					<div class="form-control-feedback" id="id_error_kpi_cohort_course">
					<?php
					if(isset($msg6)){
						echo $msg6;
					}
					?>
					</div>
				</div>
			</div>
-->
			<div class="form-group row fitem ">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
						<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right"
                        data-content="&lt;div class=&quot;no-overflow&quot;&gt;&lt;p&gt;Individual (mapping of a PLO to a student) – All CLOs mapped to a PLO in a course have been attained&lt;/p&gt;&lt;/div&gt; "
                        data-html="true" tabindex="0" data-trigger="focus">
                        <i class="icon fa fa-question-circle text-info fa-fw " aria-hidden="true" title="Help with Passing Percentage" aria-label="Help with Passing Percentage"></i>
                        </a>
					</span>
					<label class="col-form-label d-inline" for="id_kpi_individual_student">
						Passing Percentage Individual (mapping of a PLO to a student)
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="number">
					<span class="input-group-addon" style="display: inline;"><i class="fa fa-percent"></i></span>
					<input type="number"
							class="form-control"
							name="kpi_individual_student"
							id="id_kpi_individual_student"
							size=""
							required
							placeholder="eg. 50"
							maxlength="10"
							step="0.001"
							min="0" max="100"
							value="50">
					<div class="form-control-feedback" id="id_error_kpi_individual_student">
					<?php
					if(isset($msg7)){
						echo $msg7;
					}
					?>
					</div>
				</div>
			</div>
			
			<div class="form-group row fitem ">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
					</span>
					<label class="col-form-label d-inline" for="id_shortname">
						Map to PEO
					</label>
				</div>
				<div class="col-md-9 form-inline felement">
					<select onChange="dropdownTip(this.value)" name="peo" class="select custom-select" required id="id_select_peo">
						<option value=''>Choose..</option>
						<?php
						foreach ($peos as $p) {
						$id =  $p->id;
						$name = $p->shortname;
						$idnumpeo = $p->idnumber;
						?>
						<option value='<?php echo $id; ?>'><?php echo $idnumpeo; ?></option>
						<?php
						}
						?>
					</select>
					<span id="peosidnumber"></span>
					<div class="form-control-feedback" id="id_error_shortname">
					<?php
					if(isset($msg4)){
						echo $msg4;
					}
					?>
					</div>
				</div>
			</div>

			<input type="hidden" name="framework_shortname" value="<?php echo $framework_shortname; ?>"/>
			<input type="hidden" name="frameworkid" value="<?php echo $frameworkid; ?>"/>
			<input class="btn btn-info" type="submit" name="save" value="Save and continue"/>
			<input class="btn btn-info" type="submit" name="return" value="Save and return"/>
            <a class="btn btn-default" type="submit" href="./select_frameworktoPLO.php">Cancel</a>
            <br/>
            <br/>
			 <a class="btn btn-default" href="./report_chairman.php">Go Back to Chairman Reports & Forms</a>
		</form>
		<?php
		//echo $shortname;
		if((isset($_POST['save']) || isset($_POST['return'])) && !isset($msg3)){
		?>
		<script>
			document.getElementById("id_idnumber").value = <?php echo json_encode($idnumber); ?>;
			document.getElementById("id_shortname").value = <?php echo json_encode($shortname); ?>;
			document.getElementById("id_description").value = <?php echo json_encode($description); ?>;
			document.getElementById("id_kpi_cohort_programme").value = <?php echo json_encode($cpkpi); ?>;
			//document.getElementById("id_kpi_cohort_course").value = <?php echo json_encode($cckpi); ?>;
			document.getElementById("id_kpi_individual_student").value = <?php echo json_encode($iskpi); ?>;
			document.getElementById("id_select_peo").value = <?php echo json_encode($peo); ?>;
		</script>
		<?php
		}
		?>
		<br />
		<div class="fdescription required">There are required fields in this form marked <i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required field" aria-label="Required field"></i>.</div>

		<script>
			var peoIdNumber = <?php echo json_encode($peoNameArray); ?>;
			var peoId = <?php echo json_encode($peoIdArray); ?>;
			function dropdownTip(value){
				//var peosidnumber = "peosidnumber";
				if(value == ''){
					document.getElementById("peosidnumber").innerHTML = "";
				}
				else{
					for(var i=0; i<peoIdNumber.length ; i++){
						if(peoId[i] == value){
							document.getElementById("peosidnumber").innerHTML = peoIdNumber[i];
							break;
						}
					}
				}
			}
		</script>

		<script>
			//form validation
			$(document).ready(function () {
				$('#ploForm').validate({ // initialize the plugin
					rules: {
						"idnumber": {
							required: true,
							minlength: 1,
							maxlength: 20,
							pattern: /^[p/P][l/L][o/O]-[0-9]{1,}$/
						},
						"shortname": {
							required: true,
							minlength: 1,
							maxlength: 30
						},
						"kpi_cohort_programme": {
							number: true,
							required: true,
							step: 0.001,
							range: [0, 100],
							min: 0,
							max: 100,
							minlength: 1,
							maxlength: 7
						},/*
						"kpi_cohort_course": {
							number: true,
							required: true,
							step: 0.001,
							range: [0, 100],
							min: 0,
							max: 100,
							minlength: 1,
							maxlength: 7
						},*/
						"kpi_individual_student": {
							number: true,
							required: true,
							step: 0.001,
							range: [0, 100],
							min: 0,
							max: 100,
							minlength: 1,
							maxlength: 7
						},
						"peo": {
							required: true
						}
					},
					messages: {
						"idnumber": {
							required: "Please enter ID number.",
							pattern: "Please enter correct format."
						},
						"shortname": {
							required: "Please enter Name."
						},
						"kpi_cohort_programme": {
							number: "Only numeric values are allowed.",
							required: "Please enter percentage.",
							step: "Please enter nearest percentage value.",
							range: "Please enter percentage between 0 and 100%.",
							min: "Please enter percentage greater than or equal to 0%.",
							max: "Please enter percentage less than or equal to 100%.",
							minlength: "Please enter more than 1 numbers.",
							maxlength: "Please enter no more than 6 numbers (including decimal part)."
						},/*
						"kpi_cohort_course": {
							number: "Only numeric values are allowed.",
							required: "Please enter percentage.",
							step: "Please enter nearest percentage value.",
							range: "Please enter percentage between 0 and 100%.",
							min: "Please enter percentage greater than or equal to 0%.",
							max: "Please enter percentage less than or equal to 100%.",
							minlength: "Please enter more than 1 numbers.",
							maxlength: "Please enter no more than 6 numbers (including decimal part)."
						},*/
						"kpi_individual_student": {
							number: "Only numeric values are allowed.",
							required: "Please enter percentage.",
							step: "Please enter nearest percentage value.",
							range: "Please enter percentage between 0 and 100%.",
							min: "Please enter percentage greater than or equal to 0%.",
							max: "Please enter percentage less than or equal to 100%.",
							minlength: "Please enter more than 1 numbers.",
							maxlength: "Please enter no more than 6 numbers (including decimal part)."
						},
						"peo": {
							required: "Please select PEO."
						}
					}
				});
			});
		</script>
  
		<?php 

			echo $OUTPUT->footer();
	}
	else
	{?>
    	<h3 style="color:red;"> Invalid Selection </h3>
    	<a href="./select_frameworktoPLO.php">Back</a>
    	<?php
        echo $OUTPUT->footer();
    }?>
