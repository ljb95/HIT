<?php

require_once("../../config.php");
require_once($CFG->dirroot."/course/lib.php");
require_once $CFG->dirroot . '/local/repository/lib.php';

$id = optional_param('id',0,PARAM_INT);
$delete_array = optional_param_array('check', array(), PARAM_RAW);
$ref = optional_param('ref',0,PARAM_INT);

if(!$ref){

if($id){
    $delete_array = array($id=>'on');
}

foreach ($delete_array as $key => $val) {

    $repository = $DB->get_record('lcms_repository',array('lcmsid'=>$key));

    $id = $repository->id;
          
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
            join {lcms} l on l.id = cm.instance and l.contents = :conid;";
        $lcms_cms = $DB->get_records_sql($query,array('conid'=>$repository->lcmsid));
        foreach($lcms_cms as $lcms_cm){
            course_delete_module($lcms_cm->id);
        }
        
        
        $fs = get_file_storage();
        $captions = $fs->get_area_files($context->id, 'local_repository', 'subtitle', $repository->lcmsid, 'id');
        if(!empty($captions)) {
            foreach($captions as $caption) {
                    $caption->delete();
            }
        }
        $DB->delete_records('lcms_repository', array('id' => $id));  
        insert_lcms_history($repository->lcmsid,'Delete lcms content in multiple',4);
}
insert_lcms_history(0,'Delete lcms contents in admin',4);
} else {
    
        $id = $DB->get_field('lcms_repository_reference','id',array('lcmsid'=>$id));
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
        insert_lcms_history($repository->lcmsid,'Delete lcms ref In admin',4);
        redirect('detail.php?id='.$parent->id);
}
redirect('index.php');