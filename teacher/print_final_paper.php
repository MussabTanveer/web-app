


<?php 
   require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Print Final Exam");
    $PAGE->set_heading("Print Final Exam");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/print_final_paper.php');
    
    require_login();
    if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();

    if(!empty($_GET['type']) && !empty($_GET['course']))
    {
        $course_id=$_GET['course'];
        // echo "$course_id";
        $type=$_GET['type'];
        //echo " Activity Type : $type";
        
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
                
                $table->data[] = array($serialno,"<a href='./print_final.php?quiz=$qid&courseid=$course_id'>Print $qname</a>");
            
            }

            echo html_writer::table($table);
            echo "<br />";

        }

        else
            echo "<h3>You do not have any manual $type in this course!</h3>";

    }
    else
	{?>
		<h3 style="color:red;"> Invalid Selection </h3>
    	<a href="../index.php">Back</a>
    	<?php
    }

    echo $OUTPUT->footer();
    
?>