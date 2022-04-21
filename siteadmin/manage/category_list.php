<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/category_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$search       = optional_param('search', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT);

//데이터 가져오기
$sql_select = "select lc.*,c4.name as cataname4, c3.name as cataname3, c4.coursecount, c2.name as cataname2, c1.name as cataname1 ";
$sql_from = " from {lmsdata_categories} lc 
            join {course_categories} c4 on c4.id=lc.category 
            join {course_categories} c3 on c3.id=c4.parent 
            join {course_categories} c2 on c2.id=c3.parent 
            join {course_categories} c1 on c1.id=c2.parent ";
$sql_orderby = " order by lc.id desc ";
$conditions = array();
$params = array();
if($cata3) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata3));
} else if($cata2) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata2));
} else if($cata1) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata1));
}
if(!empty($cata_path)) {
    $conditions[] = $DB->sql_like('c4.path', ':category');
    $params['category'] = $cata_path.'%';
}

if(!empty($searchtext)) {
    switch($search) {
        case 1: // 전체
            $conditions2 = array();
            $conditions2[] = $DB->sql_like('lc.subject_id', ':subject_id');
            $conditions2[] = $DB->sql_like('lc.kor_cat_name', ':kor_cat_name');
            $conditions2[] = $DB->sql_like('lc.eng_cat_name', ':eng_cat_name');
            $conditions[] = '('.implode(' OR ',$conditions2).')';
            $params['subject_id'] = '%'.$searchtext.'%';
            $params['kor_cat_name'] = '%'.$searchtext.'%';
            $params['eng_cat_name'] = '%'.$searchtext.'%';
            break;
        case 2: // 강의코드
            $conditions[] = $DB->sql_like('lc.subject_id', ':subject_id');
            $params['subject_id'] = '%'.$searchtext.'%';
            break;
        case 3; // 과정명
            $conditions2 = array();
            $conditions2[] = $DB->sql_like('lc.kor_cat_name', ':kor_cat_name');
            $conditions2[] = $DB->sql_like('lc.eng_cat_name', ':eng_cat_name');
            $conditions[] = '('.implode(' OR ',$conditions2).')';
            $params['kor_cat_name'] = '%'.$searchtext.'%';
            $params['eng_cat_name'] = '%'.$searchtext.'%';
            break;
        default:
            break;
    }
}

$sql_where = (!empty($conditions))? ' WHERE '.implode(' AND ',$conditions):'';

$datas = array();  // $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($currpage-1)*$perpage, $perpage);
$count_datas = 0; //$DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);

include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 
?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    
    <div id="content">
        <h3 class="page_title"><?php echo get_string('course_management', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <?php echo get_string('list','local_lmsdata'); ?></div>
        
        <form name="" id="course_search" class="search_area" action="category_list.php" method="get">
            <input type="hidden" name="page" value="1" />
            <select name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160">
                <option value="0">- <?php echo get_string('case1','local_lmsdata'); ?> -</option>
                <?php
                $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>0), 'sortorder', 'id, idnumber, name');
                foreach($catagories as $catagory) {
                    $selected = ($catagory->id == $cata1)? 'selected':'';
                    echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                }
                ?>
            </select>                        
            <select name="cata2" id="course_search_cata2" onchange="cata2_changed(this);" class="w_160">
                <option value="0">- <?php echo get_string('case2','local_lmsdata'); ?> -</option>
                <?php
                if($cata1) {
                    $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$cata1), 'sortorder', 'id, idnumber, name');
                    foreach($catagories as $catagory) {
                        $selected = ($catagory->id == $cata2)? 'selected':'';
                        echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                    }
                }
                ?>
            </select>
            <select name="cata3" id="course_search_cata3" class="w_160">
                <option value="0">- <?php echo get_string('case3','local_lmsdata'); ?> -</option>
                <?php
                if($cata1) {
                    $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$cata2), 'sortorder', 'id, idnumber, name');
                    foreach($catagories as $catagory) {
                        $selected = ($catagory->id == $cata3)? 'selected':'';
                        echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                    }
                }
                ?>
            </select>
            <br>
            <select name="search" class="w_160">
                <option value="1" <?php echo ($search==1)? 'selected':'';?>>- <?php echo get_string('all','local_lmsdata'); ?> -</option>
                <option value="2" <?php echo ($search==2)? 'selected':'';?>><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
                <option value="3" <?php echo ($search==3)? 'selected':'';?>><?php echo get_string('cata_name','local_lmsdata'); ?></option>
            </select> 
            <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        
        <table>
            <tr>
                <th width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th width="10%"><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
                <th width="20%"><?php echo get_string('case','local_lmsdata'); ?></th>
                <th><?php echo get_string('cata_name','local_lmsdata'); ?></th>
                <th width="10%"><?php echo get_string('opencourse_count', 'local_lmsdata'); ?></th>
            </tr>
            <?php
            if($count_datas > 0){
                $startnum = $count_datas - (($currpage - 1) * $perpage);
                foreach($datas as $data){
                    $cataname = $data->cataname1.' > '.$data->cataname2;
                    echo '<tr>
                    <td>'.$startnum.'</td>
                    <td>'.$data->subject_id.'</td>
                    <td>'.$cataname.'</td>
                    <td><a href="category_list_add.php?id='.$data->id.'">'.$data->kor_cat_name.'</a></td>
                    <td>'.$data->coursecount.'</td>
                    </tr>';
                                        $startnum--;
                }
            }else{
                echo '<tr><td colspan="5">'.get_string('empty_course','local_lmsdata').'</td></tr>';
            }
            ?>
            
        </table><!--Table End-->
        <div class="btn_area">
            <input type="button" value="<?php echo get_string('add_course','local_lmsdata'); ?>" onclick="location.href = 'category_list_add.php'" class="blue_btn" style="float:right;"/>
        </div>  
        
        <?php
            print_paging_navbar_script($count_datas, $currpage, $perpage, 'javascript:cata_page(:page);');
        ?>
          
        
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
