<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';

global $SITECFG;
?>
<html>
    <head>
        <title>발송대상추가</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot . '/siteadmin/css/style_lms_admin.css'; ?>" />
    </head>
    <body>
        <table cellpadding="0" cellspacing="0" class="detail">
            <tbody>
                <tr>
                    <td class="field_title">역할별</td>
                    <td class="field_value">
                        <input type="checkbox" name="role" value="role;student;학습자" style="margin: 0 3px 0 10px !important"/>학습자
                        <input type="checkbox" name="role" value="role;teacher;교수자" style="margin: 0 3px 0 10px !important"/>교수자
                        <input type="checkbox" name="role" value="role;manager;과정별운영자" style="margin: 0 3px 0 10px !important"/>과정별 운영자
                        <input type="checkbox" name="role" value="role;admin;관리자" style="margin: 0 3px 0 10px !important"/>관리자
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>