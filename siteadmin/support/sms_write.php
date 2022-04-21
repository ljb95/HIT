<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/sms_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$data = new stdClass();

$data->sdate = date('Y-m-d');
$data->sh = date('H');
$data->sm = date('i');

$sh_options = '';
$sm_options = '';

for($i=0;$i<24;$i++){
    $selected1 = '';
    if($i==$data->sh) $selected1 = 'selected';
    $sh_options .= '<option value="'.$i.'" '.$selected1.'>'.$i.'</option>';
}

for($i=0;$i<60;$i++){
    $selected1 = '';
    if($i==$data->sm) $selected1 = 'selected';
    $sm_options .= '<option value="'.$i.'" '.$selected1.'>'.$i.'</option>';
}   

?>
<?php include_once('../inc/header.php');?>
<style>
    ul#mailto{ width: 80%;height:40px;float:left;background:#fff;border:1px solid #ccc;overflow:auto;
    padding:10px; margin-right:5px;}
    ul#mailto li{ background:#efefef;padding:3px; border: 1px solid #ccc; margin-right:5px;}
    ul#mailto span{padding-right:3px;}
    ul#mailto img{vertical-align: middle;}
</style>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title">문자발송</h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./sms.php">SMS 발송관리</a> > 메세지 작성</div>

        <p>*는 필수입력사항입니다.</p>
        <form name="write_form"  method="POST" enctype="multipart/form-data" action="./sms_send.php">
        <table cellpadding="0" cellspacing="0" class="detail">

    <tbody>
        <tr>
            <td class="field_title">발송자명</td>
            <td class="field_value">
                <input type="text" name="fullname" value="<?php echo fullname($USER);?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title">*제목</td>
            <td class="field_value">
                <input type="text" name="subject" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_title">*발송자연락처</td>
            <td class="field_value">
                <input type="text" name="sphone" value="042-670-9000"/>
                <!--<input type="text" name="sphone" value="<?php echo $USER->phone2;?>"/>-->
                형식) 010-1111-1111 : 대쉬(-)를 포함하여 작성
            </td>
        </tr>
<!--        
        <tr>
            <td class="field_title">*발송시간</td>
            <td class="field_value">
                <input type="text" name="sendd" id="id_sendd" size="10" value="<?php echo $data->sdate;?>"/>
                    <select name="sendh" id="id_sendh" style="width:50px;">
                        <?php echo $sh_options;?>
                    </select>시
                    <select name="sendm" id="id_sendm" style="width:50px;">
                        <?php echo $sm_options;?>
                    </select>분
                <input type="checkbox" name="now_send" value="1"/>지금 발송
            </td>
        </tr>
-->
        <tr>
            <td class="field_title">*발송타입</td>
            <td class="field_value">
                <input type="radio" name="mailto_type" value="1" checked/> 지정발송
                <input type="radio" name="mailto_type" value="2"/> 전체발송
            </td>
        </tr>
        <tr>
            <td class="field_title">발송대상목록</td>
            <td class="field_value">
                <div id="id_mailto_list">
                    <input type="hidden" name="mailto"/>
                <ul id="mailto"></ul>
                <div class="clear">
                <input type="button" value="추가" class="blue_btn" style="margin-bottom:5px;" onclick="email_select_popup();"/>
                <input type="button" value="전체삭제" onclick="$('#mailto').html('');" class="orange_btn"/>
                </div>
                </div>
                <div id="id_mailto_all">회원 전체에게 SMS를 발송합니다.</div>
            </td>
        </tr>
        <tr>
            <td class="field_title">*내용</td>
            <td class="field_value">
                <textarea name="contents" maxlength="2000" onkeyup="fnChkByte_adm(this,'2000')" style="width: 98%" rows="10"></textarea>
                <span id="byteInfo">0</span>/2000Byte
                <input type="hidden" id="byteInfoinput" name="byteInfoinput" value="">
            </td>
        </tr>
    </tbody>

</table>

