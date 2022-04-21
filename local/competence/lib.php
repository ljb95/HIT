<?php

/**
 * array로 전달받은 courseid 값에 있는 grade_item 갯수를 course id 별로 반환
 * @param array $courseid
 * @return array
 */

function local_competence_get_activity_grade_count($courses) {
    global $DB;
    
    $activity_grade_count = array();
    
    list($sql_in, $params) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'courseid');
    $sql_conditions[] = ' courseid '.$sql_in;
    
    $sql_select = 'SELECT courseid, COUNT(*) as cnt FROM {grade_items} ';

    $params['itemtype'] = 'course';
    $sql_conditions[] = ' itemtype <> :itemtype ';
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_groupby = ' GROUP BY courseid ';
    
    $activity_grade_count = $DB->get_records_sql($sql_select.$sql_where.$sql_groupby, $params);
    
    return $activity_grade_count;
}

/**
 * user가 등록되어 있는 강의목록을 전달 받아 강의에 있는 lcms 총갯수와 총점을 반환
 * @param int $userid
 * @param array $courseid
 * @return array
 */

function local_competence_get_course_lcms_progress($userid, $courses) {
    global $DB;

    $activity_grade_count = array();
    
    list($sql_in, $params) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'course');
    $sql_conditions[] = ' lc.course '.$sql_in;
    $sql_select = 'SELECT 
                        lc.course, count(lc.course) as lccount, sum(lt.progress) as sumprogress 
                   FROM {lcms} lc
                   JOIN {lcms_track} lt ON lt.lcms = lc.id ';

    $params['userid'] = $userid;
    $sql_conditions[] = ' lt.userid = :userid ';
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_groupby = ' GROUP BY lc.course ';
    $activity_grade_count = $DB->get_records_sql($sql_select.$sql_where.$sql_groupby, $params);
    
    return $activity_grade_count;
}

/**
 * user가 등록되어 있는 강의의 역량 목록을 받아서 갯수 정렬
 * @param array $competencies
 * @return array
 */

function local_competence_user_competence_order($competencies) {
    
    foreach($competencies as $comp) {
        $competencyid = $comp->competencyid;
        $array_comp[$competencyid] = $competencyid;
        
        //해당되는 frameworkid list
        $compframework = $comp->competencyframeworkid;
        $array_frame[$compframework] = $compframework;
    }
    
    $framework_comp = local_competence_framework_comp_sort($array_frame);
    
    return $framework_comp;
}

/**
 * competency frameworkid 배열을 받아 framework에 해당하는 모든 역량을 가져와 최하위 역량의 갯수를 반환
 * @param array $frameworkids
 * @return array
 */

function local_competence_framework_comp_sort($frameworkids) {
    global $DB;
    
    list($sql_in, $params) = $DB->get_in_or_equal($frameworkids, SQL_PARAMS_NAMED, 'frameworkid');
    $sql_conditions[] = ' competencyframeworkid '.$sql_in;
   
    $sql_select = 'SELECT * FROM {competency} ';

    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $orderby = ' ORDER BY length(path) DESC, parentid ASC ';
    $framework_comp = $DB->get_records_sql($sql_select.$sql_where.$orderby, $params);
    
    $max_depth = 1;
    foreach($framework_comp as $comp) {
        $parentid = $comp->parentid;
        $comp->depth = substr_count($comp->path, '/', 1);
        
        if($comp->depth > $max_depth) {
            $max_depth = $comp->depth;
        }
        
    }
    
    $framework_count = array();
    $comp_count = array();
    foreach($framework_comp as $comp) {
        //last competency cehck
        if(local_competence_is_last_comp($comp, $framework_comp)) {
            $parentid = $comp->parentid;
            if(isset($comp_count[$parentid])) {
                $comp_count[$parentid]->count++;
            }else{
                $count = new Stdclass();
                $count->count = 1;
                $count->parentid = $parentid;
                $comp_count[$parentid] = $count;
            }
        } else {
            $id = $comp->id;
            $parentid = $comp->parentid;
            if(empty($comp_count[$id])) {
                $count = new Stdclass();
                $count->count = 0;
                $count->parentid = $parentid;
                $comp_count[$parentid] = $count;
            } else {
                $count = new Stdclass();
                $count->count = $comp_count[$parentid]->count + $comp_count[$id]->count;
                $count->parentid = $parentid;
                $comp_count[$parentid] = $count;
            }
        } 
        
        $frameid = $comp->competencyframeworkid;
        $framework_count[$frameid] = $comp_count;
    }
    return $framework_count;
}

/**
 * 해당 역량이 dapth가 가장 낮은 노드인지 반환
 * @param object $comp
 * @param array $framework_comp $comp object array
 * @return boole
 */

function local_competence_is_last_comp($comp, $framework_comp){
    foreach($framework_comp as $framework) {
        if($framework->parentid == $comp->id) {
            return false;
        }
    }
    
    return true;
}

/**
 * 역량 프레임웍의 역량 수 반환 
 * @param int $frameworkid
 * @param int $compid $comp 
 * @param array $framework_count  
 * @return int
 */

function local_competence_count($frameworkid, $compid, $framework_count){
    
    $count = $framework_count[$frameworkid][$compid]->count;

    if(is_null($count)) {
        $total_count = 1;
    }else {
        $total_count = $count;
    }
    
    return $total_count;
}

/**
 * user의 해당 강의 역량 성취율을 계산하여 반환
 * @param array $competencies
 * @return array
 */

