<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);  
}

$type = optional_param('type', 'upload', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/local/lmsdata/quizupload/quizupload.php');
$PAGE->set_pagelayout('standard');

$strplural = '퀴즈 엑셀 업로드';
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php');?>
    <div id="content">
        <h3 class="page_title">퀴즈 엑셀 업로드</h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/support/quiz_upload.php'; ?>">퀴즈 엑셀 업로드</a></div>
<?php if($type == 'upload'){ ?>
<form name="frm_quizupload" id="frm_quizupload" class="search_area table-search-option" action="<?php echo $CFG->wwwroot . '/local/lmsdata/quizupload/quiz.excel.php'; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $service->id; ?>"/>
    <input type="file" name="quizupload" size="50"/>
    <input type="button"  class="blue_btn" value="등록하기" onclick="upload_quiz();"/>
    <div><a href="<?php echo $CFG->wwwroot; ?>/local/lmsdata/quizupload/quizupload_sample.xlsx" >[샘플 엑셀 양식]</a> 퀴즈를 등록전 카테고리명과 매칭되는 강좌가 있어야 정상적으로 퀴즈가 등록됩니다.</div>
</form>
<?php } else if($type == 'down'){ ?>
<div>
    <h2>퀴즈 다운로드</h2>
    <input type="text" name="idnumber" placeholder="강의코드를 입력"/>
    <input type="button" class="blue_btn" onclick="download_quiz()" value="다운로드"/>
</div>
<?php } ?>


    </div>
</div>


<script type="text/javascript">
    function upload_quiz() {
        if($.trim($("input[name='quizupload']").val()) != '') {
             var filename = $.trim($("input[name='quizupload']").val());
             var extension = filename.replace(/^.*\./, ''); 
             if(extension == filename) {
                 extension = "";
             } else {
                 extension = extension.toLowerCase();
             }
             
             if($.inArray( extension, [ "xlsx" ] ) == -1) {
                 alert("엑셀(.xlsx)만 가능합니다.");
                 return false;
             } else {
                $("#frm_quizupload").submit();
             }
        }
    }
    
    function download_quiz() {
        var idnumber = $("input[name='idnumber']").val();
        idnumber = $.trim(idnumber);
        if(idnumber != ''){
            var url = "./quizdownload.php?idnumber="+idnumber;
             document.location.href = url;
        }
    }
</script>