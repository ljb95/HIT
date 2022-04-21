<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/lib/phpexcel/PHPExcel/Shared/PCLZip/pclzip.lib.php';
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/IOFactory.php');
require_once($CFG->libdir . '/excellib.class.php');
require_once $CFG->dirroot . '/lib/form/filemanager.php';
require_once $CFG->dirroot . '/local/repository/lib.php';

$type = optional_param('con_type', 'video', PARAM_RAW);

$temp_dir = $CFG->dirroot . '/siteadmin/contents/contents_upload/server/php/files/' . $USER->id . '/';
$count = 0;
if (is_dir($temp_dir)) {
    $dirs = dir($temp_dir);
    while (false !== ($entry = $dirs->read())) {
        if (($entry != '') && ($entry != '.') && ($entry != '..')) {
            //파일정보가져오기
            $path = pathinfo($temp_dir . $entry);
            $path_dir = $path['dirname'] . '/' . $path['basename'];
            $zipfile = new PclZip($path_dir);
            $extract = $zipfile->extract(PCLZIP_OPT_PATH, $temp_dir);
            @unlink($path_dir);
            break;
        }
    } // while end
    $dirs = dir($temp_dir);
    $items = array();
    while (false !== ($entry = $dirs->read())) {
        if (($entry != '') && ($entry != '.') && ($entry != '..')) {
            //파일정보가져오기
            $path = pathinfo($temp_dir . $entry);
            if ($path['extension'] == 'xlsx') {
                $items['base'] = $path['dirname'] . '/' . $path['basename'];
            } else {
                $items[] = $path['dirname'] . '/' . $path['basename'];
            }
        }
    } // while end
}

$objReader = PHPExcel_IOFactory::createReaderForFile($items['base']);
$objReader->setReadDataOnly(true);
$objExcel = $objReader->load($items['base']);

$objExcel->setActiveSheetIndex(0);
$objWorksheet = $objExcel->getActiveSheet();
$rowIterator = $objWorksheet->getRowIterator();

$rowIterator = $objWorksheet->getRowIterator();

foreach ($rowIterator as $row) { // 모든 행에 대해서
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
}

$maxRow = $objWorksheet->getHighestRow();
for ($i = 2; $i <= $maxRow; $i++) {
        $file_data = new stdClass();
        $file_data->area_cd = 1;
        $file_data->major_cd = 1;
        $file_data->course_cd = $USER->id;
        $file_data->user_no = $USER->id;
        $file_data->con_hit = 0;
        $file_data->reg_dt = time();
        $file_data->update_dt = time();
    if ($type == 'embed') {
        $file_data->userid = $USER->id;
        $file_data->teacher = $objWorksheet->getCell('C' . $i)->getValue();
        $file_data->share_yn = $objWorksheet->getCell('F' . $i)->getValue();

        $file_data->con_name = $objWorksheet->getCell('A' . $i)->getValue();

        $file_data->con_type = $type;
        $file_data->con_total_time = 0;

        $file_data->con_des = $objWorksheet->getCell('B' . $i)->getValue();
        $file_data->author = $objWorksheet->getCell('D' . $i)->getValue();
        $file_data->cc_mark = $objWorksheet->getCell('E' . $i)->getValue();
        $file_data->embed_type = $objWorksheet->getCell('G' . $i)->getValue();
        $file_data->embed_code = $objWorksheet->getCell('H' . $i)->getValue();
        $file_data->data_dir = '';
        $newconid = lcms_insert_db_inadmin($file_data, $id);
    } else {
        $file_data->userid = $USER->id;
        $file_data->teacher = $objWorksheet->getCell('C' . $i)->getValue();
        $file_data->share_yn = $objWorksheet->getCell('F' . $i)->getValue();

        $file_data->con_name = $objWorksheet->getCell('A' . $i)->getValue();

        $file_data->con_type = $type;
        $file_data->con_total_time = strtotime($objWorksheet->getCell('G' . $i)->getValue());

        $file_data->con_des = $objWorksheet->getCell('B' . $i)->getValue();
        $file_data->author = $objWorksheet->getCell('D' . $i)->getValue();
        $file_data->cc_mark = $objWorksheet->getCell('E' . $i)->getValue();


        $file = new stdClass();
        $file->name = $objWorksheet->getCell('H' . $i)->getValue();

        $path = 'lms/' . $USER->id . 'u' . date('ymdAh') . 'r' . mt_rand(1, 99);
        $returnpath = $CFG->wwwroot . '/local/repository/return_file_data.php';

        $file_data->data_dir = $path;

        $newconid = $DB->insert_record('lcms_contents', $file_data);
        mkdir('/video/' . $path,0777);
        copy($temp_dir . $file->name, '/video/' . $path.'/'. $file->name);
        
        $post_data  = array(
            'filename' => trim($file->name),
            'path' => trim($path),
            'userid' => trim($USER->id),
            'returnpath' => trim($returnpath),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://open.jinotech.com:20080/ffmpeg.php");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $string = curl_exec($ch);
        

        if (curl_error($ch)) {
            echo curl_error($ch);
            echo '<br>';
        }

        curl_close($ch);

        $return_path = optional_param('return_path', '', PARAM_RAW);

        $o_file = $file->name;
        $d_num = optional_param('d_num', 0, PARAM_INT);
        $f_num = optional_param('f_num', '', PARAM_RAW);


        $data = new stdClass();
        $data->con_seq = $newconid;
        $data->filepath = $path;
        $data->fileoname = $o_file;

        $name_ary = explode('.', $o_file);
        $ext = $name_ary[count($name_ary) - 1];
        $mp4file = preg_replace('/\.' . $ext . '$/', '.mp4', $o_file);

        $data->filename = $mp4file;
        $data->filesize = '0';
        $data->duration = 0;
        $data->con_type = 'video';
        $data->user_no = $USER->id;
        $up = $DB->insert_record('lcms_contents_file', $data);
    }
    $rep_con_db = new stdClass();
    $rep_con_db->lcmsid = $newconid;
    $rep_con_db->userid = $USER->id;
    /*
      if(empty($contents->groupid))$contents->groupid=0;
      $rep_con_db->groupid = $contents->groupid;
     */
    $rep_con_db->groupid = 0;
    $rep_con_db->referencecnt = 0;

    $new_rep_conid = $DB->insert_record('lcms_repository', $rep_con_db);
    insert_lcms_history($newconid,'Insert lcms content in multiple',1);
}

insert_lcms_history(0,'Use multiple Upload',1);

@shell_exec("rm -rf $temp_dir");

redirect('index.php');
?>
