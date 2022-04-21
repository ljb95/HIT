<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

    require_once dirname(dirname(__FILE__)) . '/lib/paging.php';    
    require_once $CFG->dirroot . '/local/jinoboard/lib.php';
    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');

    $type = optional_param('type', 1, PARAM_INT);
    $page = optional_param('page', 1, PARAM_INT);
    $search = optional_param('search', '', PARAM_RAW);
    $perpage = optional_param('perpage', 10, PARAM_INT);

    $board = $DB->get_record('jinoboard', array('type' => $type));
    $context = context_system::instance();        
    $nav = array('top'=>'site', 'left'=>'board', 'sub'=>'notice');;
    
    $id = optional_param("id", 0, PARAM_INT);
    $mod = optional_param("mod", "write", PARAM_TEXT);
    
     if ($type) {
         if (! $board = $DB->get_record("jinoboard", array("id" => $type))) {
            print_error('invalidboardid', 'jinotechboard');
        }
    }
   
    $temp = new stdclass();
    
    $fullname = '';
    if($mod == 'edit'){
    $sql = "SELECT u.*, lu.psosok, lu.usergroup, lu.usergroup, itemid
            FROM {user} u 
            JOIN {lmsdata_user} lu ON lu.userid = u.id 
            LEFT JOIN (SELECT userid as fuserid, itemid FROM {files} WHERE component = 'user' AND filesize >0 AND mimetype LIKE '%image%' AND source is null) f ON u.id = fuserid
            WHERE u.id = ".$id;
    $temp = $DB->get_record_sql($sql);
    
    $file_obj = $DB->get_record('files', array('itemid'=> $temp->itemid, 'license'=>'allrightsreserved'));
    if(!empty($file_obj)){
        $file_stored = get_file_storage()->get_file_instance($file_obj);

        $file_url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $file_stored->get_contextid(). '/'. $file_stored->get_component(). '/'.
                        $file_stored->get_filearea(). $file_stored->get_filepath().$file_stored->get_itemid().'/'. $file_stored->get_filename());

    }
    
    $fullname = fullname($temp);
    }
?>
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_users.php');?>
    <div id="content">
    <h3 class="page_title"><?php echo get_string('user_accountsreg', 'local_lmsdata'); ?></h3>
    <div class="page_navbar"><a href="./info.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./inftemp.php"><?php echo get_string('user_manageaccounts', 'local_lmsdata');?></a> > <?php echo get_string('user_accountsreg', 'local_lmsdata'); ?></div>
    <?php if(!empty($id) &&  $mod==='edit'){ ?>
        <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="<?php echo './inftemp_submit.php?mod=edit&id='.$id.'&type='.$type; ?>" method="POST">
    <?php }else{?>
         <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="<?php echo './inftemp_submit.php?type='.$type; ?>" method="POST">
    <?php }?>
