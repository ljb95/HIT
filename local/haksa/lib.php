<?php

define('LOCAL_HAKSA_CATEGORY_SEPARATOR', '//');
define('LOCAL_HAKSA_CATEGORY_PREFIX', 'GTEC_');

function local_haksa_assign_user($courseid, $userid, $role, $timestart = 0, $timeend = 0, $timemodified = 0) {
    global $CFG, $PAGE, $DB;

    $enrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $courseid));
    local_haksa_enrol_user($enrol, $userid, $role->id, $timestart, $timeend, $timemodified);

    return true;
}

function local_haksa_unassign_user($course, $userid, $roleid) {
    global $PAGE;

    $manager = new course_enrolment_manager($PAGE, $course);

    $manager->unassign_role_from_user($userid, $roleid);

    if (!$manager->get_user_roles($userid)) { // 권한이 하나도 없으면 등록을 해지한다.
        $ues = $manager->get_user_enrolments($userid);
        foreach ($ues as $ue) {
            $manager->unenrol_user($ue);
        }
    }

    return true;
}

function local_haksa_enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $timemodified = 0) {
    global $DB, $USER, $CFG; // CFG necessary!!!

    $context = context_course::instance($instance->courseid, MUST_EXIST);

    if (!$timemodified) {
        $timemodified = time();
    }

    if ($ue = $DB->get_record('user_enrolments', array('enrolid' => $instance->id, 'userid' => $userid))) {
        //only update if timestart or timeend or status are different.
        //if ($ue->timestart != $timestart or $ue->timeend != $timeend or (!is_null($status) and $ue->status != $status)) {
        //    $this->update_user_enrol($instance, $userid, $status, $timestart, $timeend);
        //}
    } else {
        $ue = new stdClass();
        $ue->enrolid = $instance->id;
        $ue->status = ENROL_USER_ACTIVE;
        $ue->userid = $userid;
        $ue->timestart = $timestart;
        $ue->timeend = $timeend;
        $ue->modifierid = $USER->id;
        $ue->timecreated = $timemodified;
        $ue->timemodified = $ue->timecreated;
        $ue->id = $DB->insert_record('user_enrolments', $ue);
    }

    if ($roleid) {
        // this must be done after the enrolment event so that the role_assigned event is triggered afterwards
        local_haksa_role_assign($roleid, $userid, $context->id, '', 0, $timemodified);
    }
}

function local_haksa_role_assign($roleid, $userid, $contextid, $component = '', $itemid = 0, $timemodified = '') {
    global $USER, $DB;

    if (!$timemodified) {
        $timemodified = time();
    }

    // Check for existing entry
    $ras = $DB->get_records('role_assignments', array('roleid' => $roleid, 'contextid' => $contextid, 'userid' => $userid, 'component' => $component, 'itemid' => $itemid), 'id');

    if ($ras) {
        // role already assigned - this should not happen
        if (count($ras) > 1) {
            // very weird - remove all duplicates!
            $ra = array_shift($ras);
            foreach ($ras as $r) {
                $DB->delete_records('role_assignments', array('id' => $r->id));
            }
        } else {
            $ra = reset($ras);
        }

        // actually there is no need to update, reset anything or trigger any event, so just return
        return $ra->id;
    }

    // Create a new entry
    $ra = new stdClass();
    $ra->roleid = $roleid;
    $ra->contextid = $contextid;
    $ra->userid = $userid;
    $ra->component = $component;
    $ra->itemid = $itemid;
    $ra->timemodified = $timemodified;
    $ra->modifierid = empty($USER->id) ? 0 : $USER->id;
    $ra->sortorder = 0;

    $ra->id = $DB->insert_record('role_assignments', $ra);

    return $ra->id;
}

function local_haksa_create_course($data) {
    global $DB;

    if (!$data->timemodified) {
        $data->timemodified = time();
    }
    $data->timecreated = $data->timemodified;

    // place at beginning of any category
    $data->sortorder = 0;

    $data->visibleold = $data->visible;

    $newcourseid = $DB->insert_record('course', $data);
    //$context = context_course::instance($newcourseid, MUST_EXIST);
    $parentcontext = context_coursecat::instance($data->category);
    local_haksa_insert_context_record(CONTEXT_COURSE, $newcourseid, $parentcontext->path);

    // update course format options
    course_get_format($newcourseid)->update_course_format_options($data);

    $course = course_get_format($newcourseid)->get_course();

    // Setup the blocks
    blocks_add_default_course_blocks($course);

    // Create a default section.
    course_create_sections_if_missing($course, 0);

    //fix_course_sortorder();
    // purge appropriate caches in case fix_course_sortorder() did not change anything
    //cache_helper::purge_by_event('changesincourse');
    // new context created - better mark it as dirty
    //$context->mark_dirty();
    // Save any custom role names.
    //save_local_role_names($course->id, (array)$data);
    // set up enrolments
    enrol_course_updated(true, $course, $data);

    // Trigger a course created event.
//    $event = \core\event\course_created::create(array(
//        'objectid' => $course->id,
//        'context' => context_course::instance($course->id),
//        'other' => array('shortname' => $course->shortname,
//                         'fullname' => $course->fullname)
//    ));
//    $event->trigger();

    return $course;
}

