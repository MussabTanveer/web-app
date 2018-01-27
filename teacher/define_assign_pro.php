<?php
require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Manual Assignment/Project");
    $PAGE->set_heading("Define Manual Assignment/Project");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/define_assign_pro.php');
    
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

<script src="../script/jquery/jquery-3.2.1.js"></script>

<?php
    
    if(isset($_GET['type']) && isset($_GET['course']))
    {
        $course_id=$_GET['course'];
        echo "Course ID : $course_id";
        $type=$_GET['type'];
        echo " Activity Type : $type";

		/* if user press save */
		if(isset($_POST['save'])) {
            $apname = trim($_POST["name"]);
            $apdesc = trim($_POST["description"]);
            $apmaxmark = trim($_POST["maxmark"]);
            $apclo = trim($_POST["clo"]);

            $record = new stdClass();
            $record->name = $apname;
            $record->description = $apdesc;
            $record->maxmark = $apmaxmark;
            $record->cloid = $apclo;
            $assign_pro_id = $DB->insert_record('manual_assign_pro', $record); // get assign/pro id of newly inserted record

            // KHIZAR! Insert this assign/pro id in mdl_grading_mapping table according to type (assignment, project) which is in $type variable above


           //MUSSAB! Automated mapping code starts from here
             if($type == "assign"){

            	$reca=$DB->get_records_sql('SELECT id as assign_id FROM mdl_grading_policy WHERE name="assignment" AND courseid=?',array($course_id));

            	if($reca){
            	foreach ($reca as $recorda) {

            		$assign_id=$recorda->assign_id; 
            	}
               echo $assign_id;
            $sql="INSERT INTO mdl_grading_mapping (courseid,module,instance,gradingitem) VALUES 
               ('$course_id',-2,'$assign_pro_id','$assign_id') ";
               $DB->execute($sql);

           }
else{


	$msga="Pls define Assignment in Define Grading Policy tab first";
}

           }


           elseif($type == "project"){

           
            $recp=$DB->get_records_sql('SELECT id as project_id FROM mdl_grading_policy WHERE name="project" AND courseid=?',array($course_id));
            if($recp){

            foreach ($recp as $recordp) {

            		$project_id=$recordp->project_id; 
            	}


             $sql="INSERT INTO mdl_grading_mapping (courseid,module,instance,gradingitem) VALUES 
               ('$course_id',-2,'$assign_pro_id','$project_id') ";
                  $DB->execute($sql);
              }

              else{


              	$msgp="Pls define Project in Define Grading Policy tab first";
              }


         

           }
                             

                               





			$redirect_page1="./report_teacher.php?course=$course_id";
			redirect($redirect_page1);
		}

		//Get course clo with its level, plo and peo
		$courseclos=$DB->get_records_sql(
        "SELECT clo.id AS cloid, clo.shortname AS cloname, plo.shortname AS ploname, peo.shortname AS peoname, levels.name AS lname, levels.level AS lvl
    
        FROM mdl_competency_coursecomp cc, mdl_competency clo, mdl_competency plo, mdl_competency peo, mdl_taxonomy_levels levels, mdl_taxonomy_clo_level clolevel

        WHERE cc.courseid = ? AND cc.competencyid=clo.id  AND peo.id=plo.parentid AND plo.id=clo.parentid AND 
        clo.id=clolevel.cloid AND levels.id=clolevel.levelid",
        
        array($course_id));
            
        $clonames = array(); $closid = array(); $plos = array(); $peos = array(); $levels = array(); $lvlno = array();
        foreach ($courseclos as $recC) {
            $cid = $recC->cloid;
            $clo = $recC->cloname;
            $plo = $recC->ploname;
            $peo = $recC->peoname;
            $lname = $recC->lname;
            $lvl = $recC->lvl;
            array_push($closid, $cid); // array of clo ids
            array_push($clonames, $clo); // array of clo names
            array_push($plos, $plo); // array of plos
            array_push($peos, $peo); // array of peos
            array_push($levels, $lname); // array of levels
            array_push($lvlno, $lvl); // array of level nos
        }

		if(isset($msg3)){
			echo $msg3;
		}

		?>
		<br />
		
		<form method='post' action="" class="mform" id="cloForm">
            
            <?php
            if($type == "assign"){
                ?>
                <h3>Assignment</h3>
                <?php
            }
            elseif($type == "project"){
                ?>
                <h3>Project</h3>
                <?php
            }
            ?>

            <div class="form-group row fitem ">
                <div class="col-md-3">
                    <span class="pull-xs-right text-nowrap">
                        <abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
                    </span>
                    <label class="col-form-label d-inline" for="id_name">
                        Name
                    </label>
                </div>
                <div class="col-md-9 form-inline felement" data-fieldtype="text">
                    <input type="text"
                            class="form-control"
                            name="name"
                            id="id_name"
                            size=""
                            required
                            maxlength="100">
                    <div class="form-control-feedback" id="id_error_name">
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
					</span>
					<label class="col-form-label d-inline" for="id_maxmark">
						Max Mark
					</label>
				</div>
				<div class="col-md-9 form-inline felement" data-fieldtype="number">
					<input type="number"
							class="form-control"
							name="maxmark"
							id="id_maxmark"
							size=""
							required
							step="0.001">
					<div class="form-control-feedback" id="id_error_maxmark">
					</div>
				</div>
			</div>
			
			<div class="form-group row fitem ">
				<div class="col-md-3">
					<span class="pull-xs-right text-nowrap">
						<abbr class="initialism text-danger" title="Required"><i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required" aria-label="Required"></i></abbr>
					</span>
					<label class="col-form-label d-inline" for="id_clo">
						CLO
					</label>
				</div>
				<div class="col-md-9 form-inline felement">
                    <select onChange="dropdownTip(this.value, 0)" name="clo" class="select custom-select">
                        <option value='NULL'>Choose..</option>
                        <?php
                        foreach ($courseclos as $recC) {
                        $cid =  $recC->cloid;
                        $cname = $recC->cloname;
                        $plname = $recC->ploname;
                        $pename = $recC->peoname;
                        ?>
                        <option value='<?php echo $cid; ?>'><?php echo $cname; ?></option>
                        <?php
                        }
                        ?>
                    </select>
					<span id="plo0"></span>
                    <span id="tax0"></span>
					<div class="form-control-feedback" id="id_error_clo">
					</div>
				</div>
			</div>
            
            <br />
			
			<button class="btn btn-info" type="submit"  name="save" id="button" /> Save </button>
            <a class="btn btn-default" type="submit" href="./report_teacher.php?course=<?php $course_id ?>">Cancel</a>
			<br /><br />
			<div class="fdescription required">There are required fields in this form marked <i class="icon fa fa-exclamation-circle text-danger fa-fw " aria-hidden="true" title="Required field" aria-label="Required field"></i>.</div>
		</form>
		
		<?php
		if(isset($_POST['save']) && !isset($msg3)){
		?>
		<script>
			document.getElementById("id_shortname").value = <?php echo json_encode($shortname); ?>;
			document.getElementById("id_description").value = <?php echo json_encode($description); ?>;
			document.getElementById("id_idnumber").value = <?php echo json_encode($idnumber); ?>;
		</script>
		<?php
		}
		?>
				
		<script>
            var closid = <?php echo json_encode($closid); ?>;
			var plos = <?php echo json_encode($plos); ?>;
			//var peos = <?php echo json_encode($peos); ?>;
			var levels = <?php echo json_encode($levels); ?>;
			var levelnos = <?php echo json_encode($lvlno); ?>;

			function dropdownTip(value,id){
				var plo = "plo" + id;
				//var peo = "peo" + id;
				var tax = "tax" + id;
				if(value == 'NULL'){
					document.getElementById(plo).innerHTML = "";
					//document.getElementById(peo).innerHTML = "";
					document.getElementById(tax).innerHTML = "";
				}
				else{
					for(var i=0; i<closid.length ; i++){
						if(closid[i] == value){
							document.getElementById(plo).innerHTML = "PLO: " + plos[i];
							//document.getElementById(peo).innerHTML = peos[i];
							document.getElementById(tax).innerHTML = "LEVEL: " + levels[i] + " (" + levelnos[i] + ")";
							break;
						}
					}
				}
			}
			
		</script>




    <?php
    if(isset($msga)){

    	echo $msga;
    }
   elseif(isset($msgp)){

   	echo $msgp;
   }

	}
	else
	{?>
		<h3 style="color:red;"> Invalid Selection </h3>
    	<a href="../index.php">Back</a>
    	<?php
    }

    echo $OUTPUT->footer();
?>