<?php 
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Select Final Exam");
    $PAGE->set_heading("Select Final Exam");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/sample_paper_final_selection.php');
    
    require_login();
    if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();

    if(!empty($_GET['type']) && !empty($_GET['course']) && isset($_GET['upload']))
    {
        $course_id=$_GET['course'];
        // echo "$course_id";
        $coursecontext = context_course::instance($course_id);
        is_enrolled($coursecontext, $USER->id) || die($OUTPUT->header().'<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
        $type=$_GET['type'];
        //echo " Activity Type : $type";

         $type=$_GET['type'];
        //echo " Activity Type : $type";
        $upload = $_GET['upload'];
        
        $finals= $DB->get_records_sql("SELECT * FROM mdl_manual_quiz WHERE courseid = ? AND module = ?",array($course_id,-3));

        if($finals)
        {
            $serialno = 0;
            $table = new html_table();
            $table->head = array('S. No.', 'Final Exam Name');
            foreach ($finals as $records) {
                $serialno++;
                $qid = $records->id;
                $qname = $records->name;
                
                if ($upload)
                    $table->data[] = array($serialno,"<a href='./upload_samples.php?type=finalexam&instance=$qid&courseid=$course_id'>$qname</a>");
                else
                     $table->data[] = array($serialno,"<a href='./view_samples.php?type=finalexam&instance=$qid&courseid=$course_id'>$qname</a>");
            }

            echo html_writer::table($table);
            echo "<br />";

        }

        else
            echo "<h3>You do not have any manual $type in this course!</h3>";
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