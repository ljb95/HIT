<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
    require_once dirname(dirname(dirname (__FILE__))).'/config.php';
    
    
    require_login(0, false);
    if (isguestuser()) {
        $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices_submit.php');
        redirect(get_login_url());
    }
    $context = context_system::instance();
    require_capability('moodle/site:config', $context);
    
    require_once (dirname(dirname(dirname(__FILE__))) .'/lib/filelib.php');
    require_once (dirname(dirname(dirname(__FILE__))) .'/lib/adminlib.php');
    require_once (dirname(dirname(dirname(__FILE__))) .'/lib/sessionlib.php');
	
	
    $id = optional_param('id', 0, PARAM_INT);
    $mod = optional_param('mod', "", PARAM_TEXT);
    $title = optional_param('title', "", PARAM_TEXT);
    $timedue = optional_param('timedue', "", PARAM_TEXT);
    $timeavailable = optional_param('timeavailable', "", PARAM_TEXT);
    $popupwidth = optional_param('popupwidth', 0, PARAM_INT);
    $type = optional_param('type', 1, PARAM_INT);
    $popupheight = optional_param('popupheight', 0, PARAM_INT);
    $popupx = optional_param('popupx', 0, PARAM_INT);
    $popupy = optional_param('popupy', 0, PARAM_INT);
    $availablescroll = optional_param('availablescroll', 0, PARAM_INT);
    $description    = optional_param_array('editor',"", PARAM_CLEANHTML);
	$context =  context_system::instance();

	$PAGE->set_context($context);
	

    $popup = new stdClass();
    
    $popup->isactive = 1;
    $popup->title = $title;

    list($y, $m, $d) = explode('-', $timedue);
    $popup->timedue = mktime(0, 0, 0, $m, $d+1, $y)-1;

    list($y, $m, $d) = explode('-', $timeavailable);
    $popup->timeavailable =  mktime(0, 0, 0, $m, $d, $y);

    $popup->popupwidth = $popupwidth;
    $popup->popupheight = $popupheight;
	$popup->type = $type;
    $popup->popupx = $popupx;
    $popup->popupy = $popupy;
    $popup->cookieday = 1;
    $popup->user = $USER->id;
    $popup->timecreated = time();
    $popup->timemodified = time();
    $popup->availablescroll = (!$availablescroll)?0:$availablescroll;
	
    if($mod == 'edit' && !empty($id)){
		
        $popup->id = $id;
	// 	$currenttext = file_prepare_draft_area($description['itemid'], $context->id, 'local_popup', 'popup', empty($popup->id) ? null : $popup->id, null,$description['text']);
		$popup->description = file_save_draft_area_files($description['itemid'], $context->id, 'local_popup', 'popup', $popup->id, null, $description['text']);
        $DB->update_record('popup', $popup);
    }else{
        $newpopup = $DB->insert_record('popup', $popup);
		
		$text = file_save_draft_area_files($description['itemid'], $context->id, 'local_popup', 'popup', $newpopup, null, $description['text']);
		$DB->set_field('popup', 'description', $text, array('id'=>$newpopup));
    }
?>
<script>
    window.onload = function(){
        location.href = '<?php echo $CFG->wwwroot;?>/siteadmin/support/popup.php'
    }
</script>