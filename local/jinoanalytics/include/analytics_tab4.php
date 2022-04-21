<?php
defined('MOODLE_INTERNAL') || die();

$c = new local_jinoanalytics($course->id, $display_modules);
$analytics = new stdClass();
$analytics->course_info = $c->get_course_info();
$id = optional_param('id',0, PARAM_INT);
?>

<div class="dashboard-header">
    <a href="#tab4_sub1" class="switch-layer active"><?php echo get_string('assignment', $pluginname); ?></a> | 
    <a href="#tab4_sub2" class="switch-layer"><?php echo get_string('quiz', $pluginname); ?></a> |
    <a href="#tab4_sub3" class="switch-layer"><?php echo get_string('vod', $pluginname); ?></a> |
    <a href="#tab4_sub4" class="switch-layer"><?php echo get_string('forum', $pluginname); ?></a>
</div>

<div class="dashboard-content" id="tab4_sub1">
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('learningactivity_assignment', $pluginname); ?></h2>
            <table class="table table-bordered table-analytics">
                <thead>
                    <tr>
                        <th><?php echo get_string('week', $pluginname); ?></th>
                        <th><?php echo get_string('assignment_name', $pluginname); ?></th>
                        <th><?php echo get_string('assignment_affected', $pluginname); ?></th>
                        <th><?php echo get_string('assignment_submitted', $pluginname); ?></th>
                        <th><?php echo get_string('assignment_percent', $pluginname); ?></th>
                        <!--<th><?php echo get_string('assignment_graded', $pluginname); ?></th>-->
                        <th><?php echo get_string('assignment_avg_score', $pluginname); ?> / <?php echo get_string('assignment_total', $pluginname); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rolesqlfrom = 'from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 5)
                        where mc.instanceid = :instanceid and mc.contextlevel = 50';
                        $rolecount = $DB->count_records_sql("SELECT COUNT(ra.id) ".$rolesqlfrom, array('instanceid'=>$course->id));
                    $sections = $DB->get_records_sql('select cm.id, cs.section as cssection, cm.* from {course_sections} cs join {course_modules} cm on cs.id = cm.section and cs.course = cm.course where cs.course=:course and module = 1 order by cs.section asc',array('course'=>$id));
                    $rolecntave = 0;
                    $submitave = 0;
                    $i = 0;
                    $agrade = 0;
                    $aggrade = 0;
                    foreach($sections as $section){
                       $assigns = $DB->get_records('assign',array('id'=>$section->instance,'course'=>$id));
                       
                       foreach($assigns as $assign){
                           $assignsubmits = $DB->count_records_sql("select count(id) from {assign_submission} where assignment = :assignment and status = 'submitted'",array('assignment'=>$section->instance));
                           $assigngrades = $DB->get_field_sql("select sum(grade) from {assign_grades} where assignment = :assignment",array('assignment'=>$section->instance));
                           //$assignsubmits = $DB->count_records_sql("select COUNT(id) {assign_grades} where assignment = :assignment",array('assignment'=>$assign->id));
                           $subrate = $assignsubmits/$rolecount*100;
                           $avepoint = '0.00';
                           if($assignsubmits){
                               $avepoint = sprintf('%0.2f',$assigngrades/$assignsubmits);  
                           }
                           
                           echo '<tr><td>'.$section->cssection.'주차</td>';
                           echo '<td>'.$assign->name.'</td>';
                           echo '<td>'.$rolecount.'</td>';
                           echo '<td>'.$assignsubmits.'</td>';
                           echo '<td>'.sprintf('%0.2f', $subrate).'%'.'</td>';
                           echo '<td class="text-right">'.$avepoint.' / '.$assign->grade.'</td></tr>';
                       $i++;
                       $rolecntave = $rolecntave + $rolecount;
                       $submitave = $submitave + $assignsubmits;
                       $agrade = $agrade+$assign->grade;
                       $aggrade = $aggrade+$assigngrades;
                       }
                       
                   }
                    ?>
                    
                </tbody>
                <?php 
                $submitted = '0.00';
                $submitrate = '0.00';
                $maxagrade = '0.00';
                $aveagrade = '0.00';
                if($i){
                    $submitted = sprintf('%0.2f', $submitave/$i);
                    $maxagrade = $agrade/$i;
                }
                if($rolecntave){
                    $submitrate = sprintf('%0.2f', $submitave/$rolecntave*100);   
                }
                if($agrade){
                    $aveagrade = sprintf('%0.2f', $aggrade/$agrade*100);
                }
                
                ?>
                <tfoot>
                    <tr>
                        <th colspan="2">평균</th>
                        <th><?php echo $rolecount;?></th>
                        <th><?php echo $submitted; ?></th>
                        <th><?php echo $submitrate.'%'?></th>
                        <th class="text-right"><?php echo $aveagrade.' / '.$maxagrade;?></th>
                    </tr>
                </tfoot>

                <?php //$c->diplay_activity_table('assign'); ?>
            </table>
        </div>
    </div>
