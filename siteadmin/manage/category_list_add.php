<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once $CFG->dirroot.'/lib/coursecatlib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/category_list_add.php');
    redirect(get_login_url());
}

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$id = optional_param('id',0,PARAM_INT);
$mode = optional_param('mode','',PARAM_RAW);

if($id){
    if(!$data = $DB->get_record('lmsdata_categories',array('id'=>$id))){
        $err_msg = get_string('err9','local_lmsdata');
        print_error($err_msg,'error',$CFG->wwwroot.'/siteadmin/manage/category_list.php');
    }
    $coursecat = coursecat::get($data->category, MUST_EXIST, true);
    $cate = $coursecat->get_db_record();
    $context = context_coursecat::instance($cate->id);
    $paths = explode('/',$cate->path);
    $cata1 = $paths[1];
    $cata2 = $paths[2];
    $cata3 = $paths[3];
}else{
    $data = new stdClass();
    $cate = new stdClass();
    $cata1 = 0;
    $cata2 = 0;
    $cata3 = 0;
}

if($mode=='update'){    
    $data->cata1 = required_param('cata1',PARAM_INT);
    $data->cata2 = required_param('cata2',PARAM_INT);
    $data->cata3 = required_param('cata3',PARAM_INT);
    $data->subject_id = required_param('subject_id',PARAM_RAW);
    $data->kor_cat_name = required_param('kor_cat_name',PARAM_RAW);
    $data->eng_cat_name = optional_param('eng_cat_name',' ',PARAM_RAW);
    
    $cate->name = $data->kor_cat_name;
    $cate->idnumber = $data->subject_id;
    $cate->parent = $data->cata3;
    
    if (empty($data->cata1)||empty($data->cata2)||empty($data->cata3)) {
        $err_msg = get_string('err10','local_lmsdata');
    } else if (empty($data->subject_id)) {
        $err_msg = get_string('err11','local_lmsdata');
    } else if (empty($data->kor_cat_name)) {
        $err_msg = get_string('err12','local_lmsdata');
    }
    
    if(!empty($err_msg)) {
        print_error($err_msg,'error',$CFG->wwwroot.'/siteadmin/manage/category_list_add.php');
        exit;
    }else{
        if($id){
            $cate->path = '/'.$data->cata1.'/'.$data->cata2.'/'.$data->cata3.'/'.$cate->id;
            $DB->update_record('course_categories',$cate);
            $DB->update_record('lmsdata_categories',$data);   
            redirect('category_list_add.php?id='.$id);
        }else{
            if($DB->record_exists('lmsdata_categories', array('subject_id'=>$data->subject_id))) {
                echo '<script type="text/javascript">'
                        .'alert("'.get_string('alert1','local_lmsdata').'");'
                        .'  document.location.href="category_list_add.php";'
                        .'</script>';
                exit;
            }
            $cate->sortorder = 999;
            $category = coursecat::create($cate);
            $data->category = $category->id;
            $DB->insert_record('lmsdata_categories',$data);
            redirect('category_list.php');
        }    
    }
    
}else if($mode=='delete'){
    if(isset($cate->coursecount) && $cate->coursecount==0){
        if($DB->delete_records('course_categories',array('id'=>$cate->id))){
            if($DB->delete_records('lmsdata_categories',array('id'=>$data->id))){
                redirect('category_list.php');
            }
        }       
        
    }else{
        $err_msg = get_string('err13','local_lmsdata');
        print_error($err_msg,'error',$CFG->wwwroot.'/siteadmin/manage/category_list_add.php');
        exit;
    }
    
}else{
    
    $js = array(
        $CFG->wwwroot.'/siteadmin/manage/course_list.js'
    );
?>

<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('add_course','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <?php echo get_string('add_course','local_lmsdata'); ?></div>

        <form name="update_form" action="category_list_add.php" method="post" enctype="multipart/form-data" onsubmit="return update_category_list();">
            <input type="hidden" name="mode"/>
            <input type="hidden" name="id" value="<?php echo $id;?>"/>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><?php echo get_string('case','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <select name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160" required>
                                <option value="">- <?php echo get_string('case1','local_lmsdata'); ?> -</option>
                                <?php
                                $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>0), 'sortorder', 'id, idnumber, name');
                                foreach($catagories as $catagory) {
                                    $selected = ($catagory->id == $cata1)? 'selected':'';
                                    echo '<option value="'.$catagory->id.'" '.$selected.'> '.$catagory->name.'</option>';
                                }
                                ?>
                            </select>
                            <select name="cata2" id="course_search_cata2" onchange="cata2_changed(this);" class="w_160" required>
                                <option value="">- <?php echo get_string('case2','local_lmsdata'); ?> -</option> 
                                <?php
                                if($cata2){
                                $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$cata1), 'sortorder', 'id, idnumber, name');
                                foreach($catagories as $catagory) {
                                    $selected = ($catagory->id == $cata2)? 'selected':'';
                                    echo '<option value="'.$catagory->id.'" '.$selected.'> '.$catagory->name.'</option>';
                                }
                                }
                                ?>
                            </select>
                            <select name="cata3" id="course_search_cata3" class="w_160" required>
                                <option value="">- <?php echo get_string('case3','local_lmsdata'); ?> -</option> 
                                <?php
                                if($cata3){
                                $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$cata2), 'sortorder', 'id, idnumber, name');
                                foreach($catagories as $catagory) {
                                    $selected = ($catagory->id == $cata3)? 'selected':'';
                                    echo '<option value="'.$catagory->id.'" '.$selected.'> '.$catagory->name.'</option>';
                                }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('course_code', 'local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" name="subject_id" value="<?php echo isset($data->subject_id)? $data->subject_id:'';?>" placeholder="<?php echo get_string('course_code', 'local_lmsdata'); ?>" required/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('cata_name','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" name="kor_cat_name" value="<?php echo isset($data->kor_cat_name)? $data->kor_cat_name:'';?>" size="50" required/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('eng_name','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" name="eng_cat_name" value="<?php echo isset($data->eng_cat_name)? $data->eng_cat_name:'';?>" size="50"/>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div id="btn_area">
                <div style="float:left;">
                    <input type="button" class="gray_btn" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="javascript:location.href='category_list.php';"/>
                </div>
                <div style="float:right;">
                    <input type="submit" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>"/>
                    <?php if(isset($cate->coursecount) && $cate->coursecount==0){?><input type="button" class="gray_btn" style="margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="delete_category_list();"/><?php }?>
                </div>
            </div>
        </form><!--Search Area2 End-->        
    </div><!--Content End-->

</div> <!--Contents End-->

<script type="text/javascript">
function update_category_list(){
    var frm = $('form[name=update_form]');
    frm.find('input[name=mode]').val('update');
    return validate_required_fields(frm); //유효성 체크 공통 함수
    frm.submit();
}

function delete_category_list(){
    if(!confirm('<?php echo get_string('delete_confirm','local_lmsdata'); ?>')) return;
    else location.href = 'category_list_add.php?id=<?php echo $id;?>&mode=delete';
}
</script>
 <?php
 include_once ('../inc/footer.php');
}
