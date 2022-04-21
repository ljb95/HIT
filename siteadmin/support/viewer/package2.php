<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/local/repository/config.php';
require_once '../lib.php';

$id = required_param('id',PARAM_INT);

$PAGE->set_url('/local/repository/viewer/package.php', array('id'=>$id));

if ($id) {
    if (!$contents = $DB->get_record('lcms_contents', array('id' => $id))) {
        print_error('lcms contents is incorrect');
    }
    if (!$lcms = $DB->get_record('lcms_repository', array('lcmsid' => $id))) {
        print_error('lcms repository is incorrect');
    }
} else {
    print_error('missingparameter');
}

require_login();

?>

<iframe id="package" style="width:100%;height:100%;border:0;overflow:hidden;" src="<?php echo $CFG->wwwroot.'/storage/'.$contents->data_dir.'/index.html';?>"></iframe>


<!--
<script>
    window.open('<?php echo $CFG->wwwroot.'/storage/'.$contents->data_dir.'/index.html';?>','html_viewer','width=700 height=500 scrolling=yes');
    $('#viewer').dialog('destroy').remove();
    $('#page-mod-lcms-view').find('body').css({'overflow':'auto'});
</script>
-->