function local_competence_user_competency_achieve($competencies) {
    //framework에 있는 역량 수
    $framework_count = local_competence_user_competence_order($competencies);
    $achieve_arr = array();
    $framework_arr = array();
    foreach($competencies as $comp) {
        $frameworkid = $comp->competencyframeworkid;
        $competencyid = $comp->competencyid;
        if($comp->proficiency) {
            $achieve_count += local_competence_count($frameworkid, $competencyid, $framework_count);
        }
        $achieve_arr[$comp->courseid]->achieve = $achieve_count;
        $achieve_arr[$comp->courseid]->total += local_competence_count($frameworkid, $competencyid, $framework_count);
    }
    
    foreach($achieve_arr as $achieve) {
        $percent = ($achieve->achieve / $achieve->total) * 100;
        $achieve->percent = round($percent, 1);
    }
    return $achieve_arr;
}

/**
 * user의 역량 프레임웍 목록
 * @param array $competencies
 * @return array
 */

function local_competence_get_framework_list($competencies) {
    if(!isset($competencies)) {
        return null;
    }
    
    $freamework_list = array();
    foreach($competencies as $competence) {
        $freamework_list[$competence->competencyframeworkid] = $competence->fname;
    }
    return $freamework_list;
}

/**
 * 역량 별 목록
 * @param array $competencies
 * @return array
 */

function local_competence_get_status($competencies, $frameid) {
    if(!isset($competencies)) {
        return null;
    }
    $framework_comp = local_competence_get_freamework_comp($competencies, $frameid);
    $competencies = local_competence_set_depth($competencies);
    $usercomp_arr = array();
    
    foreach($competencies as $comp) {
       $path_arr = explode('/', $comp->path);
       $childcount = $framework_comp[$comp->competencyid]->childcount;
       
       if(empty($childcount)) {
           $childcount = 1;
       } else {
           $children =  $framework_comp[$comp->competencyid]->child;
           foreach($children as $child) {
               $child_count = $framework_comp[$child]->childcount;
               if(empty($child_count)) {
                   $framework_comp[$child]->setcount += 1;
               } 
               
               $framework_comp[$child]->setcount += $child_count;
                if(!empty($framework_comp[$comp->competencyid]->proficiency)) {
                    $framework_comp[$child]->setcount += $child_count;
                }
               
           }
       }
       
       foreach($path_arr as $path) {
           if(!empty($path)) {
               $framework_comp[$path]->setcount += $childcount;
               if(!empty($comp->proficiency)) {
                   $framework_comp[$path]->completecount += $childcount;
               }
           }
       }
       
       $framework_comp[$comp->competencyid]->setcount += $childcount;
       if(!empty($comp->proficiency)) {
           $framework_comp[$comp->competencyid]->completecount += $childcount;
       }
     
                
    }
    return $framework_comp;
}
/**
 * 프레임웍 역량의 하위역량 목록
 * @param array $competencies
 * @return array
 */

function local_competence_get_freamework_comp($competencies, $frameid) {
    global $DB;
    $fids = array();
    if($frameid) {
        $fids = array($frameid => $frameid);
    } else {
        foreach($competencies as  $competence) {
            $fids[$competence->competencyframeworkid] = $competence->competencyframeworkid;
        }
        
    }
    list($sql_in, $params) = $DB->get_in_or_equal($fids, SQL_PARAMS_NAMED, 'freameworks');
    $sql_conditions[] = ' cf.id '.$sql_in;
    
    $sql_select = 'SELECT com.* FROM {competency_framework} cf
                   JOIN {competency} com ON com.competencyframeworkid = cf.id ';

    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = 'ORDER BY com.idnumber ';
    $freamework_comp = $DB->get_records_sql($sql_select.$sql_where.$sql_orderby, $params);
    
    $freamework_comp = local_competence_set_depth($freamework_comp);
    
    $comptree = array();
    foreach($freamework_comp as $comp) {
        $islastcomp = local_competence_is_last_comp($comp, $freamework_comp);
        if($comp->depth != 1 && $islastcomp) {
            
            $path = str_replace('/0/', '', $comp->path);
            $path_arr = explode('/', $path);
            $parentdepth = 1;
            foreach($path_arr as $parentid) {
                if(!empty($parentid)) {
                    if(isset($comptree[$parentid])) {
                        $comptree[$parentid]->child[$comp->id] = $comp->id;
                        $comptree[$parentid]->childcount++;
                    } else {
                        $tcomp = new Stdclass();
                        $tcomp->id = $parentid;
                        $tcomp->child = array($comp->id => $comp->id);
                        $tcomp->childcount = 1;
                        $tcomp->shortname = $freamework_comp[$parentid]->shortname;
                        $tcomp->depth = $parentdepth;
                        $tcomp->setcount = 0;
                        $tcomp->completecount = 0;
                        $tcomp->lastcomp = false;
                        $comptree[$parentid] = $tcomp;
                    }
                    $parentdepth++;
                }
            }
            
            $tcomp = new Stdclass();
            $tcomp->id = $comp->id;
            $tcomp->child = array();
            $tcomp->childcount = 0;
            $tcomp->shortname = $freamework_comp[$comp->id]->shortname;
            $tcomp->depth = $comp->depth;
            $tcomp->setcount = 0;
            $tcomp->completecount = 0;
            $tcomp->lastcomp = true;
            $comptree[$comp->id] = $tcomp;
        }
    }
    
    foreach($freamework_comp as $comp) {
       $comptree[$comp->id]->path = str_replace('/0/', '', $comp->path);
    }
    
    return $comptree;
}

/**
 * competencies에 depth 추가
 * @param array $competencies
 * @return array
 */

function local_competence_set_depth($competencies) {
    if(!isset($competencies)) {
        return null;
    }
    foreach($competencies as $comp) {
        $comp->depth = substr_count($comp->path, '/', 1);
    }
    
    return $competencies;
}

