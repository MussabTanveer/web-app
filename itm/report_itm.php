<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("ITM Reports & Forms");
    $PAGE->set_heading("ITM Reports & Forms");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/itm/report_itm.php');
    
    require_login();
    if($SESSION->oberole != "itm"){
        header('Location: ../index.php');
    }
    echo $OUTPUT->header();
    
?>
    <link rel="stylesheet" type="text/css" href="../css/cool-link/style.css" />

	<div>
        <h3>Click the links down below as per need </h3><br>

        <a href="./select_frameworktoCourse.php" class="cool-link">Create Courses &amp; Map CLOs</a><br><br>

        <a href="../../../course/management.php" class="cool-link">Delete/Edit Courses</a><br><br>
        <!--<a href="./select_frameworktopracCourse.php" class="cool-link">Create Practical Courses &amp; Map CLOs</a><br><br>-->

        <!--<a href="./select_course.php" class="cool-link">Add CLOs to Courses</a><br><br>-->

        <a href="../../../cohort/index.php" class="cool-link">Add/Edit/Delete/View Cohorts</a><br><br>

        <a href="../../../user/editadvanced.php?id=-1" class="cool-link">Add a New User</a><br><br>
        
        <a href="../../../admin/tool/uploaduser/index.php" class="cool-link">Upload Users</a><br><br>

        <a href="../../../admin/user.php" class="cool-link">View/Edit/Delete Users</a><br><br>
        
        <a href="./select_course_enrol.php" class="cool-link">Enrol/Unenrol Users from Courses</a><br><br>
    </div>
<?php

echo $OUTPUT->footer();

?>
