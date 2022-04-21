<?php
require_once '../../../config.php';
require_once '../config.php';
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

<iframe id="package" style="width:100%;height:100%;border:0;overflow:hidden;" src="<?php echo $CFG->wwwroot.STORAGE.'/'.$contents->data_dir.'/index.html';?>"></iframe>