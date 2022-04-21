<?php
defined('MOODLE_INTERNAL') || die();

$c = new local_jinoanalytics($course->id, $display_modules);
$analytics = new stdClass();
$analytics->course_info = $c->get_course_info();
$analytics->institution_info = $c->get_institution_info();
$analytics->department_info = $c->get_department_info();

$deptsql = 'select distinct dept,dept_cd from {lmsdata_user} where dept is not null order by dept_cd desc';
$depts = $DB->get_records_sql($deptsql);

$cdeptsql = 'select distinct lu.dept,lu.dept_cd from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 5)
                        join {lmsdata_user} lu on lu.userid = ra.userid where mc.instanceid = :instanceid and mc.contextlevel = 50 and dept is not null order by dept_cd desc';
$cdepts = $DB->get_records_sql($cdeptsql,array('instanceid'=>$course->id));

$classstudents = $DB->count_records_sql('select count(userid) from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 5)
                        where mc.instanceid = :instanceid and mc.contextlevel = 50',array('instanceid'=>$course->id));
?>
<div class="dashboard-header">
    <a href="#tab2_sub1" class="switch-layer active"><?php echo get_string('characteristicsoflearners', $pluginname); ?></a> ( <?php echo get_string('thenumberoflearners', $pluginname, $classstudents); ?> )
</div>

<div class="dashboard-content" id="tab2_sub1">
    <div class="block-half-left line-right">
        <div class="block-padding">
            <h2><?php echo get_string('collegedistribution', $pluginname); ?></h2>
            <div id="tab2_sub1_institution"></div>
        </div>
    </div>

    <div class="block-half-right">
        <div class="block-padding">
            <h2>&nbsp;</h2>
            <table class="table table-bordered table-analytics">
                <?php //$c->diplay_institution_table($analytics->institution_info); ?>
                <tbody>
                    <tr>
                        <th>학과</th>
                        <th>인원(명)</th>
                    </tr>
                    
                    <?php 
                    foreach($cdepts as $cdept){
                        $sql = 'select count(id) from {lmsdata_user} where usergroup="rs" and dept = :dept';
                        $students = $DB->count_records_sql('select count(lu.userid) from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 5) 
                        join {lmsdata_user} lu on lu.userid = ra.userid 
                        where  dept = :dept and mc.instanceid = :instanceid and mc.contextlevel = 50',array('instanceid'=>$course->id,'dept'=>$cdept->dept));
                        echo '<tr><td>'.$cdept->dept.'</td>';
                        echo '<td>'.$students.'</td></tr>';
                    }
                    ?>
                    
                </tbody>
            </table>
        </div>
    </div>

    <div class="clear-both"></div>

    <div class="block-half-left line-right">
        <div class="block-padding">
            <h2><?php echo get_string('departmentdistribution', $pluginname); ?></h2>
            <div id="tab2_sub1_department"></div>
        </div>
    </div>

    <div class="block-half-right">
        <div class="block-padding">
            <h2>&nbsp;</h2>
            <table class="table table-bordered table-analytics">
                <?php // $c->diplay_department_table($analytics->department_info); ?>
                <tbody>
                    <tr>
                        <th>학과</th>
                        <th>인원(명)</th>
                    </tr>
                    
                    <?php 
                    foreach($depts as $dept){
                        $sql = 'select count(id) from {lmsdata_user} where usergroup="rs" and dept_cd = :dept_cd';
                        $students = $DB->count_records_sql($sql, array('dept_cd'=>$dept->dept_cd));
                        echo '<tr><td>'.$dept->dept.'</td>';
                        echo '<td>'.$students.'</td></tr>';
                    }
                    ?>
                    
                </tbody>
            </table>
        </div>
    </div>

    <div class="clear-both"></div>

</div>


<script type="text/javascript">
    $(function () {

    // 학습자 > 단과대학 분포도
    $('#tab2_sub1_institution').highcharts({
    credits: {
    enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
            align: 'right', // css랑 마찬가지로 align기준으로
                    x: - 10, // x값 y값을 정하면 되요
                    verticalAlign: 'bottom',
                    y: - 5
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
                    enabled: false,
                            alpha: 45,
                            beta: 0
                    },
                    marginTop: 10,
            },
            title: {
            text: '',
                    y:17
            },
            tooltip: {
            pointFormat: '{' + 'series.name}: <b>{' + 'point.percentage:.1f}%</b>'
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
                    name: '비율',
                    data: [
<?php foreach ($analytics->institution_info as $key => $value) { ?>
                        {
                        name: '<?php echo $value->name; ?>',
                                y: <?php echo $value->cnt; ?>,
                                sliced: true,
                                selected: false
                        },
<?php } ?>
                    ]
            }]
    });
    // 학습자 > 학과 분포도
    $('#tab2_sub1_department').highcharts({
    credits: {
    enabled: true, // false 로 바꾸면 안보여요! (by.B-ver)
            text: "JINOTECH",
            href: "http://www.jinotech.com",
            position: {
            align: 'right', // css랑 마찬가지로 align기준으로
                    x: - 10, // x값 y값을 정하면 되요
                    verticalAlign: 'bottom',
                    y: 10
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
                    enabled: false,
                            alpha: 45,
                            beta: 0
                    },
                    marginTop: 10,
            },
            title: {
            text: '',
                    y:17
            },
            tooltip: {
            pointFormat: '{' + 'series.name}: <b>{' + 'point.percentage:.1f}%</b>'
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
                    name: '비율',
                    data: [
<?php foreach ($depts as $dept) { 
    $sql = 'select count(id) from {lmsdata_user} where usergroup="rs" and dept_cd = :dept_cd';
                        $students = $DB->count_records_sql($sql, array('dept_cd'=>$dept->dept_cd));
                        ?>
                        {
                        name: '<?php echo $dept->dept; ?>',
                                y: <?php echo $students; ?>,
                                sliced: true,
                                selected: false
                        },
<?php } ?>
                    ]
            }]
    });
    });
</script>