<?php 
defined('MOODLE_INTERNAL') || die();

$c = new local_jinoanalytics($course->id, $display_modules);
$analytics = new stdClass();
$analytics->course_info = $c->get_course_info();
$analytics->institution_info = $c->get_institution_info();
$analytics->department_info = $c->get_department_info();
$analytics->course_info = $c->get_course_info();
$analytics->module_info = $c->get_module_info();
$analytics->modlue_count = $c->get_modules_count();
$analytics->section_modules_count = $c->get_section_modules_count();
$analytics->module_week_count = $c->get_modules_week_count();
$analytics->session_week_count = $c->get_session_week_count();
$analytics->logs_week_count = $c->get_logs_week_count();
$analytics->relation_courses = $c->get_relation_courses();




// 모듈 정보
$modinfo = get_fast_modinfo($course);
$modnames = $modinfo->get_used_module_names();

//강좌 정보
$coursename = $DB->get_field('course','fullname',array('id'=>$course->id));
$class = $DB->get_record('lmsdata_class',array('course'=>$course->id));

//선생님
$teacher = $DB->get_field_sql('select eng_name from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 3)
                        join {lmsdata_user} lu on lu.userid = ra.userid 
                        where mc.instanceid = :instanceid and mc.contextlevel = 50',array('instanceid'=>$course->id));
//조교
$assistants = $DB->get_records("approval_reason",array('courseid'=>$course->id));
$j = 0;
$assistsarr = array();
foreach($assistants as $assist){
    $usertype = $DB->get_field_sql('select dept from {lmsdata_user} where userid =:userid',array('userid'=>$assist->userid));
    $assists[$j] = $usertype;
        $j++;
}
$assists = implode(', ',$assistsarr);
if(empty($assists)){
    $assists = '-';
}

//학과 정보
$classtypes = $DB->get_records_sql('select userid from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 5)
                        where mc.instanceid = :instanceid and mc.contextlevel = 50',array('instanceid'=>$course->id));
$type = array();
$i = 0;
$stu = 0;
foreach($classtypes as $ct){
    $usertype = $DB->get_field_sql('select dept from {lmsdata_user} where userid =:userid',array('userid'=>$ct->userid));
    
    
    if(!(in_array($usertype, $type))){
        $type[$i] = $usertype;
        $i++;
    }
    $stu++;
}
$types2 = implode(', ', $type);
$types = substr($types2 , 0, -2);
?>
<div class="dashboard-header">
    <a href="#tab1_sub1" class="switch-layer active"><?php echo get_string('status', $pluginname);?></a> | <a href="#tab1_sub2" class="switch-layer"><?php echo get_string('analysis', $pluginname);?></a>
</div>

<div class="dashboard-content" id="tab1_sub1">
    <div class="block-half-left line-right">
        <div class="block-padding">
            <h2><?php echo get_string('courseinfo', $pluginname);?></h2>
            <ul>
                <li><span class="title"><?php echo get_string('coursename', $pluginname);?></span><span class="content"><?php echo $coursename;?></span></li>
                <li><span class="title"><?php echo get_string('semester', $pluginname);?></span><span class="content"><?php echo $class->term; //echo $analytics->course_info->semester;?>학기</span></li>
                <li><span class="title"><?php echo get_string('college', $pluginname);?></span><span class="content"><?php //echo $analytics->course_info->dept_name_01;?>대전보건대학교</span></li>
                <li><span class="title"><?php echo get_string('department', $pluginname);?></span><span class="content"><?php echo $types; //cho $analytics->course_info->dept_name_02;?></span></li>
                <?php /*
                <li><span class="title">강좌 유형</span><span class="content"><?php echo $analytics->course_info->format;?></span></li>
                <li><span class="title">강좌 타입</span><span class="content"><?php echo $analytics->course_info->curriculum;?></span></li>
                 */
                ?>
                <li><span class="title"><?php echo get_string('studentcount', $pluginname);?></span><span class="content"><?php echo $stu;?></span></li>
            </ul>
        </div>
    </div>
    
    <div class="block-half-right">
        <div class="block-padding">
            <h2>&nbsp;</h2>
            <ul>
                <li><span class="title"><?php echo get_string('instructor', $pluginname);?></span><span class="content"><?php echo $teacher; //echo $c->display_user_name($analytics->course_info->teachers);?></span></li>
                <li><span class="title"><?php echo get_string('ta', $pluginname);?></span><span class="content"><?php echo $assists; //echo $c->display_user_name($analytics->course_info->assistants);?></span></li>
                <?php /* <li><span class="title">교수방법</span><span class="content"><?php echo join(', ',$modnames);?></span></li> */?>
                <li><span class="title"><?php echo get_string('duration', $pluginname);?></span><span class="content"><?php if($class->timeend > $class->timestart){ echo date("Y.m.d",$class->timestart).' ~ '.date("Y.m.d",$class->timeend);}else{echo date("Y.m.d",$class->timestart).' ~ ';}?></span></li>
                <li><span class="title">수업시간</span><span class="content"><?php echo $class->learningtime;//echo $analytics->course_info->class_times;?> 시간</span></li>
                <li><span class="title"><?php echo get_string('classroom', $pluginname);?></span><span class="content"><?php //echo $analytics->course_info->class_place; ?> 제1강의실</span></li>
            </ul>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-half-left">
        <div class="block-padding">
            <h2><?php echo get_string('resources_activities', $pluginname);?>(<?php echo get_string('intotal', $pluginname);?>)</h2>
            <table class="table table-bordered table-analytics">
            <?php echo $c->display_modules_count(); ?>
            </table>
        </div>
    </div>
    <div class="block-half-right">
        <div id="tab1_sub1_reg_activities"></div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('statistics', $pluginname);?></h2>
            <table class="table table-bordered table-analytics">
            <?php echo $c->display_activity_count(); ?>
            </table>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('weekly', $pluginname);?> | <?php echo get_string('flowoflearningactivity', $pluginname);?></h2>
            <div id="tab1_sub1_week_all_access"></div>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('weekly', $pluginname);?> | <?php echo get_string('thenumberofaccess', $pluginname);?></h2>
            <div id="tab1_sub1_week_user_access"></div>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('weekly', $pluginname);?> | <?php echo get_string('resources', $pluginname);?></h2>
            <div id="tab1_sub1_reg_week_activities"></div>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('weekly', $pluginname);?> | <?php echo get_string('activities', $pluginname);?></h2>
            <div id="tab1_sub1_module_access"></div>
        </div>
    </div>
    
</div>

<div class="dashboard-content hide" id="tab1_sub2">
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('comparison', $pluginname);?> | <?php echo get_string('createdlearningactivity', $pluginname);?></h2>
            <table class="table table-bordered table-analytics">
            <?php $c->display_modules_compare(null, true, true); ?>
            <?php if (is_array($analytics->relation_courses) && count($analytics->relation_courses)) $c->display_modules_compare($analytics->relation_courses, false, true); ?>
            </table>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('comparison', $pluginname);?> | <?php echo get_string('statistics', $pluginname);?></h2>
            <table class="table table-bordered table-analytics">
            <?php $c->display_activity_count(null, true, true); ?>
            <?php if (is_array($analytics->relation_courses) && count($analytics->relation_courses)) $c->display_activity_count($analytics->relation_courses, false, true); ?>
            </table>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('comparison', $pluginname);?> | <?php echo get_string('thenumberofaccess', $pluginname);?></h2>
            <div id="tab1_sub2_week_user_access"></div>
        </div>
    </div>
    
    <div class="clear-both"></div>
    
    <div class="block-full">
        <div class="block-padding">
            <h2><?php echo get_string('comparison', $pluginname);?> | <?php echo get_string('flowoflearningactivity', $pluginname);?></h2>
            <div id="tab1_sub2_week_all_access"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function () {
       
    // 강좌운영 > 운영현황 > 학습자원 등록
    $('#tab1_sub1_reg_activities').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },        
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            },
            marginTop: -10,
        },
        title: {
            text: '',
            y:17
        },
        tooltip: {
            pointFormat: '{'+'series.name}: <b>{'+'point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: '<?php echo get_string('ratio', $pluginname);?>',
            data: [
                <?php
                
                foreach($analytics->modlue_count as $key=>$value) { ?>
                    {
                    name: '<?php echo $value->name;?>',
                    y: <?php echo $value->cnt;?>,
                    sliced: true,
                    selected: false
                    },
                <?php } ?>
            ]
        }]
    });
    
    // 강좌운영 > 운영현황 > 주차별 학습활동 등록 : 주차단위로 등록된 학습자료의 수
    $('#tab1_sub1_reg_week_activities').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },
        chart: {
            type: 'column',
            options3d: {
                enabled: false,
                alpha: 15,
                beta: 15,
                viewDistance: 100,
                depth: 80
            },
            marginTop: 50,
            marginRight: 40,
            marginBottom: 100
        },

        title: {
            text: ''
        },

        xAxis: {
            categories: [<?php foreach($analytics->section_modules_count as $week=>$value) echo ($week == 0)?"'".get_string('overview', $pluginname)."',":"'".get_string('numweek', $pluginname, $week)."',";?>]
        },

        yAxis: {
            allowDecimals: false,
            min: 0,
            title: {
                text: 'Number of Learning activity'
            }
        },

        tooltip: {
            headerFormat: '<b>{'+'point.key}</b><br>',
            pointFormat: '{'+'series.name}: {'+'point.y} / {'+'point.stackTotal}'
        },

        plotOptions: {
            column: {
                stacking: 'normal',
                depth: 40
            }
        },

        series: [
        <?php foreach($display_modules as $mod_name) { ?>
        {
            name: '<?php echo "{$analytics->module_info[$mod_name]->name}";?>',
            data: [<?php foreach($analytics->section_modules_count as $week=>$data_cnt)  echo (!empty($data_cnt[$mod_name]->cnt)) ? "{$data_cnt[$mod_name]->cnt},":"0,"; ?>]
        },
        <?php } ?>
    ]
    });
    
    // 강좌운영 > 운영현황 > 학습자료별 참여 현황 : 주차단위로 등록된 학습자료에 대한 이용율
    $('#tab1_sub1_module_access').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },
        chart: {
            type: 'column',
            options3d: {
                enabled: false,
                alpha: 15,
                beta: 15,
                viewDistance: 100,
                depth: 80
            },
            marginTop: 50,
            marginRight: 40,
            marginBottom: 100
        },

        title: {
            text: ''
        },

        xAxis: {
            categories: [<?php foreach($analytics->module_week_count as $week=>$value) echo ($week == 0)?"'".get_string('overview', $pluginname)."',":"'".get_string('numweek', $pluginname, $week)."',";?>]
        },

        yAxis: {
            allowDecimals: false,
            min: 0,
            title: {
                text: 'Number of Learning activites'
            }
        },

        tooltip: {
            headerFormat: '<b>{'+'point.key}</b><br>',
            pointFormat: '{'+'series.name}: {'+'point.y} / {'+'point.stackTotal}'
        },

        plotOptions: {
            column: {
                stacking: 'normal',
                depth: 40
            }
        },

        series: [
        <?php foreach($display_modules as $mod_name) { ?>
        {
            name: '<?php echo "{$analytics->module_info[$mod_name]->name}";?>',
            data: [<?php foreach($analytics->module_week_count as $week=>$data_cnt)  echo (!empty($data_cnt[$mod_name]->cnt)) ? "{$data_cnt[$mod_name]->cnt},":"0,"; ?>]
        },
        <?php } ?>
    ]
    });
    
    // 강좌운영 > 운영현황 > 주차별 학습 참여자 : 강좌 시작일 기준, 주차 단위 활동 학생수
    $('#tab1_sub1_week_user_access').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },
        title: {
            text: '',
            y:17
        },
        xAxis: {
            categories: [<?php foreach($analytics->session_week_count as $week=>$cnt) echo "'".get_string('numweek', $pluginname, $week)."',";?>]
        },
        yAxis: {
            title: { 
                enabled:false,
                text: 'Score'
            },
            min:0,
            //max:100,
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            pointFormat: '{'+'point.y} <?php echo get_string('activity', $pluginname);?>',
            valueSuffix: '',
            shared: true
        },
        legend: {
            enabled:false,
            layout: 'vertical',
            borderWidth: 0
        },
        series: [{
            name: '<?php echo get_string('region', $pluginname);?>',
            data: [<?php foreach($analytics->session_week_count as $week=>$cnt) echo "{$cnt},";?>]
        }]
    });
    
    // 강좌운영 > 운영현황 > 주차별 모든 활동
    $('#tab1_sub1_week_all_access').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },
        title: {
            text: '',
            y:17
        },
        xAxis: {
            categories: [<?php foreach($analytics->logs_week_count as $week=>$cnt) echo "'".get_string('numweek', $pluginname, $week)."',";?>]
        },
        yAxis: {
            title: { 
                enabled:false,
                text: 'Score'
            },
            min:0,
            //max:100,
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            pointFormat: '{'+'point.y}',
            valueSuffix: '',
            shared: true
        },
        legend: {
            enabled:false,
            layout: 'vertical',
            borderWidth: 0
        },
        series: [
                {
                    name: '<?php echo get_string('activity', $pluginname);?>',
                    data: [<?php foreach($analytics->logs_week_count as $week=>$cnt) echo "{$cnt},";?>]
                },
            ]
    });
    
    
    // 강좌운영 > 비교분석 > 주차별 학습 참여자 : 강좌 시작일 기준, 주차 단위 활동 학생수
    $('#tab1_sub2_week_user_access').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },
        title: {
            text: '',
            y:17
        },
        xAxis: {
            categories: [<?php foreach($analytics->session_week_count as $week=>$cnt) echo "'".get_string('numweek', $pluginname, $week)."',";?>]
        },
        yAxis: {
            title: { 
                enabled:false,
                text: 'User'
            },
            min:0,
            //max:100,
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
           // pointFormat: '{point.y}',
            valueSuffix: '',
            shared: true
        },
        legend: {
            enabled:true,
            layout: 'horizontal',
            borderWidth: 0
        },
        series: [
            {
            name: '<?php echo $analytics->course_info->year.' '.$c->get_semester_name($analytics->course_info->semester_code); ?>',
            data: [<?php foreach($analytics->session_week_count as $week=>$cnt) echo "{$cnt},";?>]
            },
            <?php
                foreach($analytics->relation_courses as $courseid=>$course_info) {
                    $session_week_count = $c->get_session_week_count($courseid);
                    echo '{';
                        echo "name:'".$course_info->year.' '.$c->get_semester_name($course_info->semester_code)."',";
                        echo 'data:['; foreach($session_week_count as $week=>$cnt) echo "{$cnt},"; echo '],';
                    echo '},';
                }
            ?>
        ]
    });
    
    // 강좌운영 > 비교분석 > 주차별 모든 활동
    $('#tab1_sub2_week_all_access').highcharts({
        credits: {
            enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
                  align: 'right', // css랑 마찬가지로 align기준으로
                  x: -10,         // x값 y값을 정하면 되요
                  verticalAlign: 'bottom',
                  y: -5
            },
            style: {
                  cursor: 'pointer', // css cursor 속정을 따릅니다!
                  color: '#909090',
                  fontSize: '10px'
            }
        },
        title: {
            text: '',
            y:17
        },
        xAxis: {
            categories: [<?php foreach($analytics->logs_week_count as $week=>$cnt) echo "'".get_string('numweek', $pluginname, $week)."',";?>]
        },
        yAxis: {
            title: { 
                enabled:false,
                text: 'Score'
            },
            min:0,
            //max:100,
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            //pointFormat: '{'+'point.y}',
            valueSuffix: '',
            shared: true
        },
        legend: {
            enabled:true,
            layout: 'horizontal',
            borderWidth: 0
        },
        series: [
                {
                    name: '<?php echo $analytics->course_info->year.' '.$c->get_semester_name($analytics->course_info->semester_code); ?>',
                    data: [<?php foreach($analytics->logs_week_count as $week=>$cnt) echo "{$cnt},";?>]
                },
                        
                <?php
                foreach($analytics->relation_courses as $courseid=>$course_info) {
                    $logs_week_count = $c->get_logs_week_count($courseid);
                    echo '{';
                        echo "name:'".$course_info->year.' '.$c->get_semester_name($course_info->semester_code)."',";
                        echo 'data:['; foreach($logs_week_count as $week=>$cnt) echo "{$cnt},"; echo '],';
                    echo '},';
                }
                ?>
            ]
    });
});
</script>