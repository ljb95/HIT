<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/qna_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

    require_once dirname(dirname(__FILE__)) . '/lib/paging.php';    
    require_once $CFG->dirroot . '/local/jinoboard/lib.php';
    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');

    $type = optional_param('type', 5, PARAM_INT);
    $page = optional_param('page', 1, PARAM_INT);
    $search = optional_param('search', '', PARAM_RAW);
    $perpage = optional_param('perpage', 10, PARAM_INT);

    
    $context = context_system::instance();        
    $nav = array('top'=>'site', 'left'=>'board', 'sub'=>'qna');;
    
    $id = optional_param("id", 0, PARAM_INT);
    $mod = optional_param("mod", "", PARAM_TEXT);
    
     if ($type) {
         if (! $board = $DB->get_record('jinoboard', array('type' => $type))) {
            print_error('invalidboardid', 'jinotechboard');
        }
    }
   
    $temp = new stdclass();
    
    if($mod == 'reply'){
        $parent_content = $DB->get_record('jinoboard_contents',array('id'=>$id));
        $temp->title = 're: '.$parent_content->title;
        $temp->email = $parent_content->email;
    }
    
    if(!empty($id) &&  $mod==='edit'){
        $search_list = "";
    
        $temp =  $DB->get_record_sql("select * from {jinoboard_contents} jcb left join 
            (SELECT  itemid FROM {files} WHERE component = 'local_jinoboard' AND filesize >0) f ON jcb.id = f.itemid 
            where id=:id", array("id" => $id));
        
        $file_obj = $DB->get_record('files', array('itemid'=> $id, 'license'=>'allrightsreserved'));
    
        $file_url="";

        if(!empty($file_obj)){
            $file_stored = get_file_storage()->get_file_instance($file_obj);

            $file_url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                            '/'. $file_stored->get_contextid(). '/'. $file_stored->get_component(). '/'.
                            $file_stored->get_filearea(). $file_stored->get_filepath().$file_stored->get_itemid().'/'. $file_stored->get_filename());

        }
        $write_user = $DB->get_record("user", array("id"=>$temp->userid));
    }else{
        $write_user = $USER;
    }
    $js = array(
    '../js/ckeditor-4.3/ckeditor.js',
    '../js/ckfinder-2.4/ckfinder.js'
);
?>
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php');?>
    <div id="content">
    <h3 class="page_title"><?php echo get_string('admin_question','local_lmsdata'); ?></h3>
    <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./qna.php"><?php echo get_string('admin_question','local_lmsdata'); ?></a> > <?php echo get_string('write','local_lmsdata'); ?></div>
    <?php if(!empty($id) &&  $mod==='edit'){ ?>
        <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="qna_submit.php?mod=edit&id=<?php echo $id;?>&type=<?php echo $type;?>" method="POST">
    <?php }else if(!empty($id) &&  $mod==='reply'){?>
            <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="qna_submit.php?mod=reply&id=<?php echo $id;?>&type=<?php echo $type;?>" method="POST">
    <?php }else {?>
         <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="qna_submit.php?type=<?php echo $type;?>" method="POST">
    <?php }?>
<table cellpadding="0" cellspacing="0" class="detail">

    <tbody>

        <tr>
            <td class="field_title"><?php echo get_string('title', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" class="w_300" name ="title" value="<?php echo (!empty($temp->title))?$temp->title:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title">이메일</td>
            <td class="field_value">
                <input type="text" class="w_300" name ="email" id="u_email" value="<?php echo (!empty($temp->email))?$temp->email:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('contents','local_lmsdata'); ?></td>
            <td class="field_value">
                <textarea style="width: 98%" id="editor" name="editor" ><?php echo (!empty($temp->contents))?$temp->contents:""; ?></textarea>			  	

            </td>
        </tr>
        <tr>
            <td class="field_title">첨부파일</td>
            <td class="field_value number">
                <?php echo (!empty($temp->itemid) &&  $temp->itemid > 0 ? '<a name="file_link" href="'.$file_url.'">'.$file_stored->get_filename().'<img src="../img/icon-attachment.png" class="icon-attachment"/></a>':'') ?> 
                <?php if(!empty($temp->itemid)){ ?>
                <input type="button" class="gray_btn_small" name="remove_button" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="remove_file();"/><br>
                <?php  }?>
               <input type="file" name="uploadfile" style="margin-top: 10px;"/> 
               <input type="hidden" class="" name="file_id" value="<?php if(!empty($file_obj) && $file_obj->id > 0) echo $temp->itemid; else echo -1; ?>"/>
               <input type="hidden" name="file_del" value="0"/>
            </td>
        </tr>


    </tbody>

</table>

<div class="btn_area">
    <input type="button" id="notice_list" class="gray_btn" value="<?php echo get_string('list2','local_lmsdata'); ?>" style="float: left;" />
    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('save','local_lmsdata'); ?>" style="float: right;" />
</div> <!-- Bottom Button Area -->

 </form>
    </div>
</div>
<?php include_once('../inc/footer.php');?>


<script type="text/javascript">
   var editor = CKEDITOR.replace( 'editor', {
        language : '<?php echo current_language(); ?>',
	filebrowserBrowseUrl : '../js/ckfinder-2.4/ckfinder.html',
	filebrowserImageBrowseUrl : '../js/ckfinder-2.4/ckfinder.html?type=Images',
	filebrowserFlashBrowseUrl : '../js/ckfinder-2.4/ckfinder.html?type=Flash',
	filebrowserUploadUrl : '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Files',
	filebrowserImageUploadUrl : '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Images',
	filebrowserFlashUploadUrl : '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Flash'
        });
        CKFinder.setupCKEditor( editor, '../' );
        
        
$(document).ready(function () { 
    
    $('#notice_list').click(function() {
        location.href = "./qna.php";
    });
    
    $('#frm_popup_submit').submit(function (event){
        var title = $(".field_value input[name=title]").val();
        var email = $("#u_email");
        if(title.trim() == ''){
          alert("제목을 입력해 주세요");
          return false;
        };
        
        if(!email.val()){
            alert("이메일을 입력해 주세요");
            return false;
        } else {
            var reg_email=/^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$/;
            if(!reg_email.test(email.val())){
                alert("잘못된 이메일입니다");
                return false;
            }
        }
    });
 });
   function remove_file(){

        $("a[name='file_link']").remove();
        $("input[name='remove_button']").remove();
        $("input[name='file_del']").val(1);
        
    }
</script>