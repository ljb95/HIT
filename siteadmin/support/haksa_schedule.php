<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/haksa_schedule.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$year = optional_param('year', date('Y'), PARAM_INT);
$hyear = optional_param('hyear', 1, PARAM_INT); //학년정보
$univ = optional_param('univ', 2, PARAM_INT); //본과,예과 구분
$mode = optional_param('mode', 0, PARAM_INT); //편집모드상태는 1

$startyear = SYSTEM_START_YEAR; //시스템 시작 년도(siteadmin/lib.php에 선언)
$currentyear = date('Y');

//쿼리 검색 (테이블명: lmsdata_haksa_schedule)

?>
<style>
    .ui-datepicker-trigger {margin-left: 5px; width: 18px;}
</style>
<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_support.php');?>
    
    <div id="content">
        <h3 class="page_title">학사일정</h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > 학사일정</div>
        <form name="" id="schedule_search" class="search_area" action="haksa_schedule.php" method="post">
            <input type="hidden" name="mode" value="<?php echo $mode;?>"/>
            <select name="year" class="w_160" onchange="year_search()">
                <?php 
                for($i=$currentyear;$i>=$startyear;$i--){
                    $selected = ($i==$year)? 'selected':'';
                    echo '<option value="'.$i.'" '.$selected.'>'. get_string('year','local_lmsdata',$i) . '</option>';
                }
                ?>
            </select>
            <select name="univ" class="w_160" onchange="year_search()">
                <option value='2' <?php if($univ == '2') echo 'selected="selected"';?>>본과</option>
                <option value='1' <?php if($univ == '1') echo 'selected="selected"';?>>예과</option>
            </select>
            <select name="hyear" class="w_160" onchange="year_search()">
                <?php 
                for($i=1;$i<=4;$i++){
                    $selected = ($i==$hyear)? 'selected':'';
                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'학년</option>';
                }
                ?>
            </select>
        </form><!--Search Area2 End-->
        <?php 
            if(!$mode){ 
        ?>
        <div id="btn_area">
            <div style="float:right;">
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="일정등록/수정" onclick="edit_haksa_schedule(1);"/> 
            </div>
        </div>
        <table id="haksa_schedule_table" style="margin-top:-30px;">
                <tr>
                    <th width="25%">기간</th>
                    <th>학사내용</th>
                </tr>
            <?php
                $sql = 'SELECT * FROM {lmsdata_haksa_schedule} WHERE year = :year and hyear = :hyear and univ = :univ order by startdate asc';
                $contents = $DB->get_records_sql($sql,array('year'=>$year, 'hyear'=>$hyear, 'univ'=>$univ));
                $num = 0;
                foreach($contents as $content){
            ?>
                <tr>
                    <td><?php echo date('Y-m-d',$content->startdate);?> ~ <?php echo date('Y-m-d',$content->enddate);?></td>
                    <td>
                        <?php echo $content->schedule;?>
                    </td>
                </tr>
            <?php
                $num++;
                }
                if($num == 0){
                    echo "<tr><td colspan='2' align='center'>등록된 일정이 없습니다.</td></tr>";
                }
            ?>
        </table><!--Table End-->
        <?php 
        }else if($mode==1){
        ?>
        <form name="" id="schedule_setting" action="haksa_schedule_submit.php" method="post">
            <div id="btn_area">
                <div style="float:left;" id="addndeletearea">
                    <input type="button" class="gray_btn" id="add_schedule" value="일정추가" onclick="add_haksa_schedule();"/>
                </div>
                <div style="float:right;">
                    <input type="submit" class="blue_btn" style="margin-right: 10px;" value="일정저장" onclick=""/> 
                    <input type="button" class="gray_btn" style="margin-right: 10px;" value="<?php echo get_string('cancle','local_lmsdata'); ?>" onclick="edit_haksa_schedule(0);"/> 
                </div>
            </div>
            <input type="hidden" name="c_hyear" id="c_hyear" value="<?php echo $hyear;?>">
            <input type="hidden" name="c_univ" id="c_univ" value="<?php echo $univ;?>">
            <input type="hidden" name="write" id="write" value="0">
            <input type="hidden" name="modifide" id="modifide" value="0">
            <input type="hidden" name="modi_list">
            <table id="haksa_schedule_table" style="margin-top:-30px;">
                <tr id="title_tr">
                    <th width="25%">기간</th>
                    <th>학사내용</th>
                    <th width="10%"><?php echo get_string('edit','local_lmsdata'); ?></th>
                    <th width="10%"><?php echo get_string('delete', 'local_lmsdata'); ?></th>
                </tr>
                <?php
                    $sql = 'SELECT * FROM {lmsdata_haksa_schedule} WHERE year = :year and hyear = :hyear and univ = :univ order by startdate asc';
                    $contents = $DB->get_records_sql($sql,array('year'=>$year, 'hyear'=>$hyear, 'univ'=>$univ));
                    foreach($contents as $content){
                ?>
                <tr>
                    <td>
                        <input type="text" name="startdate<?php echo $content->id;?>" id="timestart<?php echo $content->id;?>" readonly style="border:0;" class="w_120" value="<?php echo date('Y-m-d',$content->startdate);?>" placeholder="yyyy-mm-dd"/> ~ 
                        <input type="text" name="enddate<?php echo $content->id;?>" id="timeend<?php echo $content->id;?>" readonly style="border:0;"  class="w_120" value="<?php echo date('Y-m-d',$content->enddate); ?>" placeholder="yyyy-mm-dd"/>
                    </td>
                    <td>
                        <input type="text" name="schedule<?php echo $content->id;?>" id="schedule<?php echo $content->id;?>"  readonly style="border:0;" value="<?php echo $content->schedule;?>" class="w_100" />
                    </td>
                    <td>
                        <input type="button" value="<?php echo get_string('edit','local_lmsdata'); ?>" class="gray_btn_small" id="<?php echo 'modify_'.$content->id?>" onclick="modify_schedule(<?php echo $content->id?>)"/>
                    </td>
                    <td>
                        <input type="button" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" class="gray_btn_small" onclick="delete_haksa_schedule('<?php echo $content->id;?>');"/>
                    </td>
                </tr>
                <?php
                    }
                ?>
            </table><!--Table End-->
        </form>
        <?php }?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

