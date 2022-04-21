<?php


defined('MOODLE_INTERNAL') || die();

class local_jinoanalytics {
    private $courseid;
    private $course_info;
    private $modules;
    private $display_modules;
    
    function __construct($course, $display_modules = array('assign', 'forum', 'quiz', 'wiki', 'feedback', 'url', 'book', 'resource')) {
        if (is_object($course) && $course->id) $this->courseid = $course->id;
        else $this->courseid = $course;
        $this->display_modules = $display_modules;
        $this->course_info = $this->get_course_info();
        $this->modules = $this->get_module_info();
        
    }
    
    /**
     * 모듈 정보
     * @global type $DB
     * @return type
     */
    public function get_institution_table(){
        return (object)array('data'=>'is_null');
    }
    public function get_module_info($courseid = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        if ($courseid == $this->courseid && !empty($this->modules)) return $this->modules;
        if ($modules = $DB->get_records_sql("SELECT name, 0 cnt FROM {modules} WHERE visible=:visible order by name", array('visible'=>1))) {
            $sql = "SELECT m.name, count(*) cnt 
            FROM {course_modules} cm
            JOIN {modules} m on m.id = cm.module AND cm.course = :courseid AND m.visible = :visible GROUP BY m.name";
            if ($mod_count = $DB->get_records_sql($sql, array('courseid'=>$courseid, 'visible'=>1))) {
                foreach($modules as $key=>$value) {
                    $value->name = get_string('pluginname',$value->name);
                    if (!empty($mod_count[$key])) $value->cnt = $mod_count[$key]->cnt;
                }
            }
        }
        $this->modules = $modules;
        return $modules;
    }
    
