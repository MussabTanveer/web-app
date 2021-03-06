<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Assignment Results");
    $PAGE->set_heading("Assignment Results");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/student/display_manual_assignment.php');

    header('Content-Type: text/plain');
   
    require_login();
    if($SESSION->oberole != "student"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();
    ?>
    <script src="../script/jquery/jquery-3.2.1.js"></script>
    <script src="../script/table2excel/jquery.table2excel.min.js"></script>
    <?php

    if(!empty($_POST['maid']))
    {
    $assign_id=$_POST['maid'];
    //echo $assign_id;
    //$courseid=$_GET['courseid'];

    //$id=$_POST['id'];
    //echo $id;
    $rec1=$DB->get_recordset_sql('SELECT ma.name,ma.maxmark,comp.idnumber,ma.cloid, comp.id from mdl_manual_assign_pro ma,mdl_competency comp WHERE comp.id=ma.cloid AND ma.id=?',array($assign_id));

    if($rec1){

        foreach ($rec1 as $records) {
            $name = $records->name;
            $clo=$records->idnumber;
            $maxmark=$records->maxmark;
        }

        echo "<h3>".$name." "."(".$clo.")"."</h3>";

        echo "<h3>"."Max Marks:"." ".$maxmark."</h3>";
    }
    else{
	    echo "No record present!";
    }
    $rec=$DB->get_recordset_sql(
        'SELECT substring(us.username,4,8) AS seatorder,us.username,us.id,maa.obtmark, ma.id,maa.id from mdl_manual_assign_pro_attempt maa , mdl_manual_assign_pro ma, mdl_user us where us.id=maa.userid AND ma.id=maa.assignproid  AND ma.id= ? AND maa.userid=? AND ma.module=? ORDER BY seatorder ',array($assign_id,$USER->id,'-4'));

    if($rec){

 
        $serialno = 0;
        $table = new html_table();
        $table->id = "mytable";
        $table->head = array('Seat No.', 'Marks Obtained');

        foreach ($rec as $records) {
            $serialno++;
            $marksid=$records->id;
            $userid = $records->username;
            $obtmark = $records->obtmark;
            //"<a href='edit_assignment_marks.php?edit=$marksid' title='Edit'><img src='../img/icons/edit.png' /></a>";
            $table->data[] = array(strtoupper($userid),$obtmark);

        }
        echo html_writer::table($table);
    ?>

    <?php
    }
    else{
        echo "<h3>No students have attempted Assignment yet!</h3>";
    }
    ?>
    
    <?php
}
    echo $OUTPUT->footer();
?>
