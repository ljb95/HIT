<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';
require_once $CFG->libdir . '/formslib.php';

require_login();

$type = optional_param('type', 1, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

//tab
if (!empty($type) && $type === 1) {
    $PAGE->set_url('/local/courselist/regular.php');
    $currenttab = 'regular';
    $param["type"] = 1;
} else {
    $PAGE->set_url('/local/courselist/irregular.php');
    $currenttab = 'irregular';
    $param["type"] = 2;
}

$strplural = get_string("pluginnameplural", "local_courselist");
$PAGE->navbar->add(get_string($currenttab,'local_courselist'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

echo $OUTPUT->header();
?>
<?php
/*
$rows = array (
    new tabobject('regular', "$CFG->wwwroot/local/courselist/index.php?type=1", get_string('regular', 'local_courselist')),
    new tabobject('irregular', "$CFG->wwwroot/local/courselist/index.php?type=2", get_string('irregular', 'local_courselist'))
    );
print_tabs(array($rows), $currenttab);
*/
if($type === 1) {
    include 'index.regular.php';
}else if($type === 2) {
    include 'index.irregular.php';
}
?>


<script type="text/javascript">
    function showCourseInfo(id) {
        var dialog_modal = $("<div id='dialog_courseinfo'></div>");
        $.ajax({
            url: 'courseinfo.php',
            method: 'POST',
            data: { 'id': id },
            success: function (data) {
                dialog_modal.html(data).dialog({
                    title: '<?php echo get_string('courseinfo', 'local_courselist'); ?>',
                    modal: true,
                    width: 'auto',
                    resizable: false,
                    buttons: [{id: 'close',
                            text: '<?php echo get_string('close', 'local_courselist'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}],
                    close: function () {
                        $(this).dialog('destroy').remove();
                    }
                }).dialog('open')
            }
        });
    }
    function showSyllabus(id) {
        var dialog_modal = $("<div id='dialog_courseinfo'></div>");
        $.ajax({
            url: 'syllabus.php',
            method: 'POST',
            data: { 'id': id },
            success: function (data) {
                dialog_modal.html(data).dialog({
                    title: '<?php echo get_string('syllabus', 'local_courselist'); ?>',
                    modal: true,
                    width: 'auto',
                    resizable: false,
                    buttons: [{id: 'close',
                            text: '<?php echo get_string('close', 'local_courselist'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}],
                    close: function () {
                        $(this).dialog('destroy').remove();
                    }
                }).dialog('open')
            }
        });
    }
    
    function applyEnrol() {
        var ids = [];
        $("input:checkbox[name='id[]']:checked").each(function() {
            ids.push($(this).val());
        });
        
        if($(ids).length == 0) {
            alert("<?php echo get_string('selectcourses', 'local_courselist'); ?>");
            return false;
        }
        
        if(!confirm('<?php echo get_string('apply:doyouwantto', 'local_courselist'); ?>')) {
            return false;
        }
        
        jQuery.ajax({
            url: "<?php echo $CFG->wwwroot.'/local/courselist/apply_enrol.ajax.php'; ?>",
            data: {
                    id: ids
            },
            dataType: "json",
            type: "POST",
            async: false,
            success: function(data) {
                if(data.status == 'success') {
                    alert(data.message);
                    $("input:checkbox[name='id[]']:checked").each(function() {
                        $(this).attr("checked", false);
                        $(this).attr("disabled", true);
                    });
                } else {
                    alert(data.message);
                }
            }
	});
    }
    function applySititon(){
        var ids = [];
        $("input:checkbox[name='id[]']:checked").each(function() {
            ids.push($(this).val());
        });
        
        if($(ids).length == 0) {
            alert("<?php echo get_string('selectcourses', 'local_courselist'); ?>");
            return false;
        }
        
        if(!confirm('<?php echo get_string('sititon:doyouwantto', 'local_courselist'); ?>')) {
            return false;
        }
        
        jQuery.ajax({
            url: "<?php echo $CFG->wwwroot.'/local/courselist/apply_sititon.ajax.php'; ?>",
            data: {
                    id: ids
            },
            dataType: "json",
            type: "POST",
            async: false,
            success: function(data) {
                if(data.status == 'success') {
                    alert(data.message);
                    $("input:checkbox[name='id[]']:checked").each(function() {
                        $(this).attr("checked", false);
                        $(this).attr("disabled", true);
                        
                        $("td#reg_status_"+$(this).val()).html("<?php echo get_string('course:registered', 'local_courselist'); ?>");
                    });
                } else {
                    alert(data.message);
                }
            }
	});
    }
    
    function course_all_select_submit(){
        $('#frm_course').submit();
    }
    
    function category_parent1_changed(parent, frm_id) {
        var mform = document.getElementById(frm_id);
        var elemCate = mform["parent2"];
        
        remove_select_options(elemCate);
        
        var categories = category_get_parent(parent);
	jQuery.each(categories, function (index, value) {
		var elOptNew = document.createElement('option');
		elOptNew.text = value.name;
                elOptNew.value = value.id;
		elemCate.add(elOptNew);
	});
        
        var elemSel = mform["parent3"];
        if(elemSel != undefined) {
            remove_select_options(elemSel);
        }
        
    }
     
    function category_parent2_changed(parent, frm_id) {
        var mform = document.getElementById(frm_id);
        var elemCate = mform["parent3"];
        
        remove_select_options(elemCate);
        
        var categories = category_get_parent(parent);
	jQuery.each(categories, function (index, value) {
		var elOptNew = document.createElement('option');
		elOptNew.text = value.name;
                elOptNew.value = value.id;
		elemCate.add(elOptNew);
	});
        
    }
    
    function category_get_parent(parent) {
	var categories = null;

	jQuery.ajax({
		url: "<?php echo $CFG->wwwroot.'/local/courselist/parent.ajax.php'; ?>",
		data: {
			"parent" : parent
		},
		dataType: "json",
		type: "POST",
		async: false,
		success: function(data) {
			categories = data;
		}
	});

	return categories;
    }
         
    function remove_select_options(elSel) {
        for(var i = elSel.length - 1; i > 0 ; i--) {
            elSel.remove(i);
        }
    }
    
    function goto_page(page) {
        $('[name=page]').val(page);
        $('#frm_course').submit();
    }
    
    function change_perpage(perpage) {
        $('[name=perpage]').val(perpage);
        $('#frm_course').submit();
    }
    
</script>

<?php
echo $OUTPUT->footer();
?>