    public function diplay_activity_table($mod_name = null, $courseid = null) {
        global $DB, $CFG;
        if (empty($courseid)) $courseid = $this->courseid;
        switch($mod_name) {
            case 'assign':
//                                $sql = "select
//                        concat(cs.section, '_', cm.id, '_', a.id) seq,  
//                        cs.section, cm.id cmid, a.id mod_id, gi.id itemid, a.name,
//                        count(ra.id) student_cnt, count(s.id) submitted_cnt, count(gg.finalgrade) grade_cnt, round(gi.grademax,2) max_grade, round(avg(gg.finalgrade),2) avg_score
//                    from {course} c
//                    join {context} ctx on ctx.instanceid = c.id and ctx.contextlevel = :contextlevel and c.id = :courseid
//                    join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = :roleid
//                    join {course_modules} cm on cm.course = c.id
//                    join {modules} m on m.id = cm.module and m.name = :mod_name
//                    join {course_sections} cs on cs.id = cm.section
//                    join {assign} a on a.id = cm.instance 
//                    left join {assign_submission} s on s.assignment = a.id and s.status = 'submitted' and s.userid = ra.userid 
//                    left join {assign_grades} g on g.assignment = a.id and g.userid = ra.userid
//                    left join {grade_items} gi on gi.iteminstance = a.id and gi.itemtype = 'mod' and gi.itemmodule = m.name
//                    join {grade_grades} gg on gg.itemid = gi.id and gg.userid = ra.userid 
//                    group by cs.section, cm.id, a.id
//                    order by cs.section";
//                if ($datas = $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'courseid'=>$courseid, 'roleid'=>$CFG->UB->ROLE->STUDENT, 'mod_name'=>$mod_name))) {
//                    foreach($datas as $value) {
//                        $result[$value->section][$value->cmid] = $value;
//                    }
//                    unset($datas);
//                    
//                    $loop_cnt = $total_student_cnt = $total_submitted_cnt = $total_submitted_percent = $total_grade_cnt = $total_score = 0;
//                    foreach($result as $section=>$modules) {
//                        $row_span = count($modules);
//                        echo "<tr>";
//                        echo "<td rowspan='{$row_span}'>".get_string('numweek', 'local_jinoanalytics', $section)."</td>";
//                        $first_row = true;
//                        foreach($modules as $cmid=>$module) {
//                            if (!$first_row) echo "<tr>"; $first_row = false;
//                            if ($module->submitted_cnt == 0 || $module->student_cnt == 0) $percent_submitted = 0;
//                            else $percent_submitted = $module->submitted_cnt*100/$module->student_cnt;
//                            if (empty($module->avg_score)) $module->avg_score = 0;
//                            
//                            echo "<td class='align-left'><a href='/mod/{$mod_name}/view.php?id={$module->cmid}' target='_blank'>{$module->name}</a></td>";
//                            echo "<td>{$module->student_cnt}</td>";
//                            echo "<td>{$module->submitted_cnt}</td>";
//                            echo "<td>".sprintf("%02.2f", $percent_submitted)."%</td>";
//                            echo "<td><a href='/grade/report/singleview/index.php?id={$courseid}&item=grade&itemid={$module->itemid}' target='_blank' class='btn-analytics'>{$module->grade_cnt}</a></td>";
//                            echo "<td class='align-right'>{$module->avg_score} / {$module->max_grade}</td>";
//                            echo "</tr>";
//                            
//                            $loop_cnt ++;
//                            $total_student_cnt += $module->student_cnt;
//                            $total_submitted_cnt += $module->submitted_cnt;
//                            $total_submitted_percent += $percent_submitted;
//                            $total_grade_cnt += $module->grade_cnt;
//                            $total_score += $module->avg_score * 100 / $module->max_grade;
//                        }
//                    }
//                    
//                    $total_student_cnt = sprintf("%02.2f", $total_student_cnt / $loop_cnt);
//                    $total_submitted_cnt = sprintf("%02.2f", $total_submitted_cnt / $loop_cnt);
//                    $total_submitted_percent = sprintf("%02.2f", $total_submitted_percent / $loop_cnt);
//                    $total_grade_cnt = sprintf("%02.2f", $total_grade_cnt / $loop_cnt);
//                    $total_score = sprintf("%02.2f", $total_score / $loop_cnt);
//
//                    echo "<tr><th colspan=2>".get_string('average', 'local_jinoanalytics')."</th><th>{$total_student_cnt}</th><th>{$total_submitted_cnt}</th><th>{$total_submitted_percent}%</th><th>{$total_grade_cnt}</th><th>{$total_score}%</th></tr>";
//                } else {
//                    echo "<tr><td colspan=7>".get_string('assignment_nocreated', 'local_jinoanalytics')."</td></tr>";
//                }
                break;
            case 'quiz':
//                $sql = "select 
//                        concat(cs.section, '_', cm.id, '_', a.id) seq,  
//                        cs.section, cm.id cmid, a.id mod_id, gi.id itemid, a.name, 
//                        count(ra.id) student_cnt, count(s.id) submitted_cnt, count(gg.finalgrade) grade_cnt, round(gi.grademax,2) max_grade, round(avg(gg.finalgrade),2) avg_score
//                    from {course} c
//                    join {context} ctx on ctx.instanceid = c.id and ctx.contextlevel = :contextlevel and c.id = :courseid
//                    join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = :roleid
//                    join {course_modules} cm on cm.course = c.id
//                    join {modules} m on m.id = cm.module and m.name = :mod_name
//                    join {course_sections} cs on cs.id = cm.section
//                    join {quiz} a on a.id = cm.instance 
//                    left join {quiz_attempts} s on s.quiz = a.id and s.userid = ra.userid and s.attempt = 1 and s.state = 'finished'
//                    left join {grade_items} gi on gi.iteminstance = a.id and gi.itemtype = 'mod' and gi.itemmodule = m.name 
//                    join {grade_grades} gg on gg.itemid = gi.id and gg.userid = ra.userid
//                    group by cs.section, cm.id, a.id
//                    order by cs.section";
//                if ($datas = $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'courseid'=>$courseid, 'roleid'=>$CFG->UB->ROLE->STUDENT, 'mod_name'=>$mod_name))) {
//                    foreach($datas as $value) {
//                        $result[$value->section][$value->cmid] = $value;
//                    }
//                    unset($datas);
//                    
//                    $loop_cnt = $total_student_cnt = $total_submitted_cnt = $total_submitted_percent = $total_grade_cnt = $total_score = 0;
//                    foreach($result as $section=>$modules) {
//                        $row_span = count($modules);
//                        echo "<tr>";
//                        echo "<td rowspan='{$row_span}'>".get_string('numweek', 'local_jinoanalytics', $section)."</td>";
//                        $first_row = true;
//                        foreach($modules as $cmid=>$module) {
//                            if (!$first_row) echo "<tr>"; $first_row = false;
//                            if ($module->submitted_cnt == 0 || $module->student_cnt == 0) $percent_submitted = 0;
//                            else $percent_submitted = $module->submitted_cnt*100/$module->student_cnt;
//                            if (empty($module->avg_score)) $module->avg_score = 0;
//                            echo "<td class='align-left'><a href='/mod/{$mod_name}/view.php?id={$module->cmid}' target='_blank'>{$module->name}</a></td>";
//                            echo "<td>{$module->student_cnt}</td>";
//                            echo "<td>{$module->submitted_cnt}</td>";
//                            echo "<td>".sprintf("%02.2f", $percent_submitted)."%</td>";
//                            echo "<td><a href='/grade/report/singleview/index.php?id={$courseid}&item=grade&itemid={$module->itemid}' target='_blank' class='btn-analytics'>{$module->grade_cnt}</a></td>";
//                            echo "<td class='align-right'>{$module->avg_score} / {$module->max_grade}</td>";
//                            echo "</tr>";
//                            
//                            $loop_cnt ++;
//                            $total_student_cnt += $module->student_cnt;
//                            $total_submitted_cnt += $module->submitted_cnt;
//                            $total_submitted_percent += $percent_submitted;
//                            $total_grade_cnt += $module->grade_cnt;
//                            $total_score += $module->avg_score * 100 / $module->max_grade;
//                        }
//                    }
//                    
//                    $total_student_cnt = sprintf("%02.2f", $total_student_cnt / $loop_cnt);
//                    $total_submitted_cnt = sprintf("%02.2f", $total_submitted_cnt / $loop_cnt);
//                    $total_submitted_percent = sprintf("%02.2f", $total_submitted_percent / $loop_cnt);
//                    $total_grade_cnt = sprintf("%02.2f", $total_grade_cnt / $loop_cnt);
//                    $total_score = sprintf("%02.2f", $total_score / $loop_cnt);
//
//                    echo "<tr><th colspan=2>".get_string('average','local_jinoanalytics')."</th><th>{$total_student_cnt}</th><th>{$total_submitted_cnt}</th><th>{$total_submitted_percent}%</th><th>{$total_grade_cnt}</th><th>{$total_score}%</th></tr>";
//                } else {
//                    echo "<tr><td colspan=7>".get_string('quiz_nocreated', 'local_jinoanalytics')."</td></tr>";
//                }
                break;
            
            case 'forum':
//                $sql = "select 
//                        concat(cs.section, '_', cm.id, '_', a.id) seq,  
//                        cs.section, cm.id cmid, a.id mod_id, a.name, 
//                        count(ra.id) student_cnt, count(s1.userid) user_cnt, count(s2.userid) write_cnt, count(s3.userid) comment_cnt
//                    from {course} c
//                    join {context} ctx on ctx.instanceid = c.id and ctx.contextlevel = :contextlevel and c.id = :courseid
//                    join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = :roleid
//                    join {course_modules} cm on cm.course = c.id
//                    join {modules} m on m.id = cm.module and m.name = :mod_name
//                    join {course_sections} cs on cs.id = cm.section
//                    join {forum} a on a.id = cm.instance 
//                    left join (
//                        select a.id, p.userid
//                        from {forum} a
//                        join {forum_discussions} s on s.forum = a.id and a.course = :courseid_1
//                        left join {forum_posts} p on p.discussion = s.id
//                        group by a.id, p.userid
//                    ) s1 on s1.id = a.id and s1.userid = ra.userid
//                    left join (
//                        select a.id, p.userid
//                        from {forum} a
//                        join {forum_discussions} s on s.forum = a.id and a.course = :courseid_2
//                        left join {forum_posts} p on p.discussion = s.id and p.parent = 0
//                        group by a.id, p.userid
//                    ) s2 on s2.id = a.id and s2.userid = ra.userid
//                    left join (
//                        select a.id, p.userid
//                        from {forum} a
//                        join {forum_discussions} s on s.forum = a.id and a.course = :courseid_3
//                        left join {forum_posts} p on p.discussion = s.id and p.parent > 0
//                        group by a.id, p.userid
//                    ) s3 on s3.id = a.id and s3.userid = ra.userid
//                    group by cs.section, cm.id, a.id
//                    order by cs.section";
//                $bind = array('contextlevel'=>CONTEXT_COURSE, 
//                    'courseid'=>$courseid, 
//                    'courseid_1'=>$courseid, 
//                    'courseid_2'=>$courseid, 
//                    'courseid_3'=>$courseid, 
//                    'roleid'=>$CFG->UB->ROLE->STUDENT, 
//                    'mod_name'=>$mod_name);
//                if ($datas = $DB->get_records_sql($sql, $bind)) {
//                    foreach($datas as $value) {
//                        $result[$value->section][$value->cmid] = $value;
//                    }
//                    unset($datas);
//                    
//                    $loop_cnt = $total_student_cnt = $total_user_cnt = $total_write_cnt = $total_comment_cnt = 0;
//                    foreach($result as $section=>$modules) {
//                        $row_span = count($modules);
//                        echo "<tr>";
//                        echo ($section == 0)?"<td rowspan='{$row_span}'>".get_string('overview', 'local_jinoanalytics')."</td>":"<td rowspan='{$row_span}'>".get_string('numweek', 'local_jinoanalytics', $section)."</td>";
//                        
//                        $first_row = true;
//                        foreach($modules as $cmid=>$module) {
//                            
//                            if (!$first_row) echo "<tr>"; $first_row = false;
//                            
//                            if (empty($module->user_cnt)) $module->user_cnt = 0;
//                            if (empty($module->write_cnt)) $module->write_cnt = 0;
//                            if (empty($module->comment_cnt)) $module->comment_cnt = 0;
//
//                            echo "<td class='align-left'><a href='/mod/{$mod_name}/view.php?id={$module->cmid}' target='_blank'>{$module->name}</a></td>";
//                            echo "<td>{$module->student_cnt}</td>";
//                            echo "<td>{$module->user_cnt}</td>";
//                            echo "<td>{$module->write_cnt}</td>";
//                            echo "<td>{$module->comment_cnt}</td>";
//                            echo "</tr>";
//                            
//                            $loop_cnt ++;
//                            $total_student_cnt += $module->student_cnt;
//                            $total_user_cnt += $module->user_cnt;
//                            $total_write_cnt += $module->write_cnt;
//                            $total_comment_cnt += $module->comment_cnt;
//                        }
//                    }
//                    
//                    $total_student_cnt = sprintf("%02.2f", $total_student_cnt / $loop_cnt);
//                    $total_user_cnt = sprintf("%02.2f", $total_user_cnt / $loop_cnt);
//                    $total_write_cnt = sprintf("%02.2f", $total_write_cnt / $loop_cnt);
//                    $total_comment_cnt = sprintf("%02.2f", $total_comment_cnt / $loop_cnt);
//
//                    echo "<tr><th colspan=2>".get_string('average', 'local_jinoanalytics')."</th><th>{$total_student_cnt}</th><th>{$total_user_cnt}</th><th>{$total_write_cnt}</th><th>{$total_comment_cnt}</th></tr>";
//                } else {
//                    echo "<tr><td colspan=6>".get_string('forum_nocreated', 'local_jinoanalytics')."</td></tr>";
//                }
                break;
                
            case 'vod':
//                $sql = "select 
//                        concat(cs.section, '_', cm.id, '_', a.id) seq,  
//                        cs.section, cm.id cmid, a.id mod_id, a.name, 
//                        count(ra.id) student_cnt, count(s.id) submitted_cnt, sum(if(s.totaltime = s.paytime, 1, 0)) completed_cnt
//                    from {course} c
//                    join {context} ctx on ctx.instanceid = c.id and ctx.contextlevel = :contextlevel and c.id = :courseid_1
//                    join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = :roleid
//                    join {course_modules} cm on cm.course = c.id
//                    join {modules} m on m.id = cm.module and m.name = :mod_name
//                    join {course_sections} cs on cs.id = cm.section
//                    join {vod} a on a.id = cm.instance 
//                    left join (
//                        select v.id, vt.userid, vt.totaltime, max(vt.progress) paytime
//                        from {vod} v
//                        join {vod_track} vt on vt.vodid = v.id and v.course = :courseid_2
//                        group by v.id, vt.userid
//                    ) s on s.id = a.id and s.userid = ra.userid 
//                    group by cs.section, cm.id, a.id
//                    order by cs.section";
//                if ($datas = $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'courseid_1'=>$courseid, 'courseid_2'=>$courseid, 'roleid'=>$CFG->UB->ROLE->STUDENT, 'mod_name'=>$mod_name))) {
//                    $log_modules_cnt = $this->get_log_modules_viewed_cnt($mod_name);
//                    foreach($datas as $value) {
//                        $value->viewed_cnt = (!empty($log_modules_cnt[$value->cmid]->cnt))?$log_modules_cnt[$value->cmid]->cnt:0;
//                        $result[$value->section][$value->cmid] = $value;
//                    }
//                    unset($datas);
//                    
//                    $loop_cnt = $total_student_cnt = $total_submitted_cnt = $total_viewed_cnt = $total_grade_cnt = $total_score = 0;
//                    foreach($result as $section=>$modules) {
//                        $row_span = count($modules);
//                        echo "<tr>";
//                        echo "<td rowspan='{$row_span}'>".get_string('numweek', 'local_jinoanalytics', $section)."</td>";
//                        $first_row = true;
//                        foreach($modules as $cmid=>$module) {
//                            $viewed_cnt = 0;
//                            if (!$first_row) echo "<tr>"; $first_row = false;
//                            if ($module->submitted_cnt == 0 || $module->student_cnt == 0) $percent_submitted = 0;
//                            else $percent_submitted = $module->submitted_cnt*100/$module->student_cnt;
//                            if ($module->submitted_cnt == 0 || $module->student_cnt == 0) $percent_completed = 0;
//                            else $percent_completed = $module->completed_cnt * 100 / $module->student_cnt;
//                            if (empty($module->avg_score)) $module->avg_score = 0;
//                            
//                            echo "<td class='align-left'><a href='/mod/{$mod_name}/view.php?id={$module->cmid}' target='_blank'>{$module->name}</a></td>";
//                            echo "<td><a href='/report/log/index.php?chooselog=1&showusers=0&showcourses=0&id={$courseid}&user=&date=&modid={$module->cmid}&modaction=r&edulevel=-1&logreader=logstore_standard' target='_blank' class='btn-analytics'>{$module->viewed_cnt}</a></td>";
//                            echo "<td>{$module->student_cnt}</td>";
//                            echo "<td>{$module->submitted_cnt}</td>";
//                            echo "<td>{$module->completed_cnt}</td>";
//                            echo "<td>".sprintf("%02.2f", $percent_completed)."%</td>";
//                            echo "</tr>";
//                            
//                            $loop_cnt ++;
//                            $total_viewed_cnt += $module->viewed_cnt;
//                            $total_student_cnt += $module->student_cnt;
//                            $total_submitted_cnt += $module->submitted_cnt;
//                            $total_grade_cnt += $module->completed_cnt;
//                            $total_score += $percent_completed;
//                        }
//                    }
//                    
//                    $total_viewed_cnt = sprintf("%02.2f", $total_viewed_cnt / $loop_cnt);
//                    $total_student_cnt = sprintf("%02.2f", $total_student_cnt / $loop_cnt);
//                    $total_submitted_cnt = sprintf("%02.2f", $total_submitted_cnt / $loop_cnt);
//                    $total_grade_cnt = sprintf("%02.2f", $total_grade_cnt / $loop_cnt);
//                    $total_score = sprintf("%02.2f", $total_score / $loop_cnt);
//
//                    echo "<tr><th colspan=2>".get_string('average','local_jinoanalytics')."</th>";
//                    echo "<th>{$total_viewed_cnt}</th>";
//                    echo "<th>{$total_student_cnt}</th>";
//                    echo "<th>{$total_submitted_cnt}</th>";
//                    echo "<th>{$total_grade_cnt}</th><th>{$total_score}%</th></tr>";
//                } else {
//                    echo "<tr><td colspan=7>".get_string('vod_nocreated', 'local_jinoanalytics')."</td></tr>";
//                }
                break;
        }
    }
    
