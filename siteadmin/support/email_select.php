<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$gubun = optional_param('gubun','',PARAM_ALPHA);

?>

<style>
    iframe{ width:98%; height:800px; border:0; overflow:hidden;}
</style>

<div class="popup_content">

    <h2>발송대상 추가</h2>
    <form id="email_select_form">

        <table cellpadding="0" cellspacing="0" class="detail">
            <tbody>
                <tr>
                    <td class="field_title">발송대상</td>
                    <td class="field_value">
                        <input type="radio" name="target_type" value="role" checked style="margin: 0 3px 0 10px !important" onclick="select_type('role');"/>역할별
                        <input type="radio" name="target_type" value="course" style="margin: 0 3px 0 10px !important" onclick="select_type('course');"/>강좌별
                        <input type="radio" name="target_type" value="user" style="margin: 0 3px 0 10px !important" onclick="select_type('user');"/>개별
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="target role">
        <iframe src="email_select1.php" id="role"></iframe>
        </div>
        <div class="target course" style="display:none;">
        <iframe src="email_select2.php?gubun=<?php echo $gubun;?>" id="course"></iframe>
        </div>
        <div class="target user" style="display:none;">
        <iframe src="email_select3.php?gubun=<?php echo $gubun;?>" id="user"></iframe>
        </div> 

</div> <!-- Popup Content End -->

</form>


</div>

<script type="text/javascript">
    
    function select_type(type){
        
        $('.target').css({'display':'none'});
        
        $('.'+type).css({'display':'block'});
        
        $('#'+type).css({'height':$('#'+type).contents().height()});
         

    }

</script>

