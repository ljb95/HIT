<?php 
require_once dirname(dirname(dirname (dirname (__FILE__)))).'/config.php';
require_once dirname(dirname(dirname (__FILE__))).'/lib/paging.php';
require_once dirname(dirname (dirname (__FILE__))).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/evaluation_course/evaluation_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$hakyear        = optional_param('hakyear', '', PARAM_RAW); //학년
$search       = optional_param('search', 1, PARAM_INT);
$term       = optional_param('term', 0, PARAM_RAW);
$year       = optional_param('year', 0, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT); //3차분류는 과정과 동일

// 현재 년도, 학기

$page_params = array();
$params = array(
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
        <h3 class="page_title"><?php echo get_string('lectureevaluation', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="../category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="../evaluation/evaluation_form.php"><?php echo get_string('evaluation_questionnaire', 'local_lmsdata'); ?></a> > <?php echo get_string('lectureevaluation', 'local_lmsdata'); ?></div>
        <form name="" id="course_search" class="search_area" action="evaluation_list.php" method="get">
            <input type="hidden" name="page" value="1" />
            
            <select title="category01" name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160">
                <option value=""><?php echo get_string('case1','local_lmsdata'); ?></option>
                <?php
                    $cata1_select = " SELECT * FROM {course_categories} ";
                    $cata1_where = array('depth = :depth ');
                    $cata1_params['depth'] = 1;
                    $cata1_sql = $cata1_select.' WHERE '.implode(' AND ', $cata1_where);
                    
                    $cata1_arr = $DB->get_records_sql($cata1_sql, $cata1_params);
                    foreach($cata1_arr as $category1) {
                        $selected = "";
                        if($category1->id == $cata1) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$category1->id.'"  '.$selected.'>'.$category1->name.'</option>';
                    }
                ?>
            </select>
            <select title="category02" name="cata2" id="course_search_cata2" onchange="cata2_changed(this)" class="w_160">
                <option value=""><?php echo get_string('case2','local_lmsdata'); ?></option>
                <?php
                    if(!empty($cata1)) {
                        $category_sql = ' SELECT id,name FROM {course_categories} WHERE ';
                        $sql_where = array();
                        $sql_where[] = 'parent =:parent';
                        $where = implode(" AND ", $sql_where);

                        $sql_params =array('parent'=>$cata1);
                        $catagories = $DB->get_records_sql($category_sql.$where, $sql_params);

                        foreach($catagories as $catagory2) {
                            $selected = "";
                            if($catagory2->id == $cata2) {
                               $selected = "selected";
                            } 
                            echo '<option value="'.$catagory2->id.'" '.$selected.'> '.$catagory2->name.'</option>';
                        }
                    }
                ?>
            </select>
            <select title="category03" name="cata3" id="course_search_cata3" class="w_160">
                <option value="0"><?php echo get_string('case3','local_lmsdata'); ?></option>
                 <?php
                    if(!empty($cata2)) {
                        $category_sql = ' SELECT id,name FROM {course_categories} WHERE ';
                        $sql_where = array();
                        $sql_where[] = 'parent =:parent';
                        $where = implode(" AND ", $sql_where);

                        $sql_params =array('parent'=>$cata2);

                        $catagories = $DB->get_records_sql($category_sql.$where, $sql_params);

                        foreach($catagories as $catagory3) {
                            $selected = "";
                            if($catagory3->id == $cata3) {
                               $selected = "selected";
                            } 
                            echo '<option value="'.$catagory3->id.'" '.$selected.'> '.$catagory3->name.'</option>';
                        }
                    }
                ?>
            </select>
            <br/>
            <select  title="year" name="year" class="w_160">
                <option value=""><?php echo get_string('all','local_lmsdata'); ?></option>
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
             <select title="term" name="term" class="w_160">
                <option value="0" <?php echo $term == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
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
            <br/>
            <select title="search" name="search" class="w_160">
                <option value="0" <?php echo !empty($search) == 0 ? 'selected' : ''?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                <option value="1" <?php echo !empty($search) == 1 ? 'selected' : ''?> ><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
                <option value="2" <?php echo !empty($search) == 2 ? 'selected' : ''?>><?php echo get_string('board_category','local_lmsdata'); ?></option>
            </select> 
            <input title="search" type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        
        <table>
            <caption class="hidden-caption">강의평가</caption>
            <tr>
                <th scope="row"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('eval_period','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('unwriter','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('viewresult','local_lmsdata'); ?></th>
            </tr>
            <?php 
            $join = "";
            $lcon = "";
            $params = array('type'=>1);
            if($cata3 || $cata2 || $cata1)$join = "JOIN {course_categories} cc on cc.path like :cata and cc.path like '%/'||c.category  ";
            if($cata3){         
                $params['cata'] = '/'.$cata1.'/'.$cata2.'/'.$cata3;
            } else if($cata2){            
                $params['cata'] = '/'.$cata1.'/'.$cata2.'/%';
            } else if($cata1){
                $params['cata'] = '/'.$cata1.'/%';
            }
            if($year){ 
                $lcon .= ' and lc.year = :year ';
                $params['year'] = $year;
            }
            if($term){ 
                $lcon .= ' and lc.term = :term ';
                $params['term'] = $term;
            }
            
            if(!empty($searchtext)){
                $params['searchtxt'] = '%'.$searchtext.'%';
                $params['searchtxt2'] = '%'.$searchtext.'%';
                $params['searchtxt3'] = '%'.$searchtext.'%';
                switch($search){
                    case 0:
                         $lcon .= ' and (lc.kor_lec_name like :searchtxt or lc.eng_lec_name like :searchtxt2 or lc.subject_id like :searchtxt3) '; 
                    break;                    
                    case 1:
                        $lcon .= ' and lc.subject_id like :searchtxt '; 
                    break;    
                    case 2:
                        $lcon .= ' and (lc.kor_lec_name like :searchtxt or lc.eng_lec_name like :searchtxt2) '; 
                    break;    
                    default:
                        $lcon .= ' and (lc.kor_lec_name like :searchtxt or lc.eng_lec_name like :searchtxt2 or lc.subject_id like :searchtxt3) '; 
                        break;
                }
            }
            $offset =  ($currpage -1) * $perpage;
            $sql = 'select e.*, c.fullname , lc.subject_id '
                    . 'from {lmsdata_evaluation} e '
                    . 'join {course} c on c.id = e.course '
                    . ' join {lmsdata_class} lc on lc.course = c.id '.$lcon
                                        .$join
                    . ' where e.type = :type '
                    . 'order by e.id desc';
            $evaluations = $DB->get_records_sql($sql,$params,$offset,$perpage);
            $cnt = 1;
            foreach($evaluations as $evaluation){
                ?>
              <tr>
                <td><?php echo $cnt++; ?></td>
                <td><?php echo $evaluation->subject_id ?></td>
                <td><a href='<?php echo $CFG->wwwroot."/siteadmin/manage/evaluation_course/evaluation_list_modify.php?id=".$evaluation->id; ?>'><?php echo $evaluation->fullname;  ?></a></td> 
                <td><?php echo date("Y-m-d",$evaluation->timestart)." ~ ".date("Y-m-d",$evaluation->timeend); ?></td>
                <td><?php echo $DB->count_records('lmsdata_evaluation_submits',array('evaluation'=>$evaluation->id,'completion'=>1)); ?></td>
                <td><input type="button" class="gray_btn_small" onclick="window.open('<?php echo $CFG->wwwroot."/local/evaluation/answers.php?id=".$evaluation->id; ?>','answers','')" value="<?php echo get_string('viewresult','local_lmsdata'); ?>"></td>
            </tr>
            <?php
             } 
                if(empty($evaluations)){
                    echo '<tr><td colspan="7">'.get_string('Explanation', 'local_lmsdata').'</td></tr>';
                }
            ?>
         
        </table><!--Table End-->
        
        <div id="btn_area">
            <div style="float:right;">
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_evaluation','local_lmsdata'); ?>" onclick="location.href='evaluation_list_add.php'"/> 
            </div>
        </div>
        <?php
            print_paging_navbar_script($evaluations_cnt, $currpage, $perpage, 'javascript:cata_page(:page);');       
        ?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../../inc/footer.php');?>
