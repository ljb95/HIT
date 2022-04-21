<?php 

global $CFG,$con,$DB,$seq;

$data_list = '';
$vod_list = '';

list($con_total_time1,$con_total_time2) = explode(':',$con['CON_TOTAL_TIME']);

if($con['CON_TYPE']!='Embed'){

$query = "select DATA_DIR from LCMS_DATA where CON_SEQ = :b1";
$DB->setBind($seq);
$dataaddr = $DB->selectOne($query);

if($con['CON_TYPE']=='Video') $datadir = $CFG->serviceroot.'/'.$dataaddr;
else $datadir = $CFG->packageroot.'/'.$dataaddr;

$storage = $DB->getStorage($con['COURSE_CD']);


$dir_size = get_dir_size($datadir);
$datas = $DB->listData($seq,0);

if(sizeof($datas)<3){
    $data_list .= '<tr><td colspan="2">등록된 파일이 없거나 인코딩 중입니다.</td></tr>';
}else{
    foreach($datas as $key=>$val){
        if($key=='dir'){
            foreach($datas['dir'] as $dir){
                $data_list .= '<tr><td colspan="2"><img src="../img/icon_folder.png"/> '.$dir.'</td></tr>';
            }
        }
        
        if($key=='file'){
            foreach($datas['file'] as $file){
                $filedir = $datadir.'/'.$file;
                $path = pathinfo($filedir);
                
                $filenm = $path['filename'];
                $filenms = explode('_',$filenm);
                $k = sizeof($filenms)-1;
                
                $ext = strtolower($path['extension']);
                
                $data_list .= '<tr><td>'.$file.'</td><td>';
                $data_list .= ($con['CON_TYPE']=='Video'&&$ext!='mp4')? '<input type="button" value="변경" onclick="load_upload_form('.$seq.',\''.$datadir.'\',\''.$file.'\');"/>':'-';
                $data_list .= '</td></tr>';
                
                if($con['CON_TYPE']=='Video'&&$ext=='mp4'&&$filenms[$k]!='p'){
                    $streaming = $storage.'.'.$CFG->streaming;
                    $rtmp = 'rtmp://'.$streaming.'/vod/_definst_/mp4:'.$dataaddr.'/'.$file;
                    $rtsp = 'rtsp://'.$streaming.':1935/vod/_definst_/mp4:'.$dataaddr.'/'.$file;
                    $http = 'http://'.$streaming.':1935/vod/_definst_/mp4:'.$dataaddr.'/'.$file.'/playlist.m3u8';
                    $vod_list .= '<p style="border-bottom:1px dashed #ccc;">'.$rtmp.'<br/>'.$rtsp.'<br/>'.$http.'</p>';
                    
                }
                
            }
        }
    }
}


}else{
    
    $vids = explode('/', $con['EMBED_CODE']);
    $vid1 = trim(str_replace("watch?v=", "", $vids[sizeof($vids) - 1])); 
    $vid1s = explode('&', $vid1);
    $vid = trim($vid1s[0]);
    
    $srt_ko = $CFG->captionroot.'/'.$con['EMBED_TYPE'].'/'.$seq.'/'.$seq.'-'.$vid.'_ko.srt';
    $srt_en = $CFG->captionroot.'/'.$con['EMBED_TYPE'].'/'.$seq.'/'.$seq.'-'.$vid.'_en.srt';
    
    if(file_exists($srt_ko)) $data_list .= '<li id="#caption_ko">[한글자막] '.$seq.'-'.$vid.'_ko.srt <a href="javascript:file_delete(\''.$srt_ko.'\',\'ko\');">[x]</a></li>';
    if(file_exists($srt_en)) $data_list .= '<li id="#caption_en">[영문자막] '.$seq.'-'.$vid.'_en.srt <a href="javascript:file_delete(\''.$srt_en.'\',\'en\');">[x]</a></li>';
    
}

