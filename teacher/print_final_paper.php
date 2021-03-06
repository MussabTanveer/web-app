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
        $coursecontext = context_course::instance($course_id);
        is_enrolled($coursecontext, $USER->id) || die($OUTPUT->header().'<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
        $type=$_GET['type'];
        //echo " Activity Type : $type";
        
        $finals= $DB->get_records_sql("SELECT * FROM mdl_manual_quiz WHERE courseid = ? AND module = ?",array($course_id,-3));

        if($finals)
        {
            $serialno = 0;
            $table = new html_table();
            $table->head = array('S. No.', 'Print Final Exam Details','Print Final Exam Paper','Export To Excel','Delete','Edit');
            foreach ($finals as $records) {
                $serialno++;
                $qid = $records->id;
                $qname = $records->name;
                 $mime = $records->mime;
                if ($mime)
                    $table->data[] = array($serialno,"<a href='./print_final.php?quiz=$qid&courseid=$course_id'>Print $qname</a>","<a href='./print_uploaded_paper.php?quiz=$qid&courseid=$course_id'>Print $qname</a>","<a href='./downloadExcel.php?id=$qid&course=$course_id&type=$type' title='Download') ><i class='icon fa fa-download text-info' aria-hidden='true' title='Download' aria-label='Download'></i></a>","<a href='./delete_manual_activity.php?id=$qid&course=$course_id&type=$type' title='Delete' onClick=\"return confirm('Are you sure you want to delete this activity and its attempt?')\" ><i class='icon fa fa-trash text-danger' aria-hidden='true' title='Delete' aria-label='Delete'></i></a>","<a href='./edit_manual_activity.php?id=$qid&course=$course_id&type=$type' title='Edit') ><i class='icon fa fa-pencil text-info' aria-hidden='true' title='Edit' aria-label='Edit'></i></a>");
                else
                    $table->data[] = array($serialno,"<a href='./print_final.php?quiz=$qid&courseid=$course_id'>Print $qname</a>",'-',"<a href='./downloadExcel.php?id=$qid&course=$course_id&type=$type' title='Download') ><i class='icon fa fa-download text-info' aria-hidden='true' title='Download' aria-label='Download'></i></a>","<a href='./delete_manual_activity.php?id=$qid&course=$course_id&type=$type' title='Delete' onClick=\"return confirm('Are you sure you want to delete this activity and its attempt?')\" ><i class='icon fa fa-trash text-danger' aria-hidden='true' title='Delete' aria-label='Delete'></i></a>","<a href='./edit_manual_activity.php?id=$qid&course=$course_id&type=$type' title='Edit') ><i class='icon fa fa-pencil text-info' aria-hidden='true' title='Edit' aria-label='Edit'></i></a>");
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