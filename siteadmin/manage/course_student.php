<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_student.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);
$hyear        = optional_param('hyear', 0, PARAM_INT); //학년
$search       = optional_param('search', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT); //3차분류는 과정과 동일

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

<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    
    <div id="content">
        <h3 class="page_title"><?php echo get_string('opencourse', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo get_string('course_list','local_lmsdata'); ?></div>
        <form name="" id="course_search" class="search_area" action="course_list.php" method="get">
            <input type="hidden" name="page" value="1" />
            
            <select name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160">
                <option value=""><?php echo get_string('case1','local_lmsdata'); ?></option>
            </select>
            <select name="cata2" id="course_search_cata2" onchange="cata2_changed(this)" class="w_160">
                <option value=""><?php echo get_string('case2','local_lmsdata'); ?></option>
            </select>
            <select name="cata3" id="course_search_cata3" class="w_160">
                <option value="0"><?php echo get_string('case3','local_lmsdata'); ?></option>
            </select>
            <br/>
            <select name="year" class="w_160">
                <option value="0"  <?php echo $year == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    $year_arr = lmsdata_get_years();
                    foreach($year_arr as $tg_year) {
                        $selected = "";
                        if($tg_year == $year) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$tg_year.'"  '.$selected.'>'. get_string('year','local_lmsdata',$tg_year) . '</option>';
                    }
                ?>
            </select>
            <select name="term" class="w_160">
                <option value="0" <?php echo $term == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                echo $term;
                    $term_arr =lmsdata_get_terms();
                    foreach($term_arr as $term_key=> $tg_term) {
                        $selected = "";
                        if($term_key === $term) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$term_key.'"  '.$selected.'>'.$tg_term.'</option>';
                    }
                ?>
            </select>
            <select name="hyear" class="w_160">
                <option value="" <?php echo $hyear == '' ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    $hyear_arr = lmsdata_get_hyears();
                    foreach($hyear_arr as $hak_key => $tg_hyear) {
                        $selected = "";
                        if($hak_key == trim($hyear)) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$hak_key.'"  '.$selected.'>'.$tg_hyear.'학년</option>';
                    }
                ?>
            </select>
            <br/>
            <select name="search" class="w_160">
                <option value=""><?php echo get_string('all','local_lmsdata'); ?></option>
                <option value="subjectid"><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
                <option value="coursename"><?php echo get_string('cata_name','local_lmsdata'); ?></option>
            </select> 
            <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        
        <table>
            <tr>
                <th><input type="checkbox" onclick="check_course_id(this, 'courseid')"/></th>
                <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('opencourse_term', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('required','local_lmsdata'); ?></th>
                <th><?php echo get_string('class','local_lmsdata'); ?></th>
                <th><?php echo get_string('add_cnt','local_lmsdata'); ?></th>
                <th>수강생</th>
            </tr>
            <tr>
                <td><input type="checkbox" class="courseid" name="courseid" value="<?php echo $course->id; ?>"/></td>
                <td><?php $startnum=1; echo $startnum--; ?></td>
                <td>1111</td>
                <td class="text-left"><?php echo get_string('course_name', 'local_lmsdata'); ?></td>
                <td><?php echo get_string('teachername', 'local_lmsdata'); ?></td>
                <td><?php echo get_string('opencourse_term', 'local_lmsdata'); ?></td>
                <td><?php echo get_string('required','local_lmsdata'); ?></td>
                <td>1</td>
                <td>0</td>
                <td><input type="button" value="보기" class="blue_btn"></td>
            </tr>
        </table><!--Table End-->
        
        <div id="btn_area">
            <div style="float:left;">
                <input type="submit" class="gray_btn" value="분반선택" onclick="add_session_courses();"/>
            </div>
            <div style="float:right;">
                <input type="submit" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_excellstudent','local_lmsdata'); ?>" onclick="javascript:location.href='course_list_add.php';"/>
            </div>
        </div>
        <?php
        $count_courses = 0; // 페이지화필요
            print_paging_navbar_script($count_courses, $currpage, $perpage, 'javascript:cata_page(:page);');       
        ?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