if($con['CON_TYPE']=='Video'){
    
    $caption_list = '';
    $srt_ko = $CFG->captionroot.'/'.$storage.'/'.$seq.'/'.$seq.'_ko.srt';
    $srt_en = $CFG->captionroot.'/'.$storage.'/'.$seq.'/'.$seq.'_en.srt';
    
    $caption_ko = (file_exists($srt_ko))? '[한글자막] '.$seq.'_ko.srt <a href="javascript:file_delete(\''.$srt_ko.'\',\'ko\');">[x]</a>':'-';
    $caption_en = (file_exists($srt_en))?'[영문자막] '.$seq.'_en.srt <a href="javascript:file_delete(\''.$srt_en.'\',\'en\');">[x]</a>':'-';
?>

<!-- 비디오폼 시작 -->
<table border="1" class="write_form">
<caption>비디오 콘텐츠 등록 영역</caption>
<tr>
<th>기본화질 </th>
<td>
<input type="radio" name="quality" value="1" checked/> 고화질
<input type="radio" name="quality" value="2"/> 저화질
<script>optionSelect('radio','quality','<?php echo $con['QUALITY'];?>');</script>
</td>
</tr>
<tr>
<th>기본자막 </th>
<td>
<input type="radio" name="caption" value="ko" checked/> 한국어
<input type="radio" name="caption" value="en"/> 영어
<script>optionSelect('radio','caption','<?php echo $con['CAPTION']?>');</script>
</td>
</tr>
<tr>
<th>총학습시간</th>
<td>
    <input type="text" name="con_total_time1" value="<?php echo $con_total_time1;?>" size="5" maxlength="3" onkeyup="onlynumber(this);"/> 
    :
    <input type="text" name="con_total_time2" value="<?php echo $con_total_time2;?>" size="5" maxlength="2" onkeyup="onlynumber(this);"/> 
    - [분:초]로 표기 (예시 - 105:00)
</td>
</tr>
<tr>
<th>VOD경로</th>
<td>
    - 화질별 VOD파일명은 파일명 뒤에 고화질:_h,저화질:_l,모바일:_m 로 구분합니다.<br/>
    <?php echo $vod_list;?>
</td>
</tr>
<tr>
<th>파일목록 <span class="mark_star">*</span><br/>(<?php echo $dir_size;?>)</th>
<td>
<table width="100%">
    <tr>
        <th style="width:10%;" rowspan="2">자막추가<br/>(or 변경)</th>
        <td>영문: <input type="file" name="caption_file_en"/></td>
        <td id="caption_en"><?php echo $caption_en;?></td>
    </tr>
    <tr>
        <td>한글: <input type="file" name="caption_file_ko"/></td>
        <td id="caption_ko"><?php echo $caption_ko;?></td>
    </tr> 
</table>
<h4>- 콘텐츠파일목록 (파일위치 : <?php echo $dataaddr;?>) 
<input type="checkbox" value="1" name="file_change" onclick="change_file();"/> 전체변경</h4>
<div id="file_area" class="display_none">
<input type="radio" name="file_type" value="1" checked onclick="change_filetype(1);"/> 단일파일
<input type="radio" name="file_type" value="2" onclick="change_filetype(2);"/> 파일경로입력
<div id="file_type1" class="box_gray">
<div>
    - 캡쳐: 
    <input type="radio" name="capture" value="1" checked/> 원본에서 자동생성
    &nbsp;&nbsp;
    - 맛보기:
    <input type="radio" name="preview" value="1" checked/> 원본에서 자동생성
</div>
<iframe src="../contents_upload/index.html" frameborder="0" id="frame1" class="upload_frame" scrolling="auto"></iframe>
<p>
- 등록가능파일형식 : mp4,flv,wmv<br/>
- 파일명에 _는 생략합니다. 
</p>
</div>
<div id="file_type2" class="box_gray display_none">
<?php echo get_string('contents_filepath', 'local_lmsdata'); ?> : //172.16.100.194/vod/content/<input type="text" name="file_dir" size="30" placeholder="폴더명을 입력하세요."/><br/>
- //172.16.100.194/vod/content/ 다음의 폴더명을 입력해주세요.<br/>
- 폴더구조는 상위폴더와 하위폴더를 /(슬러쉬)로 구분하여 입력해주세요. 
단, 앞뒤에 /(슬러쉬)는 생략해주세요. 예)folder1/folder2
</div>
</div>
<table width="100%">
    <tr>
        <th style="width:90%;">파일명</th>
        <th>변경</th>
    </tr>
