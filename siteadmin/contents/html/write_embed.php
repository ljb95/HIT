<!-- Embed 폼 시작 -->
<script type="text/javascript">
    
function search_embed_contents(){
    
    var type = 'youtube';
    type_txt = (type=='youtube')? '유튜브':'비메오';
    var tit = type_txt+' 콘텐츠 검색';
    var dir = './html/search_embed_'+type+'.php';
    var search = $('input[name=emb_search]').val(), type_txt = '';
       
    var tag = $("<div id='load_form'></div>");
    $.ajax({
        url: dir,
        data: {
            search: search
        },
        success: function(data) {
            
            $('body').css({'overflow':'hidden'});
            
            tag.html(data).dialog({
                title: tit,
                modal: true,
                width: 670,
                height: 700,
                maxheight: $(window).height()-100,
                buttons: [{
                    id:'close',
                    text:'닫기', 
                    disable: true,
                    click: function() {
                        $('body').css({'overflow':'auto'});
                        $('#load_form').hide();
                        $( this ).dialog( "close" );
                    }
                }]
            }).dialog('open');
            
            $(".ui-dialog-titlebar-close").click(function(){
                $('body').css({'overflow':'auto'});
                $('#load_form').hide(); 
             });
            
        //hideLoadingBar();
    }
        
    });
} 
function option_select(type, fl, val) {

    if (type == 'radio') {
        var len = $(':radio[name=' + fl + ']').length;
        for (var i = 0; i < len; i++) {
            field = $(':radio[name=' + fl + ']:eq(' + i + ')');
            if (field.val() == val)
                field.attr('checked', true);
        }
    }

    if (type == 'checkbox') {
        var len = $(':checkbox[name=' + fl + ']').length;
        for (var i = 0; i < len; i++) {
            field = $(':checkbox[name=' + fl + ']:eq(' + i + ')');
            if (field.val() == val)
                field.attr('checked', true);
        }
    }

    if (type == 'select') {
        var len = $('select[name=' + fl + '] option').length;
        for (var i = 0; i < len; i++) {
            field = $('select[name=' + fl + '] option:eq(' + i + ')');
            if (field.val() == val)
                field.attr('selected', true);
        }
    }

    return;

}

</script>
<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $val) {
        ${$key} = $val;
    }
}
    
//if($seq){
//    $query = "select embed_type,embed_code from ".$CFG->prefix."CONTENTS where ID=:seq";
//    $bind = array('seq'=>$seq);
//    $data = $DB->select_row($query,$bind);    
//}else{
//    $data = array('EMBED_TYPE'=>'','EMBED_CODE'=>'');
//}
?>
<table border="1" class="write_form">
    <caption>외부 콘텐츠 등록 영역</caption>
    <input type="hidden" value="youtube">
    <tr>
        <td>
            <input type="text" title="search" name="emb_search" placeholder="검색어를 입력하세요"/>
            <input type="button" value="<?php echo get_string('stats_search1', 'local_lmsdata'); ?>" onclick="search_embed_contents();" class="blue_btn">
        </td>
    </tr>
    <!--
    <tr>
        <th>동영상시간</th>
        <td>
            <input type="text" name="con_total_time1" size="5" maxlength="3"/> 
            분
            <input type="text" name="con_total_time2" size="5" maxlength="2"/> 
            초
             * 동영상 검색시 자동으로 들어갑니다.
        </td>
    </tr>
    -->
    <tr>
        <td>
            <input name="emb_code" title="code" size="100" placeholder="동영상 검색시 자동으로 들어갑니다. 직접 입력도 가능합니다." value="<?php echo $data['EMBED_CODE'];?>"/> 
            <br/>유튜브 : 예1) http://www.youtube.com/v/9tz4ToEQ_jw
            <br/>예2) http://youtu.be/9tz4ToEQ_jw
            <br/>비메오 : 예) http://vimeo.com/82299487
        </td>
    </tr>
</table>
<!-- Embed 폼 끝 -->
