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


$context = context_system::instance();
$nav = array('top' => 'site', 'left' => 'board', 'sub' => 'notice');


$id = optional_param("id", 0, PARAM_INT);
 include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_users.php'); ?>
    
    <?php 
    if ($id) {
        if ($ipblock = $DB->get_record("ipblock", array("id" => $id))) {
            $ips = explode('.', $ipblock->ip);
        }
}

    ?>
    <div id="content">
        <h3 class="page_title">IP 제한</h3>
        <div class="page_navbar"><a href="./info.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./inftemp.php"><?php echo get_string('user_manageaccounts', 'local_lmsdata'); ?></a> > <?php echo get_string('user_accountsreg', 'local_lmsdata'); ?></div>

        <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="<?php echo './ipblock_add_submit.php?id=' . $id; ?>" method="POST">

            <table cellpadding="0" cellspacing="0" class="detail">

                <tbody>
                    <tr>
                        <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font>IP Address</td>
                        <td class="field_value">
                            <input type="text" title="ip"  size="1" maxlength="3" name="ip1" value="<?php if ($id) echo $ips[0]; ?> "/> .
                            <input type="text" title="ip"  size="1" maxlength="3"  name="ip2" value="<?php if ($id) echo $ips[1]; ?>"/> .
                            <input type="text" title="ip" size="1" maxlength="3"  name="ip3" value="<?php if ($id) echo $ips[2]; ?>"/> .
                            <input type="text" title="ip"  size="1" maxlength="3"  name="ip4" value="<?php if ($id) echo $ips[3]; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font>사용여부</td>
                        <td class="field_value">
                            <select name="isused" class="w_90">
                                <option value="1">사용</option>
                                <option value="0" <?php if ($id && $ipblock->isused == '0') echo 'selected'; ?>>미사용</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">사유</td>
                        <td class="field_value" >
                            <textarea name="description" maxlength="800" style="width:100%; height: 150px;"><?php if($id)echo trim($ipblock->description); ?></textarea>
                        </td>
                    </tr>
                </tbody>

            </table>

            <div id="btn_area">
                <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('save', 'local_lmsdata'); ?>"  />
                <?php
                if ($id) {
                    ?>
                    <input type="button" id="add_delete" class="red_btn" onclick="if(confirm('삭제하시겠습니까?')){location.href='ipblock_del.php?id=<?php echo $id; ?>'}" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: right; margin-left: 15px;" />
                    <?php
                }
                ?>
                <input type="button" id="temp_list" class="normal_btn" value="<?php echo get_string('list2', 'local_lmsdata'); ?>"  />
            </div> <!-- Bottom Button Area -->

        </form>
    </div>
</div>
<?php include_once('../inc/footer.php'); ?>


<script type="text/javascript">
    $(document).ready(function () {

        $('.field_value input[name=pw_edit]').click(function () {
            if ($('.field_value input[name=pw_edit]').prop('checked') == true) {
                $(".field_value input[name=password]").prop('disabled', false);
                $(".field_value input[name=repassword]").prop('disabled', false);
            } else {
                $(".field_value input[name=password]").prop('disabled', true);
                $(".field_value input[name=repassword]").prop('disabled', true);
            }
        });

        $('#temp_list').click(function () {
            location.href = "./ipblock.php";
        });

        var check_true = 0;
        $(".field_value input[name=userid]").keyup(function () {
            check_true = 0;
        });

        $(".field_value input[name=id_chk]").click(function () {
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
        });

        $('#frm_popup_submit').submit(function (event) {
            var userid = $(".field_value input[name=userid]").val();
            if (userid.trim() == '') {
                alert("임시번호를 입력해 주세요");
                return false;
            }
            ;
<?php
if ($mod == 'write') {
    ?>
                if (check_true == 0) {
                    alert("임시번호 중복조회를 해주세요");
                    return false;
                }
    <?php
}
?>

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
        });
    });

</script>
