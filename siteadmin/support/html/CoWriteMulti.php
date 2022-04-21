<?php
global $DB,$smenu,$locat;

$amode = 'reg_con_multi';

?>
<section id="lc_contents">
<p>다음 콘텐츠업로드는 올리시려는 콘텐츠 정보가 다를 경우 사용합니다. 
<br/>콘텐츠 정보를 위해 info.xlsx 파일을 꼭 포함하셔야 합니다. 템플릿을 다운로드하여 작성하세요.
<input type="button" value="템플릿다운로드" onclick="location.href='templete_down.php';"/>
</p>
<!-- 비디오폼 시작 -->
<form method="post" name="update_form" enctype="multipart/form-data">
<input type="hidden" name="amode" value="<?php echo $amode?>"/>
<input type="hidden" name="locat" value="<?php echo $locat?>"/>
<input type="hidden" name="sel1"/>
<input type="hidden" name="sel2"/>
<input type="hidden" name="sel3"/>
<table border="1" class="write_form">
<caption>일괄 콘텐츠 등록 영역</caption>
<tr>
<th>유형 <span class="mark_star">*</span></th>
<td>
<input type="radio" name="con_type" value="Video" checked/> <?php echo get_string('contents_video', 'local_lmsdata'); ?>
<input type="radio" name="con_type" value="Embed"/> 임베디드
<!--<input type="radio" name="con_type" value="Flash"/> 패키지-->
</td>
</tr>
<tr>
<th><?php echo get_string('stats_classification', 'local_lmsdata'); ?> <span class="mark_star">*</span></th>
<td>
<select name="area_cd"><option value="">1차분류</option></select>
<select name="major_cd"><option value="">2차분류</option></select>
<select name="course_cd"><option value="">3차분류</option></select>
</td>
</tr>
<tr>
<th><?php echo get_string('contents_filebatchregistration', 'local_lmsdata'); ?> <span class="mark_star">*</span></th>
<td>
<iframe src="../contents_upload/index.html" frameborder="0" id="frame1" class="upload_frame" scrolling="auto"></iframe>
<p class="box_gray type_txt" id="Video_txt">
- 동영상 파일 일괄 등록은 캡쳐,미리보기 등 자동생성시에만 적용됩니다.<br/>
- 동영상 파일 일괄 등록은 기본자막 한글, 기본영상 고화질로 자동 설정됩니다.<br/>
- 파일정보(info.xlsx)를 포함해야 합니다.<br/>
- 파일명에 언더바(_)를 포함하지 않도록 주의합니다.<br/>
- 파일명 규칙은 콘텐츠 동영상 등록과 동일합니다.<br/>
- info.xlsx 컬럼: 콘텐츠명,설명,강의자,저작자,저작권표시,총학습시간,파일명(mp4,flv)
</p>
<p class="box_gray type_txt" id="Embed_txt" style="display:none;">
- Embed 정보(info.xlsx)를 포함해야 합니다.<br/>
- youtube 일괄 등록시 자막 파일은 자동생성됩니다.<br/>
- info.xlsx 컬럼: 콘텐츠명,설명,강의자,저작자,저작권표시,Embed타입(youtube/vimeo),Embed주소
</p>
<p class="box_gray type_txt" id="Flash_txt" style="display:none;">
- 패키지 정보(info.xlsx)를 포함해야 합니다.<br/>
- 패키지 파일은 등록할 패키지 개수만큼 zip으로 압축하여 등록합니다.<br/>
- 파일명 규칙은 콘텐츠 패키지 등록과 동일합니다.<br/>
- info.xlsx 컬럼: 콘텐츠명,설명,강의자,저작자,저작권표시,총학습시간,파일명(zip)
</p>
</div>
</td>
</tr>
</table>
<!-- 비디오폼 끝 -->
<div class="box_center">
<input type="button" value="완료하기" onclick="updateFormMulti($('form[name=update_form]'),'<?php echo $locat;?>');"/>
</div>
</form>
</section>
<script type="text/javascript">
    $(function(){
        
       $('input[name=con_type]').click(function(){
           var type = $('input[name=con_type]:checked').val();
           $('.type_txt').css('display','none');
           $('#'+type+'_txt').css('display','block');
       });
        
        
    });
</script>