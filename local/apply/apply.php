<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * My Moodle -- a user's personal dashboard
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    moodlecore
 * @subpackage my
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/siteadmin/lib.php');
//require_once($CFG->dirroot . '/local/coursepoint/index.php');

redirect_if_major_upgrade_required();

purge_all_caches();

// TODO Add sesskey check to edit
$edit = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset = optional_param('reset', null, PARAM_BOOL);
$type = optional_param('type', 0, PARAM_INT); // 페이지 타입
$coursetype = optional_param('coursetype', 3, PARAM_INT); // 강좌타입
$year = optional_param('courseyear', 0, PARAM_INT); // 강의년도
$term = optional_param('courseterm', 0, PARAM_INT); // 학기
$searchvalue = optional_param('searchvalue', '', PARAM_RAW); // 강좌명
$searchtype = optional_param('searchtype', 0, PARAM_INT); // 검색타입
$perpage = optional_param('perpage', 10, PARAM_INT); // 보여줄 개수
$page = optional_param('page', 1, PARAM_INT); // 페이지

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
//$strmymoodle = get_string('myhome');
//페이지 브라우저 타이틀을 사이트명으로 변경(지노테크 정민정 - 2016/10/20)
$strmymoodle = $SITE->fullname;


// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}

// Start setting up the page
$params = array();
$PAGE->set_context($context);
//$PAGE->set_url('/my/allcourse.php', $params);
$strplural = get_string("major_auditor", "local_lmsdata");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('application','local_lmsdata'));
$PAGE->set_title($strplural);



$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('my-allcourse');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);

if (!isguestuser()) {   // Skip default home page for guests
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)), navigation_node::TYPE_SETTING);
        }
    }
}


