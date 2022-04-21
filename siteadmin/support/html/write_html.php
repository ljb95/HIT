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

    if(!$seq){?>
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
                <input type="text" title="text" name="file_dir" size="30" placeholder="폴더명을 입력하세요."/><br/>
                - //스토리지경로/ 다음의 폴더명을 입력해주세요.<br/>
                - 폴더구조는 상위폴더와 하위폴더를 /(슬러쉬)로 구분하여 입력해주세요. 
                  단, 앞뒤에 /(슬러쉬)는 생략해주셔야 합니다. 예)folder1/folder2
            </div>
        </td>
    </tr>
    <?php }else{

        $query = "select DATA_DIR from {lcms_contents} where ID = :seq";
        $bind = array('seq'=>$seq);
        $dataaddr = $DB->get_records($query,$bind);
        $datadir = $CFG->serviceroot.'/'.$dataaddr;
        $datas = 0;

        if(sizeof($datas)<3){
            $data_list .= '<tr><td colspan="2">등록된 파일이 없거나 인코딩 중입니다.</td></tr>';
        }else{
            foreach($datas as $key=>$val){
                if($key=='dir'){
                    foreach($datas['dir'] as $dir){
                        $data_list .= '<tr><td colspan="2"><img src="../images/icon_folder.png"/> '.$dir.'</td></tr>';
                    }
                }
        
                if ($key == 'file') {
                    foreach ($datas['file'] as $file) {
                        $filedir = $datadir . '/' . $file;
                        $path = pathinfo($filedir);

                        $filenm = $path['filename'];
                        $filenms = explode('_', $filenm);
                        $k = sizeof($filenms) - 1;

                        $ext = strtolower($path['extension']);
                        $data_list .= '<tr><td>' . $file . '</td></tr>';

                    }
                }
            }
        }
        ?>
    <tr>
        <th>파일목록 <span class="mark_star">*</span></th>
        <td>
            <h4>- 콘텐츠파일목록 (파일위치 : <?php echo $dataaddr;?>) 
            <input type="checkbox" value="1" name="file_change" onclick="change_file();"/> 전체변경</h4>
            <div id="file_area" class="box_gray display_none">
            <input type="radio" title="radio" name="file_type" value="1" checked/> 단일파일
            <input type="radio" title="radio" name="file_type" value="2"/> 파일경로입력
            <div id="file_type1" class="box_gray">
	        <iframe src="../contents_upload/index.html" frameborder="0" id="frame1" title="upload"  class="upload_frame" scrolling="auto"></iframe>
                <br/>
                - <?php echo get_string('contents_multipledocuments', 'local_lmsdata'); ?>
	    </div>
            <div id="file_type2" class="box_gray display_none">
                <?php echo get_string('contents_filepath', 'local_lmsdata'); ?> : //스토리지경로/
                <input type="text" name="file_dir" title="text" size="30" placeholder="폴더명을 입력하세요."/><br/>
                - //스토리지경로/ 다음의 폴더명을 입력해주세요.<br/>
                - 폴더구조는 상위폴더와 하위폴더를 /(슬러쉬)로 구분하여 입력해주세요. 
                  단, 앞뒤에 /(슬러쉬)는 생략해주셔야 합니다. 예)folder1/folder2
            </div>
            </div>
            <table width="100%">
                <tr>
                    <th>파일명</th>
                </tr>
                <?php echo $data_list;?>   
            </table>
        </td>
    </tr>
    <?php }?>
</table>
<!-- 문서 폼 끝 -->