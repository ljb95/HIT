<!-- 문서 폼 시작 -->
<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . "/lib/filelib.php");
?>
<script>
    $("input.delete").click(function () {

        var filedir = $(this).attr('data-url');
        var papa = $('#file'+filedir);

        $.ajax({
            url: '<?php echo $CFG->wwwroot; ?>/local/repository/delete_contents.php',
            type: 'POST',
            async: true,
            data: {
                mode: 'cfiledel',
                fileid: filedir
            },
            success: function (data, textStatus, jqXHR) {
                papa.hide();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });

    });
</script>
<table border="1" class="write_form">
    <caption>공통 콘텐츠 등록 영역</caption>
    <?php 
    
    $id = optional_param("con_id", 0, PARAM_INT);
    if($id){
        $query = "select * from {lcms_contents_file} where con_seq = :id";
        $bind = array('id'=>$id);
        $datadirs = $DB->get_records_sql($query,$bind);
            foreach($datadirs as $file){
                    $filename = $file->fileoname;
                    $filepath = explode('/', $file->filepath);
                    if ($filepath[0] == 'lms' || $filepath[0] == 'lcms')
                        $lcmsdata = '/lcmsdata/';
                    else
                        $lcmsdata = '/';
                    $mimetype = mime_content_type(STORAGE2 . $lcmsdata . $file->filepath . '/' . $file->filename);
                    $path = 'viewer/download.php?id=' . $id . '&fileid=' . $file->id;

                    $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
                    $attfile .= '<li id="file'.$file->id.'">';
                    $attfile .= "<a href=\"$path\">$iconimage</a> ";
                    $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a></li>", FORMAT_HTML, array('context' => $context));
                    $attfile .= '<input type="button" class="btn btn-delete delete" data-type="DELETE" data-url="'.$file->id.'" value="'. get_string('delete', 'local_lmsdata') .'">';
                    $attfile .= "</li>";
            }
    }
    echo '<div class="admin_lcmsfiles">'. get_string('contents_existingfile', 'local_lmsdata').$attfile.'</div>';
    if (!empty($_REQUEST)) {
        foreach ($_REQUEST as $key => $val) {
            $$key = $val;
        }
    }
    ?>
    <tr>
        <td scope="col">
            <input type="radio" title="radio" name="file_type" value="1" checked/> <?php echo get_string('contents_uploadfile', 'local_lmsdata'); ?>
            <input type="radio" title="radio" name="file_type" value="2"/> <?php echo get_string('contents_filepath', 'local_lmsdata'); ?>
            <div id="file_type1" class="box_gray">
	        <iframe src="./contents_upload/index.html" frameborder="0" id="frame1" title="upload"  class="upload_frame" scrolling="auto"></iframe>
                <br/>
                - <?php echo get_string('contents_multipledocuments', 'local_lmsdata'); ?>
	    </div>
            <div id="file_type2" class="box_gray display_none">
                <?php echo get_string('contents_filepath', 'local_lmsdata'); ?> : //스토리지경로/
                <input type="text" title="file" name="file_dir" size="30" placeholder="폴더명을 입력하세요."/><br/>
                - //스토리지경로/ 다음의 폴더명을 입력해주세요.<br/>
                - 폴더구조는 상위폴더와 하위폴더를 /(슬러쉬)로 구분하여 입력해주세요. 
                  단, 앞뒤에 /(슬러쉬)는 생략해주셔야 합니다. 예)folder1/folder2
            </div>
        </td>
    </tr>
</table>
<!-- 문서 폼 끝 -->