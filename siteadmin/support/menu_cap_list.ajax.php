<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once $CFG->dirroot . '/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';
// Check for valid admin user - no guest autologin

$id = required_param('id', PARAM_INT);
$totalcount = $DB->count_records('menu_auth');

$offset = ($page - 1) * $perpage;
?>

<table class="generaltable">
    <tbody>
        <tr>
            <th>대체 권한</th>
            <td>
                <select name="auth" class="w_200">
                    <?php
                    $caps = $DB->get_records('menu_auth');
                    $num = $totalcount - $offset;
                    $cnt = 0;
                    foreach ($caps as $cap) {
                        $menu_auth_name = $DB->get_record('menu_auth_name', array('authid' => $cap->id,'lang'=>'ko'));
                        print_object($menu_auth_name);
                        if ($cap->id == $id)
                            continue;
                        $cnt++;
                        ?>
                        <option value="<?php echo $cap->id ?>"><?php echo $menu_auth_name->name; ?></option>
                    <?php } ?>
                    <?php
                    if ($cnt == 0) {
                        echo '<option value="-1">대체할 권한이 없습니다.</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>