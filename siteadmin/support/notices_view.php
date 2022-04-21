<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
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
    $type = optional_param('type', 1, PARAM_INT);
    
    $contentsRS = $DB->get_record_sql("select jcb.*,u.firstname,u.lastname from {jinoboard_contents} jcb left join {user} u ON u.id=jcb.userid "
            . " where jcb.id=:id", array('id' => $contentId));    
    
    $postuser = $DB->get_record('user', array('id' => $contentsRS->userid));
    //뷰 카운트 증가 (운영자는 뷰카운터 증가 x)
    //$DB->set_field_select('jinoboard_contents', 'viewcnt', intval($boardContent->viewcnt)+1, " id='$contentId'");
    
    
    // 파일 다운로드
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $contentId, 'timemodified', false);
    
    $page = new stdClass();
    $page->title = get_string('notice','local_lmsdata');
    
    $timestatus = ($contentsRS->timeend >= time())? '게시중':'종료';
    
    $targets = define_targets();
    $targets_txt_arr = array(); 
    $targets_txt = '';
    if($contentsRS->targets){
        $tars = explode(',',$contentsRS->targets); 
        foreach($tars as $val){
            $targets_txt_arr[] = $targets[$val];
        }
        $targets_txt = implode(',',$targets_txt_arr);
    }
   
    ?>

    
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php');?>
    <div id="content">
        <form action="notices_del.php" method="post">
            <input type="hidden" name="check_list[]" value="<?php echo $contentsRS->id;?>">
<h3 class="page_title"><?php echo get_string('notice','local_lmsdata'); ?></h3>
<div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./notices.php"><?php echo get_string('notice','local_lmsdata'); ?></a> > 상세보기</div>
<div class="detail-title-area">
    <span class="detail-title">
        <?php echo $contentsRS->title ?>
    </span>
    <br/>
    <span class="detail-viewinfo">
        <?php echo get_string('target','local_lmsdata'); ?> : <?php echo $targets_txt;?>  <?php echo get_string('post_period','local_lmsdata'); ?> : <?php echo date('Y-m-d',$contentsRS->timestart).' ~ '.date('Y-m-d',$contentsRS->timeend);?>
    </span>
    <span class="detail-status area-right"><?php echo $timestatus;?></span>
    <?php
        $fullname = fullname($postuser);
    ?>
    <span class="detail-date area-right"><?php echo date('Y-m-d',$contentsRS->timecreated).' '.$fullname.' 작성';?></span>
</div>
<div class="detail-contents">
    <?php 
        $contentsRS->contents = file_rewrite_pluginfile_urls($contentsRS->contents, 'pluginfile.php', $context->id, 'local_jinoboard', 'contents', $contentsRS->id);
        $contentsRS->contents = format_text($contentsRS->contents, true, $options);
        echo $contentsRS->contents; 
    ?>
</div>
<div class="detail-attachment-area">
    <span class="detail-attachment-title">첨부</span>
    <ul class="detail-attachment">
    <?php
        if(!empty($files)){
            foreach ($files as $file) {
                $filename = $file->get_filename();
                $mimetype = $file->get_mimetype();
                $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
                $path = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/local_jinoboard/attachment/' . $contentId . '/' . $filename);
            ?>
            <li><?php echo '<a href="'.$path.'">'.$filename.'<img src="../img/icon-attachment.png" class="icon-attachment"/></a>';?></li>
            <?php    
            }
        } else {
        ?>
            <li><?php echo '첨부된 파일이 없습니다.';?></li>
    <?php        
        }
    ?>
    </ul>
</div>

<div id="btn_area">
    <input type="button" id="notice_update" class="blue_btn" value="<?php echo get_string('edit','local_lmsdata'); ?>" style="float: right; margin: 0 10px 0 0" />
    <input type="submit" id="delete_notice" class="normal_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: right" />
    <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('list2','local_lmsdata'); ?>" style="float: left" />
   
</div> <!-- Bottom Button Area -->
</form>
    </div>
</div>


<?php 
    include_once '../footer.php';
?>


<script type="text/javascript">
      $(document).ready(function () {
		                
//                 $('#delete_notice').click(function() {
//                      var del_list =[];
//                      del_list.push(<?php echo $contentId?>);
//                    $.ajax({
//                        url : "./notices_del.php",
//                        type: "post",
//                        data : {
//                            data : del_list                            
//                        },
//                        async: false,
//                        success: function(data){
//                           location.href = "./notices.php";
//                        },
//                        error:function(e){
//                            console.log(e.responseText);
//                        }
//                    }); 
//                });
                
                
                $('#notice_update').click(function() {
                       location.href = "./notices_write.php?mod=edit&type=<?php echo $type?>&id=<?php echo $contentId?>";
                });
                
                $('#notice_list').click(function() {
                   location.href = "./notices.php";
                });
        });
</script>
