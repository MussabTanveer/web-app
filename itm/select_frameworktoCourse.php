<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("OBE Frameworks");
    $PAGE->set_heading("OBE Framework Selection");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/itm/select_frameworktoCourse.php');
    
    require_login();
    if($SESSION->oberole != "itm"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();
    ?>
    <script src="../script/jquery/jquery-3.2.1.js"></script>
    <?php

    // Dispaly all frameworks
    $rec=$DB->get_records_sql('SELECT * FROM mdl_competency_framework');
    if($rec){
        ?>
        <form method="post" action="add_course.php" id="form_check">
        <?php
        $serialno = 0;
        $table = new html_table();
        $table->head = array('S. No.', 'Framework', 'Select');
        foreach ($rec as $records) {
            $serialno++;
            $id = $records->id;
            $sname = $records->shortname;
            $table->data[] = array($serialno, $sname, '<input type="radio" value="'.$id.'" name="fwid">');
        }
        if($serialno == 1){
            
            global $SESSION;
            $SESSION->fid11 = $id;
        
            redirect('add_course.php');
        }
        echo html_writer::table($table);
        ?>
        <input type='submit' value='NEXT' name='submit' class="btn btn-primary">
        </form>
        <br />
        <p id="msg"></p>

        <script>
        $('#form_check').on('submit', function (e) {
            if ($("input[type=radio]:checked").length === 0) {
                e.preventDefault();
                $("#msg").html("<font color='red'>Select any one framework!</font>");
                return false;
            }
        });
        </script>
        
        <?php
        echo $OUTPUT->footer();
    }
    else{
        echo "<h3>No framework found!</h3>";
        echo $OUTPUT->footer();
    }
?>
