<script src="../script/jquery/jquery-3.2.1.js"></script>

<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Students");
    $PAGE->set_heading("Select Student");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/chairman/display_students.php');
    
    echo $OUTPUT->header();
    require_login();

    //query to show all students
    $rec=$DB->get_records_sql('SELECT distinct u.id, CONCAT( u.firstname, " ", u.lastname ) AS student , u.idnumber FROM mdl_course as c, mdl_role_assignments AS ra, mdl_user AS u, mdl_context AS ct WHERE c.id = ct.instanceid AND ra.roleid =5 AND ra.userid = u.id AND ct.id = ra.contextid ORDER BY u.idnumber');
    
    $serialno=0;

    if($rec)//executing query to display all students
   	{
        $table = new html_table();
        $table->head = array('S. No.','Name', 'Seat No.', 'Batch');
        foreach ($rec as $records)
        {
            $studentName=$records->student;
            $studentIdNumber=$records->idnumber;
            $sid=$records->id;
            $batch = substr($studentIdNumber,3,2);
            $serialno++;
            $table->data[] = array($serialno, "<a href='display_course_progress.php?sid=$sid'>$studentName</a>", "<a href='display_course_progress.php?sid=$sid'>$studentIdNumber", "<a href='display_course_progress.php?sid=$sid'>Batch-$batch</a>");
            //echo "<h4>$serialno. <a href='display_course_progress.php?sid=$sid'>$studentName ($studentIdNumber) (Batch-$batch)</a></h4>";
            //echo "<br>";
        }
        echo html_writer::table($table);
   	}
    echo $OUTPUT->footer();
?>
