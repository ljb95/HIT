<?php 
require_once dirname(dirname(dirname (dirname (__FILE__)))).'/config.php';
require_once dirname(dirname(dirname (__FILE__))).'/lib/paging.php';
require_once dirname(dirname (dirname (__FILE__))).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/evaluation_course/survey_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 15, PARAM_INT);
$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);
$search       = optional_param('search', "", PARAM_RAW);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);

// 현재 년도, 학기
if(!$year) {
    $year = get_config('moodle', 'haxa_year');
}
if(!$term) {
    $term = get_config('moodle', 'haxa_term');
}

$page_params = array();
$params = array(
    'year'=>$year,
    'term'=>$term,
    'contextlevel'=>CONTEXT_COURSE
);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
?>

<?php include_once (dirname(dirname(dirname(__FILE__))).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname(dirname(__FILE__))).'/inc/sidebar_manage.php');?>
    
    <div id="content">
        <h3 class="page_title"><?php echo get_string('survey', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="../category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="../evaluation/evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <?php echo get_string('survey', 'local_lmsdata'); ?></div>
        <form name="" id="course_search" class="search_area" action="survey_list.php" method="get">
            <input type="hidden" name="page" value="1" />
            <input type="text" title="search" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        
        <table>
            <caption class="hidden-caption">설문</caption>
            <tr>
                <th scope="row"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('survey_name','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('survey_period','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('answer_cnt','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('viewresult','local_lmsdata'); ?></th>
            </tr>
            <?php 
            $offset =  ($currpage -1) * $perpage;
            $sql_like = "";
            if($search){
                $sql_like .= 'and e.targets like :search ';
            }
            if($searchtext){
                $sql_like .= 'and f.title like :searchtxt ';
            }
            $sql = 'select e.*,f.title '
                    . 'from {lmsdata_evaluation} e '
                    . 'join {lmsdata_evaluation_forms} f on f.id = e.formid '
                    . 'where e.type = :type '.$sql_like
                    .' order by e.id desc';
            $evaluations = $DB->get_records_sql($sql,array('type'=>2 ,'search'=>"%".$search."%",'searchtxt'=>"%".$searchtext."%"),$offset,$perpage);
            $cnt_sql = 'select count(e.id) ' 
                    . 'from {lmsdata_evaluation} e '
                    . 'join {lmsdata_evaluation_forms} f on f.id = e.formid '
                    . 'where e.type = :type '.$sql_like;
            $evaluations_cnt = $DB->count_records_sql($cnt_sql,array('type'=>2 ,'search'=>"%".$search."%",'searchtxt'=>"%".$searchtext."%"));
            $num = $evaluations_cnt- $offset;
            foreach($evaluations as $evaluation){
            ?>
            <tr>
                <td><?php echo $num--; ?></td>
                <td><a href="survey_list_modify.php?id=<?php echo $evaluation->id; ?>"><?php echo $evaluation->title; ?></a></td>
                <td><?php echo date("Y-m-d",$evaluation->timestart)." ~ ".date("Y-m-d",$evaluation->timeend); ?></td>
                <td><?php echo $DB->count_records('lmsdata_evaluation_submits',array('evaluation'=>$evaluation->id,'completion'=>1)); ?></td>
                <td><input type="button" class="gray_btn_small" onclick="window.open('<?php echo $CFG->wwwroot."/local/evaluation/answers.php?id=".$evaluation->id; ?>','answers','')" value="<?php echo get_string('viewresult','local_lmsdata'); ?>"></td>
            </tr>
            <?php 
                } 
                if(empty($evaluations)){
                    echo '<tr><td colspan="5">'.get_string('empty_survey','local_lmsdata').'</td></tr>';
                }
            ?>
        </table><!--Table End-->
        
        <div id="btn_area">
            <div style="float:right;">
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_survey','local_lmsdata'); ?>" onclick="location.href='survey_list_add.php'"/> 
            </div>
        </div>
        <?php
            print_paging_navbar_script($evaluations_cnt, $currpage, $perpage, $CFG->wwwroot.'/siteadmin/manage/evaluation_survey/survey_list.php?searchtext='.$searchtext.'&page=:page');       
        ?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
