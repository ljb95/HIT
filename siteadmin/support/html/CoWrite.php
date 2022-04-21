<?php
global $DB,$smenu,$locat;

if($locat=='write'){

    $amode = 'reg_con';
    
    $sel1 = $_REQUEST['sel1'];
    $sel2 = $_REQUEST['sel2'];
    $sel3 = $_REQUEST['sel3'];
    $type = $_REQUEST['type'];
    $name = $_REQUEST['name'];
    $teacher = $_REQUEST['teacher'];
    $author = $_REQUEST['author'];
    $cctype = $_REQUEST['cctype'];
    if($cctype=='2') $ccmark = trim($_REQUEST['ccmark']);
    else if($cctype=='3') $ccmark = trim($_REQUEST['cctext']);
    $share = $_REQUEST['share'];
    $des = $_REQUEST['des'];
	$logo = $_REQUEST['logo'];
    
    if(!$type) $type = 'Video';
    
}

//콘텐츠 상세보기
if($locat=='edit'){

    $seq = $_REQUEST['seq'];
    $amode = 'edit_con';

    if(!$seq){
        err_msq('정보가 정확하지 않습니다.');
    }else{
        $con = $DB->getOneContents($seq);
    
        $seq = $con['CON_SEQ'];
        $area = $con['AREA_NAME'];
        $major = $con['MAJOR_NAME'];
        $course = $con['COURSE_NAME'];
        $type = $con['CON_TYPE'];
        $name = $con['CON_NAME'];
        $teacher = $con['TEACHER'];
        $author = $con['AUTHOR'];
        $cctype = $con['CC_TYPE'];
        $ccmark = trim($con['CC_MARK']);
        $share = $con['SHARE_YN'];
        $des = $con['CON_DES'];
        $logo = $con['LOGO_YN'];
        

        if($type=='Video') $typetxt =  get_string('contents_video', 'local_lmsdata');
        else if($type=='Embed') $typetxt = 'EMBED';
        else if($type=='Flash') $typetxt = '패키지';
    
    }
    
}

//$name = htmlspecialchars_decode($name);

?>
<?php if($locat=='write'){?>
<nav class="lc_tab clearfix">
<h1 class="hx_hide">콘텐츠 유형 링크 탭</h1>
<ul>
<li><a href="#Video" id="tab_Video" class="tab_round tab_sel" onclick="loadConForm('write','Video');"><?php echo get_string('contents_video', 'local_lmsdata'); ?></a><li>
<li><a href="#Embed" id="tab_Embed" class="tab_round" onclick="loadConForm('write','Embed');">EMBED</a><li>
<li><a href="#Pack" id="tab_Flash" class="tab_round" onclick="loadConForm('write','Flash');">패키지</a><li>
</ul>
<div class="box_right"><span class="mark_star">*</span> 표시는 필수입력항목입니다.</div>
</nav>
<?php }else{?>
<div class="title_area">
    <h1>[<?php echo $typetxt?>] <b><?php echo $name?></b></h1>
    <div class="top_right_btn">
        <a href="javascript:loadInfoPopup('./html/ScList.php','학습파일 상세보기',<?php echo $seq;?>);" class="btn_s02">학습파일</a>
        &nbsp;<a href="javascript:loadInfoPopup('../viewer/join.php','콘텐츠 뷰어',<?php echo $seq;?>);" class="btn_s02">미리보기</a>
        &nbsp;<a href="javascript:deleteContents(<?php echo $seq;?>);" class="btn_s02"><?php echo get_string('deletes', 'local_lmsdata'); ?></a>
    </div>
</div>
<?php }?>
<section id="lc_contents">
<!--콘텐츠등록-->
<form method="post" name="update_form" enctype="multipart/form-data">

<input type="hidden" name="amode" value="<?php echo $amode?>"/>
<input type="hidden" name="locat" value="<?php echo $locat?>"/>
<input type="hidden" name="con_seq" value="<?php echo $seq;?>"/>
<input type="hidden" name="con_type" value="<?php echo $type;?>"/>
<input type="hidden" name="sel1"/>
<input type="hidden" name="sel2"/>
<input type="hidden" name="sel3"/>

