<script src="../script/jquery/jquery-3.2.1.js"></script>
<script src="../script/table2excel/jquery.table2excel.js"></script>
<?php 
    require_once('../../../config.php');
    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title("CLO Wise Report");
    $PAGE->set_heading("CLO Wise Report");
    $PAGE->set_url($CFG->wwwroot.'/local/ned_obe/teacher/clo_wise_report.php');
    
	require_login();
	if($SESSION->oberole != "teacher"){
        header('Location: ../index.php');
	}
    echo $OUTPUT->header();
?>
<style>
td{
    text-align:center;
}
th{
    text-align:center;
}
</style>
<?php

    if(!empty($_GET['course']))
    {
        $course_id=$_GET['course'];
        $coursecontext = context_course::instance($course_id);
        is_enrolled($coursecontext, $USER->id) || die('<h3>You are not enrolled in this course!</h3>'.$OUTPUT->footer());
        
        // Get all students of course
        $recStudents=$DB->get_records_sql("SELECT u.id AS sid, u.username AS seatnum, u.firstname, u.lastname
        FROM mdl_role_assignments ra, mdl_user u, mdl_course c, mdl_context cxt
        WHERE ra.userid = u.id
        AND ra.contextid = cxt.id
        AND cxt.contextlevel = ?
        AND cxt.instanceid = c.id
        AND c.id = ?
        AND (roleid=5)", array(50, $course_id));

        $stdids = array();
        $seatnos = array();
        foreach($recStudents as $records){
            $id = $records->sid;
            $seatno = $records->seatnum ;
            array_push($stdids,$id);
            array_push($seatnos,$seatno);
        }

        //Get course clo with its plo, level, passing percentage
		$courseclos=$DB->get_records_sql(
        "SELECT clo.id AS cloid, clo.shortname AS cloname, plo.idnumber AS ploidn, clokpi.kpi AS passpercent, taxlvl.level
    
        FROM mdl_competency_coursecomp cc, mdl_competency clo, mdl_competency plo, mdl_clo_kpi clokpi, mdl_taxonomy_clo_level taxclolvl, mdl_taxonomy_levels taxlvl

        WHERE cc.courseid = ? AND cc.competencyid=clo.id AND clo.id=clokpi.cloid AND plo.id=clo.parentid AND clo.id=taxclolvl.cloid AND taxclolvl.levelid=taxlvl.id",
        
        array($course_id));
            
        $clonames = array(); $plonames = array(); $lnames = array(); $closid = array(); $clospasspercent = array();
        foreach ($courseclos as $recC) {
            $cid = $recC->cloid;
            $clo = $recC->cloname;
            $plo = $recC->ploidn;
            $level = $recC->level;
            $pp = $recC->passpercent;
            array_push($closid, $cid); // array of clo ids
            array_push($clonames, $clo); // array of clo names
            array_push($plonames, $plo); // array of plo idnum
            array_push($lnames, $level); // array of levels
            array_push($clospasspercent, $pp); // array of clo pass percent
        }
        $closidCountActivity = array();
        for($j=0; $j<count($closid); $j++)
            $closidCountActivity[$j]=0;

        // Get course online quiz ids
        $courseQuizId=$DB->get_records_sql("SELECT * FROM `mdl_quiz` WHERE course = ? ", array($course_id));
        $quizids = array();
        foreach ($courseQuizId as $qid) {
            $id = $qid->id;
            array_push($quizids, $id); // array of quiz ids
        }
        
        // Get course online assignment ids
        $courseAssignId=$DB->get_records_sql("SELECT * FROM `mdl_assign` WHERE course = ? ", array($course_id));
        $assignids = array();
        foreach ($courseAssignId as $aid) {
            $id = $aid->id;
            array_push($assignids, $id); // array of assign ids
        }

        // Get attempted course manual quiz/midterm/final ids
        $courseMQuizId=$DB->get_records_sql("SELECT * FROM `mdl_manual_quiz` WHERE courseid = ? AND id IN (SELECT quizid FROM `mdl_manual_quiz_attempt`)", array($course_id));
        $mquizids = array();
        foreach ($courseMQuizId as $qid) {
            $id = $qid->id;
            array_push($mquizids, $id); // array of quiz/mt/final ids
        }
        //print_r($mquizids);

        // Get attempted course manual assignment/project ids
        $courseMAssignId=$DB->get_records_sql("SELECT * FROM `mdl_manual_quiz` WHERE courseid = ? AND id IN (SELECT assignproid FROM `mdl_manual_assign_pro_attempt`)", array($course_id));
        $massignids = array();
        foreach ($courseMAssignId as $qid) {
            $id = $qid->id;
            array_push($massignids, $id); // array of assign/pro ids
        }
        //print_r($massignids);
        
        /**** ONLINE+MANUAL QUIZZES ****/
        // Find students quiz records
        $seatnosQMulti = array();
        $closUniqueQMulti = array();
        $closQMulti = array();
        $resultQMulti = array();
        $cloQCount = array();
        $quiznames = array();
        
        // ONLINE QUIZ
        for($i=0; $i < count($quizids); $i++){
            $recQuiz=$DB->get_recordset_sql(
            'SELECT
            q.name AS quiz_name,
            qa.userid,
            u.idnumber AS std_id,
            u.username AS seat_no,
            CONCAT(u.firstname, " ", u.lastname) AS std_name,
            qu.competencyid,
            SUM(qua.maxmark) AS maxmark,
            SUM(qua.maxmark*qas.fraction) AS marksobtained
            FROM
                mdl_quiz q,
                mdl_quiz_slots qs,
                mdl_question qu,
                mdl_question_categories qc,
                mdl_quiz_attempts qa,
                mdl_question_attempts qua,
                mdl_question_attempt_steps qas,
                mdl_user u
            WHERE
                q.id=? AND qa.attempt=? AND q.id=qs.quizid AND qu.id=qs.questionid AND qu.category=qc.id AND q.id=qa.quiz AND qa.userid=u.id
                AND qa.uniqueid=qua.questionusageid AND qu.id=qua.questionid AND qua.id=qas.questionattemptid AND qas.fraction IS NOT NULL
            GROUP BY qa.userid, qu.competencyid
            ORDER BY qa.userid, qu.competencyid',
            
            array($quizids[$i],1));

            $seatnosQ = array();
            $closQ = array();
            $resultQ = array();
            
            $quizname = "";
            foreach($recQuiz as $rq){
                $quizname = $rq->quiz_name;
                $un = $rq->seat_no;
                $clo=$rq->competencyid;
                $qmax = $rq->maxmark; $qmax = number_format($qmax, 2); // 2 decimal places
                $mobtained = $rq->marksobtained; $mobtained = number_format($mobtained, 2);
                /*if( (($mobtained/$qmax)*100) > 50){
                    array_push($resultQ,"P");
                }
                else{
                    array_push($resultQ,"F");
                }*/
                array_push($resultQ,(($mobtained/$qmax)*100));
                array_push($seatnosQ,$un);
                array_push($closQ,$clo);
            }
            array_push($quiznames,$quizname);
            $cloQuizUnique = array_unique($closQ);

            array_push($cloQCount,count($cloQuizUnique));
            array_push($seatnosQMulti,$seatnosQ);
            array_push($closUniqueQMulti,$cloQuizUnique);
            array_push($closQMulti,$closQ);
            array_push($resultQMulti,$resultQ);
        }

        // MANUAL QUIZ/MIDTERM/FINAL
        for($i=0; $i < count($mquizids); $i++){
            $recMQuiz=$DB->get_recordset_sql(
            'SELECT
            q.name AS quiz_name,
            qa.userid,
            u.username AS seat_no,
            CONCAT(u.firstname, " ", u.lastname) AS std_name,
            qu.cloid,
            SUM(qu.maxmark) AS maxmark,
            SUM(qa.obtmark) AS marksobtained
            FROM
                mdl_manual_quiz q,
                mdl_manual_quiz_question qu,
                mdl_manual_quiz_attempt qa,
                mdl_user u
            WHERE
                q.id=? AND q.id=qu.mquizid AND q.id=qa.quizid AND qa.userid=u.id AND qu.id=qa.questionid
            GROUP BY qa.userid, qu.cloid
            ORDER BY qa.userid, qu.cloid',
            
            array($mquizids[$i]));

            $seatnosQ = array();
            $closQ = array();
            $resultQ = array();
            
            $quizname = "";
            foreach($recMQuiz as $rq){
                $quizname = $rq->quiz_name;
                $un = $rq->seat_no;
                $clo=$rq->cloid;
                $qmax = $rq->maxmark; $qmax = number_format($qmax, 2); // 2 decimal places
                $mobtained = $rq->marksobtained; $mobtained = number_format($mobtained, 2);
                /*if( (($mobtained/$qmax)*100) > 50){
                    array_push($resultQ,"P");
                }
                else{
                    array_push($resultQ,"F");
                }*/
                array_push($resultQ,(($mobtained/$qmax)*100));
                array_push($seatnosQ,$un);
                array_push($closQ,$clo);
            }
            array_push($quiznames,$quizname);
            $cloQuizUnique = array_unique($closQ);

            array_push($cloQCount,count($cloQuizUnique));
            array_push($seatnosQMulti,$seatnosQ);
            array_push($closUniqueQMulti,$cloQuizUnique);
            array_push($closQMulti,$closQ);
            array_push($resultQMulti,$resultQ);
        }
        /*
        echo "<br><br>"; print_r($quiznames); echo "<br><br>"; print_r($cloQCount);
        echo "<br><br>"; print_r($seatnosQMulti); echo "<br><br>"; print_r($closUniqueQMulti);
        echo "<br><br>"; print_r($closQMulti); echo "<br><br>"; print_r($resultQMulti);*/

        /**** ONLINE+MANUAL ASSIGNMENTS ****/
        // Find students assignment records
        $seatnosAMulti = array();
        $closUniqueAMulti = array();
        $closAMulti = array();
        $resultAMulti = array();
        $cloACount = array();
        $assignnames = array();
        
        // ONLINE ASSIGNMENTS
        for($i=0; $i < count($assignids); $i++){
            // Get assign records
            $recAssign=$DB->get_recordset_sql(
                'SELECT
                u.username AS seat_no,
                a.name AS assign_name,
                a.grade AS maxmark,
                ag.grade AS marksobtained,
                cmc.competencyid AS clo_id
                FROM
                    mdl_assign a,
                    mdl_assign_grades ag,
                    mdl_user u,
                    mdl_course_modules cm,
                    mdl_competency_modulecomp cmc
                WHERE
                    a.id=? AND ag.userid=u.id AND ag.grade != ? AND a.id=ag.assignment AND cm.course=? AND cm.module=? AND a.id=cm.instance AND cm.id=cmc.cmid
                ORDER BY ag.userid',
                
            array($assignids[$i],-1,$course_id,1));

            $seatnosA = array();
            $closA = array();
            $resultA = array();
            
            $assignname = "";
            foreach($recAssign as $as){
                $assignname = $as->assign_name;
                $un = $as->seat_no;
                $clo = $as->clo_id;
                $amax = $as->maxmark; $amax = number_format($amax, 2); // 2 decimal places
                $mobtained = $as->marksobtained; $mobtained = number_format($mobtained, 2);
                /*if( (($mobtained/$amax)*100) > 50){
                    array_push($resultA,"P");
                }
                else{
                    array_push($resultA,"F");
                }*/
                array_push($resultA,(($mobtained/$amax)*100));
                array_push($seatnosA,$un);
                array_push($closA,$clo);
            }

            array_push($assignnames,$assignname);
            $cloAssignUnique = array_unique($closA);

            array_push($seatnosAMulti,$seatnosA);
            array_push($closAMulti,$closA);
            array_push($resultAMulti,$resultA);

            array_push($cloACount,count($cloAssignUnique));
            array_push($closUniqueAMulti,$cloAssignUnique);
        }

        // MANUAL ASSIGNMENTS/PROJECTS
        for($i=0; $i < count($massignids); $i++){
            // Get assign records
            $recMAssign=$DB->get_recordset_sql(
                'SELECT
                u.username AS seat_no,
                a.name AS assign_name,
                a.maxmark AS maxmark,
                att.obtmark AS marksobtained,
                a.cloid AS clo_id
                FROM
                    mdl_manual_assign_pro a,
                    mdl_user u,
                    mdl_manual_assign_pro_attempt att
                WHERE
                    a.id=? AND att.userid=u.id AND a.id=att.assignproid
                ORDER BY att.userid',
                
            array($massignids[$i]));

            $seatnosA = array();
            $closA = array();
            $resultA = array();
            
            $assignname = "";
            foreach($recMAssign as $as){
                $assignname = $as->assign_name;
                $un = $as->seat_no;
                $clo = $as->clo_id;
                $amax = $as->maxmark; $amax = number_format($amax, 2); // 2 decimal places
                $mobtained = $as->marksobtained; $mobtained = number_format($mobtained, 2);
                /*if( (($mobtained/$amax)*100) > 50){
                    array_push($resultA,"P");
                }
                else{
                    array_push($resultA,"F");
                }*/
                array_push($resultA,(($mobtained/$amax)*100));
                array_push($seatnosA,$un);
                array_push($closA,$clo);
            }

            array_push($assignnames,$assignname);
            $cloAssignUnique = array_unique($closA);

            array_push($seatnosAMulti,$seatnosA);
            array_push($closAMulti,$closA);
            array_push($resultAMulti,$resultA);

            array_push($cloACount,count($cloAssignUnique));
            array_push($closUniqueAMulti,$cloAssignUnique);
        }
        
        for($i=0; $i<(count($quizids)+count($mquizids)); $i++)
            for($j=0; $j<count($closid); $j++)
                if(in_array($closid[$j], $closUniqueQMulti[$i]))
                    $closidCountActivity[$j]++;
        
        for($i=0; $i<(count($assignids)+count($massignids)); $i++)
            for($j=0; $j<count($closid); $j++)
                if(in_array($closid[$j], $closUniqueAMulti[$i]))
                    $closidCountActivity[$j]++;
        
    ?>

    <table class="generaltable" border="1">
        <tr>
            <th>Seat Number</th>
            <?php /****** CLO, Taxonomy, PLO ******/
            for($i=0; $i<count($closid); $i++) {
                if($closidCountActivity[$i]>0){
                ?>
                <th colspan="<?php echo $closidCountActivity[$i]; ?>"><?php echo $clonames[$i].", Taxonomy: ".strtoupper($lnames[$i]).", ".$plonames[$i]."<br>Passing Percentage: ".$clospasspercent[$i]."%"; ?></th>
                <?php
                }
            }
            $uniqueplonames = array_unique($plonames);
            ?>
        </tr>
        <tr>
            <th></th>
            <?php
            /****** Activity Names + Attempt ******/
            for($i=0; $i<count($closid); $i++){
                $attemptno = 1;
                for($j=0; $j<(count($quizids)+count($mquizids)); $j++)
                    if(in_array($closid[$i], $closUniqueQMulti[$j])){
                    ?>
                    <th><?php echo $quiznames[$j]."<br>(Attempt: ".$attemptno.")"; $attemptno++; ?></th>
                    <?php
                    }
                for($j=0; $j<(count($assignids)+count($massignids)); $j++)
                    if(in_array($closid[$i], $closUniqueAMulti[$j])){
                    ?>
                    <th><?php echo $assignnames[$j]."<br>(Attempt: ".$attemptno.")"; $attemptno++; ?></th>
                    <?php
                    }
            }
            ?>
        </tr>
        <?php
        foreach ($seatnos as $seatno) {
        ?>
        <tr> 
            <td> <?php echo "$seatno" ?> </td>
            <?php
            /****** QUIZZES/ASSIGNMENTS RECORDS ******/
            for($i=0; $i<count($closid); $i++){
                for($j=0; $j<(count($quizids)+count($mquizids)); $j++)
                    if(in_array($closid[$i], $closUniqueQMulti[$j])){
                        $flag=0;
                        for($k=0; $k<count($seatnosQMulti[$j]); $k++){
                            if($seatno == $seatnosQMulti[$j][$k] && $closid[$i] == $closQMulti[$j][$k])
                            {
                                $flag=1;
                                //if($resultQMulti[$j][$k] == 'P')
                                if($resultQMulti[$j][$k] >= $clospasspercent[$i])
                                    echo "<td><i class='fa fa-square' aria-hidden='true' style='color: #05E177'></i></td>";
                                else
                                    echo "<td><i class='fa fa-square' aria-hidden='true' style='color: #FE3939'></i></td>";
                            }
                        }
                        if($flag==0)
                        {
                            echo '<td><i class="fa fa-times" aria-hidden="true"></i></td>';
                        }
                    }
                for($j=0; $j<(count($assignids)+count($massignids)); $j++)
                    if(in_array($closid[$i], $closUniqueAMulti[$j])){
                        $flag=0;
                        for($k=0; $k<count($seatnosAMulti[$j]); $k++){
                            if($seatno == $seatnosAMulti[$j][$k] && $closid[$i] == $closAMulti[$j][$k])
                            {
                                $flag=1;
                                //if($resultAMulti[$j][$k] == 'P')
                                if($resultAMulti[$j][$k] >= $clospasspercent[$i])
                                    echo "<td><i class='fa fa-square' aria-hidden='true' style='color: #05E177'></i></td>";
                                else
                                    echo "<td><i class='fa fa-square' aria-hidden='true' style='color: #FE3939'></i></td>";
                            }
                        }
                        if($flag==0)
                        {
                            echo '<td><i class="fa fa-times" aria-hidden="true"></i></td>';
                        }
                    }
            }
            ?>
        </tr>
        <?php
        }
        ?>
    </table>

    <button id="myButton" class="btn btn-primary">Export to Excel</button>

    <!-- Export html Table to xls -->
    <script type="text/javascript" >
        $(document).ready(function(e){
            $("#myButton").click(function(e){ 
                $(".generaltable").table2excel({
                    name: "file name",
                    filename: "CLO-Report",
                    fileext: ".xls"
                });
            });
        });
    </script>

<?php
    }
    else
    {?>
        <h2 style="color:red;"> Invalid Selection </h2>
        <a href="./teacher_courses.php">Back</a>
    <?php
    }
    echo $OUTPUT->footer();
?>
