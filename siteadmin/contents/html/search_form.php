<?php 
//분류 가져오기
$areas = $DB->get_clas_area();
if($sel1) $majors = $DB->get_clas_major($sel1);
if($sel2) $courses = $DB->get_clas_course($sel2);

?>
<script>
     $(document).ready(function () {
        <?php if($_GET['area_cd'] != ""){ ?>
        select_clas_major($('form[name=search_form]'), $('select[name=area_cd]').val(),'<?php echo $_GET['major_cd']; ?>');
        <?php 
        } 
        if($_GET['major_cd'] != ""){
        ?>
           select_clas_course($('form[name=search_form]'), $('select[name=major_cd]').val(), $('select[name=major_cd]').attr('class'),'<?php echo $_GET['course_cd']; ?>');
        <?php } ?>
    });
</script>
<section class="lc_sub_search">
    <form method="GET" name="search_form">
        <fieldset>
            <p>
                <select name="area_cd" onchange="$('form[name=search_form]').submit();">
                    <option value="">1차분류</option>
                    <?php 
                    foreach($areas as $area){
                        $area_selected = '';
                        if($area['AREA_CD']==$sel1) $area_selected = 'selected';
                        echo '<option value="'.$area['AREA_CD'].'" '.$area_selected.'>'.$area['AREA_NAME'].'</option>';
                    }
                    ?>
                </select>
                <select name="major_cd" onchange="$('form[name=search_form]').submit();">
                    <option value="">2차분류</option>
                    <?php 
                    foreach($majors as $major){
                        $major_selected = '';
                        if($major['MAJOR_CD']==$sel2) $major_selected = 'selected';
                        echo '<option value="'.$major['MAJOR_CD'].'" '.$major_selected.'>'.$major['MAJOR_NAME'].'</option>';
                    }
                    ?>
                </select>
                <select name="course_cd" onchange="$('form[name=search_form]').submit();">
                    <option value="">3차분류</option>
                    <?php 
                    foreach($courses as $course){
                        $course_selected = '';
                        if($course['COURSE_CD']==$sel3) $course_selected = 'selected';
                        echo '<option value="'.$course['COURSE_CD'].'" '.$course_selected.'>'.$course['COURSE_NAME'].'</option>';
                    }
                    ?>
                </select>
            </p>
            <p>            
                <select name="type" onchange="$('form[name=search_form]').submit();">
                    <option value="">콘텐츠종류</option>
                    <option value="video"><?php echo get_string('contents_video', 'local_lmsdata'); ?></option>
                    <option value="html">HTML</option>
                    <option value="embed"><?php echo get_string('contents_externalcontent', 'local_lmsdata'); ?></option>
                    <option value="word"><?php echo get_string('contents_documentothers', 'local_lmsdata'); ?></option>
                </select>
                <script type="text/javascript">option_select('select','type','<?php echo $type;?>');</script>
                <select name="share_yn" onchange="$('form[name=search_form]').submit();">
                    <option value=""><?php echo get_string('contents_visibility', 'local_lmsdata'); ?></option>
                    <option value="L">LMS</option>
                    <option value="M">MOOCs</option>
                    <option value="O">OCX</option>
                </select>
                <script type="text/javascript">option_select('select','share_yn','<?php echo $share_yn;?>');</script>
                <input type="text" name="search" size="30" value="<?php echo $search; ?>"/> 
                <input type="hidden" name="currpage" value="<?php echo $currpage;?>"/>
                <input type="submit" id="btnSearch" value="<?php echo get_string('stats_search', 'local_lmsdata'); ?>" class="blue_btn"/>
                <input type="button" id="resetSearch" value="검색초기화" onclick="location.href='index.php';"/>
            </p>
        </fieldset>
    </form>
</section>