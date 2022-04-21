<?php 
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

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

echo $OUTPUT->header();
echo $OUTPUT->heading('퀴즈 엑셀 업로드 (선다형만 등록 가능)');

?>
<?php if($type == 'upload'){ ?>
<form name="frm_quizupload" id="frm_quizupload" class="search_area table-search-option" action="<?php echo $CFG->wwwroot . '/local/lmsdata/quizupload/quiz.excel.php'; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $service->id; ?>"/>
    <input type="file" name="quizupload" size="50"/>
    <input type="button"  class="blue_btn" value="등록하기" onclick="upload_quiz();"/>
    <div><a href="<?php echo $CFG->wwwroot; ?>/local/lmsdata/quizupload/quizupload_sample.xlsx" >[샘플 엑셀 양식]</a></div>
</form>
<?php } else if($type == 'down'){ ?>
<div>
    <h2>퀴즈 다운로드</h2>
    <input type="text" name="idnumber" placeholder="강의코드를 입력"/>
    <input type="button" class="blue_btn" onclick="download_quiz()" value="다운로드"/>
</div>
<?php } ?>


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

<?php
echo $OUTPUT->footer();
?>
