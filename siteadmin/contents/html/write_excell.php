<!-- 문서 폼 시작 -->
<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

?>
<table border="1" class="write_form">
    <caption>공통 콘텐츠 등록 영역</caption>
    <?php 
    
    if (!empty($_REQUEST)) {
        foreach ($_REQUEST as $key => $val) {
            ${$key} = $val;
        }
    }
?>
    <tr>
        <td scope="col"> 
            <input type="radio" title="radio" name="con_type" value="video" checked/> <?php echo get_string('contents_video', 'local_lmsdata'); ?>
            <input type="radio" title="radio" name="con_type" value="embed" checked/> <?php echo get_string('contents_embed', 'local_lmsdata'); ?>
            <div id="file_type1" class="box_gray">
	        <iframe src="./contents_upload/index.html" frameborder="0" id="frame1" title="upload" class="upload_frame" scrolling="auto"></iframe>
                <br/>
                - [<a href="./sample.zip" alt="sample" title="15 MB"><?php echo get_string('contents_samplefile', 'local_lmsdata'); ?></a>]<?php echo get_string('contents_sampleprecautions', 'local_lmsdata'); ?>
                <br/>
                - <?php echo get_string('contents_fileprecautions', 'local_lmsdata'); ?>
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