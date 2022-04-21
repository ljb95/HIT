<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

    require_once dirname(dirname(__FILE__)) . '/lib/paging.php';    
    require_once $CFG->dirroot . '/local/jinoboard/lib.php';
    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');

    $type = optional_param('type', 1, PARAM_INT);
    $page = optional_param('page', 1, PARAM_INT);
    $search = optional_param('search', '', PARAM_RAW);
    $perpage = optional_param('perpage', 10, PARAM_INT);

    $board = $DB->get_record('jinoboard', array('type' => $type));
    $context = context_system::instance();        
    $nav = array('top'=>'site', 'left'=>'board', 'sub'=>'notice');;
    
    $id = optional_param("id", 0, PARAM_INT);
    $mod = optional_param("mod", "", PARAM_TEXT);
    
     if ($type) {
         if (! $board = $DB->get_record("jinoboard", array("id" => $type))) {
            print_error('invalidboardid', 'jinotechboard');
        }
    }
   
    $temp = new stdclass();
    
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
    <h3 class="page_title"><?php echo get_string('notice','local_lmsdata'); ?></h3>
    <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./notices.php"><?php echo get_string('notice','local_lmsdata'); ?></a> > <?php echo get_string('write','local_lmsdata'); ?></div>
    <?php if(!empty($id) &&  $mod==='edit'){ ?>
        <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./notices_submit.php?mod=edit&id=<?php echo $id;?>&type=<?php echo $type;?>" method="POST">
    <?php }else{?>
         <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./notices_submit.php?type=<?php echo $type;?>" method="POST">
    <?php }?>
<table cellpadding="0" cellspacing="0" class="detail">

    <tbody>

        <tr>
            <td class="field_title"><label for="title"><?php echo get_string('title', 'local_lmsdata'); ?></label></td>
            <td class="field_value">
                <input type="text" class="w_300" id="title" name ="title" value="<?php echo (!empty($temp->title))?$temp->title:""; ?>"/>
                <input type="checkbox" name ="isnotice" <?php echo (!empty($temp->isnotice))?"checked":""; ?>/>
                <label>상단노출</label>
                <input type="checkbox" name ="ispush"/>
                <label>알림보내기</label>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('target','local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="checkbox" name="alltargets"> <label><?php echo get_string('all','local_lmsdata'); ?></label>
                <?php 
                $targets = define_targets_check();
                $tars= array();
               if(!empty($temp->targets)){
                    $tars = explode(',',$temp->targets);
               }
                foreach($targets as $key=>$val){
                    echo '<input type="checkbox" name ="targets[]" '.(in_array($key,$tars) ? 'checked' : '').' value="'.$key.'"/> <label>'.$val.'</label>';    
                }?>
            </td>
        </tr>
        <tr>
            <td class="field_title">공지기간</td>
            <td class="field_value">
                <input type="text" name="timestart" id="timestart" class="w_120" value="<?php echo empty($temp->timestart) ?  date('Y-m-d', time()) : date('Y-m-d', $temp->timestart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                <input type="text" name="timeend" id="timeend" class="w_120" value="<?php echo empty($temp->timeend) ?  date('Y-m-d', time()+604800) : date('Y-m-d', $temp->timeend); ?>" placeholder="yyyy-mm-dd"/> 
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
               <input type="hidden" class="" name="file_id" value="<?php echo $temp->itemid ? $temp->itemid : -1 ?>"/>
               <input type="hidden" name="file_del" value="0"/>
            </td>
        </tr>


    </tbody>

</table>

<div id="btn_area">
    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('save','local_lmsdata'); ?>"  />
    <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('list2','local_lmsdata'); ?>"  />
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
        location.href = "./notices.php";
    });
    
    $('#frm_popup_submit').submit(function (event){
          var title = $(".field_value input[name=title]").val();
          if(title.trim() == ''){
              alert("제목을 입력해 주세요");
              return false;
          };
          
          if ($('input:checkbox[name="targets[]"]:checked').length == 0){
            alert('대상을 선택해주세요');
            return false;
          }         
    });
    
    $('input:checkbox[name="targets[]"]').click(function(){
       if ($('input:checkbox[name="targets[]"]:checked').length == 7){
            $('input:checkbox[name="alltargets"]').prop('checked',true);
        } else {
            $('input:checkbox[name="alltargets"]').prop('checked',false);
        } 
    });
     
    $('input:checkbox[name="alltargets"]').click(function(){
        var all = $('input:checkbox[name="alltargets"]');
        if(all.is(':checked')){
            $('input:checkbox[name="targets[]"]').prop('checked',true);
        } else {
            $('input:checkbox[name="targets[]"]').prop('checked',false);
        }
    });
 });
   function remove_file(){

        $("a[name='file_link']").remove();
        $("input[name='remove_button']").remove();
        $("input[name='file_del']").val(1);
        
    }
</script>

<script type="text/javascript">
  $(function() {
    $("input:radio[name=noticescore]").each(function() {
        $(this).click(function(){
            noticescore_changed($(this).val());
            objection_changed($("input:radio[name=objection]:checked").val());
        });
    });
    $("input:radio[name=objection]").each(function() {
        $(this).click(function(){
            objection_changed($(this).val());
        });
    });
    $( "#timestart" ).datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
        $( "#timeend" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#timeend" ).datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function( selectedDate ) {
        $( "#timestart" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
    $("input[name=seq]").numeric({ decimal: false, negative: false }, function () { alert("숫자만 입력 가능합니다."); });
  });
</script>
