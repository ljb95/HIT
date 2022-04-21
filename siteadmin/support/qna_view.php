<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/qna_view.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);
    
    require_once dirname(dirname(__FILE__)) . '/lib/paging.php';    
    require_once $CFG->dirroot . '/local/jinoboard/lib.php';
    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');
    
    
    $contentId        = optional_param('id', 0, PARAM_INT);
    $type = optional_param('type', 5, PARAM_INT);
    
    $contentsRS = $DB->get_record_sql("select jcb.*,u.firstname,u.lastname from {jinoboard_contents} jcb left join {user} u ON u.id=jcb.userid "
            . " where jcb.id=:id", array('id' => $contentId));    
    
    $postuser = $DB->get_record('user', array('id' => $contentsRS->userid));
    // 파일 다운로드
    $file_obj = $DB->get_record('files', array('itemid'=> $contentId, 'license'=>'allrightsreserved'));
    if(!empty($file_obj)){
    
    $file_url="";
    
        $file_stored = get_file_storage()->get_file_instance($file_obj);

        $file_url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file_stored->get_contextid(). '/'. $file_stored->get_component(). '/'.
                        $file_stored->get_filearea(). $file_stored->get_filepath().$file_stored->get_itemid().'/'. $file_stored->get_filename());

    }
    
    $page = new stdClass();
    $page->title = get_string('admin_question','local_lmsdata');   
?>

    
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php');?>
    <div id="content">
        <form action="qna_del.php" method="post">
            <input type="hidden" name="check_list[]" value="<?php echo $contentsRS->id;?>">
<h3 class="page_title"><?php echo get_string('admin_question','local_lmsdata'); ?></h3>
<div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./qna.php"><?php echo get_string('admin_question','local_lmsdata'); ?></a> > 상세보기</div>
<div class="detail-title-area">
    
    <span class="detail-title">
        <?php echo $contentsRS->title ?>
    </span>
    <br/>
    <?php
        $fullname = fullname($postuser);
    ?>
    <span class="detail-date area-right"><?php echo date('Y-m-d',$contentsRS->timecreated).' '.$fullname.' 작성';?></span>
</div>
<div class="detail-contents">
    <?php echo $contentsRS->contents; ?>
</div>
<div class="detail-attachment-area">
    <span class="detail-attachment-title">첨부</span>
    <ul class="detail-attachment">
        <li><?php if(!empty($file_obj) && $file_obj->id > 0) echo '<a href="'.$file_url.'">'.$file_stored->get_filename().'<img src="../img/icon-attachment.png" class="icon-attachment"/></a>'; else echo '첨부된 파일이 없습니다.' ?></li>
    </ul>
</div>

<div clas="btn_area">
   <input type="button" id="qna_list" class="gray_btn" value="<?php echo get_string('list2','local_lmsdata'); ?>" style="float: left" />
<input type="submit" id="deleste_qna" class="gray_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: right" />
<input type="button" id="qna_update" class="blue_btn" value="<?php echo get_string('edit','local_lmsdata'); ?>" style="float: right; margin: 0 10px 0 0" />
<input type="button" id="comment_qna" class="blue_btn" value="답글" style="float: right; margin: 0 10px 0 0" />   

</div> <!-- Bottom Button Area -->
</form>
    </div>
</div>


<?php 
    include_once '../footer.php';
?>


<script type="text/javascript">
      $(document).ready(function () {
		                
//                 $('#delete_qna').click(function() {
//                      var del_list =[];
//                      del_list.push(<?php echo $contentId?>);
//                    $.ajax({
//                        url : "./qna_del.php",
//                        type: "post",
//                        data : {
//                            data : del_list                            
//                        },
//                        async: false,
//                        success: function(data){
//                           location.href = "./qna.php";
//                        },
//                        error:function(e){
//                            console.log(e.responseText);
//                        }
//                    }); 
//                });
                
                
                $('#qna_update').click(function() {
                       location.href = "./qna_write.php?mod=edit&type=5&id=<?php echo $contentId?>";
                });
                
                $('#qna_list').click(function() {
                   location.href = "./qna.php";
                });
                
                $('#comment_qna').click(function() {
                       location.href = "./qna_write.php?mod=reply&type=5&id=<?php echo $contentId?>";
                });
        });
</script>