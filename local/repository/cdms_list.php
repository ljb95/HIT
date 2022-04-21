<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';
require_once $CFG->libdir . '/formslib.php';

require_login();

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('search', '', PARAM_RAW);
$status = optional_param('status', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/local/repository/cdms_list.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

switch ($status){
    case 1:$title = get_string('review_contents_list','local_repository'); break;
    case 2:$title = get_string('approved_contents_list','local_repository'); break;
    case 3:$title = get_string('hold_contents_list','local_repository'); break;
}

$strplural = get_string("cdms_manage", "local_repository");
$PAGE->navbar->add(get_string("pluginnameplural", "local_repository"));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);


echo $OUTPUT->header();
?>
<h2><?php echo $title; ?></h2>
<div class="table-header-area">
<form>
    <select name="status">
        <option <?php if ($status == 1) {
    echo 'selected';
} ?> value="1"><?php echo get_string('review','local_repository');?></option>
        <option <?php if ($status == 2) {
    echo 'selected';
} ?> value="2"><?php echo get_string('approved','local_repository');?></option>
        <option <?php if ($status == 3) {
    echo 'selected';
} ?> value="3"><?php echo get_string('hold','local_repository');?></option>
    </select>
    <input type="text" name="search" <?php echo 'value="' . $searchtext . '"' ?>>
    <input type="submit" value="<?php echo get_string('search');?>">
</form>
</div>
<div class="table-filter-area">
    <input type="button" value="<?php echo get_string('pluginname','local_repository');?>" onclick="location.href='index.php'">
    <?php if (is_siteadmin()) { ?>
    <input type="button" value="CDMS" onclick="location.href='cdms.php'">
    <?php } ?>
</div>
    
<table class="generaltable">
    <thead>
    <tr>
        <th><?php echo get_string('list:no','local_repository');?></th>
        <th><?php echo get_string('contentsname','local_repository');?></th>
        <th><?php echo get_string('file_type','local_repository');?></th>
        <th><?php echo get_string('list:timecreated','local_repository');?></th>
        <th><?php echo get_string('lasteditdate','local_repository');?></th>
        <th><?php echo get_string('course');?></th>
        <?php if(is_siteadmin()){ ?>
        <th><?php echo get_string('administration');?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php
    if (is_siteadmin()) {
        $params = array('iscdms' => 1);
        $where = "";
        $join = "";
        if ($status != 0) {
            $where = " and r.status = :status";
            $params['status'] = $status;
        }
        if ($searchtext != "") {
            $join = "  and lc.con_name like :searchtxt  ";
            $params['searchtxt'] = '%' . $status . '%';
        }
        $sql = "select r.id, r.status , lc.con_name,lc.con_type ,lc.reg_dt,lc.update_dt, lc.id as conid from {lcms_repository} r "
                . "join {lcms_contents} lc on lc.id = r.lcmsid ".$join
                . "where r.iscdms = 1" . $where;
        $files = $DB->get_records_sql($sql, $params);
        $num = 1;
        foreach ($files as $file) {
            ?>
            <tr>
                <td><?php echo $num++; ?></td>
                <td style="text-align: left;"><a href="detail.php?id=<?php echo $file->id ?>&userid=<?php echo $userid; ?>"><?php echo $file->con_name; ?></a></td>
                <td><?php echo $file->con_type; ?></td>
                <td><?php echo date('Y-m-d', $file->reg_dt); ?></td>
                <td><?php echo date('Y-m-d', $file->update_dt); ?></td>
                <td><span style="cursor: pointer;" onclick="view_courses(<?php echo $file->conid; ?>)"><?php echo get_string('course');?></span></td>
                <?php if(is_siteadmin()){ ?>
                <td>
            <?php if ($file->status != 2) { ?>
                        <span style="cursor: pointer;" onclick="status_change(2,<?php echo $file->id; ?>)"><?php echo get_string('approve','local_repository');?></span>
            <?php } ?>
            <?php if ($file->status != 3) { ?>
                        <span style="cursor: pointer;" onclick="status_change(3,<?php echo $file->id; ?>)"><?php echo get_string('hold','local_repository');?></span>
            <?php } ?>
                </td>
        <?php } ?>
            </tr>
        <?php
    }
    if(!$files){
        echo '<tr><td colspan="8">'.get_string('empty_file','local_repository').'</td></tr>';
    }
} else {
        $params = array('iscdms' => 1);
        $where = "";
        $join = "";
        if ($status != 0) {
            $where = " and r.status = :status";
            $params['status'] = $status;
        }
        if ($searchtext != "") {
            $join = "  and lc.con_name like :searchtxt  ";
            $params['searchtxt'] = '%' . $status . '%';
        }
        $params['userid'] = $USER->id;
        $sql = "select r.id, r.status , lc.con_name,lc.con_type ,lc.reg_dt,lc.update_dt, lc.id as conid from {lcms_repository} r "
                . "join {lcms_contents} lc on lc.id = r.lcmsid ".$join
                . "where r.userid = :userid and r.iscdms = 1" . $where;
        $files = $DB->get_records_sql($sql, $params);
        $num = 1;
        foreach ($files as $file) {
            ?>
            <tr>
                <td><?php echo $num++; ?></td>
                <td style="text-align: left;"><a href="detail.php?id=<?php echo $file->id ?>&userid=<?php echo $userid; ?>"><?php echo $file->con_name; ?></a></td>
                <td><?php echo $file->con_type; ?></td>
                <td><?php echo date('Y-m-d', $file->reg_dt); ?></td>
                <td><?php echo date('Y-m-d', $file->update_dt); ?></td>
                <td><span style="cursor: pointer;" onclick="view_courses(<?php echo $file->conid; ?>)"><?php echo get_string('course');?></span></td>
                <?php if(is_siteadmin()){ ?>
                <td>
            <?php if ($file->status != 2) { ?>
                        <span style="cursor: pointer;" onclick="status_change(2,<?php echo $file->id; ?>)"><?php echo get_string('approve','local_repository');?></span>
            <?php } ?>
            <?php if ($file->status != 3) { ?>
                        <span style="cursor: pointer;" onclick="status_change(3,<?php echo $file->id; ?>)"><?php echo get_string('hold','local_repository');?></span>
            <?php } ?>
                </td>
        <?php } ?>
            </tr>
        <?php
    }
    if(!$files){
        echo '<tr><td colspan="8">'.get_string('empty_file','local_repository').'</td></tr>';
    }
}
?>
    </tbody>
</table>

<script>
    function status_change(status, id) {
        if(status == 3){
             var msg = prompt('<?php echo get_string('holdreason','local_repository');?>');
        } else {
             var msg = '';
        }
        $.ajax({
            method: "POST",
            url: "./cdms/status_change.ajax.php",
            data: {id: id, status: status , msg:msg}
        })
                .done(function (html) {
                    location.reload();
                });
    }
    function view_courses(id) {

        var tag2 = $("<div id='view_courses'></div>");

        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/repository/cdms/view_courses.ajax.php?id=" ?>'+id,
            success: function (data) {
                tag2.html(data).dialog({
                    title: '<?php echo get_string('view_courses', 'local_repository'); ?>',
                    modal: true,
                    width: 400,
                    resizable: false,
                    height: 200,
                    close: function () {
                        $('#mkfile').remove();
                        console.log(this);
                        $(this).dialog('destroy');
                    }
                }).dialog('open');
            }
        });
    }
</script>
