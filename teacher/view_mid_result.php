v<?php 
   require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Select Midterm");
    $PAGE->set_heading("Select Midterm");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/view_mid_result.php');
    
    require_login();
    if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();

    if(!empty($_GET['type']) && !empty($_GET['course']))
    {
        $course_id=$_GET['course'];
        // echo "$course_id";
        $coursecontext = context_course::instance($course_id);
        is_enrolled($coursecontext, $USER->id) || die('<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
        $type=$_GET['type'];
        //echo " Activity Type : $type";
        
        $midterms= $DB->get_records_sql("SELECT * FROM mdl_manual_quiz WHERE courseid = ? AND module = ?",array($course_id,-2));

        if($midterms)
        {
            $serialno = 0;
            $table = new html_table();
            $table->head = array('S. No.', 'Midterm Name','Delete');
            foreach ($midterms as $records) {
                $serialno++;
                $qid = $records->id;
                $qname = $records->name;
                
                $table->data[] = array($serialno,"<a href='./view_activity_result1.php?type=midterm&quiz=$qid&courseid=$course_id'>$qname</a>","<a href='./delete_manual_activity_marks.php?id=$qid&course=$course_id&type=midterm' title='Delete' onClick=\"return confirm('Are you sure you want to delete marks of this activity?')\" ><i class='icon fa fa-trash text-danger' aria-hidden='true' title='Delete' aria-label='Delete'></i></a>");
            
            }

            echo html_writer::table($table);
            echo "<br />";

        }

        else
            echo "<h3>You do not have any manual Midterm in this course!</h3>";
        ?>
        <a class="btn btn-default" href="./report_teacher.php?course=<?php echo $course_id ?>">Go Back</a>
        <?php
    }
    else
	{?>
		<h3 style="color:red;"> Invalid Selection </h3>
    	<a href="./teacher_courses.php">Back</a>
    	<?php
    }

    echo $OUTPUT->footer();
    
?>