</div>

<div class="dashboard-content hide" id="tab4_sub2">
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('learningactivity_quiz', $pluginname); ?></h2>
            <table class="table table-bordered table-analytics">
                <thead>
                    <tr>
                        <th><?php echo get_string('week', $pluginname); ?></th>
                        <th><?php echo get_string('quiz_name', $pluginname); ?></th>
                        <th><?php echo get_string('quiz_affected', $pluginname); ?></th>
                        <th><?php echo get_string('quiz_examinee', $pluginname); ?></th>
                        <th><?php echo get_string('quiz_percent', $pluginname); ?></th>
                        <!--<th><?php echo get_string('quiz_graded', $pluginname); ?></th>-->
                        <th><?php echo get_string('quiz_result', $pluginname); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $qsections = $DB->get_records_sql('select cm.id, cs.section as cssection, cm.* from {course_sections} cs join {course_modules} cm on cs.id = cm.section and cs.course = cm.course where cs.course=:course and module = 23 order by cs.section asc',array('course'=>$course->id));
                    $qrolecntave = 0;
                    $qsubmitave = 0;
                    $j = 0;
                    $qgrade = 0;
                    $qggrade = 0;
                    $averagegrade = 0;
                    foreach($qsections as $section){
                       $quizes = $DB->get_records('quiz',array('id'=>$section->instance,'course'=>$id));
                         
                       foreach($quizes as $quiz){
                           
                           $quizsubmits = $DB->count_records_sql("select distinct(count(gg.userid)) from {grade_items} gi join {grade_grades} gg on gi.id = gg.itemid where gi.itemmodule = 'quiz' and gi.courseid = :courseid and gi.iteminstance = :instance", array('courseid'=>$id,'instance'=>$quiz->id));
                           $quizgrades = $DB->get_record_sql("select gg.id, sum(gg.finalgrade) as grade,gg.rawgrademax from {grade_items} gi join {grade_grades} gg on gi.id = gg.itemid where gi.itemmodule = 'quiz' and gi.courseid = :courseid and gi.iteminstance = :instance", array('courseid'=>$id,'instance'=>$quiz->id));
                           $subrate = $quizsubmits/$rolecount*100;
                           $qsubmitrate = '0.00';
                           $qpoint = '0.00';
                           $qmaxpoint = '0.00';
                           if($subrate){
                              $qsubmitrate = sprintf('%0.2f', $subrate);
                           }
                           if($quizsubmits){
                               $qpoint = sprintf('%0.2f', $quizgrades->grade/$quizsubmits);
                           }
                           if($quizgrades->rawgrademax){
                               $qmaxpoint = sprintf('%0.2f', $quizgrades->rawgrademax);
                           }
                           echo '<tr><td>'.$section->cssection.'주차</td>';
                           echo '<td>'.$quiz->name.'</td>';
                           echo '<td>'.$rolecount.'</td>';
                           echo '<td>'.$quizsubmits.'</td>';
                           echo '<td>'.$qsubmitrate.'%'.'</td>';
                           echo '<td class="text-right">'.$qpoint.' / '.$qmaxpoint.'</td></tr>';
                       $j++;
                       $qrolecntave = $qrolecntave + $rolecount;
                       $qsubmitave = $qsubmitave + $quizsubmits;
                       $qgrade = $qgrade+$quizgrades->rawgrademax;
                       $qggrade = $qggrade+$quizgrades->grade;
                       $averagegrade = $averagegrade+($quizgrades->grade/$quizsubmits/$quizgrades->rawgrademax*100);
                       }
                       
                   }
                    ?>
                </tbody>
                <?php 
                $qsubmit = '0.00';
                $qsubmitted = '0.00';
                $qmax = '0.00';
                if($j){
                    $qsubmit =  sprintf('%0.2f', $qsubmitave/$j);
                    $qmax = sprintf('%0.2f', $averagegrade/$j);
                }
                if($qrolecntave){
                    $qsubmitted = sprintf('%0.2f', $qsubmitave/$qrolecntave*100);
                }
                ?>
                <tfoot>
                    <tr>
                        <th colspan="2">평균</th>
                        <th><?php echo $rolecount;?></th>
                        <th><?php echo $qsubmit;?></th>
                        <th><?php echo $qsubmitted.'%'?></th>
                        <th><?php echo $qmax.' / 100';?></th>
                    </tr>
                </tfoot>

                <?php //$c->diplay_activity_table('quiz'); ?>

            </table>
        </div>
    </div>