/**
 * 범주를 찾거나 없을 경우 생성한다.
 * @param array $path  범주 경로
 * @return int 찾거나 생성한 범주 아이디
 */
function local_haksa_find_or_create_category($path, &$categories) {
    $parent = 0;
    $length = count($path);  
    for ($i = 0; $i < $length; $i++) {
        $pathstring = implode(LOCAL_HAKSA_CATEGORY_SEPARATOR, array_slice($path, 0, $i + 1));
        if ($categoryid = array_search($pathstring, $categories)) {
            $parent = $categoryid;
        } else {
            $parent = local_haksa_create_category($path[$i], '', $parent);
            $categories[$parent] = $pathstring;
        }
    }

    return $parent;
}

/**
 * 범주를 생성한다.
 * @param string $name  범주 이름
 * @param string $description  설명
 * @param int $name  부모 범주 아이디
 * @return int 생성한 범주 아이디
 */
require_once $CFG->libdir . '/coursecatlib.php';

function local_haksa_create_category($name, $description, $parent) {
    global $DB;

    $newcategory = new stdClass();
    $newcategory->name = $name;
    $newcategory->description = $description;
    $newcategory->parent = $parent; // if $parent = 0, the new category will be a top-level category
    $newcategory->sortorder = 999;

    $category = null;
    if ($parent) {
        if ($parent_category = $DB->get_record('course_categories', array('id' => $parent))) {
            $category = coursecat::create($newcategory);
        }
    } else {
        $category = coursecat::create($newcategory);
    }

    if ($category) {
        // Update idnumber
        //$DB->set_field('course_categories', 'idnumber', LOCAL_HAKSA_CATEGORY_PREFIX . $category->id, array('id' => $category->id));

        return $category->id;
    } else {
        return false;
    }
}

function local_haksa_get_category_path($course) {
    global $DB;
    
    $path = array();
    $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber'=>'oklass_regular'));
    $path[] = $course->cata1;
    $path[] = $course->cata2; 

    return $path;
}

function local_haksa_get_course_categories(&$list, $parentid = 0) {
    global $DB;

    $sql = "SELECT id, name
        FROM {course_categories}
        WHERE parent = :parent ";

    $params = array();
    $params['parent'] = $parentid;

    $categories = false;
    if (!$parentid) {
        $sql .=  "AND " . $DB->sql_like('idnumber', ':idnumber', false);
        $params['idnumber'] = 'oklass_regular%';
        if ($categories = $DB->get_records_sql($sql, $params)) {
            foreach ($categories as $category) {
                $list[$category->id] = $category->name;
                local_haksa_get_course_categories($list, $category->id);
            }
        }
    } else {
        if ($categories = $DB->get_records_sql($sql, $params)) {
            foreach ($categories as $category) {
                $list[$category->id] = $list[$parentid] . LOCAL_HAKSA_CATEGORY_SEPARATOR . $category->name;
                local_haksa_get_course_categories($list, $category->id);
            }
        }
    }
}

function local_haksa_get_ircategory_path($course) {
    global $DB;
    
    $path = array();
    $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber'=>'oklass_irregular'));
    $path[] = $course->cata1;
    $path[] = $course->cata2; 

    return $path;
}

function local_haksa_get_course_ircategories(&$list, $parentid = 0) {
    global $DB;

    $sql = "SELECT id, name
        FROM {course_categories}
        WHERE parent = :parent ";

    $params = array();
    $params['parent'] = $parentid;

    $categories = false;
    if (!$parentid) {
        $sql .=  "AND " . $DB->sql_like('idnumber', ':idnumber', false);
        $params['idnumber'] = 'oklass_irregular%';
        if ($categories = $DB->get_records_sql($sql, $params)) {
            foreach ($categories as $category) {
                $list[$category->id] = $category->name;
                local_haksa_get_course_categories($list, $category->id);
            }
        }
    } else {
        if ($categories = $DB->get_records_sql($sql, $params)) {
            foreach ($categories as $category) {
                $list[$category->id] = $list[$parentid] . LOCAL_HAKSA_CATEGORY_SEPARATOR . $category->name;
                local_haksa_get_course_categories($list, $category->id);
            }
        }
    }
}


