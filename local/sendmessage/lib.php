<?php
function local_sendmessage_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) { 
	global $CFG, $DB;

	$fileareas = array('attachment');
	if (!in_array($filearea, $fileareas)) {
		return false;
	} 


	$fs = get_file_storage();
	$relativepath = implode('/', $args);

	$fullpath = "/$context->id/local_sendmessage/$filearea/$relativepath";
	if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
		return false;
	}


	// finally send the file
	send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}