</div>

<div class="dashboard-content hide" id="tab4_sub3">
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('learningactivity_vod', $pluginname); ?></h2>
            <table class="table table-bordered table-analytics">
                <tr>
                    <th><?php echo get_string('week', $pluginname); ?></th>
                    <th><?php echo get_string('vod_name', $pluginname); ?></th>
                    <th><?php echo get_string('vod_view_s', $pluginname); ?></th>
                    <th><?php echo get_string('vod_affected', $pluginname); ?></th>
                    <th><?php echo get_string('vod_watched', $pluginname); ?></th>
                    <th><?php echo get_string('vod_completion', $pluginname); ?></th>
                    <th><?php echo get_string('vod_progress', $pluginname); ?></th>
                </tr>
                <?php //$c->diplay_activity_table('vod'); ?>

                <tbody>
                    <?php 
                    $lsections = $DB->get_records_sql('select cm.id, cs.section as cssection, cm.* from {course_sections} cs join {course_modules} cm on cs.id = cm.section and cs.course = cm.course where cs.course=:course and module = 16 order by cs.section asc',array('course'=>$id));
                    $lrolecntave = 0;
                    $lsubmitave = 0;
                    $k = 0;
                    $vcountsum = 0;
                    $lgrade = 0;
                    $ltgrade = 0;
                    foreach($lsections as $section){
                       $lcmses = $DB->get_records('lcms',array('id'=>$section->instance,'course'=>$id));
                       
                       foreach($lcmses as $lcms){
                           $lselect = "select count(l.id)";
                           $vselect = "select l.*, lt.*, lt.progress as nowprogress "; 
                           $lfromsql = " from {lcms} l 
                           join {lcms_track} lt on l.id = lt.lcms and l.type ='video' ";
                           $lwheresql = " l.completionprogress <= lt.progress ";
                           
                           $lcount = $DB->count_records_sql($lselect.$lfromsql,array('id'=>$lcms->id));
                           $lcomcount = $DB->count_records_sql($lselect.$lfromsql.$lwhere,array('id'=>$lcms->id));
                           $trackinfo = $DB->get_record_sql($vselect.$lfromsql,array('id'=>$lcms->id));
                           
                           
                           //$assignsubmits = $DB->count_records_sql("select COUNT(id) {assign_grades} where assignment = :assignment",array('assignment'=>$assign->id));
                           $subrate = $assignsubmits/$rolecount*100;
                           echo '<tr><td>'.$section->cssection.'주차</td>';
                           echo '<td class="text-left">'.$lcms->name.'</td>';
                           echo '<td><a href="#" class="btn btn-primary">'.$lcms->viewcount.'</a></td>';
                           echo '<td>'.$rolecount.'</td>';
                           echo '<td>'.$lcount.'</td>';
                           echo '<td>'.$lcomcount.'</td>';
                           if($trackinfo->nowprogress==null){
                               $track = 0;
                           }else{
                               $track = $trackinfo->nowprogress;
                           }
                           if($lcms->completionprogress==null || $lcms->completionprogress==0){
                               $completion = 0;
                               echo '<td>-</td></tr>';
                           }else{
                               $completion = $lcms->completionprogress;
                               $vcomp = '0.00';
                               if($completion){
                                 $vcomp = sprintf('%0.2f', $track/$completion*100);
                               }
                               echo '<td>'.$vcomp.'%</td></tr>';
                           }
                           
                       $k++;
                       $lrolecntave = $lrolecntave + $lcount;
                       $lsubmitave = $lsubmitave + $lcomcount;
                       $vcountsum = $vcountsum + $lcms->viewcount;
                       $lgrade = $lgrade+$completion;
                       $ltgrade = $ltgrade+$track;
                       }
                       
                   }
                    ?>
                </tbody>
                <?php 
                $vavecount = '0.00';
                $vavesubitted = '0.00';
                $vavecompleted = '0.00';
                $vavecompletion = '0.00';
                if($k){
                    $vavecount = sprintf('%0.2f', $vcountsum/$k);
                    $vavesubitted = sprintf('%0.2f', $lrolecntave/$k);
                    $vavecompleted = sprintf('%0.2f', $lsubmitave/$k);
                }
                if($lgrade){
                   $vavecompletion = sprintf('%0.2f', $ltgrade/$lgrade*100);
                }
                ?>
                <tfoot>
                    <tr>
                        <th colspan="2">평균</th>
                        <th><a href="#" class="btn btn-primary"><?php echo $vavecount;?></a></th>
                        <th><?php echo $rolecount;?></th>
                        <th><?php echo $vavesubitted;?></th>
                        <th><?php echo $vavecompleted;?></th>
                        <th><?php echo $vavecompletion;?>%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="dashboard-content hide" id="tab4_sub4">
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('learningactivity_forum', $pluginname); ?></h2>
            <table class="table table-bordered table-analytics">
                <thead>
                    <tr>
                        <th><?php echo get_string('week', $pluginname); ?></th>
                        <th><?php echo get_string('forum_name', $pluginname); ?></th>
                        <th><?php echo get_string('forum_affected', $pluginname); ?></th>
                        <th><?php echo get_string('forum_writer_s', $pluginname); ?></th>
                        <th><?php echo get_string('forum_write', $pluginname); ?></th>
                        <th><?php echo get_string('forum_reply', $pluginname); ?></th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php 
                    $fsections = $DB->get_records_sql('select cm.id, cs.section as cssection, cm.* from {course_sections} cs join {course_modules} cm on cs.id = cm.section and cs.course = cm.course where cs.course=:course and module = 10 order by cs.section asc',array('course'=>$id));
                    $writercntave = 0;
                    $writtencntave = 0;
                    $rewritecntave = 0;
                    $l = 0;
                    foreach($fsections as $section){
                       $forums = $DB->get_records('forum',array('id'=>$section->instance,'course'=>$id));
                       
                       foreach($forums as $forum){
                           $fselectsql1 = "select count(distinct(fp.userid)) ";
                           $fselectsql2 = "select count(fp.id) ";
                           
                           $ffromsql = " from {forum_discussions} fd join {forum_posts} fp on fd.id = fp.discussion where course=:course and forum=:forum ";
                           $writercnt = $DB->count_records_sql($fselectsql1.$ffromsql,array('course'=>$id,'forum'=>$forum->id));
                           $writtencnt = $DB->count_records_sql($fselectsql2.$ffromsql,array('course'=>$id,'forum'=>$forum->id));
                           $rewritecnt = $writtencnt - $DB->count_records_sql($fselectsql2.$ffromsql." and parent = 0",array('course'=>$id,'forum'=>$forum->id));

                           //$assignsubmits = $DB->count_records_sql("select COUNT(id) {assign_grades} where assignment = :assignment",array('assignment'=>$assign->id));
                           $subrate = $assignsubmits/$rolecount*100;
                           echo '<tr><td>'.$section->cssection.'주차</td>';
                           echo '<td class="text-left">'.$forum->name.'</td>';
                           echo '<td>'.$rolecount.'</td>';
                           echo '<td>'.$writercnt.'</td>';
                           echo '<td>'.$writtencnt.'</td>';
                           echo '<td>'.$rewritecnt.'</td></tr>';
                        $l++;
                        $writercntave = $writercntave+$writercnt;
                        $writtencntave = $writtencntave+$writtencnt;
                        $rewritecntave = $rewritecntave+$rewritecnt;
                       }
                       
                   }
                    ?>
                </tbody>
                <?php 
                    $avewriter = '0.00';
                    $avewritten = '0.00';
                    $avereply = '0.00';
                    if($l){
                        $avewriter = sprintf('%0.2f', $writercntave/$l);
                        $avewritten = sprintf('%0.2f', $writtencntave/$l);
                        $avereply = sprintf('%0.2f', $rewritecntave/$l);
                    }
                ?>
                <tfoot>
                    <tr>
                        <th colspan="2">평균</th>
                        <th><?php echo $rolecount;?></th>
                        <th><?php echo $avewriter;?></th>
                        <th><?php echo $avewritten;?></th>
                        <th><?php echo $avereply;?></th>
                    </tr>
                </tfoot>
                <?php //$c->diplay_activity_table('forum'); ?>
            </table>
        </div>
    </div>
</div>