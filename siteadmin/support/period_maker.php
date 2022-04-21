<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib.php';
    
    $year = required_param('year', PARAM_INT);
    $term = required_param('term', PARAM_INT);
    $firstdate = required_param('firstdate', PARAM_RAW);
    
    $firstday = explode('-',$firstdate);
    $firstmonth = $firstday[0];
    $firstday = $firstday[1];
    
    for($i=0; $i<=14; $i++){
        $plusday = $i * 7;
        $new_period = new stdClass();
        $new_period->year = $year;
        $new_period->term = $term;
        $new_period->userid = $USER->id;
        $new_period->startdate = strtotime('+'.$plusday.' days',strtotime($year.'-'.$firstmonth.'-'.$firstday));
        $new_period->enddate =   strtotime('+13 days',$new_period->startdate);
        $new_period->section = $i+1;
        $new_period->timecreated = time();
        $new_period->timemodified = time();
        
        $DB->insert_record('lmsdata_trust',$new_period);
    }
    ?>
    <script>
        location.href='period.php?year=<?php echo $year; ?>&term=<?php echo $term; ?>';
    </script>