echo $OUTPUT->header();
?>
        <div class="table_group">
            <h3><?php echo get_string('application','local_lmsdata') ?></h3>
        </div>

        <div class="table-title">
            <b ><?php echo get_string('assistant_application','local_lmsdata') ?></b>
        </div>
        <div class="table_group">
            <table class="generaltable regular-courses">
                <thead>
                    <tr>
                        <th scope="row" width="5%"><?php echo get_string('stats_years','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('stats_terms','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('lecture_num','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('stats_coursename','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('apply_handle','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('apply_reason','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('Approval_status','local_lmsdata') ?></th>
                        <th scope="row" width="5%">미승인/승인취소사유</th>
                    </tr>   
                </thead>
<!--                <tbody>
                    <?php
                    $applies = $DB->get_records('approval_reason',array('userid'=>$USER->id,'application_type'=>'assistant'),'apply_date desc');
                     foreach ($applies as $apply) {
                         
                         $sql_select  = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, lc.ohakkwa ,
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid,
                ur.firstname, ur.lastname";

            $sql_from    = " FROM {course} mc
                             JOIN {lmsdata_class} lc ON lc.course = mc.id
                             JOIN {course_categories} ca ON ca.id = mc.category 
                             LEFT JOIN {user} ur ON ur.id = lc.prof_userid 
                             where mc.id = :courseid";
            $course = $DB->get_record_sql($sql_select.$sql_from,array('courseid'=>$apply->courseid));
                         ?>
                    <tr>
                        <td><?php echo $course->year; ?></td>
                        <td><?php echo $course->term; ?></td>
                        <td><?php echo $course->subject_id ?></td>
                        <td style="text-align: left; cursor: pointer;" onclick="course_preview('<?php echo $course->id; ?>')"><?php echo $course->fullname ?></td>
                        <td><?php echo date('Y-m-d',$apply->apply_date); ?> / <?php if($apply->processing_date){ echo date('Y-m-d',$apply->processing_date); } else { echo '-'; } ?></td>
                        <td><input type="button" onclick="approval('<?php echo $course->id; ?>','<?php echo $apply->approval_status; ?>')" class="btn_st01" value="상세보기"></td>
                        <td>
                            <?php 
                            switch($apply->approval_status){
                                case 0:
                                    echo '승인대기';
                                    break;
                                    case 1:
                                        echo '승인';
                                        break;
                                        case 2:
                                            echo '미승인';
                                            break;
                                            case 3:
                                                echo '승인취소';
                                                break;
                            }
                            ?>
                        </td>
                        <td> <?php if($apply->approval_status > 1){ ?> <input type="button" onclick="javascript:view_value('<?php echo $apply->id;?>')" class="btn_st01" value="상세보기"> <?php } else { echo '-'; } ?></td>
                    </tr>
                    <?php
                      }
                      if(!$applies){
                            echo '<tr><td colspan="8">' . get_string('course:empty', 'local_okregular') . '</td></tr>';
                      }
        ?>
            </tbody>-->
                 <tbody>
                    <?php
                    $applies = $DB->get_records('approval_reason',array('userid'=>$USER->id,'application_type'=>'assistant'),'apply_date desc');
                     foreach ($applies as $apply) {
                         
                         $sql_select  = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, lc.ohakkwa ,
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid,
                ur.firstname, ur.lastname";

            $sql_from    = " FROM {course} mc
                             JOIN {lmsdata_class} lc ON lc.course = mc.id
                             JOIN {course_categories} ca ON ca.id = mc.category 
                             LEFT JOIN {user} ur ON ur.id = lc.prof_userid 
                             where mc.id = :courseid";
            $course = $DB->get_record_sql($sql_select.$sql_from,array('courseid'=>$apply->courseid));
                       
            ?>
                    <tr>
                        <td><?php echo $course->year; ?></td>
                        <td>
                            <?php
                              if($course->term == 1 || $course->term == 2){
                                    $term =$course->term.get_string('term','local_lmsdata');
                                } else if ($course->term == 3 || $course->term == 4) {
                                    $term = str_replace(array(3,4), array( get_string('summer', 'local_okregular'), get_string('winter','local_okregular')),$course->term);
                                } else {
                                $term = '-';
                               }
                        echo $term; 
                        ?>
                        </td>
                        <td><?php echo $course->subject_id ?></td>
                        <td style="text-align: left; cursor: pointer;" onclick="course_preview('<?php echo $course->id; ?>')"><?php echo $course->fullname ?></td>
                        <td><?php echo date('Y-m-d',$apply->apply_date); ?> / <?php if($apply->processing_date){ echo date('Y-m-d',$apply->processing_date); } else { echo '-'; } ?></td>
                        <td><input type="button" onclick="approval('<?php echo $course->id; ?>','<?php echo $apply->approval_status; ?>')" class="btn_st01" value="상세보기"></td>
                        <td>
                            <?php 
                            switch($apply->approval_status){
                                case 0:
                                    echo '승인대기';
                                    break;
                                    case 1:
                                        echo '승인';
                                        break;
                                        case 2:
                                            echo '미승인';
                                            break;
                                            case 3:
                                                echo '승인취소';
                                                break;
                            }
                            ?>
                        </td>
                        <td> <?php if($apply->approval_status > 1){ ?> <input type="button" onclick="javascript:view_value('<?php echo $apply->id;?>')" class="btn_st01" value="상세보기"> <?php } else { echo '-'; } ?></td>
                    </tr>
                    <?php
                      }
                      if(!$applies){
                            echo '<tr><td colspan="8">' . get_string('course:empty', 'local_okregular') . '</td></tr>';
                      }
        ?>
            </tbody>
            </table>
        </div>  
                <div>
                    <?php
                    $baseurl = '/local/apply/apply.php';
                    $params = array('searchtype'=>$searchtype,'searchvalue'=>$searchvalue,'year'=>$year,'term'=>$term);
                     echo my_print_paging_navbar($total_count, $page, $perpage, $baseurl, $params); 
                    ?>
                </div>

        <div class="table-title">
            <b><?php echo get_string('auditor_application','local_lmsdata') ?></b>
        </div>
        <div class="table_group">
            <table class="generaltable regular-courses">
                <thead>
                    <tr>
                        <th scope="row" width="5%"><?php echo get_string('stats_years','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('stats_terms','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('lecture_num','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('stats_coursename','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('apply_handle','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('apply_reason','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('Approval_status','local_lmsdata') ?></th>
                        <th scope="row" width="5%">미승인/승인취소사유</th>
                    </tr>   
                </thead>
                 <tbody>
                    <?php
                    $applies = $DB->get_records('approval_reason',array('userid'=>$USER->id,'application_type'=>'auditor'),'apply_date desc');
                     foreach ($applies as $apply) {
                         
                         $sql_select  = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, lc.ohakkwa ,
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid,
                ur.firstname, ur.lastname";

            $sql_from    = " FROM {course} mc
                             JOIN {lmsdata_class} lc ON lc.course = mc.id
                             JOIN {course_categories} ca ON ca.id = mc.category 
                             LEFT JOIN {user} ur ON ur.id = lc.prof_userid 
                             where mc.id = :courseid";
            $course = $DB->get_record_sql($sql_select.$sql_from,array('courseid'=>$apply->courseid));
                         ?>
                    <tr>
                        <td><?php echo $course->year; ?></td>
<!--                        <td><?php echo $course->term; ?></td>-->
                        <td>
                            <?php
                                if($course->term == 1 || $course->term == 2){
                                    $term =$course->term.get_string('term','local_lmsdata');
                                } else if ($course->term == 3 || $course->term == 4) {
                                    $term = str_replace(array(3,4), array( get_string('summer', 'local_okregular'), get_string('winter','local_okregular')),$course->term);
                                } else {
                                $term = '-';
                               }
                        echo $term; 
                        ?>
                        </td>
                        <td><?php echo $course->subject_id ?></td>
                        <td style="text-align: left; cursor: pointer;" onclick="course_preview('<?php echo $course->id; ?>')"><?php echo $course->fullname ?></td>
                        <td><?php echo date('Y-m-d',$apply->apply_date); ?> / <?php if($apply->processing_date){ echo date('Y-m-d',$apply->processing_date); } else { echo '-'; } ?></td>
                        <td><input type="button" onclick="approval('<?php echo $course->id; ?>','<?php echo $apply->approval_status; ?>')" class="btn_st01" value="상세보기"></td>
                        <td>
                            <?php 
                            switch($apply->approval_status){
                                case 0:
                                    echo '승인대기';
                                    break;
                                    case 1:
                                        echo '승인';
                                        break;
                                        case 2:
                                            echo '미승인';
                                            break;
                                            case 3:
                                                echo '승인취소';
                                                break;
                            }
                            ?>
                        </td>
                     <td> <?php if($apply->approval_status > 1){ ?> <input type="button" onclick="view_value('<?php echo $apply->id;?>')" class="btn_st01" value="상세보기"> <?php } else { echo '-'; } ?></td>
                    </tr>
                    <?php
                      }
                      if(!$applies){
                            echo '<tr><td colspan="8">' . get_string('course:empty', 'local_okregular') . '</td></tr>';
                      }
        ?>
            </tbody>
            </table>
        </div> 
                <div>
                    <?php
                    $baseurl = '/local/apply/apply.php';
                    $params = array('searchtype'=>$searchtype,'searchvalue'=>$searchvalue,'year'=>$year,'term'=>$term);
                     echo my_print_paging_navbar($total_count, $page, $perpage, $baseurl, $params); 
                    ?>
                </div>
<?php
echo $OUTPUT->footer();
?>
<script type="text/javascript">
function course_preview(courseid){
        var tag = $("<div id='course_preview'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/local/apply/assistance_ajax.php'; ?>',
          method: 'POST',
          data: {
            id : courseid
          },
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('course_preview', 'local_lmsdata')?>',
                modal: true,
                width: 800,
                resizable: false,
                height: 500,
                buttons: [ 
                        {id:'close',
                            text:'닫기',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}
                       
                    ],
                close: function () {
                    $( this ).dialog('destroy').remove();
                }
            }).dialog('open');
          }
        });
    }

    function approval(courseid,status){
    if(!status){
        status = 0;
    }
        var tag2 = $("<div id='appval'></div>");
        if(status == 0){
        var btn = [ 
                        {id:'save',
                            text:'저장',
                            disable: true,
                            click: function() {
                                $('#apply_reason').submit();
                            }},
                        {id:'close',
                            text:'닫기',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}
                       
                    ];
                } else { 
               var btn = [ 
                    {id:'close',
                            text:'닫기',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}
                       
                    ];
                }
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/local/apply/apply_reason.php'; ?>',
          method: 'POST',
          data: {
            id : courseid
          },
          success: function(data) {
            tag2.html(data).dialog({
                title: '신청사유',
                modal: true,
                width: 400,
                resizable: false,
                height: 257,
                buttons: btn,
                close: function () {
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
    
    
    function view_value(id){
        var tag2 = $("<div id='appval'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/local/apply/cancel_reason.php'; ?>',
          method: 'POST',
          data: {
            id : id
          },
          success: function(data) {
            tag2.html(data).dialog({
                title: '미승인/승인취소 사유',
                modal: true,
                width: 400,
                resizable: false,
                height: 257,
                buttons: [ 
                        {id:'close',
                            text:'닫기',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }
                        }  
                    ],
                close: function () {
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }

</script>