    /**
     * 모듈의 조회수를 전달한다.
     * @global type $DB
     * @param type $mod_name
     * @param type $courseid
     * @return type
     */
    public function get_log_modules_viewed_cnt($mod_name, $courseid = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        $sql = "select cm.id, m.name, count(*) cnt
            from {logstore_standard_log} l 
            join {course_modules} cm on cm.id = l.contextinstanceid and l.contextlevel = :contextlevel and l.courseid = :courseid and l.crud = :crud
            join {modules} m on m.id = cm.module and m.name = :mod_name
            group by cm.id";
        return $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_MODULE, 'courseid'=>$courseid, 'crud'=>'r', 'mod_name'=>$mod_name));
    }
    
    public function display_modules_compare($courses = array(), $display_title = true, $display_sub_title = false, $items = null) {
        // $modules = $this->get_modules_compare();
        
        if (empty($courses) || count($courses) == 0) $courses[$this->courseid] = $this->course_info;
        if (empty($items)) $items = $this->display_modules;
        
        // Header
        $th = '';
        foreach($items as $item) {
            if (empty($this->modules[$item]->name)) continue;
            $th.="<th>{$this->modules[$item]->name}</th>";
        }
        $th.="<th>".get_string('etc', 'local_jinoanalytics')."</th>";
        $th.="<th>".get_string('total', 'local_jinoanalytics')."</th>";
        
        
        $tds = array();
        foreach($courses as $course) {
            $courseid = $course->id;
            if (empty($tds[$courseid])) $tds[$courseid] = '';
            $modules = $this->get_module_info($courseid, $items);
            
            $total = 0;
            foreach($items as $mod_name) {
                if (empty($this->modules[$mod_name]->name)) continue;
                if (empty($modules[$mod_name]->cnt)) $modules[$mod_name]->cnt = 0;
                $tds[$courseid] .= "<td>{$modules[$mod_name]->cnt}</td>";
                $total += $modules[$mod_name]->cnt;
                unset($modules[$mod_name]);
            }
            
            $total_etc = 0;
            foreach($modules as $mod_name=>$module) {
                $total += $module->cnt;
                $total_etc += $module->cnt;
            }
            $tds[$courseid] .= "<td>{$total_etc}</td><td>{$total}</td>";
        }
        
        // 화면 출력
        if ($display_title) {
            if ($display_sub_title) {
                echo "<tr><th></th>{$th}</tr>";
            } else {
                echo "<tr>{$th}</tr>";
            }
        }

        foreach($tds as $key=>$td) {
            echo "<tr>";
            if ($display_sub_title) {
                $course = $courses[$key];
                echo "<td><a href='/course/view.php?id={$course->id}' target='_blank'>{$course->year} ".$this->get_semester_name($course->semester_code)."</a></td>";
            }
            echo $td;
            echo "</tr>";
        }
    }
    
    
    /**
     * 강좌운영 > 비교분석 > 학습자원 등록
     * @global type $DB
     * @param type $items
     * @return type
     */
    public function get_modules_compare($courseid = null, $items = null) {
        global $DB;
        
        if (empty($courseid)) $courseid = $this->courseid;
        if (empty($items)) $items = $this->display_modules;
        
        // 초기화
        $result = $init_result = array();
        foreach($this->modules as $mod_name=>$value) {
            if (!in_array($mod_name, $items)) continue;
            $init_result[$mod_name] = 0;
        }
        $init_result['etc'] = 0;
        $init_result['total'] = 0;
                
        $filter_idnumber = '____'.substr($this->course_info->idnumber, 4);
        $sql = "select concat('2017_', c.id, '_', m.id) id, 2017 as year , c.id courseid, m.name mod_name, count(*) cnt
            from {course} c 
            join {course_modules} cm on cm.course = cu.id  
            join {modules} m on m.id = cm.module 
            group by cu.year, cu.semester_code, c.id, m.id order by cu.year desc, cu.semester_code desc";
        if ($datas = $DB->get_records_sql($sql, array('courseid'=>$courseid))) {
            foreach($datas as $key=>$value) {
                if (empty($result[$value->year][$value->courseid])) $result[$value->year][$value->courseid] = $init_result;
                if (in_array($value->mod_name, $items)) $result[$value->year][$value->courseid][$value->mod_name] = $value->cnt;
                else $result[$value->year][$value->courseid]['etc'] += $value->cnt;
                $result[$value->year][$value->courseid]['total'] += $value->cnt;
            }
        }
        return $result;
    }
    
    /**
     * 강좌운영 > 주차별 활동변화 : 주차별 모든 활동 로그수
     * @global type $DB
     * @return type
     */
    public function get_logs_week_count($courseid = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        $result = array();
        
        $num_sections = $this->course_info->numsections;
        for($i=1; $i<=$num_sections; $i++) $result[$i] = 0;
        $sql = "select 
                floor((l.timecreated-c.startdate)/604800)+1 as week, count(*) cnt
            from {course} c
            join {logstore_standard_log} l on l.courseid = c.id and c.id = :courseid and l.userid > 2
            group by floor((l.timecreated-c.startdate)/604800)+1 having floor(604800)+1 > 0 and floor(604800)+1 <= 10000000";
        if ($logs=$DB->get_records_sql($sql, array('courseid'=>$courseid, 'limit_week'=>$num_sections))) {
            foreach($logs as $key=>$value) {
                $result[$value->week] = $value->cnt;
            }
        }
        return $result;
    }
    
    /**
     * 강좌운영 > 주차별 활동변화 : 주차별 활동 유저의 수를 제공
     * @global type $DB
     * @return type
     */
    public function get_session_week_count($courseid = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        $result = array();
        
        $num_sections = $this->course_info->numsections;
        for($i=1; $i<=$num_sections; $i++) $result[$i] = 0;
        $sql = "select weeks, count(*) cnt 
            from (
                select 
                    l.userid, floor((l.timecreated-c.startdate)/604800)+1 as weeks
                from {course} c
                join {logstore_standard_log} l on l.courseid = c.id and c.id = :courseid and l.userid > 2
                group by l.userid, floor((l.timecreated-c.startdate)/604800)+1  
            ) s where weeks > 0 and weeks <= :limit_week group by weeks
            order by weeks";
        if ($logs=$DB->get_records_sql($sql, array('courseid'=>$courseid, 'limit_week'=>$num_sections))) {
            foreach($logs as $key=>$value) {
                $result[$key] = $value->cnt;
            }
        }
        return $result;
    }
    
    /**
     * Section별로 등록된 모듈의 수를 제공
     * @global type $DB
     * @param type $courseid
     * @param type $items
     * @return \stdClass
     */
    public function get_section_modules_count($courseid = null, $items = null) {
        global $DB;
        
        if (empty($courseid)) $courseid = $this->courseid;
        if (empty($items)) $items = $this->display_modules;
        
        // 초기화
        $result = array();
        for($week_i = 0; $week_i<=$this->course_info->numsections; $week_i++) {
            foreach($this->modules as $mod_name=>$value) {
                if (!in_array($mod_name, $items)) continue;
                if (empty($result[$week_i][$mod_name])) $result[$week_i][$mod_name] = new stdClass();
                $result[$week_i][$mod_name]->cnt = 0;
            }
        }
        
        $sql = "select cs.section || '_' || m.name as id, cs.section, m.name mod_name, COUNT(cm.id) cnt from {course_modules} cm
            join {course_sections} cs on cs.id = cm.section and cm.course = :courseid
            join {modules} m on m.id = cm.module 
            group by cs.section, m.name";
        if ($datas = $DB->get_records_sql($sql, array('courseid'=>$courseid))) {
           foreach($datas as $value) {
               if (!in_array($value->mod_name, $items)) continue;
               if (empty($result[$value->section][$value->mod_name])) {
                   $result[$value->section][$value->mod_name] = new stdClass();
                   $result[$value->section][$value->mod_name]->cnt = 0;
               }
               $result[$value->section][$value->mod_name]->cnt = $value->cnt;
           }
        }
        return $result;
    }
    
    /**
     * 강좌운영 > 주차별 활동변화 : 주차별로 등록된 학습활동 모듈의 활용률을 제공
     * @global type $DB
     * @param type $items
     */
    function get_modules_week_count($items = null) {
        global $DB;
        if (empty($items)) $items = $this->display_modules;
        
        // 초기화
        $result = array();
        for($week_i = 0; $week_i<=$this->course_info->numsections; $week_i++) {
            foreach($this->modules as $mod_name=>$value) {
                if (!in_array($mod_name, $items)) continue;
                if (empty($result[$week_i][$mod_name])) $result[$week_i][$mod_name] = new stdClass();
                $result[$week_i][$mod_name]->cnt = 0;
            }
        }
        
        $sql = "select 
                cs.section || '_' || m.name as id, cs.section, m.name mod_name, count(*) cnt 
            from {logstore_standard_log} l 
            join {context} ctx on ctx.id = l.contextid and l.contextlevel = :contextlevel and l.courseid = :courseid and l.userid > 2
            join {course_modules} cm on cm.id = ctx.instanceid 
            join {course_sections} cs on cs.id = cm.section and cs.section >= 0
            join {modules} m on m.id = cm.module
            group by cs.section, m.name";
        if ($datas = $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_MODULE, 'courseid'=>$this->courseid))) {
           foreach($datas as $key=>$value) {
               if (!in_array($value->mod_name, $items)) continue;
               if (empty($result[$value->section][$value->mod_name])) $result[$value->section][$value->mod_name] = new stdClass();
               $result[$value->section][$value->mod_name]->cnt = $value->cnt;
           }
        }
        return $result;
    }
   
    /**
     * 강좌운영 > 학습활동 집계 : 화면 출력
     * @param type $courses
     * @param type $items
     * @param type $display_title
     * @param type $display_sub_title
     */
    public function display_activity_count($courses = array(), $display_title = true, $display_sub_title = false, $items = null) {
        $th = $td_title = '';
        $tds = array();
        if (empty($courses) || count($courses) == 0) $courses[$this->courseid] = $this->course_info;
        if (empty($items)) $items = $this->display_modules;
        
        foreach($courses as $course) {
            $th = $td_title = '';
            $courseid = $course->id;
            $module_count = $this->get_avtivity_count($courseid, $items);
            foreach($items as $mod_name) {
                if (empty($this->modules[$mod_name]->name)) continue;
                if (empty($tds[$courseid])) $tds[$courseid] = '';
                switch($mod_name) {
                    case 'ubboard':
                        $th.="<th colspan=3>{$this->modules[$mod_name]->name}</th>";
                        $td_title.="<td>".get_string('read', 'local_jinoanalytics')."</td><td>".get_string('write', 'local_jinoanalytics')."</td><td>".get_string('comment', 'local_jinoanalytics')."</td>";
                        $tds[$courseid].="<td>{$module_count[$mod_name]->read_cnt}</td><td>{$module_count[$mod_name]->write_cnt}</td><td>{$module_count[$mod_name]->comment_cnt}</td>";
                        break;
                    case 'forum':
                        $th.="<th colspan=3>{$this->modules[$mod_name]->name}</th>";
                        $td_title.="<td>".get_string('read', 'local_jinoanalytics')."</td><td>".get_string('write', 'local_jinoanalytics')."</td><td>".get_string('reply', 'local_jinoanalytics')."</td>";
                        $tds[$courseid].="<td>{$module_count[$mod_name]->read_cnt}</td><td>{$module_count[$mod_name]->write_cnt}</td><td>{$module_count[$mod_name]->comment_cnt}</td>";
                        break;
                    case 'assign':
                        $th.="<th colspan=2>{$this->modules[$mod_name]->name}</th>";
                        $td_title.="<td>".get_string('read', 'local_jinoanalytics')."</td><td>".get_string('submit', 'local_jinoanalytics')."</td>";
                        $tds[$courseid].="<td>{$module_count[$mod_name]->read_cnt}</td><td>{$module_count[$mod_name]->submit_cnt}</td>";
                        break;
                    case 'book':
                    case 'wiki':
                    case 'url':
                    case 'vod':
                    case 'ubfile':
                        $th.="<th colspan=1>{$this->modules[$mod_name]->name}</th>";
                        $td_title.="<td>".get_string('view', 'local_jinoanalytics')."</td>";
                        $tds[$courseid].="<td>{$module_count[$mod_name]->read_cnt}</td>";
                        break;
                    default:
                        $th.="<th colspan=1>{$this->modules[$mod_name]->name}</th>";
                        $td_title.="<td>".get_string('view', 'local_jinoanalytics')."</td>";
                        $tds[$courseid].="<td>{$module_count[$mod_name]->read_cnt}</td>";
                        break;
                }
            }
        }
        
        // 화면 출력
        if ($display_title) {
            if ($display_sub_title) {
                echo "<tr><th></th>{$th}</tr>";
                echo "<tr><td></td>{$td_title}</tr>";
            } else {
                echo "<tr>{$th}</tr>";
                echo "<tr>{$td_title}</tr>";
            }
        }

        foreach($tds as $key=>$td) {
            echo "<tr>";
            if ($display_sub_title) {
                $course = $courses[$key];
                echo "<td><a href='/course/view.php?id={$course->id}' target='_blank'>{$course->year} ".$this->get_semester_name($course->semester_code)."</a></td>";
            }
            echo $td;
            echo "</tr>";
        }
    }
    
    /**
     * 강좌운영 > 학습활동 집계 : 데이터 추출
     * @global type $DB
     * @param type $items
     * @return \stdClass
     */
    public function get_avtivity_count($courseid = null, $items = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        if (empty($items)) $items = $this->display_modules;
        $result = array();
        $log = new stdClass();
        
        // Write
        $sql = "select component, count(*) cnt "
                . "from {logstore_standard_log} where courseid=:courseid and contextlevel = :contextlevel and crud = :crud group by component";
        $log->write = $DB->get_records_sql($sql, array('courseid'=>$courseid, 'contextlevel'=>CONTEXT_MODULE, 'crud'=>'c'));
        
        // Read
        $sql = "select component, count(*) cnt 
                from {logstore_standard_log} l 
                join {course_modules} cm on cm.id = l.contextinstanceid and l.contextlevel = :contextlevel and l.courseid = :courseid and l.crud = :crud
                group by component";
        $log->read = $DB->get_records_sql($sql, array('courseid'=>$courseid, 'contextlevel'=>CONTEXT_MODULE, 'crud'=>'r'));
        
        // Submit
        $sql = "select component, count(*) cnt "
                . "from {logstore_standard_log} where courseid=:courseid and contextlevel = :contextlevel and action = :action group by component";
        $log->submit = $DB->get_records_sql($sql, array('courseid'=>$courseid, 'contextlevel'=>CONTEXT_MODULE, 'action'=>'submitted'));

        foreach($items as $mod_name) {
            if (empty($this->modules[$mod_name])) continue;
            $function_name = 'get_activity_'.$mod_name.'_count';
            if (method_exists($this,$function_name)) {
                $result[$mod_name] = $this->$function_name($log, $courseid);
            } else {
                $result[$mod_name] = new stdClass();
                $result[$mod_name]->write_cnt = (!empty($log->write['mod_'.$mod_name]->cnt))?$log->write['mod_'.$mod_name]->cnt:0;
                $result[$mod_name]->read_cnt = (!empty($log->read['mod_'.$mod_name]->cnt))?$log->read['mod_'.$mod_name]->cnt:0;
                $result[$mod_name]->comment_cnt = 0;
                $result[$mod_name]->submit_cnt = (!empty($log->submit['mod_'.$mod_name]->cnt))?$log->submit['mod_'.$mod_name]->cnt:0;
            }
        }
        
        return $result;
    }
    
    /**
     * 강좌운영 > 학습활동 집계 : 데이터 추출(ubboard)
     * @global type $DB
     * @param type $log
     * @return \stdClass
     */
    function get_activity_ubboard_count($log, $courseid = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        $mod_name = 'ubboard';
        $sql="select count(bw.id) write_cnt, sum(bw.hit) read_cnt, sum(bw.comment_cnt) comment_cnt from {ubboard} b
            join {ubboard_write} bw on bw.bid = b.id and b.course = :courseid";
        if (!$result = $DB->get_record_sql($sql, array('courseid'=>$courseid))) {
            $result = new stdClass();
            $result->write_cnt = (!empty($log->write['mod_'.$mod_name]->cnt))?$log->write['mod_'.$mod_name]->cnt:0;
            $result->read_cnt = (!empty($log->read['mod_'.$mod_name]->cnt))?$log->read['mod_'.$mod_name]->cnt:0;;
            $result->comment_cnt = 0;
            $result->submit_cnt = (!empty($log->submit['mod_'.$mod_name]->cnt))?$log->submit['mod_'.$mod_name]->cnt:0;
        }
        return $result;
    }
    
    /**
     * 강좌운영 > 학습활동 집계 : 데이터 추출(forum)
     * @global type $DB
     * @param type $log
     * @return \stdClass
     */
    function get_activity_forum_count($log, $courseid = null) {
        global $DB;
        if (empty($courseid)) $courseid = $this->courseid;
        $mod_name = 'forum';
        $result = new stdClass();
        $result->write_cnt = 0;
        $result->read_cnt = 0;
        $result->cooment_cnt = 0;
        $result->submit_cnt = 0;
        $sql="select count(fp.id) cnt  from {forum} f
            join {forum_discussions} fd on fd.forum = f.id and f.course = :courseid
            join {forum_posts} fp on fp.discussion = fd.id and fp.parent = 0";
        if (!$result->write_cnt = $DB->get_field_sql($sql, array('courseid'=>$courseid))) {
            $result->write_cnt = (!empty($log->write['mod_'.$mod_name]->cnt))?$log->write['mod_'.$mod_name]->cnt:0;
        }

        $result->read_cnt = (!empty($log->read['mod_'.$mod_name]->cnt))?$log->read['mod_'.$mod_name]->cnt:0;;

        $sql="select count(fp.id) cnt  from {forum} f
            join {forum_discussions} fd on fd.forum = f.id and f.course = :courseid
            join {forum_posts} fp on fp.discussion = fd.id and fp.parent > 0";
        if (!$result->comment_cnt = $DB->get_field_sql($sql, array('courseid'=>$courseid))) {
            $result->comment_cnt = 0;
        }
        
        return $result;
    }
    
    
    /**
     * 강좌운영 > 학습자원 등록 : 화면 출력
     * @param type $items
     * @return string
     */
    public function display_modules_count($items = null) {
        if (empty($items)) $items = $this->display_modules;
        $modules = $this->modules;
        $th = $th = array();
        $display_cnt = $row = 0;
        
        foreach($items as $idx=>$key) {
            if (empty($modules[$key])) { continue; }
            $start_tag = $end_tag = '';
            if ($display_cnt == 0 || $display_cnt == 4 || $display_cnt == 8) { $row++; $start_tag = "<tr>"; }
            if ($display_cnt == 3 || $display_cnt == 7) { $end_tag = "</tr>";  }
            if ($display_cnt == 11) break;
            $display_cnt ++;
            
            if (empty($th[$row])) $th[$row] = '';
            if (empty($td[$row])) $td[$row] = '';
            $th[$row].="{$start_tag}<th>{$modules[$key]->name}</th>{$end_tag}\n";
            $td[$row].="{$start_tag}<td>{$modules[$key]->cnt}</td>{$end_tag}\n";
            unset($modules[$key]);
        }
        
        if ($display_cnt < 11) {
            foreach($modules as $key=>$value) {
                $start_tag = $end_tag = '';
                if ($display_cnt == 3) $start_tag = "<tr>";
                if ($display_cnt == 11) break;
                $display_cnt ++;
                
                if (empty($th[$row])) $th[$row] = '';
                if (empty($td[$row])) $td[$row] = '';
                $th[$row].="{$start_tag}<th>{$modules[$key]->name}</th>{$end_tag}\n";
                $td[$row].="{$start_tag}<td>{$modules[$key]->cnt}</td>{$end_tag}\n";
                unset($modules[$key]);
            }
        }
        // 기타 처리
        $another_cnt = 0;
        foreach($modules as $key=>$value) {
            $another_cnt += $value->cnt;
        }
        $th[$row].="<th>".get_string('etc', 'local_jinoanalytics')."</th></tr>";
        $td[$row].="<td>{$another_cnt}</td></tr>";

        
        $html = '';
        foreach($th as $key=>$value) {
            $html.=$th[$key].$td[$key];
        }
        return $html;
    }
    
    /**
     * 강좌운영 > 학습자원 등록 : 그래프 출력
     * @param type $items
     * @return \stdClass
     */
    public function get_modules_count($items = null) {
        if (empty($items)) $items = $this->display_modules;
        $modules = $this->modules;
        $result = array();
        $display_cnt = 0;

        foreach($items as $idx=>$key) {
            if (empty($modules[$key]) || $modules[$key]->cnt == 0) { continue; }
            if ($display_cnt == 11) break;
            $display_cnt++;
            $result[$key] = $modules[$key];
            unset($modules[$key]);
        }
        
        if ($display_cnt < 11) {
            foreach($modules as $key=>$value) {
                if ($modules[$key]->cnt == 0) continue;
                if ($display_cnt == 11) break;
                $display_cnt++;
                $result[$key] = $modules[$key];
                unset($modules[$key]);
            }
        }
        return $result;
    }
    
    /**
     * 강좌운영 > 강좌정보
     * @global type $DB
     * @global type $CFG
     * @return type
     */
    public function get_course_info($courseid = null) {
        global $DB, $CFG;
        $result = new stdClass(); 
        $result->semester = '';
        if (empty($courseid)) $courseid = $this->courseid;
        
        $sql = "SELECT   
            c.id, c.fullname, c.shortname, c.idnumber, c.summary, c.format, cf.value numsections, c.startdate 
        FROM {course} c
        JOIN {course_format_options} cf on cf.courseid = c.id AND cf.format = c.format AND cf.name = 'numsections'";
        if ($result = $DB->get_record_sql($sql, array('courseid'=>$courseid))) {
            $result->semester = $result->year.get_string('year2', 'local_ubion').' '.$this->get_semester_name($result->semester_code);
            $result->student_count = $this->get_student_count();
            $result->teachers = $this->get_teachers($result->prof_id);
            $result->assistants = $this->get_assistants();
            $result->class_times = $this->get_class_info($result);
            $result->class_place = $this->get_class_building($result);
        }
        return $result;
    }
    
    public function diplay_institution_table($datas = null) {
        global $DB, $CFG;
        if (empty($datas)) $datas = $this->get_institution_table();

        echo '<tr><th>'.get_string('college', 'local_jinoanalytics').'</th><th>'.get_string('learner_s', 'local_jinoanalytics').'</th></tr>';
        foreach($datas as $value) {
            echo "<tr><td>{$value->name}</td><td>{$value->cnt}</td></tr>";
        }
    }
    
    public function get_institution_info($courseid = null) {
        global $DB, $CFG;
        if (empty($courseid)) $courseid = $this->courseid;
        $results = array();
        $sql = "select 
                lu.dept name, count(*) cnt
            from {course} c
            join {context} ctx on ctx.instanceid = c.id and ctx.contextlevel = :contextlevel and c.id = :coruseid
            join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = :roleid
            join {lmsdata_user} lu on lu.userid = ra.userid 
            group by lu.dept";
        if ($results = $DB->get_records_sql($sql, array('contextlevel'=>50, 'coruseid'=>$courseid, 'roleid'=>5))) {
            //chk($results,'results', true);
        }
        return $results;
    }
    
        public function diplay_department_table($datas = null) {
        global $DB, $CFG;
        if (empty($datas)) $datas = $this->get_institution_table();

        echo '<tr><th>'.get_string('department', 'local_jinoanalytics').'</th><th>'.get_string('learner_s', 'local_jinoanalytics').'</th></tr>';
        foreach($datas as $value) {
            echo "<tr><td>{$value->name}</td><td>{$value->cnt}</td></tr>";
        }
    }
    
    public function get_department_info($courseid = null) {
        global $DB, $CFG;
        if (empty($courseid)) $courseid = $this->courseid;
        
        $sql = "select 
                u.department name, count(*) cnt
            from {course} c
            join {context} ctx on ctx.instanceid = c.id and ctx.contextlevel = :contextlevel and c.id = :coruseid
            join {role_assignments} ra on ra.contextid = ctx.id and ra.roleid = :roleid
            join {user} u on u.id = ra.userid and u.deleted != 1
            group by u.department";
        return $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'coruseid'=>$courseid, 'roleid'=>$CFG->UB->ROLE->STUDENT));
    }
    
    /**
     * 현재 강좌와 관련된 강좌 목록
     * @global type $DB
     * @return type
     */
    function get_relation_courses() {
        global $DB;
        if (strlen($this->course_info->idnumber)<=5) return array();
        $relation_idnumber = '____'.substr($this->course_info->idnumber, 4);
        $sql = "SELECT 
            c.id, c.fullname, c.shortname, c.idnumber, c.summary, c.format, cf.value numsections, c.startdate, 
            cu.year, cu.semester_code, cu.ename, 
            cu.curriculum_code, cu.curriculum, cu.prof_id, cu.prof_idnumber, 
            cu.setting, cu.progress, cu.study_start, cu.study_end, cu.cd, cu.visible, cu.is_syllabus, 
            cu.day_cd, cu.day_name, cu.classtime_cd, cu.classtime, cu.building_cd, cu.building_koname, cu.building_enname, cu.room_cd,
            cu.dept_code_01, cu.dept_name_01, cu.dept_code_02, cu.dept_name_02, cu.dept_code_03, cu.dept_name_03, cu.dept_code_04, cu.dept_name_04
        FROM {course} c
        JOIN {course_ubion} cu on cu.course = c.id AND c.idnumber like :relation_idnumber and c.id != :current_courseid AND cu.year >= 2016 AND prof_id = :prof_id
        JOIN {course_format_options} cf on cf.courseid = c.id AND cf.format = c.format AND cf.name = 'numsections' order by cu.year desc, cu.semester_code desc";
        return $DB->get_records_sql($sql, array('relation_idnumber'=>$relation_idnumber, 'current_courseid'=>$this->courseid, 'prof_id'=>$this->course_info->prof_id));
    }
    
    /**
     * 강좌운영 > 강좌정보 : 학습 장소 제공
     * @param type $data
     * @return type
     */
    function get_class_building($data) {
        $current_language = current_language();
        if (!empty($data->day_cd)) {
            $result = array();
            $day_cd = explode('^', $data->day_cd);
            $building_cd = explode('^', $data->building_cd);
			$room_cd = explode('^', $data->room_cd);
            $building_name = (!empty($current_language) && $current_language == 'ko')?explode('^', $data->building_koname):explode('^', $data->building_enname);
            foreach($day_cd as $key=>$cd) {
                if (in_array("{$building_name[$key]} {$room_cd[$key]}",$result)) continue;
                $result[$cd] = "{$building_name[$key]} {$room_cd[$key]}";
            }
            return join(', ', $result);
        }
        return '';
    }
        
    /**
     * 강좌운영 > 강좌정보 : 학습 시간표 제공
     * @param type $data
     * @return type
     */
    function get_class_info($data) {
        if (!empty($data->day_cd)) {
            $result = array();
            $day_cd = explode('^', $data->day_cd);
            $day_name = explode('^', $data->day_name);
            $classtime = explode('^', $data->classtime);
            foreach($day_cd as $key=>$cd) {
                $result[$cd] = "{$day_name[$key]}({$classtime[$key]}";
            }
            return join(', ', $result);
        }
        return '';
    }
    
    
    /**
     * 유저명을 제공
     * @param type $user
     * @return type
     */
    public function display_user_name($user) {
        $result = array();
        if (is_array($user)) {
            foreach($user as $u) {
                $result[] = fullname($u);
            }
            return join(', ', $result);
        } else {
            return fullname($user);
        }
    }
    
    
    /**
     * 조교 정보를 제공
     * @global type $DB
     * @global type $CFG
     * @return type
     */
    function get_assistants() {
        global $DB, $CFG;
        $sql = "SELECT u.* FROM {course} c
                JOIN {context} ctx on ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel AND c.id = :courseid
                JOIN {role_assignments} ra on ra.contextid = ctx.id AND ra.roleid = :roleid
                JOIN {user} u ON u.id = ra.userid";
        return $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'courseid'=>$this->courseid, 'roleid'=>$CFG->UB->ROLE->ASSISTANT));
    }
    
    
    /**
     * 교수 정보를 제공
     * @global type $DB
     * @global type $CFG
     * @param type $prof_id -- 전달된 id가있을 경우 해당된 유저의 정보를 회신
     * @return type
     */
    function get_teachers($prof_id = null) {
        global $DB, $CFG;
        if (!empty($prof_id)) {
            if ($user = $DB->get_record('user', array('id'=>$prof_id))) {
                return $user;
            }
        }
        $sql = "SELECT u.* FROM {course} c
                JOIN {context} ctx on ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel AND c.id = :courseid
                JOIN {role_assignments} ra on ra.contextid = ctx.id AND ra.roleid = :roleid
                JOIN {user} u ON u.id = ra.userid";
        return $DB->get_records_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'courseid'=>$this->courseid, 'roleid'=>$CFG->UB->ROLE->PROFESSOR));
    }
    
    /**
     * 수강중인 학생의 정보를 제공
     * @global type $DB
     * @global type $CFG
     * @return type
     */
    function get_student_count() {
        global $DB, $CFG;
        $sql = "SELECT count(*) FROM {course} c
                JOIN {context} ctx on ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel AND c.id = :courseid
                JOIN {role_assignments} ra on ra.contextid = ctx.id AND ra.roleid = :roleid";
        return $DB->get_field_sql($sql, array('contextlevel'=>CONTEXT_COURSE, 'courseid'=>$this->courseid, 'roleid'=>$CFG->UB->ROLE->STUDENT));
    }
    
    /**
     * 학기명을 제공
     * @global type $CFG
     * @param type $code
     * @return type
     */
    function get_semester_name($code) {
        global $CFG;
        $semester_name = '';
        if (!empty($CFG->UB->HAKSA->SEMESTER[$code])) {
            $semester_name = $CFG->UB->HAKSA->SEMESTER[$code];
        }
        return $semester_name;
    }
}

function local_jinoanalytics_index_handler($event) {
    global $DB;

    return true;
}

function local_jinoanalytics_view_handler($event) {
    global $DB;

    return true;
}