function local_haksa_create_user($usernew, &$existingusers) {
    global $DB;

    $usernew->username = core_text::strtolower(trim($usernew->username));

    // Check username.
    if (empty($usernew->username)) {
        return -1; // Username is empty!
    }
    if ($usernew->username !== clean_param($usernew->username, PARAM_USERNAME)) {
        return -2; // Invalid username
    }


    // 성능향상
    //
    // isset이 array_key_exists 보다 2.5 배 정도 빠르다고 한다.
    // http://ilia.ws/archives/247-Performance-Analysis-of-isset-vs-array_key_exists.html
    //
    // 그러나, 배열값이 NULL 인 경우 FALSE를 리턴하기 때문에
    // 정확한 확인을 위해서는 array_key_exists를 사용하는 것이 좋다.
    // $existingusers의 경우 값이 NULL인 경우는 없을 것이므로
    // isset 함수를 사용한다.
    //
    // if(!array_key_exists($usernew->username, $existingusers)) {
    if (!isset($existingusers[$usernew->username])) {
        $usernew->password = hash_internal_user_password($usernew->password, true);

        // Insert the user into the database.
        $newuserid = $DB->insert_record('user', $usernew);
        if ($newuserid) {
            // Create USER context for this user.
            local_haksa_insert_context_record(CONTEXT_USER, $newuserid, '/' . SYSCONTEXTID, 0);

            $gtec_user = new stdClass();
            $gtec_user->userid = $newuserid;
            $gtec_user->eng_name = $usernew->eng_name;
            $gtec_user->usergroup = $usernew->usergroup;
            $gtec_user->b_temp = $usernew->b_temp;
            $gtec_user->b_mobile = $usernew->b_mobile;
            $gtec_user->b_email = $usernew->maildisplay;
            $gtec_user->univ = $usernew->univ;
            $gtec_user->major = $usernew->major;
            $gtec_user->b_tel = $usernew->b_tel;
            $gtec_user->b_univ = $usernew->b_univ;
            $gtec_user->b_major = $usernew->b_major;
            $gtec_user->ehks = $usernew->ehks;
            $gtec_user->edhs = $usernew->edhs;
            $gtec_user->domain = $usernew->domain;
            $gtec_user->hyhg = $usernew->hyhg;
            $gtec_user->persg = $usernew->persg;
            $gtec_user->psosok = $usernew->psosok;
            $gtec_user->sex = $usernew->sex;

            $DB->insert_record('lmsdata_user', $gtec_user);

            $existingusers[$usernew->username] = $newuserid;

            return $newuserid;
        } else {
            return -4; // Error
        }
    } else {
        return $existingusers[$usernew->username];
    }
}

function local_haksa_insert_context_record($contextlevel, $instanceid, $parentpath) {
    global $DB;

    $record = new stdClass();
    $record->contextlevel = $contextlevel;
    $record->instanceid = $instanceid;
    $record->depth = 0;
    $record->path = null; //not known before insert

    $record->id = $DB->insert_record('context', $record);

    // now add path if known - it can be added later
    if (!is_null($parentpath)) {
        $record->path = $parentpath . '/' . $record->id; 
        $record->depth = substr_count($record->path, '/');
        $DB->update_record('context', $record);
    }

    return $record;
}

/**
 * 브라우저로 메시지를 출력한다.
 * 메시지 끝에 '<br/>'을 붙여서 줄바꿈흘 한다.
 * @param string $message
 */
function local_haksa_println($message) {
    echo $message . '<br/>' . "\n";

    local_haksa_flushdata();
}

/**
 * 출력 버퍼에 있는 내용을 브라우저로 보낸다.
 */
function local_haksa_flushdata() {
    while (ob_get_level() > 0) {
        ob_end_flush();
    }

    flush();

    ob_start();
}

function local_haksa_scroll_down() {
    echo '<script type="text/javascript">
    window.scrollTo(0, document.body.scrollHeight);
</script>';

    local_haksa_flushdata();
}

function local_haksa_seconds_to_time($s) {
    $h = floor($s / 3600);
    $s -= $h * 3600;
    $m = floor($s / 60);
    $s -= $m * 60;
    return $h . ':' . sprintf('%02d', $m) . ':' . sprintf('%02d', $s);
}

function local_haksa_subejct_plan($subject_cd) {
    

    return  $data;
}