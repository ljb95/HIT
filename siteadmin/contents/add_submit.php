<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';
    require_once $CFG->dirroot . '/lib/form/filemanager.php';
    require_once $CFG->dirroot . '/lib//filelib.php';
    require_once $CFG->dirroot . '/local/repository/lib.php';
    
    $context = context_system::instance();        
    $fs = get_file_storage();
    $mod = optional_param("mode", 'add', PARAM_RAW);
    $id = optional_param("id", 0, PARAM_INT);
    $ref = optional_param("ref", 0, PARAM_INT);
    if($mod != 'edit'){
    $newconid = upload_lcms_contents_inadmin($_POST,$id);
    
        if(!empty($_FILES['script']['tmp_name'])){
            $file_record = array(
                                'contextid'   => $context->id,
                                'component'   => 'local_repository',
                                'filearea'    => 'subtitle',
                                'itemid'      => $newconid,
                                'filepath'    => '/',
                                'filename'    => $_FILES['script']['name'],
                                'timecreated' => time(),
                                'timemodified'=> time(),
                                'userid'      => $USER->id,
                                'author'      => fullname($USER),
                                'license'     => 'allrightsreserved',
                                'sortorder'   => 0
                            );
            $storage_id = $fs->create_file_from_pathname($file_record, $_FILES['script']['tmp_name']);
        }
        $repository = $DB->get_field('lcms_repository','id',array('lcmsid'=>$newconid));
        if($mod == 'ref'){
            redirect('detail.php?id='.$id);
        } else {
            redirect('detail.php?id='.$repository);
        }
    } else {
        
        if (!$ref) {
            $sql = "select "
                    . "rep.id , rep.referencecnt , rep.iscdms , rep.status, rep.delaymsg , "
                    . "con.id as con_id,con.con_name,con.con_type,con.con_des,con.update_dt,con.data_dir,con.embed_type,con.embed_code, "
                    . "rep_group.name as gname "
                    . "from {lcms_repository} rep "
                    . "join {lcms_contents} con on con.id= rep.lcmsid "
                    . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
                    . "where rep.id= :id";
        } else {
            $sql = "select "
                    . "rep.id , "
                    . "con.id as con_id,con.con_name,con.con_type,con.con_des,con.update_dt,con.data_dir,con.embed_type,con.embed_code,"
                    . "rep_group.name as gname "
                    . "from {lcms_repository_reference} rep "
                    . "join {lcms_contents} con on con.id= rep.lcmsid "
                    . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
                    . "where rep.id= :id";
        }

        $data = $DB->get_record_sql($sql, array('id' => $id));
        
        if(empty($_POST['groupid'])){
            $_POST['groupid'] = 0;
        }
        $_POST['con_id'] = $data->con_id;
        
        $newid = update_lcms_contents_inadmin($_POST);
        
        if(!empty($_FILES['script']['tmp_name'])){
             $overlap_files = $DB->get_records('files', array('itemid'=> $id,'component'=>'local_repository'));
            foreach($overlap_files as $file){
                $fs->get_file_instance($file)->delete();
            }
        }
        if(!empty($_FILES['script']['tmp_name'])){
            $file_record = array(
                                'contextid'   => $context->id,
                                'component'   => 'local_repository',
                                'filearea'    => 'subtitle',
                                'itemid'      => $newid,
                                'filepath'    => '/',
                                'filename'    => $_FILES['script']['name'],
                                'timecreated' => time(),
                                'timemodified'=> time(),
                                'userid'      => $USER->id,
                                'author'      => fullname($USER),
                                'license'     => 'allrightsreserved',
                                'sortorder'   => 0
                            );
            $storage_id = $fs->create_file_from_pathname($file_record, $_FILES['script']['tmp_name']);
        }
        if(!$ref){
            redirect('detail.php?id='.$data->id);
        } else {
             redirect('detail.php?id='.$id.'&ref='.$ref);
        }
    }
    