<table border="1" class="write_form">
<caption>공통 콘텐츠 등록 영역</caption>
<tr>
<th><?php echo get_string('stats_classification', 'local_lmsdata'); ?> <span class="mark_star">*</span></th>
<td>
<?php if($locat=='write'){?>
<select name="area_cd"><option value="">1차분류</option></select>
<select name="major_cd"><option value="">2차분류</option></select>
<select name="course_cd"><option value="">3차분류</option></select>
<?php }else{?>
<?php echo $area.' -> '.$major.' -> '.$course;?>
<?php }?>
</td>
</tr>
<tr>
<th><?php echo get_string('contents_contentname', 'local_lmsdata'); ?> <span class="mark_star">*</span></th>
<td>
<input type="text" name="con_name" size="60" maxlength="60" required value="<?php echo $name;?>" /> <span class="gray_text">* 영문 60자까지 입력 가능</span>
</td>
</tr>
<tr>
<th><?php echo get_string('contents_lecturer', 'local_lmsdata'); ?> </th>
<td>
<input type="text" name="teacher" size="30" maxlength="30" value="<?php echo $teacher;?>"/> <span class="gray_text">* 영문 30자까지 입력 가능</span>
</td>
</tr>
<tr>
<th><?php echo get_string('contents_showauthor', 'local_lmsdata'); ?> <span class="mark_star">*</span></th>
<td> 
<input type="radio" name="cc_type" value="1" checked/> <?php echo get_string('contents_nocopyright', 'local_lmsdata'); ?>
<input type="radio" name="cc_type" value="2" /> <?php echo get_string('contents_creativecommons', 'local_lmsdata'); ?>
<input type="radio" name="cc_type" value="3" /> <?php echo get_string('contents_directinput', 'local_lmsdata'); ?>
<script>optionSelect('radio','cc_type','<?php echo $cctype?>');</script>
<div id="cc_type1" class="box_gray">
- <?php echo get_string('contents_nocopyright', 'local_lmsdata'); ?>
</div>
<div id="cc_type2" class="box_gray display_none">
<?php echo get_string('contents_writer', 'local_lmsdata'); ?> : 
<input type="text" name="author" size="30" maxlength="30" value="<?php echo $author?>"/> <span class="gray_text"> * <?php echo get_string('contents_30characters', 'local_lmsdata'); ?></span><br/>
<select name="cc_mark">
    <option value="CC BY"><?php echo get_string('contents_copyright1', 'local_lmsdata'); ?> </option>
    <option value="CC BY-NC"><?php echo get_string('contents_copyright2', 'local_lmsdata'); ?> </option>
    <option value="CC BY-ND"><?php echo get_string('contents_copyright3', 'local_lmsdata'); ?> </option>
    <option value="CC BY-SA"><?php echo get_string('contents_copyright4', 'local_lmsdata'); ?> </option>
    <option value="CC BY-NC-SA"><?php echo get_string('contents_copyright5', 'local_lmsdata'); ?> </option>
    <option value="CC BY-NC-ND"><?php echo get_string('contents_copyright6', 'local_lmsdata'); ?> </option>
</select>
<script>optionSelect('select','cc_mark','<?php echo $ccmark?>');</script>
</div>
<div id="cc_type3" class="box_gray display_none">
    <input type="text" name="cc_text" size="70" maxlength="70" placeholder="카피라이트를 입력하세요." value="<?php echo $ccmark?>"/>
</div>

</td>
</tr>
<tr>
<th>로고표시 <span class="mark_star">*</span></th>
<td>
<input type="radio" name="logo_yn" value="N" checked/> 표시안함
<input type="radio" name="logo_yn" value="Y"/> 표시함
<script>optionSelect('radio','logo_yn','<?php echo $logo?>');</script>
</td>
</tr>
<tr>
<th><?php echo get_string('contents_visibility', 'local_lmsdata'); ?> <span class="mark_star">*</span></th>
<td>
<input type="radio" name="share_yn" value="1" checked/> LMS
<input type="radio" name="share_yn" value="2"/> OCW
<input type="radio" name="share_yn" value="3"/> <?php echo get_string('contents_open', 'local_lmsdata'); ?>
<input type="radio" name="share_yn" value="N"/> <?php echo get_string('contents_private', 'local_lmsdata'); ?>
<script>optionSelect('radio','share_yn','<?php echo $share?>');</script>
</td>
</tr>
<tr>
<th>콘텐츠설명</th>
<td>
<textarea name="con_des" rows="7"><?php echo $des;?></textarea>
</td>
</tr>
</table>

<br/>
<?php if($locat=='write'){?>
<div id="con_form"></div>
<script type="text/javascript">
    loadConForm('write','<?php echo $type;?>');
</script>
<?php }else{?>
<?php include_once('./html/CoEdit.php');?>
<?php }?>

<div class="box_center">
<?php if($locat=='write'){?>
<input type="button" id="btnContinue" value="연속등록하기" onclick="updateForm($('form[name=update_form]'),'<?php echo $locat;?>',1);"/>
<input type="button" value="완료하기" onclick="updateForm($('form[name=update_form]'),'<?php echo $locat;?>');"/>
<?php }else{?>
<input type="button" value="<?php echo get_string('updates', 'local_lmsdata'); ?>" onclick="updateForm($('form[name=update_form]'),'<?php echo $locat;?>');"/>
<?php }?>
<input type="button" id="btnReset" value="초기화" onclick="resetForm($('form[name=update_form]'),'<?php echo $locat;?>');"/>
</div>

</form>
</section>
