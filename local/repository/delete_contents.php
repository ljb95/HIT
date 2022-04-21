<?php

require_once("../../config.php");
require_once("./config.php");
require_once("./lib.php");
require_once($CFG->dirroot."/course/lib.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");
require_once($CFG->dirroot . "/lib/filelib.php");

$PAGE->set_url('/local/repository/detail.php');
$PAGE->set_pagelayout('standard');

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

$id = optional_param('id', 0, PARAM_INT); // repository id
$ref = optional_param('ref', 0, PARAM_INT); // repository id
$mode = optional_param('mode', '', PARAM_RAW);

if($mode=='filedel'){
    
    $filedir = required_param('filedir',PARAM_RAW);
    $filedir = rawurldecode($filedir);
    if(file_exists($filedir)){
        @unlink($filedir);
    }
    
}else if($mode=='cfiledel'){
    
    $fileid = required_param('fileid',PARAM_INT);
    $data = $DB->get_record('lcms_contents_file',array('id'=>$fileid));
    $filepath = explode('/',$data->filepath);
    if($filepath[0]=='storage') $lcmsdata = '/';
    else $lcmsdata = '/lcmsdata/';
    $file = STORAGE2 . $lcmsdata . $data->filepath . '/' . $data->filename;
    $filename = $data->fileoname;
    $filesize = filesize($file);
    if(file_exists($file)){
        @unlink($file);
    }
    $DB->delete_records('lcms_contents_file',array('id'=>$fileid));
    
}else{

    if ($ref) {
        $repository = $DB->get_record('lcms_repository_reference',array('id'=>$id));
        $parent = $DB->get_record('lcms_repository',array('id'=>$repository->repository));
        $filedir = $DB->get_field('lcms_contents','data_dir',array('id'=>$repository->lcmsid));
        $DB->delete_records('lcms_repository_reference', array('id' => $ref));
        $DB->delete_records('lcms_contents', array('id' => $repository->lcmsid));
        $DB->delete_records('lcms_contents_file', array('con_seq' => $repository->lcmsid));     

        $filedir = rawurldecode(STORAGE.'/'.$filedir.'/');
        if(is_dir($filedir)){
            delete_tree($filedir);
        }
        $DB->set_field('lcms_repository','referencecnt',$parent->referencecnt -1 ,array('id'=>$parent->id));
        insert_lcms_history($repository->lcmsid,'Delete lcms ref In repository',4);
        redirect('detail.php?id='.$parent->id);
    } else {
        $repository = $DB->get_record('lcms_repository',array('id'=>$id));
          
        /* ref delete */
        $rrefs = $DB->get_records('lcms_repository_reference',array('repository'=>$id));
        foreach($rrefs as $rref){
            $filedir = $DB->get_field('lcms_contents','data_dir',array('id'=>$rref->lcmsid));
            $DB->delete_records('lcms_contents', array('id' => $rref->lcmsid));
            $DB->delete_records('lcms_contents_file', array('con_seq' => $rref->lcmsid));     
            $filedir = rawurldecode(STORAGE.'/'.$filedir.'/');
            if(is_dir($filedir)){
                delete_tree($filedir);
            }
        }
        $DB->delete_records('lcms_repository_reference', array('repository' => $id));
        
        
        $filedir = $DB->get_field('lcms_contents','data_dir',array('id'=>$repository->lcmsid));
        $DB->delete_records('lcms_contents', array('id' => $repository->lcmsid));
        $DB->delete_records('lcms_contents_file', array('con_seq' => $repository->lcmsid));
        
        $filedir = rawurldecode(STORAGE.'/'.$filedir.'/');
        if(is_dir($filedir)){
              delete_tree($filedir);
        }
        
        $query = "select cm.id,l.name from {course_modules} cm 
            join {modules} m on m.id = cm.module and m.name = 'lcms' 
            join {lcms} l on l.id = cm.instance and l.contents = :conid";
        $lcms_cms = $DB->get_records_sql($query,array('conid'=>$repository->lcmsid));
        foreach($lcms_cms as $lcms_cm){
            course_delete_module($lcms_cm->id);
        }
        
        
        $fs = get_file_storage();
        $captions = $fs->get_area_files($context->id, 'local_repository', 'subtitle', $repository->id, 'id');
        if(!empty($captions)) {
            foreach($captions as $caption) {
                    $caption->delete();
            }
        }
        $DB->delete_records('lcms_repository', array('id' => $id));
        insert_lcms_history($repository->lcmsid,'Delete lcms content In repository',4);
    }
    
  
}    
redirect('index.php','',0);