<?php echo $data_list;?>   
</table>
</td>
</tr>
</table>
<!-- 비디오폼 끝 -->
<?php }else if($con['CON_TYPE']=='Embed'){?>

<!-- Embed 폼 시작 -->
<table border="1" class="write_form">
    <caption>임베디드 콘텐츠 등록 영역</caption>
    <tr>
        <th>Embed유형 <span class="mark_star">*</span></th>
        <td>
            <input type="radio" name="emb_type" value="youtube" checked/> Youtube
            <input type="radio" name="emb_type" value="vimeo"/> Vimeo
            <script>optionSelect('radio','emb_type','<?php echo $con['EMBED_TYPE'];?>');</script>
            
        </td>
    </tr>
    <tr>
        <th>Embed 주소</th>
        <td>
            <input name="emb_code" size="50" value="<?php echo $con['EMBED_CODE'];?>"/> 
            <br/>유튜브 : 예1) http://www.youtube.com/watch?v=9tz4ToEQ_jw
            <br/>예2) http://youtu.be/9tz4ToEQ_jw
            <br/>비메오 : 예) http://vimeo.com/82299487
        </td>
    </tr>
    <tr>
        <th>자막목록 <span class="mark_star">*</span><br/><input type="checkbox" value="1" name="caption_change" onclick="change_caption_file();"/> 파일변경</th>
        <td>
            <div id="file_area display_none">
                <input type="radio" name="caption_type" value="1" checked onclick="change_caption(1);"/> 자동으로가져오기
                <input type="radio" name="caption_type" value="2" onclick="change_caption(2);"/> 직접등록
                <div id="caption_file" class="box_gray display_none">
                    한글 : <input type="file" name="caption_file_ko"/><br/>
                    영문 : <input type="file" name="caption_file_en"/>
                </div>
            </div>
            <ul><?php echo $data_list;?></ul> 
        </td>
    </tr>
    <tr>
        <th>기본자막 <span class="mark_star">*</span></th>
        <td>
            <input type="radio" name="caption" value="ko" checked/> 한국어
            <input type="radio" name="caption" value="en"/> 영어
            <script>optionSelect('radio','caption','<?php echo $con['CAPTION'];?>');</script>
        </td>
    </tr>
    <tr>
<th>총학습시간</th>
<td>
    <input type="text" name="con_total_time1" value="<?php echo $con_total_time1;?>" size="5" maxlength="3" readonly/> 
    :
    <input type="text" name="con_total_time2" value="<?php echo $con_total_time2;?>" size="5" maxlength="2" readonly/> 
</td>
</tr>
</table>
<!-- Embed 폼 끝 -->

<?php }else{?>

<!-- 플래시 폼 시작 -->
<table border="1" class="write_form">
    <caption>공통 콘텐츠 등록 영역</caption>
    <tr>
        <th>총학습페이지</th>
        <td>
        <input type="text" name="con_total_time2" value="<?php echo $con_total_time2;?>" size="5" maxlength="2" onkeyup="onlynumber(this);"/> 
        - 자동으로 값을 가져올 수 없습니다. 꼭 입력하셔야 진도반영이 가능합니다.
    </td>
    </tr>
    <tr>
        <th>파일목록 <span class="mark_star">*</span><br/>(<?php echo $dir_size;?>)</th>
        <td>
            <h4>- 콘텐츠파일목록 (파일위치 : <?php echo $dataaddr;?>) 
            <input type="checkbox" value="1" name="file_change" onclick="change_file();"/> 전체변경</h4>
            <div id="file_area" class="box_gray display_none">
            <input type="radio" name="file_type" value="1" checked onclick="change_filetype(1);"/> 단일파일
            <input type="radio" name="file_type" value="2" onclick="change_filetype(2);"/> 파일경로입력
            <div id="file_type1" class="box_gray">
	        <iframe src="../contents_upload/index.html" frameborder="0" id="frame1" class="upload_frame" scrolling="auto"></iframe>
                - zip파일을 등록하실 수 있습니다.<br/>
                - zip 파일 내부 최상위에는 index.html 또는 obcmanifest.xml 을 포함하셔야 합니다.<br/>
                - zip 파일 내부 최상위에는 capture.png 파일을 포함하셔야 리스트에 이미지가 보여집니다.
	    </div>
            <div id="file_type2" class="box_gray display_none">
                <?php echo get_string('contents_filepath', 'local_lmsdata'); ?> : //172.16.100.194/vod/content/
                <input type="text" name="file_dir" size="30" placeholder="폴더명을 입력하세요."/><br/>
                - //172.16.100.194/vod/content/ 다음의 폴더명을 입력해주세요.<br/>
                - 폴더구조는 상위폴더와 하위폴더를 /(슬러쉬)로 구분하여 입력해주세요. 
                  단, 앞뒤에 /(슬러쉬)는 생략해주셔야 합니다. 예)folder1/folder2
            </div>
            </div>
            <table width="100%">
                <tr>
                    <th>파일명</th>
                    <th style="width:10%;">변경</th>
                </tr>
                <?php echo $data_list;?>   
            </table>
        </td>
    </tr>
