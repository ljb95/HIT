<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$type = optional_param('type', 1, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$target = optional_param('target', '', PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);

$board = $DB->get_record('jinoboard', array('type' => $type));
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/jinoboard/index.php');

$where = array('board = :board');
$params = array('board' => $board->id);

if (!empty($search)) {
    $where[] =  $DB->sql_like('title', ':search');
    $params['search'] = '%'.$search.'%';
}

if (!empty($target)) {
    $where[] =  $DB->sql_like('targets', ':targets');
    $params['targets'] = '%'.$target.'%';
}

$select = ' SELECT * ';
$from = ' FROM {jinoboard_contents} ';
$count_where = ' WHERE '.implode(' AND ',$where);
$orderby = ' ORDER BY timemodified DESC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$from.$count_where.$orderby, $params);
$total_pages = jinoboard_get_total_pages($totalcount, $perpage);

// 상단 노출 공지
$where[] = ' isnotice = :isnotice';
$params['isnotice'] = 1;
$where = ' WHERE '.implode(' AND ',$where);
$notice_contents = $DB->get_records_sql($select.$from.$where.$orderby, $params);

$params['isnotice'] = 0;
$contents = $DB->get_records_sql($select.$from.$where.$orderby, $params);


$targets = define_targets();
$perpages = define_perpages();

$totalcontent = 0;

$offset = ($page -1) * $perpage;


?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('notice','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./notices.php"><?php echo get_string('notice','local_lmsdata'); ?></a> > <?php echo get_string('list','local_lmsdata'); ?></div>
        <form id="frm_notices_search" class="search_area">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <select name="target" class="w_160">
                <option value=""><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php foreach($targets as $key=>$val) {
                    $select = '';
                    if($target == $key){
                        $select = 'selected';
                    }
                    echo '<option value="'.$key.'" '.$select.'>'.$val.'</option>';
                }
                ?>
            </select>
            <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
            <input type="submit" class="search_btn" id="search" value="<?php echo get_string('search', 'local_jinoboard'); ?>">
            <select name="perpage" class="w_160" onchange="this.form.submit();" style="width:100px; float:right;">
                <?php foreach($perpages as $val) {
                     echo '<option value="'.$val.'" '.(($perpage==$val)?'selected':'').'>'.get_string('perpage','local_lmsdata',$val).'</option>';   
                }
                ?>
            </select>
        </form>
        <form action="./notices_del.php" id="del_form" method="post">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <th style="width:5%;"><input type="checkbox" id="allcheck" style="margin: 0 !important;"/></th>
                <th style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('title', 'local_lmsdata'); ?></th>
                <th style="width:15%;"><?php echo get_string('target','local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('author','local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('datecreated', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('gubun','local_lmsdata'); ?></th>
            </tr>
            <?php
            if(!empty($notice_contents)){
                foreach ($notice_contents as $notice) {
                    echo '<tr class="isnotice" style="background-color: #e9f3ff !important;">';
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $notice->id, 'timemodified', false);
                    if (count($files) > 0) {
                        $filecheck = '<img src="../img/icon-attachment.png" alt="' . get_string('content:file', 'local_jinoboard') . '"  class="icon-attachment">';
                    } else {
                        $filecheck = "";
                    }

                    if ($notice->step) {
                        $step_left_len = $notice->step * 15;
                        $step_left = 'style="margin-left:' . $step_left_len . 'px;border: 1px solid #ccc;"';
                    } else {
                        $step_left = '';
                    }

                    $timestatus = ($notice->timeend >= time()+86400)? '게시중':'종료';

                    $postuser = $DB->get_record('user', array('id' => $notice->userid));
                    $fullname = fullname($postuser);
                    $userdate = userdate($notice->timecreated);

                    $tars = explode(',',$notice->targets); 
                    $targets_txt_arr = array(); 
                    $targets_txt = '';
                    foreach($tars as $val){
                        $targets_txt_arr[] = $targets[$val];
                    }
                    $targets_txt = implode(',',$targets_txt_arr);

                    echo "<td style='background-color: #e9f3ff !important;'><input type='checkbox' name='check_list[]' value='".$notice->id."' class='check_notice' id='".$notice->id."' ></td>";
                    echo "<td style='background-color: #e9f3ff !important;'></td>";
                    if ($notice->issecret && $USER->id != $notice->userid && !is_siteadmin()) {
                        echo "<td style='background-color: #e9f3ff !important;'><a href='#' onclick='" . 'alert(' . get_string('secretalert', 'local_jinoboard') . ')' . "'>" . $notice->title . get_string('secreticon', 'local_jinoboard') . " </a></td>";
                    } else {
                        echo "<td style='background-color: #e9f3ff !important;'><a href='notices_view.php?id=" . $notice->id . "'>" . $notice->title . "</a>".$filecheck."</td>";
                    }
                    echo "<td style='background-color: #e9f3ff !important;'>".$targets_txt."</td>";
                    echo '<td style="background-color: #e9f3ff !important;">' . $fullname . '</td>';

                    echo "<td style='background-color: #e9f3ff !important;'>" . date("Y-m-d", $notice->timecreated) . "</td>";
                    echo "<td style='background-color: #e9f3ff !important;'>" . $timestatus . "</td>";
                    echo "</tr>";
                    $totalcontent++;
                }
            }
            
            $offset = 0;
            if ($page != 0) {
                $offset = ($page - 1) * $perpage;
            }
            $num = $totalcount - $offset;

            if ($num > 0) {
                foreach ($contents as $content) {
                        echo '<tr>';
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
                        if (count($files) > 0) {
                            $filecheck = '<img src="../img/icon-attachment.png" alt="' . get_string('content:file', 'local_jinoboard') . '"  class="icon-attachment">';
                        } else {
                            $filecheck = "";
                        }

                        if ($content->step) {
                            $step_left_len = $content->step * 15;
                            $step_left = 'style="margin-left:' . $step_left_len . 'px;border: 1px solid #ccc;"';
                        } else {
                            $step_left = '';
                        }
                        
                        $timestatus = ($content->timeend >= time()+86400)? '게시중':'종료';
                         
                        $postuser = $DB->get_record('user', array('id' => $content->userid));
                        $fullname = fullname($postuser);
                        $userdate = userdate($content->timecreated);
                        
                        $targets_txt_arr = array(); 
                        $targets_txt = '';
                        if(!empty($content->targets)){
                            $tars = explode(',',$content->targets); 
                            foreach($tars as $val){
                                $targets_txt_arr[] = $targets[$val];
                            }
                            $targets_txt = implode(',',$targets_txt_arr);
                        }

                        echo "<td><input type='checkbox' name='check_list[]' value='".$content->id."' class='check_notice' id='$content->id' ></td>";
                        echo "<td>" . $num . "</td>";
                        if ($content->issecret && $USER->id != $content->userid && !is_siteadmin()) {
                            echo "<td><a href='#' onclick='" . 'alert(' . get_string('secretalert', 'local_jinoboard') . ')' . "'>" . $content->title . get_string('secreticon', 'local_jinoboard') . " </a></td>";
                        } else {
                            echo "<td><a href='notices_view.php?id=" . $content->id . "'>" . $content->title . "</a>".$filecheck."</td>";
                        }
                        echo "<td>".$targets_txt."</td>";
                        echo '<td>' . $fullname . '</td>';

                        echo "<td>" . date("Y-m-d", $content->timecreated) . "</td>";
                        echo "<td>" . $timestatus . "</td>";
                        echo "</tr>";
                        $num--;
                        $totalcontent++;
                    }
            } 
            if($totalcontent == 0){
                echo "<tr><td colspan='8' align='center'>".get_string('nodata','local_lmsdata')."</td></tr>";
            }
            ?>
        </table>
        </form>
        <div id="btn_area">
            <input type="button" value="<?php echo get_string('writepost', 'local_jinoboard') ?>" onclick="location.href = 'notices_write.php?type=<?php echo $type; ?>'" class="blue_btn" style="float:right;"/>
            <input type="button" id="delete_notice" class="red_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: left" />
        </div>    
            <?php
            $page_params = array();
            $page_params['type'] = $type;
            $page_params['perpage'] = $perpage;
            $page_params['search'] = $search;
            print_paging_navbar_notice("notices.php", $page_params, $total_pages, $page);
            ?>
            <!-- Breadcrumbs End -->
        </div> <!-- Table Footer Area End -->
    </div>
</div>

<script>
    $(function () {
        $("#accordion").accordion({
            collapsible: true,
            heightStyle: "content",
            header: "h3",
            active: false
        });
        $("#accordion").accordion("option", "icons", null);
    });
//	$('#accordion input[type="checkbox"]').click(function(e) {
//		e.stopPropagation();
//	});

 $(document).ready(function () {
    $('#allcheck').click(function() {
           if($('#allcheck').is(":checked")){
               $(".check_notice").each(function(){
                this.checked = true;   
               });
           }else{
                $(".check_notice").each(function(){
                this.checked = false;   
               });
           }
    });
    $('#delete_notice').click(function() {
        $('#del_form').submit();
    });
//    $('#delete_notice').click(function() {
//        if(confirm("삭제 하시겠습니까")){  
//            var del_list =[];
//            $(".check_notice").each(function(index, element){
//                if($(this).is(":checked")){
//                    del_list.push(this.id) ;
//                }
//            })
//            
//              $.ajax({
//                  url : "./notices_del.php",
//                  type: "post",
//                  data : {
//                      data : del_list                            
//                  },
//                  async: false,
//                  success: function(data){
//                     location.href = "./notices.php";
//                  },
//                  error:function(e){
//                      console.log(e.responseText);
//                  }
//              }); 
//          }
//      });

    $('#search').click(function() {
       var searchfield = $('#searchfield').val();
       var searchval = $('#searchval').val();
       var timestart = $('#timestart').val();
       var timeend = $('#timeend').val();

       location.href = "./notice.php?searchfield="+searchfield+"&searchvalue="+searchval+"&timestart="+timestart+"&timeend="+timeend;
    });
});
</script>
<?php include_once('../inc/footer.php'); ?>
