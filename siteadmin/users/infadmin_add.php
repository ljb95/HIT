<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/notices_write.php');
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
$create = optional_param('create', false, PARAM_BOOL);

$context = context_system::instance();
$nav = array('top' => 'site', 'left' => 'board', 'sub' => 'notice');
;

$id = optional_param("id", 0, PARAM_INT);
$mod = optional_param("mod", "write", PARAM_TEXT);

$admins = explode(',', $CFG->siteadmins);
$superadmin = $admins[0];
if ($superadmin != $USER->id) {
    if ($id != $USER->id) {
        echo '<script type="text/javascript">document.location.href="./infadmin.php"</script>';
    }
}

$temp = new stdclass();
$fullname = '';
if ($mod == 'edit') {
    $sql = "SELECT u.*, lu.psosok, lu.usergroup, itemid, lu.menu_auth, lu.psosok
            FROM {user} u 
            JOIN {lmsdata_user} lu ON lu.userid = u.id 
            LEFT JOIN (SELECT userid as fuserid, itemid FROM {files} WHERE component = 'user' AND filesize >0) f ON u.id = fuserid
            WHERE u.id = " . $id;
    $temp = $DB->get_record_sql($sql);

    $file_obj = $DB->get_record('files', array('itemid' => $temp->itemid, 'license' => 'allrightsreserved'));
    if (!empty($file_obj)) {
        $file_stored = get_file_storage()->get_file_instance($file_obj);

        $file_url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file_stored->get_contextid() . '/' . $file_stored->get_component() . '/' .
                $file_stored->get_filearea() . $file_stored->get_filepath() . $file_stored->get_itemid() . '/' . $file_stored->get_filename());
    }

    $fullname = fullname($temp);
}
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_users.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('user_adminreg', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./info.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./infadmin.php"><?php echo get_string('admin_management', 'local_lmsdata'); ?></a> > 관리자등록</div>
        <?php if (!empty($id) && $mod === 'edit') { ?>
            <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./infadmin_submit.php?mod=edit&id=<?php echo $id; ?>" method="POST">
            <?php } else { ?>
                <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./infadmin_submit.php" method="POST">
                <?php } ?>
                <table cellpadding="0" cellspacing="0" class="detail">

                    <tbody>
                          <?php if ($mod == 'write') { ?>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font>등록구분</td>
                            <td class="field_value">
                                <input type="radio" name="add_type" checked="checked" value="0">신규
                                <input type="radio" name="add_type" value="1">교직원 등록
                            </td>
                        </tr>
                          <?php } ?>
                        <tr>
                            <td class="field_title"><?php echo get_string('user_authority', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <select title="author" class="w_160" name="usergroup" onchange="change_group()">
                                    <option value="ma"><?php echo get_string('superadmin', 'local_lmsdata'); ?></option>
                                    <!--option <?php echo (!empty($temp->usergroup)) ? "selected='selected'" : ""; ?> value="sa"><?php echo get_string('subadmin', 'local_lmsdata'); ?></option-->
                                    <option <?php echo (!empty($temp->menu_auth) && $temp->menu_auth == '8') ? "selected='selected'" : ""; ?> value="de">부서관리자</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('user_id', 'local_lmsdata'); ?></td>
                            <td class="field_value"> 
                                <input type="hidden" name="prof_userid" value="<?php echo!empty($course->prof_userid) ? $course->prof_userid : 0; ?>" />
                                <input type="text" title="id" class="w_300" name ="userid" <?php if ($mod == 'edit') echo "disabled='true'"; ?> value="<?php echo (!empty($temp->username)) ? $temp->username : ""; ?>"/>
                                <?php if ($mod == 'write') { ?>
                                <input type="button" name="id_chk" value="<?php echo get_string('user_duplicateinquiry', 'local_lmsdata'); ?>" class="gray_btn">
                                <input type="button" name="user_search" value="검색" onclick="search_prof_popup()" style="display:none;" class="gray_btn">
                                <?php } ?>
                            </td>
                        </tr>
                        <tr class="usergroup">
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font>담당부서</td>
                            <td class="field_value">
                                <input type="hidden" name="dept_code" />
                                <input type="text" title="id" class="w_300" name ="dept_name" <?php if ($mod == 'edit') echo "disabled='true'"; ?> value="<?php echo (!empty($temp->username)) ? $temp->username : ""; ?>"/>
                                <input type="button" name="dept_chk" value="검색" class="gray_btn">
                            </td>
                        </tr>
                        <tr class="tr_pw">
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('user_password', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="password" title="password" class="w_300" <?php if ($mod == 'edit') echo "disabled='true'"; ?>  name ="password" value=""/>
                                <?php if ($mod == 'edit') { ?><label for="pw_edit"><?php echo get_string('change_password', 'local_lmsdata'); ?></label><input type="checkbox" id="pw_edit" name="pw_edit"><?php } ?>
                            </td>
                        </tr>
                        <tr class="tr_pw_chk">
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('user_repassword', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="password" title="password" class="w_300" <?php if ($mod == 'edit') echo "disabled='true'"; ?> name ="repassword" value=""/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('name', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="text" title="name" class="w_200" name ="username" value="<?php echo (!empty($fullname)) ? $fullname : ""; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('attach', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="text" title="소속" class="w_300" name ="psosok" value="<?php echo (!empty($temp->psosok)) ? $temp->psosok : ""; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('email', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="text" title="email" class="w_300" name ="email" value="<?php echo (!empty($temp->email)) ? $temp->email : ""; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('contact', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="text" title="phone" class="w_300" name ="phone" value="<?php echo (!empty($temp->phone1)) ? $temp->phone1 : ""; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><?php echo get_string('user_img', 'local_lmsdata'); ?></td>
                            <td class="field_value number">
                                <?php echo (!empty($temp->itemid) && $temp->itemid > 0 ? '<a name="file_link" href="' . $file_url . '">' . $file_stored->get_filename() . '<img src="../img/icon-attachment.png" alt="fileicon" class="icon-attachment"/></a>' : '') ?> 
                                <?php if (!empty($temp->itemid)) { ?>
                                    <input type="button" class="gray_btn_small" name="remove_button" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="remove_file();"/><br>
<?php } ?>
                                <input type="file" title="file" name="uploadfile" styleㅊ="margin-top: 10px;"/> 
                                <input type="hidden" class="" name="file_id" value="<?php echo $temp->itemid ? $temp->itemid : -1 ?>"/>
                                <input type="hidden" name="file_del" value="0"/>
                            </td>
                        </tr>


                    </tbody>

                </table>

                <div id="btn_area">
                    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('save', 'local_lmsdata'); ?>"  />
                    <?php
                    if ($mod == 'edit') {
                        ?>
                        <input type="button" id="add_delete" class="red_btn" onclick="delete_admin_user('<?php echo $id; ?>');" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" />
                        <?php
                    }
                    ?> 
                    <input type="button" id="admin_list" class="normal_btn" value="<?php echo get_string('list2', 'local_lmsdata'); ?>"  />
                </div> <!-- Bottom Button Area -->
 
            </form>
    </div>
</div>
<?php include_once('../inc/footer.php'); ?>


<script type="text/javascript">
function search_prof_popup() {
        var tag = $("<div id='course_prof_popup'></div>");
        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/siteadmin/users/course_prof.php'; ?>',
            method: 'POST',
            success: function (data) {
                tag.html(data).dialog({
                    title: '교직원 검색',
                    modal: true,
                    width: 800,
                    resizable: false,
                    height: 400,
                    buttons: [{id: 'close',
                            text: '<?php echo get_string('cancle', 'local_lmsdata'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}],
                    close: function () {
                        $('#frm_course_prof').remove();
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }
    $(document).ready(function () {
        
        $('input[name=add_type]').click(function () { 
            var type = $(this).val();
            if(type == 1){
                $('input[name=id_chk]').hide();
                $('input[name=user_search]').show();
                $('.tr_pw').hide();
                $('.tr_pw_chk').hide();
                $( "input[name=phone],input[name=email],input[name=userid],input[name=psosok],input[name=username],input[name=dept_name]" ).attr("readonly",true); 
            } else {
                $('input[name=id_chk]').show();
                $('input[name=user_search]').hide();
                $('.tr_pw').show();
                $('.tr_pw_chk').show();
                $( "input[name=phone],input[name=email],input[name=userid],input[name=psosok],input[name=username]" ).removeAttr("readonly"); 
                $( "input[name=username],input[name=psosok],input[name=userid],input[name=email],input[name=phone]" ).val('');
            }
        });
        
        $('.field_value input[name=pw_edit]').click(function () {
            if ($('.field_value input[name=pw_edit]').prop('checked') == true) {
                $(".field_value input[name=password]").prop('disabled', false);
                $(".field_value input[name=repassword]").prop('disabled', false);
            } else {
                $(".field_value input[name=password]").prop('disabled', true);
                $(".field_value input[name=repassword]").prop('disabled', true);
            }
        });

        $('#admin_list').click(function () {
            location.href = "./infadmin.php";
        });

        var check_true = 0;
        $(".field_value input[name=userid]").keyup(function () {
            check_true = 0;
        });

        $(".field_value input[name=id_chk]").click(function () {
          if($("input[name=userid]").val()){
            $.ajax({
                type: 'POST',
                data: {
                    userid: $(".field_value input[name=userid]").val(),
                },
                url: './infadmin_id_check.php',
                success: function (data) {
                    if (data == false) {
                        alert("현재 사용중인 아이디 입니다.");
                    } else {
                        alert("사용 가능한 아이디 입니다.");
                        check_true = 1;
                    }
                },
            })
        } else {
            alert('아이디를 입력해주세요.');
            $("input[name=userid]").focus();
        }
        });
        
         $(".field_value input[name=dept_chk]").click(function () {
            var tag = $("<div id='dept_popup'></div>");
            $.ajax({ 
                url: '<?php echo $CFG->wwwroot . '/siteadmin/users/dept_list.php'; ?>',
                method: 'POST',
                success: function (data) {
                    tag.html(data).dialog({
                        title: '부서검색',
                        modal: true,
                        width: 800,
                        resizable: false,
                        height: 400,
                        buttons: [{id: 'close',
                                text: '<?php echo get_string('cancle', 'local_lmsdata'); ?>',
                                disable: true,
                                click: function () {
                                    $(this).dialog("close");
                                }}],
                        close: function () {
                            $('#frm_dept_search').remove();
                            $(this).dialog('destroy').remove()
                        }
                    }).dialog('open');
                }
            });
        });

        $('#frm_popup_submit').submit(function (event) {
            var userid = $(".field_value input[name=userid]").val();
            if (userid.trim() == '') {
                if($('input[name=add_type]:radio:checked').val() == '0'){
                    alert("아이디를 입력해 주세요");
                } else {
                    alert("검색을 통해 사용자를 선택해 주세요");
                    $('input[name=userid]').focus();
                }
                return false;
            };
<?php
if ($mod == 'write') {
    ?>
                            if($('input[name=add_type]') && $('input[name=add_type]:radio:checked').val() == '0'){
                                if (check_true == 0) {
                                    alert("중복조회를 해주세요");
                                    return false;
                                }
                            }
    <?php
}
?>
            if ($(".field_value input[name=password]").prop('disabled') == false && (!$('input[name=add_type]') || ($('input[name=add_type]') && $('input[name=add_type]:radio:checked').val() == '0'))) {
                var password = $(".field_value input[name=password]").val();
                if (password.trim() == '') {
                    alert("비밀번호를 입력해 주세요");
                    return false;
                }
                ;
                var repassword = $(".field_value input[name=repassword]").val();
                if (repassword.trim() == '') {
                    alert("비밀번호 확인을 입력해 주세요");
                    return false;
                }
                ;
                if (password != repassword) {
                    alert("비밀번호와 비밀번호 확인이 일치하지 않습니다.");
                    return false;
                }
            }
            
            var username = $(".field_value input[name=username]").val();
            if (username.trim() == '') {
                if($('input[name=add_type]:radio:checked').val() == '0'){
                    alert("이름을 입력해 주세요");
                } else {
                    alert("검색을 통해 사용자를 선택해 주세요");
                    $('input[name=userid]').focus();
                }
                return false;
            }
            ;
            var univ = $(".field_value input[name=psosok]").val();
            if (univ.trim() == '') {
                alert("소속을 입력해 주세요");
                return false;
            }
            ;
            var email = $(".field_value input[name=email]").val();
            if (email.trim() == '') {
                alert("<?php echo get_string('user_emailalert', 'local_lmsdata'); ?>");
                return false;
            }
            ;
            var phone = $(".field_value input[name=phone]").val();
            if (phone.trim() == '') {
                alert("연락처를 입력해 주세요");
                return false;
            }
            ;
        });
    });
    function remove_file() {

        $("a[name='file_link']").remove();
        $("input[name='remove_button']").remove();
        $("input[name='file_del']").val(1);

    }

    function delete_admin_user(did) {
        if (confirm("정말 삭제하시겠습니까??") == true) {
            location.href = '<?php echo 'infadmin_submit.php?id='; ?>' + did + '<?php echo '&mod=delete'; ?>';
        } else {
            return false;
        }
    }
    function change_group() {
        var group = $('select[name=usergroup] option:selected').val();
        if(group == 'de'){
            $('.usergroup').show();
            $('input[name=dept_name]').removeAttr('disable');
        } else {
            $('.usergroup').hide();
            $('input[name=dept_name]').attr('disable',true);
        }
    }
    change_group();
</script>
