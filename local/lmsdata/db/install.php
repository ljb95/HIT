<?php

function xmldb_local_lmsdata_install() {
    
    global $DB, $CFG;
    
    $menu_main = array();

    $menu = new Stdclass();

    $menu->sortorder = 0;
    $menu->default = true;
    $menu->koname = '강의알림사항';
    $menu->enname = 'Course Notice';
    $menu->url = $CFG->wwwroot.'/local/courselist/courseoverview.php';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'course_notice_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

    $menu = new Stdclass();
    $menu->sortorder = 1;
    $menu->default = true;
    $menu->koname = '강의콘텐츠저장소';
    $menu->enname = 'Resources';
    $menu->url = $CFG->wwwroot.'/local/repository/index.php';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'repository_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

    $menu = new Stdclass();
    $menu->sortorder = 2;
    $menu->default = true;
    $menu->koname = 'CDMS';
    $menu->enname = 'CDMS';
    $menu->url = $CFG->wwwroot.'/local/repository/cdms.php';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'cdms_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

    $menu = new Stdclass();
    $menu->sortorder = 3;
    $menu->default = true;
    $menu->koname = '평가/설문';
    $menu->enname = 'Evaluation&Survey';
    $menu->url = $CFG->wwwroot.'/local/evaluation/index.php';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'survey_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

    $menu = new Stdclass();
    $menu->sortorder = 4;
    $menu->default = true;
    $menu->koname = '개인일정표';
    $menu->enname = 'My Schedule';
    $menu->url = $CFG->wwwroot.'/calendar/view.php?view=month';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'calendar_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

                $sql = "
         SELECT DISTINCT
             h.id,
             h.name,
             h.wwwroot,
             a.name as application,
             a.display_name
         FROM
             {mnet_host} h,
             {mnet_application} a,
             {mnet_host2service} h2s_IDP,
             {mnet_service} s_IDP,
             {mnet_host2service} h2s_SP,
             {mnet_service} s_SP
         WHERE
             h.id <> ? AND
             h.id <> ? AND
             h.id = h2s_IDP.hostid AND
             h.deleted = 0 AND
             h.applicationid = a.id AND
             h2s_IDP.serviceid = s_IDP.id AND
             s_IDP.name = 'sso_idp' AND
             h2s_IDP.publish = '1' AND
             h.id = h2s_SP.hostid AND
             h2s_SP.serviceid = s_SP.id AND
             s_SP.name = 'sso_idp' AND
             h2s_SP.publish = '1' AND
             a.name = 'mahara' 
         ORDER BY
             a.display_name,
             h.name";

    $hosts = $DB->get_records_sql($sql, array($CFG->mnet_localhost_id, $CFG->mnet_all_hosts_id));
    if($hosts){
        foreach ($hosts as $host) {
            $url = $CFG->wwwroot . "/auth/mnet/jump.php?hostid={$host->id}";
        }
        $menu = new Stdclass();
        $menu->sortorder = 5;
        $menu->default = true;
        $menu->koname = '이포트폴리오';
        $menu->enname = 'My Profile';
        $menu->url = $url;
        $menu->target = 0;
        $menu->disable = true;
        $menu->icon = 'mahara_gray';
        $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
        $menu_main[$menu->sortorder] = $menu;
    }
    $menu = new Stdclass();
    $menu->sortorder = 6;
    $menu->default = true;
    $menu->koname = '공지사항';
    $menu->enname = 'Notice';
    $menu->url = $CFG->wwwroot . '/local/jinoboard/list.php?id=1';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'notice_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

    $menu = new Stdclass();
    $menu->sortorder = 7;
    $menu->default = true;
    $menu->koname = 'FAQ';
    $menu->enname = 'FAQ';
    $menu->url = $CFG->wwwroot . '/local/jinoboard/list.php?id=3';
    $menu->target = 0;
    $menu->disable = true;
    $menu->icon = 'faq_gray';
    $menu->role = array('sa'=> true, 'pr'=>true, 'ad'=>true, 'rs'=>true, 'gs'=>true);
    $menu_main[$menu->sortorder] = $menu;

    $menu_main = serialize($menu_main);
    set_config('siteadmin_menu_main', $menu_main);
    
    
    /* Tutor 추가 */
    $role = $DB->get_record('role',array('shortname'=>'Tutor'));
    if($role){
        delete_role($role->id);
    }
    create_role('Tutor', 'Tutor', 'Tutor','editingteacher');
    $rolerecord = $DB->get_record('role', array("shortname" => 'Tutor'));
    // 모듈과 코스안에서 부여 받는 권한, 선언
    set_role_contextlevels($rolerecord->id, array(CONTEXT_MODULE,CONTEXT_COURSE));
    
    
    return true;
}