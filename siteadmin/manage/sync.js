function sync_set_config() {
    var dialog_modal = $("<div id='dialog_modal'></div>");
    $.ajax({
        url: 'sync.config.ajax.php',
        method: 'POST',
        data: $("#frm_sync_config").serialize(),
        success: function (data) {
            dialog_modal.html(data).dialog({
                title: '현재학기 설정',
                modal: true,
                width: 'auto',
                resizable: false,
                buttons: [{id: 'close',
                        text: '닫기',
                        disable: true,
                        click: function () {
                            $(this).dialog("close");
                        }}],
                position: { my: "center", at: "center", of: window },
                close: function () {
                    $(this).dialog('destroy').remove();
                }
            }).dialog('open');
        }
    });
}


function sync_delete_haksa_class(year, term) {
    var dialog_modal = $("<div id='dialog_modal'></div>");
    $.ajax({
        url: 'sync.course.delete.ajax.php',
        method: 'POST',
        data: {
            'year' : year,
            'term' : term
        },
        success: function (data) {
            dialog_modal.html(data).dialog({
                title: '강의삭제',
                modal: true,
                width: 'auto',
                resizable: false,
                buttons: [{id: 'close',
                        text: '닫기',
                        disable: true,
                        click: function () {
                            $(this).dialog("close");
                        }}],
                position: { my: "center", at: "center", of: window },
                close: function () {
                    $(this).dialog('destroy').remove();
                    window.location.reload();
                }
            }).dialog('open');
        }
    });
}

function sync_delete_haksa_delete() {
    var del_list =[];
    $("input[type=checkbox]#haksa_delete_id").each(function(index, element){
        if($(this).is(":checked")){
            del_list.push($(this).val()) ;
        }
    });
    
    if(del_list.length == 0){
        alert("삭제할 대상을 선택하세요.");
        return false;
    }
    
    
    var dialog_modal = $("<div id='dialog_modal'></div>");
    $.ajax({
        url: 'sync.course.delete.delete.ajax.php',
        method: 'POST',
        data: {
            'id' : del_list                            
        },
        success: function (data) {
            dialog_modal.html(data).dialog({
                title: '삭제',
                modal: true,
                width: 'auto',
                resizable: false,
                buttons: [{id: 'close',
                        text: '닫기',
                        disable: true,
                        click: function () {
                            $(this).dialog("close");
                        }}],
                position: { my: "center", at: "center", of: window },
                close: function () {
                    $(this).dialog('destroy').remove();
                    window.location.reload();
                }
            }).dialog('open');
        }
    });
}

function sync_restore_haksa_class() {
    var res_list =[];
    $("input[type=checkbox]#haksa_class_id").each(function(index, element){
        if($(this).is(":checked")){
            res_list.push($(this).val()) ;
        }
    });
    
    if(res_list.length == 0){
        alert("복구할 강의를 선택하세요.");
        return false;
    }
    
    
    var dialog_modal = $("<div id='dialog_modal'></div>");
    $.ajax({
        url: 'sync.course.restore.ajax.php',
        method: 'POST',
        data: {
            'id' : res_list                            
        },
        success: function (data) {
            dialog_modal.html(data).dialog({
                title: '강의복구',
                modal: true,
                width: 'auto',
                resizable: false,
                buttons: [{id: 'close',
                        text: '닫기',
                        disable: true,
                        click: function () {
                            $(this).dialog("close");
                        }}],
                position: { my: "center", at: "center", of: window },
                close: function () {
                    $(this).dialog('destroy').remove();
                    window.location.reload();
                }
            }).dialog('open');
        }
    });
}

function sync_goto_config(tab) {
    location.href = "sync.php?tab=" + tab;
}

function sync_goto_page(page) {
    $('[name=page]').val(page);
    $('#course_search').submit();
}

function sync_view_classes(deleted) {
    $('form#course_search input[type=hidden][name=page]').val(1);
    $('form#course_search input[type=hidden][name=deleted]').val(deleted);
    $('form#course_search').submit();
}

function sync_delete_haksa_student_change() {
    var del_list =[];
    
    $("input[type=checkbox]#haksa_change_id").each(function(index, element){
        if($(this).is(":checked")){
            del_list.push($(this).val()) ;
        }
    });
    
    if(del_list.length == 0){
        alert("삭제할 대상을 선택하세요.");
        return false;
    }
    
    
    var dialog_modal = $("<div id='dialog_modal'></div>");
    $.ajax({
        url: 'sync.participant.change.delete.ajax.php',
        method: 'POST',
        data: {
            'id' : del_list                            
        },
        success: function (data) {
            dialog_modal.html(data).dialog({
                title: '변경 삭제',
                modal: true,
                width: 'auto',
                resizable: false,
                buttons: [{id: 'close',
                        text: '닫기',
                        disable: true,
                        click: function () {
                            $(this).dialog("close");
                        }}],
                position: { my: "center", at: "center", of: window },
                close: function () {
                    $(this).dialog('destroy').remove();
                    window.location.reload();
                }
            }).dialog('open');
        }
    });
}