<table cellpadding="0" cellspacing="0" class="detail">

    <tbody>

        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('user_temporarynumber', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" title="tempno" class="w_300" name ="userid" <?php if($mod == 'edit') echo "disabled='true'";?> value="<?php echo (!empty($temp->username))?$temp->username:""; ?>"/>
                <?php if($mod == 'write'){?><input type="button" name="id_chk" value="<?php echo get_string('user_duplicateinquiry', 'local_lmsdata'); ?>" class="gray_btn"><?php }?>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('user_password', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="password" title="password" class="w_300" <?php if($mod == 'edit') echo "disabled='true'";?>  name ="password" value=""/>
                <?php if($mod == 'edit'){ echo get_string('change_password','local_lmsdata'); ?><input type="checkbox" name="pw_edit"><?php } else { ?><input type="hidden" value="1" name="pw_edit"><?php }?>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('user_repassword', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="password" title="password" title="password" class="w_300" <?php if($mod == 'edit') echo "disabled='true'";?> name ="repassword" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('firstname','local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" title="name" class="w_200" name ="firstname" value="<?php echo (!empty($temp->firstname)) ? $temp->firstname:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('lastname','local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" title="name" class="w_200" name ="lastname" value="<?php echo (!empty($temp->lastname)) ? $temp->lastname:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('user_role', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select class="w_160" title="group" name="usergroup">
                    <option value="pr" <?php if(isset($temp->usergroup) && $temp->usergroup == 'pr'){echo (!empty($temp->usergroup))?"selected='selected'":"";} ?>><?php echo get_string('teacher', 'local_lmsdata'); ?></option>
                    <option value="rs" <?php if(isset($temp->usergroup) && $temp->usergroup == 'rs'){echo (!empty($temp->usergroup))?"selected='selected'":"";} ?>><?php echo get_string('student', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('attach', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" title="sosok" class="w_300" name ="psosok" value="<?php echo (!empty($temp->psosok))?$temp->psosok:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('email', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" class="w_300" title="email" name ="email" value="<?php echo (!empty($temp->email))?$temp->email:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('contact', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" class="w_300" title="phone" name ="phone" value="<?php echo (!empty($temp->phone2))?$temp->phone2:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('user_img', 'local_lmsdata'); ?></td>
            <td class="field_value number">
                <?php echo (!empty($temp->itemid) &&  $temp->itemid > 0 ? '<a name="file_link" href="'.$file_url.'">'.$file_stored->get_filename().'<img src="../img/icon-attachment.png" class="icon-attachment"/></a>':'') ?> 
                <?php if(!empty($temp->itemid)){ ?>
                <input type="button" class="gray_btn_small" name="remove_button" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="remove_file();"/><br>
                <?php  }?>
               <input type="file" title="file" name="uploadfile" style="margin-top: 10px;"/> 
               <input type="hidden" class="" name="file_id" value="<?php echo (!empty($temp->itemid))?$temp->itemid : -1 ?>"/>
               <input type="hidden" name="file_del" value="0"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('user_state', 'local_lmsdata'); ?></td>
            <td class="field_value" >
                <select title="status" class="w_160" name="suspended">
                    <option <?php if(isset($temp->suspended) && $temp->suspended == '0'){echo (!empty($temp->suspended))?"selected='selected'":"";} ?> value="0"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></option>
                    <option <?php if(isset($temp->suspended) && $temp->suspended == '1'){echo (!empty($temp->suspended))?"selected='selected'":"";} ?> value="1"><?php echo get_string('siteadmin_noact', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
    </tbody>

</table>

<div id="btn_area">
    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('save','local_lmsdata'); ?>" style="float: right;" />
    <?php
        if($mod == 'edit'){
    ?>
    <input type="button" id="add_delete" class="red_btn" onclick="delete_temp_user('<?php echo $id;?>');" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: right; margin-left: 15px;" />
    <?php
        }
    ?>
    <input type="button" id="temp_list" class="normal_btn" value="<?php echo get_string('list2','local_lmsdata'); ?>" style="float: left;" />
</div> <!-- Bottom Button Area -->

 </form>
    </div>
</div>
<?php include_once('../inc/footer.php');?>


<script type="text/javascript">
$(document).ready(function () { 
    
    $('.field_value input[name=pw_edit]').click(function(){
        if($('.field_value input[name=pw_edit]').prop('checked') == true){
            $(".field_value input[name=password]").prop('disabled', false);
            $(".field_value input[name=repassword]").prop('disabled', false);
        } else {
            $(".field_value input[name=password]").prop('disabled', true);
            $(".field_value input[name=repassword]").prop('disabled', true);
        }
    });
    
    $('#temp_list').click(function() {
        location.href = "./inftemp.php";
    });

    var check_true = 0;
    $(".field_value input[name=userid]").keyup(function(){
        check_true = 0;
    });
    
    $(".field_value input[name=id_chk]").click(function(){
        $.ajax({
            type:'POST',
            data: {
                userid: $(".field_value input[name=userid]").val(),
            },
            url:'./infadmin_id_check.php',
            success:function(data){
                if(data == false){
                    alert("현재 사용중인 아이디 입니다.");
                } else {
                    alert("사용 가능한 아이디 입니다.");
                    check_true = 1;
                }
            },
        })
    });
    
    $('#frm_popup_submit').submit(function (event){
        var userid = $(".field_value input[name=userid]").val();
        if(userid.trim() == ''){
            alert("임시번호를 입력해 주세요");
            return false;
        };  
        <?php
        if($mod == 'write'){
        ?>
        if(check_true == 0){
            alert("임시번호 중복조회를 해주세요");
            return false;
        }
        <?php
        }
        ?>
        if($(".field_value input[name=password]").prop('disabled') == false){
            var password = $(".field_value input[name=password]").val();
            if(password.trim() == ''){
                alert("비밀번호를 입력해 주세요");
                return false;
            };
            var repassword = $(".field_value input[name=repassword]").val();
            if(repassword.trim() == ''){
                alert("비밀번호 확인을 입력해 주세요");
                return false;
            };
            if(password != repassword){
                alert("비밀번호와 비밀번호 확인이 일치하지 않습니다.");
                return false;
            }
        }
        var username = $(".field_value input[name=username]").val();
        if(username.trim() == ''){
            alert("이름을 입력해 주세요");
            return false;
        };
        var univ = $(".field_value input[name=psosok]").val();
        if(univ.trim() == ''){
            alert("소속을 입력해 주세요");
            return false;
        };
        var email = $(".field_value input[name=email]").val();
        if(email.trim() == ''){
            alert("<?php echo get_string('user_emailalert', 'local_lmsdata'); ?>");
            return false;
        };
        var phone = $(".field_value input[name=phone]").val();
        if(phone.trim() == ''){
            alert("연락처를 입력해 주세요");
            return false;
        };
    });
 });
   function remove_file(){

        $("a[name='file_link']").remove();
        $("input[name='remove_button']").remove();
        $("input[name='file_del']").val(1);
        
    }
    
    function delete_temp_user(did){
        if (confirm("정말 삭제하시겠습니까??") == true){
            location.href='<?php echo 'inftemp_submit.php?id=';?>'+did+'<?php echo '&mod=delete';?>';
        }else{ 
            return false;
        }
    }
</script>
