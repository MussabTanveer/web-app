<script src="../script/jquery/jquery-3.2.1.js"></script>

<?php
    require_once('../../../config.php');
    require_once('../script/spout-2.4.3/spout-2.4.3/src/Spout/Autoloader/autoload.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("Upload Marks");
    $PAGE->set_heading("Upload Marks");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/upload_marks_other.php');
    
    require_login();
    if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();
    use Box\Spout\Reader\ReaderFactory;
    use Box\Spout\Common\Type;
    
    if(!empty($_GET['id']) && !empty($_GET['course']))
    {
        $course_id=$_GET['course'];
        $coursecontext = context_course::instance($course_id);
        is_enrolled($coursecontext, $USER->id) || die('<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
    ?>

    <form id="uploadMarks" method="POST" enctype="multipart/form-data" class="mform">

         <div class="btn btn-default btn-file">
            <input  type="file" name="othermarks" id="othermarks" placeholder="Only excel files are allowed!">
        </div>
        <input class="btn btn-info" type="submit" name="Upload" value="Upload" >
        
    </form>

    <?php 

    $qid=$_GET['id'];
    $othermarks = $DB->get_records_sql('SELECT maxmark From mdl_manual_other Where id=?',array($qid));
    foreach($othermarks as $om){
        $marks=$om->maxmark;
    }

    $check=$DB->get_records_sql('SELECT *  FROM mdl_manual_other_attempt WHERE otherid = ?', array($qid));
    if($check){
        echo "<font color=red>Sorry, cannot upload marks because they have already been uploaded!</font>";
        goto end;
    }

    // check file name is not empty
    if (!empty($_FILES['othermarks']['name'])) {
      
        // Get File extension eg. 'xlsx' to check file is excel sheet
        $pathinfo = pathinfo($_FILES["othermarks"]["name"]);
        
        // check file has extension xlsx, xls and also check 
        // file is not empty
        if (($pathinfo['extension'] == 'xlsx' || $pathinfo['extension'] == 'xls') && $_FILES['othermarks']['size'] > 0 ) {
         
            // Temporary file name
            $inputFileName = $_FILES['othermarks']['tmp_name']; 
        
            // Read excel file by using ReadFactory object.
            $reader = ReaderFactory::create(Type::XLSX);
    
            // Open file
            $tempfile=$reader->open($inputFileName);
            $count = 1;
            $abc=1;
            
            //Number of sheet in excel file
            foreach ($reader->getSheetIterator() as $sheet) {
                if($abc>=1){
                    // Number of Rows in Excel sheet
                    foreach ($sheet->getRowIterator() as $row) {
                        
                        // It reads data after header. In the my excel sheet, 
                        // header is in the first row. 
                        if ($count > 1) { 
                            // $arri = array_map('strval', $arr);
                            // Data of excel sheet
                            $c1=count($row);
                            $sn=$row[0];
                            $rec=$DB->get_records_sql('SELECT id  FROM mdl_user WHERE username = ?', array($sn));
                            if ($rec){
                                foreach($rec as $records){
                                    $uid=$records->id;
                                }
                            }
                            else{
                                $uid="A";
                            }
                            $checkbit=0;
                            for($x=1;$x<$c1;$x++){
                                $pfix="sn";
                                ${$pfix.strtolower($x)}=$row[$x];
                                if (${$pfix.strtolower($x)} <> "" && $uid <> "A" && ${$pfix.strtolower($x)} <= $marks){
                                    $sql1="INSERT INTO mdl_manual_other_attempt (otherid,userid,obtmark) VALUES('$qid','$uid','${$pfix.strtolower($x)}')";
                                    $DB->execute($sql1);
                                }
                                elseif(${$pfix.strtolower($x)} == "" && $uid <> "A"){
                                    $sql1="INSERT INTO mdl_manual_other_attempt (assignproid,userid,obtmark) VALUES('$qid','$uid',0)";
                                    $DB->execute($sql1);
                                }
                                elseif (${$pfix.strtolower($x)} > $marks && $uid <> "A") {
                                    $sql1="INSERT INTO mdl_manual_other_attempt (assignproid,userid,obtmark) VALUES('$qid','$uid',0)";
                                    $DB->execute($sql1);
                                    $checkbit=1;
                                }
                            }
                        }
                        $count++;
                    }
                }
                $abc++;
            }
            if($sql1){
                echo "<h3 style='color:green;'>Result has been uploaded!</h3>";
            }
            if($checkbit){
                echo "<h3 style='color:red;'>Some students with obtained marks greater than maximum marks are assigned 0.</h3>";
            }
            //Close excel file
            $reader->close();
        }
        else {
            echo "<font color=red>Please Select Valid Excel File</font>";
        }
    }
    else {
        echo "<font color=red>Please Select Excel File</font>";
    }
    }
    else {?>
        <h3 style="color:red;"> Invalid Selection </h3>
        <a href="./teacher_courses.php">Back</a>
        <?php
    }
    end:
    echo $OUTPUT->footer();
    ?>
