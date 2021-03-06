<?php
	require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Map Grading Items");
    $PAGE->set_heading("Map Grading Items");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/map_grading_item.php');
    
    require_login();
    if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();
?>
<script src="../script/jquery/jquery-3.2.1.js"></script>
<script src="../script/validation/jquery.validate.js"></script>
<style>
label.error {
    color: red;
}
</style>

<?php    
    if(!empty($_GET['course']))
    {
        $course_id=$_GET['course'];
        $coursecontext = context_course::instance($course_id);
		is_enrolled($coursecontext, $USER->id) || die('<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
        //echo "Course ID : $course_id";

        // Get Grading Items
        $rec=$DB->get_records_sql("SELECT * FROM mdl_grading_policy WHERE courseid = ? ORDER BY id", array($course_id));

        $ParentActivites = $DB->get_records_sql("SELECT * FROM mdl_parent_activity WHERE courseid =?",array($course_id));
       

        if($rec){
            $recQ=$DB->get_records_sql('SELECT * FROM  `mdl_quiz` WHERE course = ?', array($course_id));
            $recA=$DB->get_records_sql('SELECT * FROM `mdl_assign` WHERE course = ?', array($course_id));
            
            if($recQ || $recA){
            $i = 0;
            $activityids = array();
            ?>
           
            <a href="./define_parent_activity.php?course=<?php echo $course_id ?>&flag=0" style="float:right; margin-bottom: 25px" class="btn btn-primary">Define Parent Activity</a>
            
            
            <form action="confirm_grading_item.php" method="post" id="mapForm">
                <table class="generaltable">
                    <tr class="table-head">
                        <th> Activities </th>
                        <th> Grading Items </th>
                        <th> Select Parent Activity </th>
                    </tr>
                    <?php
                    $i = 0;
                    foreach($recQ as $records)
                    {
                        $qid = $records->id;
                        $childid = $qid;
                        $qname = $records->name;
                        
                    ?>
                                
                    <tr>
                        <?php

                        //Flag to check mapped activites
                        $flagQ = $DB->get_records_sql("SELECT * FROM mdl_grading_mapping WHERE instance =? AND courseid = ? AND module = ?",array($qid,$course_id,16));

                         
                         if (!$flagQ)
                          { 
                            array_push($activityids,"Q".$qid);
                            ?>
                                <td>
                              <?php  echo $qname; ?>
                             </td>
                         <?php
                              }
                               ?>
                      
                       
                       
                            <?php if (!$flagQ) 
                            {  ?>  <td>
                                        <select required name="gitem[]" class="select custom-select" id="item<?php echo $i ?>">
                                            <option value=''>Choose..</option>
                                            <?php
                                            foreach ($rec as $recItem) {
                                                $gid = $recItem->id;
                                                $gname = $recItem->name;
                                                ?>
                                                <option value='<?php echo $gid; ?>'><?php echo $gname; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                 </td>
                            <?php } ?>
                        

                      
                        
                            <?php if (!$flagQ) 
                            { ?> <td>
                                    <select class="select custom-select" name="pactivity[]" id="pact<?php echo $i ?>">
                                        <option value=''>Choose..</option>
                                        <?php

                                        $SelectedParentActivity = $DB->get_records_sql("SELECT * FROM mdl_parent_mapping WHERE childid =?",array($childid));

                                            foreach ($SelectedParentActivity as $spa)
                                            {
                                                $parentidq = $spa->parentid;
                                               // break;

                                            }

                                          /*  $SelectedParentActivityName = $DB->get_records_sql("SELECT * FROM mdl_parent_activity WHERE id =?",array($parentid));


                                             foreach ($SelectedParentActivityName as $span)
                                            {
                                                $pname = $span->name;
                                                ?>
                                                <option value="hello"> <?php echo $pname ?></option>
                                                <?php
                                                

                                            }*/

                                             foreach ($ParentActivites as $parentActivity) {
                                               
                                                $id = $parentActivity->id;
                                                $name = $parentActivity->name;
                                                echo "id = $id pid= $parentid <br>";
                                                
                                                if($id == $parentidq )
                                                {

                                               ?>
                                               
                                               <option required selected value="<?php echo $id; ?>">
                                                        <?php echo $name; ?>
                                                   
                                               </option>
                                        
                                               <?php
                                                }
                                                else
                                                {
                                                    ?>
                                                         <option  value="<?php echo $id; ?>">
                                                        <?php echo $name; ?>
                                                   
                                               </option>
                                                    <?php
                                                }
                                         }

                                        ?>

                                    </select>
                            </td>
                            <?php } ?>
                        


                    </tr>
                    <?php
                        if (!$flagQ)
                          $i++;
                        
                        }



                        foreach($recA as $records)
                        {
                            $aid = $records->id;
                            $childid = $aid;
                            $aname = $records->name;
                            


                    $flagA = $DB->get_records_sql("SELECT * FROM mdl_grading_mapping WHERE instance =? AND courseid = ? AND module = ?",array($aid,$course_id,1));
                        
                                      
                        if (!$flagA)
                        {
                            array_push($activityids,"A".$aid);
                                ?>
                                <tr>
                                    <td><?php echo $aname;?> </td>
                                    <td>
                                        <select required name="gitem[]" class="select custom-select" id="item<?php echo $i ?>">
                                            <option value=''>Choose..</option>
                                            <?php
                                            foreach ($rec as $recItem) {
                                                $gid = $recItem->id;
                                                $gname = $recItem->name;
                                                ?>
                                                <option value='<?php echo $gid; ?>'><?php echo $gname; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </td>


                                      <td>
                                    <select required class="select custom-select" name="pactivity[]" id="pact<?php echo $i ?>">
                                        <option value=''>Choose..</option>
                                        <?php

                                            $SelectedParentActivity = $DB->get_records_sql("SELECT * FROM mdl_parent_mapping WHERE childid =?",array($childid));

                                            foreach ($SelectedParentActivity as $spa)
                                            {
                                                $parentida = $spa->parentid;
                                               // break;

                                            }



                                             foreach ($ParentActivites as $parentActivity)
                                            {
                                               
                                                $id = $parentActivity->id;
                                                $name = $parentActivity->name;

                                                if($id == $parentida)
                                                {
                                               ?>
                                               <option selected value="<?php echo $id; ?>">
                                                        <?php echo $name; ?>
                                                   
                                               </option>
                                               <?php
                                                 }
                                                 else
                                                   {
                                                    ?>
                                                    <option value="<?php echo $id; ?>">
                                                                <?php echo $name; ?>
                                                           
                                                       </option>
                                                    <?php
                                                   }
                                            }

                                        ?>

                                     </select>

                                </td>


                              </tr>
                        <?php

                            $i++;
                    }
                        }
                        global $SESSION;
                        $SESSION->activityids = $activityids;
                        
                        ?>
                </table>
			
                <input type="hidden" value='<?php echo $i; ?>' name="activitycount">
                <input type="hidden" value='<?php echo $course_id; ?>' name="courseid">
                <input type="submit" value="NEXT" name="submit" class="btn btn-primary">
	    	</form>

            


            <script>
                //form validation
                $(document).ready(function () {
                    $('#mapForm').validate({ // initialize the plugin
                        rules: {
                            "gitem[]": {
                                required: true
                            },
                            "pactivity[]":{
                                required: true
                            }
                        },
                        messages: {
                            "clo": {
                                required: "&nbsp;Please select Grading Item."
                            }
                        }
                    });
                });
            </script>

            <?php
            }

            ?>
             <h3 style="margin-top: 30px">Already Mapped Activities</h3>
<?php
        $mactivitiesids = array();
        
        if($recQ || $recA)

        {
            ?>
            <table class="generaltable" style="margin-top: 25px">
                <tr class="table-head">
                    <th> Activities </th>
                    <th>Edit</th>
                </tr>
            <?php

                foreach($recQ as $records)
                        {
                            $qid = $records->id;
                           // echo "$qid<br/>";
                            $childid = $qid;
                            $qname = $records->name;
                            array_push($mactivitiesids,"Q".$qid);
                            ?>
                            <tr>
                            <?php
                            //Flag to check mapped activites
                            $flagQ = $DB->get_records_sql("SELECT * FROM mdl_grading_mapping WHERE instance =? AND courseid = ? AND module = ?",array($qid,$course_id,16));
                            if ($flagQ)
                            { // echo "$qid";
                                $qid = "Q".$qid;
                              ?>
                                
                                 <td>  <?php echo "$qname<br/>"; ?> </td>
                                <td> 
                                    <a href="./edit_mapping.php?id=<?php echo $qid; ?>&course=<?php echo $course_id; ?>" title='Edit'> <i class='icon fa fa-pencil text-info' aria-hidden='true' title='Edit' aria-label='Edit'></i></a>
                                </td>
                          
                        <?php
                          } 
                          ?>
                      </tr>
                <?php
                        }

                        foreach($recA as $records)
                        {
                            $aid = $records->id;
                            $childid = $aid;
                            $aname = $records->name;
                            array_push($mactivitiesids,"A".$aid);
                                  ?>
                            <tr>
                            <?php
                            $flagA = $DB->get_records_sql("SELECT * FROM mdl_grading_mapping WHERE instance =? AND courseid = ? AND module = ?",array($aid,$course_id,1));
                            if ($flagA)
                             {
                                //var_dump($flagA);
                                $aid = "A".$aid;
                              ?>
                                
                                 <td>  <?php echo "$aname<br/>"; ?> </td>
                                <td> 
                                    <a href="./edit_mapping.php?id=<?php echo $aid; ?>&course=<?php echo $course_id; ?>" title='Edit'> <i class='icon fa fa-pencil text-info' aria-hidden='true' title='Edit' aria-label='Edit'></i></a>
                                </td>
                          
                        <?php
                          } 
                          ?>
                      </tr>
                <?php


                        }

                        ?>

            </table>

<?php
        }


        }
        else{
            echo "<h3>Found no graded item for this course!</h3>";

        }

    }
    else
    {?>
        <h2 style="color:red;"> Invalid Selection </h2>
        <a href="./teacher_courses.php">Back</a>
    <?php
    }
    echo $OUTPUT->footer();
?>