<script type="text/javascript">
        $("#schedule_setting").submit(function(){
            var start_val = $('input:text[name="startdate[]"]');
            var start_row = 1;
            var null_start = 0;
            start_val.each(function(){
                if(null_start > 0){
                    return false;
                }
               if($(this).val() == ''){
                   alert(start_row+'번째 줄 시작 날짜를 입력해주세요');
                   null_start = null_start+1;
               }
               start_row = start_row+1;
            });
            if(null_start > 0){
                return false;
            }
            var end_val = $('input:text[name="enddate[]"]');
            var end_row = 1;
            var null_end = 0;
            end_val.each(function(){
                if(null_end > 0){
                    return false;
                }
                if($(this).val() == ''){
                   alert(end_row+'번째 줄 종료 날짜를 입력해주세요');
                   null_end = null_end+1;
                }
               end_row = end_row+1;
            });
            if(null_end > 0){
                return false;
            }
            var schedule_val = $('input:text[name="schedule[]"]');
            var schedule_row = 1;
            var null_schedule = 0;
            schedule_val.each(function(){
                if(null_schedule > 0){
                    return false;
                }
               if($(this).val() == ''){
                   alert(schedule_row+'번째 줄 내용을 입력해주세요');
                   null_schedule = null_schedule+1;
               }
               schedule_row = schedule_row+1;
            });
            if(null_schedule > 0){
                return false;
            }
            if(modify_array.length != 0){
                var null_modify = 0;
                for(var i = 0; i < modify_array.length; i++){
                    var start_val = $('input:text[name="startdate'+modify_array[i]+'"]');
                    if(start_val.val() == ''){
                        alert('빈칸을 채워주세요');
                        null_modify = null_modify+1;
                    }
                    var end_val = $('input:text[name="enddate'+modify_array[i]+'"]');
                    if(end_val.val() == ''){
                        alert('빈칸을 채워주세요');
                        null_modify = null_modify+1;
                    }
                    var schedule_val = $('input:text[name="schedule'+modify_array[i]+'"]');
                    if(schedule_val.val() == ''){
                        alert('빈칸을 채워주세요');
                        null_modify = null_modify+1;
                    }
                }
                if(null_modify > 0){
                    return false;   
                }
            }
        });
    
    function edit_haksa_schedule(mode){
        $('#schedule_search').find('input[name=mode]').val(mode);
        $('#schedule_search').submit();
    }
    var add_span = 0;
    function add_haksa_schedule(){
        var current_date = new Date();
        var next_date = new Date();
        next_date.setDate(next_date.getDate()+7);
        $('#addndeletearea').html('<input type="button" class="gray_btn" id="add_schedule" value="일정추가" onclick="add_haksa_schedule();"/>&nbsp;<input type="button" class="gray_btn" id="add_schedule" value="일정삭제" onclick="del_haksa_schedule();"/>');
        $('#title_tr').after('<tr id="rowid'+add_span+'"><td><input type="text" name="startdate[]" id="a_timestart'+add_span+'" class="w_120" placeholder="yyyy-mm-dd" > ~ <input type="text" name="enddate[]" id="a_timeend'+add_span+'" class="w_120" placeholder="yyyy-mm-dd"></td><td colspan="3"><input type="text" name="schedule[]" class="w_100" /></td></tr>');
        var current_span = add_span;
        $( "#a_timestart"+add_span ).datepicker({
            dateFormat: 'yy-mm-dd',
            showOn: 'button',
            buttonImage:"/theme/creativeband/pix/block_calendar_icon.png",
            buttonImageOnly:true,
            onClose: function( selectedDate ) {
                $( "#a_timeend"+current_span ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#a_timestart"+add_span ).datepicker("setDate", current_date);
        $( "#a_timeend"+add_span ).datepicker({
            dateFormat: 'yy-mm-dd',
            showOn: 'button',
            buttonImage:"/theme/creativeband/pix/block_calendar_icon.png",
            buttonImageOnly:true,
            onClose: function( selectedDate ) {
                $( "#a_timestart"+current_span ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#a_timeend"+add_span ).datepicker("setDate", next_date);
        add_span = add_span+1;
        $('#write').val(add_span);
    }
    function del_haksa_schedule(){
        delete_tr = --add_span;
        $('#rowid'+delete_tr).remove();
        if(add_span == 0){
            $('#addndeletearea').html('<input type="button" class="gray_btn" id="add_schedule" value="일정추가" onclick="add_haksa_schedule();"/>');
        }
        $('#write').val(add_span);
    }
    
    var modify_array = new Array();
    function modify_schedule(cid){
            c_list = $('#schedule_setting').find('input[name=modi_list]').val();
            $('#schedule_setting').find('input[name=modi_list]').val(c_list+cid+',');
            $( "#schedule"+cid ).prop('readonly', false);
            $( "#schedule"+cid ).css('border','1px solid #bfbfbf');
            $( "#timestart"+cid ).prop('readonly', false);
            $( "#timestart"+cid ).css('border','1px solid #bfbfbf');
            $( "#timestart"+cid ).datepicker({
                dateFormat: 'yy-mm-dd',
                showOn: 'button',
                buttonImage:"/theme/creativeband/pix/block_calendar_icon.png",
                buttonImageOnly:true,
                onClose: function( selectedDate ) {
                    $( "#timeend"+cid ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $( "#timeend"+cid ).prop('readonly', false);
            $( "#timeend"+cid ).css('border','1px solid #bfbfbf');
            $( "#timeend"+cid ).datepicker({
                dateFormat: 'yy-mm-dd',
                showOn: 'button',
                buttonImage:"/theme/creativeband/pix/block_calendar_icon.png",
                buttonImageOnly:true,
                onClose: function( selectedDate ) {
                    $( "#timestart"+cid ).datepicker( "option", "maxDate", selectedDate );
                }
            });
            $('#modifide').val('1');
            modify_array[modify_array.length] = cid;
    }
    
    function year_search(){
        $('#schedule_search').submit();
    }
    
    function delete_haksa_schedule(did){
        if (confirm("정말 삭제하시겠습니까??") == true){
            location.href='<?php echo 'haksa_schedule_delete.php?id=';?>'+did+'<?php echo '&year='.$year.'&hyear='.$hyear.'&univ='.$univ;?>';
        }else{ 
            return false;
        }
    }
</script>

 <?php include_once ('../inc/footer.php');?>
