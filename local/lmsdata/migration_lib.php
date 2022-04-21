<?php

function fileCopy($olddata_dir, $newdata_dir) {
    if (filetype($olddata_dir) === 'dir') {
        clearstatcache();
        if ($fp = @opendir($olddata_dir)) {
            while (false !== ($ftmp = readdir($fp))) {
                if (($ftmp !== ".") && ($ftmp !== "..") && ($ftmp !== "")) {
                    if (filetype($olddata_dir . '/' . $ftmp) === 'dir') {
                        clearstatcache();

                        @mkdir($newdata_dir . '/' . $ftmp);
                        set_time_limit(0);
                        fileCopy($olddata_dir . '/' . $ftmp, $newdata_dir . '/' . $ftmp);
                    } else {
                        copy($olddata_dir . '/' . $ftmp, $newdata_dir . '/' . $ftmp);
                    }
                }
            }
        }
        if (is_resource($fp)) {
            closedir($fp);
        }
    } else {
        copy($olddata_dir, $newdata_dir);
    }
}

function set_thumbnail_filename($newdata_dir) {
    $filename = '';
    if (filetype($newdata_dir) === 'dir') {
        clearstatcache();
        if ($fp = @opendir($newdata_dir)) {
            while (false !== ($ftmp = readdir($fp))) {
                if (($ftmp !== ".") && ($ftmp !== "..") && ($ftmp !== "")) {
                    if (is_file($newdata_dir . '/' .$ftmp)) {
                        $fileinfo = pathinfo($newdata_dir . '/' . $ftmp);
                        if($fileinfo['extension'] == 'mp4' || $fileinfo['extension'] == 'flv') {
                           $filename = $fileinfo['filename'];
                        }
                    }
                }
            }
        }
        
        if (is_resource($fp)) {
            closedir($fp);
        }
    }
    
     if (filetype($newdata_dir) === 'dir') {
        clearstatcache();
        if ($fp = @opendir($newdata_dir)) {
            while (false !== ($ftmp = readdir($fp))) {
                if (($ftmp !== ".") && ($ftmp !== "..") && ($ftmp !== "")) {
                    if (is_file($newdata_dir . '/' .$ftmp)) {
                        $fileinfo = pathinfo($newdata_dir . '/' . $ftmp);
                        if(strtolower($fileinfo['extension']) == 'jpg' || strtolower($fileinfo['extension']) == 'png') {
                           rename($newdata_dir.'/'.$fileinfo['basename'], $newdata_dir.'/'.$filename.'.'.strtolower($fileinfo['extension']));
                        }
                    }
                }
            }
        }
        
        if (is_resource($fp)) {
            closedir($fp);
        }
    }
}

function get_indexfile($newdata_dir){
    $indexdata = new stdClass();
    if (filetype($newdata_dir) === 'dir') {
        clearstatcache();
        if ($fp = @opendir($newdata_dir)) {
            while (false !== ($ftmp = readdir($fp))) {
                if (($ftmp !== ".") && ($ftmp !== "..") && ($ftmp !== "")) {
                    if (is_file($newdata_dir . '/' .$ftmp)) {
                       $fileinfo = pathinfo($newdata_dir . '/' . $ftmp);
                       if((strtolower($fileinfo['basename']) == 'index.html') || ($fileinfo['extension'] == 'mp4') || ($fileinfo['extension'] == 'flv')) {
                           $indexdata->copyfilename = $fileinfo['basename'];
                           $indexdata->copyfileoname = $fileinfo['basename'];
                           $indexdata->copyfilesize = filesize($newdata_dir.'/'.$ftmp);
                       }
                    }
                }
            }
        }
        
        if (is_resource($fp)) {
            closedir($fp);
        }
    }
 
return $indexdata;
}

function get_pakage_dirname($newdata_dir){
    $dirname = array();
    if (filetype($newdata_dir) === 'dir') {
        clearstatcache();
        if ($fp = @opendir($newdata_dir)) {
            while (false !== ($ftmp = readdir($fp))) {
                if (($ftmp !== ".") && ($ftmp !== "..") && ($ftmp !== "")) {
                    if (is_file($newdata_dir . '/' .$ftmp)) {
                       $fileinfo = pathinfo($newdata_dir . '/' . $ftmp);
                       if($fileinfo['extension'] == 'html') {
                           $substring = substr($fileinfo['basename'], 0, 2);
                           if(!empty($dirname[$substring])) {
                               $dirname[$substring]++;
                           }else {
                               $dirname[$substring] = 1;
                           }
                       }
                    }
                }
            }
        }
        
        if (is_resource($fp)) {
            closedir($fp);
        }
    }
 
return max(array_keys($dirname, max($dirname)));
}