<div id="btn_area">
    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('save','local_lmsdata'); ?>" onclick="update_data();return false;" />
    <input type="button" id="notice_list" class="normal_btn" onclick="location.href='sms.php'" value="<?php echo get_string('list2','local_lmsdata'); ?>"  />
</div> <!-- Bottom Button Area -->

 </form>
    </div>
</div>
<script type="text/javascript">
    function fnChkByte_adm(obj,maxByte){
       var str = obj.value;
       var str_len = str.length;
       
       var rbyte = 0;
       var rlen = 0;
       var one_char = "";
       var str2 = "";

       for(var i=0; i<str_len; i++){
           one_char = str.charAt(i);
           if(escape(one_char).length > 4){
               rbyte += 2;                                         //한글2Byte
           }else{
               rbyte++;                                            //영문 등 나머지 1Byte
           }
           if(rbyte <= maxByte){
                rlen = i+1;                                          //return할 문자열 갯수
           }
       }
       
       if(rbyte > maxByte){
           alert("한글 "+(maxByte/2)+"자 / 영문 "+maxByte+"자를 초과 입력할 수 없습니다.");
           str2 = str.substr(0,rlen);                                  //문자열 자르기
           obj.value = str2;
           fnChkByte(obj, maxByte);
       }else{
          document.getElementById('byteInfo').innerText = rbyte;
          document.getElementById('byteInfoinput').value = rbyte;
       }
   }
  $(function() {
      
    $("#id_issue_select").css({"display":"none"});
    $("#id_mailto_all").css({"display":"none"});
    
    $( "#id_sendd" ).datepicker({
      showOn: "focus",
      dateFormat: "yy-mm-dd",
      minDate: "<?php echo $data->sdate;?>"
    });
    
    $("input[name=mailto_type]").click(function(){
        if($(this).val()=="1"){
            $("#id_mailto_list").css({"display":"block"});
            $("#id_mailto_all").css({"display":"none"});
        }else if($(this).val()=="2"){
            $("#id_mailto_all").css({"display":"block"});
            $("#id_mailto_list").css({"display":"none"});
        }
    });
    
});

function email_select_popup() {
    var tag = $("<div></div>");
    $.ajax({
        url: '<?php echo $SITECFG->wwwroot . '/siteadmin/support/email_select.php'; ?>',
        data: {
            gubun:'sms'
        },
        success: function(data) {
            
            $('body').css({'overflow':'hidden'});
            
            tag.html(data).dialog({
                title: '발송대상추가',
                modal: true,
                width: 800,
                height: $(window).height()-400,
                buttons: [ {id:'save',
                        text:'발송대상추가',
                        click: function() {
                            var btnSave = $(this).parent().find("button[id='save']");
                            btnSave.button('disable');

                            var status = email_select_submit();
                            if(status) {
                                $('#email_select_form').remove();  // IE 오류로 임종범이 제거함 왜필요한건지 모르겠음
                                $('body').css({'overflow':'auto'});
                            } else {
                                btnSave.button('enable');
                            }
                               $( this ).dialog( "close" );
                        }},
                    {id:'close',
                        text:'닫기', 
                        disable: true,
                        click: function() {
                            $('#email_select_form').remove();
                            $('body').css({'overflow':'auto'});
                            $( this ).dialog( "close" );
                        }}]
            }).dialog('open');
            
            $(".ui-dialog-titlebar-close").click(function(){
                    $('#email_select_form').remove();
                    $('body').css({'overflow':'auto'});
            });
        }
    });
}

