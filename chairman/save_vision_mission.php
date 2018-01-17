<?php
	require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Vision & Mission");
    $PAGE->set_heading("Define Vision & Mission");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/chairman/save_vision_mission.php');
    $rec1=$DB->get_records_sql('SELECT us.username FROM mdl_user us, mdl_role r,mdl_role_assignments ra   WHERE us.id=ra.userid AND r.id=ra.roleid AND  r.shortname=? AND us.id=? ',array('chairman',$USER->id));
    $rec1 || die('<h2>This page is for Chairperson only!</h2>'.$OUTPUT->footer());
    
	$universityVision = trim($_POST["uv"]);
	$universityMission = trim($_POST["um"]);
	$departmentVision = trim($_POST["dv"]);
	$departmentMission = trim($_POST["dm"]);


	/*echo "$universityVision <br>";
	echo "$universityMission <br>";
	echo "$departmentVision <br>";
	echo "$departmentMission <br> ";*/

	$universityVision = mysql_real_escape_string($universityVision);
	$universityMission = mysql_real_escape_string($universityMission);
	$departmentVision = mysql_real_escape_string($departmentVision);
	$departmentMission = mysql_real_escape_string($departmentMission);

	
	$sql="UPDATE `mdl_vision_mission` SET description = '$universityVision' WHERE idnumber='uv'";
	$DB->execute($sql);

	$sql="UPDATE `mdl_vision_mission` SET description = '$universityMission' WHERE idnumber='um'";
	$DB->execute($sql);

	$sql="UPDATE `mdl_vision_mission` SET description = '$departmentVision' WHERE idnumber='dv'";
	$DB->execute($sql);

	$sql="UPDATE `mdl_vision_mission` SET description = '$departmentMission' WHERE idnumber='dm'";
		
	$DB->execute($sql);

?>