</table>
<!-- 플래시 폼 끝 -->

<?php }?>

<script type="text/javascript">
    
    function change_file(){
        if($('input[name=file_change]:checked').val()=='1'){
            $('#file_area').removeClass('display_none');
        }else{
            $('#file_area').addClass('display_none');
        }
    }
    
    function change_caption_file(){
        if($('input[name=caption_change]:checked').val()=='1'){
            $('#file_area').removeClass('display_none');
        }else{
            $('#file_area').addClass('display_none');
        }
    }
    
    function change_filetype(n){
        $('#file_type1').addClass('display_none');
        $('#file_type2').addClass('display_none');
        $('#file_type'+n).removeClass('display_none');
    }
    
    function change_caption(n){
        if(n==2){
           $('#caption_file').removeClass('display_none'); 
        }else{
           $('#caption_file').addClass('display_none'); 
        }
    }
    
    function load_upload_form(id,filedir,filename){
        
        var tag = $("<div></div>");
        $.ajax({
            url: 'html/DataUploadForm.php',
            data: {
                id: id,
                dir: filedir,
                name: filename
            },
            success: function(data) {
                
                $('body').css({'overflow':'hidden'});
                
                tag.html(data).dialog({
                    title: '파일변경',
                    modal: true,
                    width: 670,
                    height: 300,
                    buttons: [{
                            id:'save',
                            text:'<?php echo get_string('contents_upload', 'local_lmsdata'); ?>', 
                            disable: true,
                            click: function() {
                                var status = submit_upload_form();
                                if(status){
                                    $('body').css({'overflow':'auto'});
                                    $('#load_form').remove();
                                    $( this ).dialog( "close" );
                                }
                            }},{
                            id:'close',
                            text:'닫기', 
                            disable: true,
                            click: function() {
                                $('body').css({'overflow':'auto'});
                                $('#load_form').remove();
                                $( this ).dialog( "close" );
                            }
                        }]
                }).dialog('open');
                
                $(".ui-dialog-titlebar-close").click(function(){
                    $('body').css({'overflow':'auto'});
                    $('#load_form').remove();
                });
                
            }
            
        });
    }
    
    function submit_upload_form(){
        
        var frm = $('form[name=upload_form]');
        
        if(frm.find('input[name=extname]').val()!=frm.find('input[name=file]').val()){
            alert('<?php echo get_string('contents_alert1', 'local_lmsdata'); ?>');
            return false;
        }
        
        var status = true;
        
        frm.ajaxSubmit({
            type: 'POST',
            url:'action.php',  
            data: frm.serialize(), 
            async: false,
            cache: false,
            error:function(xhr,status,e){  
                //alert('Error');
            },
            beforeSend:function(){
            },
            success: function(result){
                if(result==1){
                    alert('<?php echo get_string('contents_alert1', 'local_lmsdata'); ?>');
                    status = false;
                }else if(result==2){
                    alert('<?php echo get_string('contents_alert2', 'local_lmsdata'); ?>');
                    status = false;
                }else if(result==3){
                    alert('<?php echo get_string('contents_alert3', 'local_lmsdata'); ?>');
                    status = true;
                }
            }
        });
        
        return status;
            
    }
    
    function file_delete(filedir,lang){
        var conf = confirm('<?php echo get_string('contents_alert4', 'local_lmsdata'); ?>');
        
        if(conf){
            $.ajax({
            type: 'POST',
            url: 'action.php',
            data: {
                amode: 'file_delete',
                dir: filedir
            },
            success: function(data) {
                if(data==1){
                    alert('<?php echo get_string('contents_alert5', 'local_lmsdata'); ?>'); 
                    $('#caption_'+lang).empty().append('-');
                }
                else{
                    alert(data);
                }
            }
        });
        }
    }
</script>