//선택한 이메일을 폼에 전달
function email_select_submit(){

    var status = true;
    var type = $('input[name=target_type]:checked');
    
    var tb_list = new Array();
    $("ul#mailto li").each(function() {
        tb_list.push($(this).attr("id"));
    });
    
    if(type.val()=='role'){
        var tg = $('.role iframe').contents().find('input:checkbox[name=role]:checked');
        var sel = '';
        
        tg.each(function(){
            var tgs = $(this).val().split(';');
            var id = tgs[0]+tgs[1];
            if(jQuery.inArray(id, tb_list) == -1) {
                sel += '<li id="'+id+'">';
                sel += '<input type="hidden" value="'+tgs[0]+';'+tgs[1]+'"/>';
                sel += '<span>'+tgs[2]+'</span>';
                sel += '<a href="javascript:email_select_del(\''+id+'\')">\n\
                <img src="../img/mark_x.png" alt="삭제"/></a>';
                sel += '</li>';
            }
        });
     
    }else if(type.val()=='course'){
        var tg = $('.course iframe').contents().find('input:checkbox[name=chkbox]:checked');
        var sel = '';
        
        tg.each(function(){
            var tgs = $(this).val().split(';');
            var cls = $('.course iframe').contents().find('select[name=class-'+tgs[0]+'] option:selected').val();
            if(cls) {
                var clss = cls.split(';');
                var clss0 = clss[0];
                var clss1 = clss[1];
            }else{
                var clss0 = '', clss1 = '';
            }
            var id = 'course'+tgs[0]+clss0;
            if(jQuery.inArray(id, tb_list) == -1) {
                sel += '<li id="'+id+'">';
                sel += '<input type="hidden" value="'+'course;'+tgs[0]+';'+clss0+'"/>';
                sel += '<span>'+tgs[1]+' '+clss1+'</span>';
                sel += '<a href="javascript:email_select_del(\''+id+'\')">\n\
                <img src="../img/mark_x.png" alt="삭제"/></a>';
                sel += '</li>';
            }
        });
    
    }else if(type.val()=='user'){
        var tg = $('.user iframe').contents().find('input:checkbox[name=chkbox]:checked');
        var sel = '', tgs='';
        
        tg.each(function(){
            var tgs = $(this).val().split(';');
            var id = tgs[0]+tgs[1];
            if(jQuery.inArray(id, tb_list) == -1) {
                sel += '<li id="'+id+'">';
                sel += '<input type="hidden" value="'+tgs[0]+';'+tgs[1]+'"/>';
                sel += '<span>'+tgs[2]+'</span>';
                sel += '<a href="javascript:email_select_del(\''+id+'\')">\n\
                <img src="../img/mark_x.png" alt="삭제"/></a>';
                sel += '</li>';
            }
        });
    }
    
    $('ul#mailto').append(sel);
    return status;

}

//선택한 이메일 삭제
function email_select_del(li){
    $('#'+li).remove();
}
    
//데이터 전송
function update_data(){
    
    var frm = $('form[name=write_form]');
    
    
    if($('input[name=sendd]').val() == ''){
        alert('발송날짜를 입력하세요.');
        $('input[name=sendd]').focus();
        return false;
    }
    
    if($('input[name=subject]').val() == ''){
        alert('제목을 입력하세요.');
        $('input[name=subject]').focus();
        return false;
    }
    
    if($('input[name=sphone]').val() == ''){
        alert('발송자 연락처를 입력하세요.');
        $('input[name=sphone]').focus();
        return false;
    }
    
    if($('input[name=mailto_type]:checked').val() == '1'){
        if($('ul#mailto li').length==0){
        alert('발송대상을 추가하세요.');
        $('input[name=mailto]').focus();
        return false;
        }else{
            var maillist = '{';
            var count = 0;
            var tgs = '';
            var tg = '';
            $('ul#mailto li').each(function(){
                tgs = $(this).find('input').val();
                
                maillist += '"'+count+'":"'+tgs+'"';
                if(count<$('ul#mailto li').length-1) maillist += ',';
                count++;
            });
            maillist += '}';
            $('input[name=mailto]').val(maillist);
        }
    }
    
    if($('textarea[name=contents]').val()==''){
        alert('내용을 입력하세요.');
        return false;
    }
    
    document.write_form.submit();
}


</script>
<?php include_once('../inc/footer.php');?>