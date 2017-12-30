<?php
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title("ITM Reports & Forms");
    $PAGE->set_heading("ITM Reports & Forms");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/itm/report_itm.php');
    echo $OUTPUT->header();
    require_login();
    //is_siteadmin() || die($OUTPUT->header().'<h2>This page is for site admins only!</h2>'.$OUTPUT->footer());
?>
    <link rel="stylesheet" type="text/css" href="../css/cool-link/style.css" />

	<div>
        <h3>Click the links down below as per need </h3><br>

       

        <a href="./select_frameworktoCourse.php" class="cool-link">Create Courses &amp; Map CLOs</a><br><br>

        <!--<a href="./select_course.php" class="cool-link">Add CLOs to Courses</a><br><br>-->

        <a href="../../../user/editadvanced.php?id=-1" class="cool-link">Add a new User</a><br><br>

        <a href="../../../admin/user.php" class="cool-link">View all Users</a><br><br>

        

        <a href="./select_course_enrol.php" class="cool-link">Enrol/Unenrol Users from Courses</a><br><br>
    </div>
<?php

echo $OUTPUT->footer();

?>
