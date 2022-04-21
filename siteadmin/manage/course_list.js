function cata1_changed(sel) {
    var selCata2 = $('#course_search_cata2');
    cata_clean_select(selCata2);

    if($(sel).val() == 0) {
        return;
    }
    var categories = cata_get_child_categories($(sel).val());
    if(categories !== null) {
        cata_add_select_options(selCata2, categories);
    }
}

function cata2_changed(sel) {
    selCata3 = $('#course_search_cata3');
    cata_clean_select(selCata3);

    if($(sel).val() == 0) {
        return;
    }
    
    var categories = cata_get_child_categories($(sel).val());
    if(categories !== null) {
        cata_add_select_options(selCata3, categories);
    }
}

function cata_get_child_categories(pid) {
    var categories = null;
    $.ajax({
        url: '/siteadmin/manage/child_categories.ajax.php',
        method: 'POST',
        dataType: 'json',
        async: false,
        data: {
            id: pid
        },
        success: function (data) {
            if(data.status == 'success') {
                categories = data.categories;
            } else {
                //alert(data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown ) {
            alert(jqXHR.responseText);
        }
    });
    
    return categories;
}

function cata_add_select_options(sel, options) {
    $.each(options, function (i, option) {
        sel.append($('<option>', { 
            value: option.id,
            text : option.name 
        }));
    });
}

function cata_clean_select(sel) {
    $(sel[0].options).each(function() {
        if($(this).val() != 0) {
            $(this).remove();
        };
    });
}

function cata_page(page) {
    $('[name=page]').val(page);
    $('#course_search').submit();
}

function add_session_courses(){
    var add_list =[];
    $(".courseid").each(function(index, element){
      if($(this).is(":checked")){
          add_list.push($(this).val()) ;
      }
    });
    
    $.ajax({
        url : "./course_list_add.session.ajax.php",
        type: "post",
        data : {
            data : add_list                            
        },
        async: false,
        success: function(data){
           $('#course_search').submit();
        },
        error:function(e){
            console.log(e.responseText);
        }
    }); 
}
function del_session_courses(){
    
    $.ajax({
        url : "./course_list_del.session.ajax.php",
        type: "post",
        async: false,
        success: function(data){
           $('#course_search').submit();
        },
        error:function(e){
            console.log(e.responseText);
        }
    }); 
}

function check_course_id(check, checkClass){
    if($(check).is(":checked")){
        $("."+checkClass).each(function(){
            this.checked = true;   
        });
    }else{
        $("."+checkClass).each(function(){
            this.checked = false;   
        });
    }
}

function create_drive_dialog(){
    
    var tag = $("<div id='course_drive_popup'></div>");
    var drive_list =[];
    var count = 0;
    var standardId;
    var flag;
    $(".scourseid").each(function(index, element){
      if($(this).is(":checked")){
          drive_list.push($(this).val()) ;
          count++;
      }
    });
    
    if(count < 2) {
        alert("2개 이상의 강의를 선택해야 합니다.");
        return false;
    }
    
     $.ajax({
          url: 'course_list_drive.ajax.php',
          method: 'POST',
          data : {
            course : drive_list
          },
          success: function(data) {
                tag.html(data).dialog({
                    title: '분반몰아넣기',
                    modal: true,
                    width: 600,
                    resizable: false,
                    buttons: [ {id:'close',
                                text:'분반몰아넣기',
                                disable: true,
                                click: function() {
                                    if($("input[name=course_standard]:radio:checked").length == 0){
                                        alert("기준 분반을 선택 하세요");
                                        return false;
                                    } if($("input[name=flag]:radio:checked").length == 0){
                                        alert("기준 외 분반 비활성/삭제 를 선택해 주세요");
                                        return false;
                                    } else {
                                        standardId = $("input:radio[name=course_standard]:checked").val();
                                        flag = $("input:radio[name=flag]:checked").val();
                                        $('#frm_course_standard').remove();
                                        $( this ).dialog('destroy').remove();
                                        course_drive_execute(standardId, drive_list, flag);
                                    }
                                }},
                                {id:'close',
                                text:'취소',
                                disable: true,
                                click: function() {
                                    $( this ).dialog( "close" );
                                }}
                        ],
                    close: function () {
                        $('#frm_course_standard').remove();
                        $( this ).dialog('destroy').remove()
                    }
                }).dialog('open');
          }
        });
}

function course_drive_execute(standard, list, flag){
      $.ajax({
          url: 'course_list_drive.execute.php',
          method: 'POST',
          dataType: 'json',
          data : {
            standard : standard,  
            list : list
          },
          success: function(data) {
            alert("분반몰아넣기를 완료 하였습니다.");
            document.location.href = "course_list.php";
          }
      });
}

function drive_log_dialog(standardId){
    var subcourse_list = [];
    var tag = $("<div id='course_drive_log_popup'></div>");
   
     $.ajax({
          url: 'course_drive_log.ajax.php',
          method: 'POST',
          data : {
            course : standardId
          },
          success: function(data) {
                tag.html(data).dialog({
                    title: '분반내역',
                    modal: true,
                    width: 600,
                    resizable: false,
                    buttons: [ {id:'close',
                                text:'분반되돌리기',
                                disable: true,
                                click: function() {
                                    $(".subcourse").each(function(index, element){
                                          subcourse_list.push($(this).val()) ;
                                    });
                                    restore_course_execute(standardId, subcourse_list);
                                }},
                                {id:'close',
                                text:'취소',
                                disable: true,
                                click: function() {
                                    $( this ).dialog( "close" );
                                }}
                        ],
                    close: function () {
                        $('#frm_course_standard').remove();
                        $( this ).dialog('destroy').remove()
                    }
                }).dialog('open');
          }
        });
}

function edit_course(){
    var count = 0;
    $(".courseid").each(function(index, element){
        if($(this).is(":checked")){
        count += 1;
      }
    });
    
    if(count == 0){
        alert("수정하려는 강의를 체크 해주세요");
        return false;
    } else if (count > 1) {
        alert("한개의 강의만 체크 해주세요");
        return false;
    }
    
    var editId = $(".courseid:checked").val();
    
    document.location.href = "course_list_add.php?id="+editId;
}

function create_merge_course(){
    
    var count = 0;
    $('#content').append('<form method="post" id="merge_course" action="course_list_merge_form.php"></form>');
    $(".scourseid").each(function(){
      if($(this).is(":checked")){
          $(this).val();
          $('#merge_course').append('<input type="hidden" name="course[]" value="'+$(this).val()+'" />');
          count++;
      }
    });
    
    if(count < 2) {
        alert("2개 이상의 강의를 선택해야 합니다.");
        return false;
    }
    
    $('#merge_course').submit();
}

function restore_course_execute(standardId, subcourse_list){
    $.ajax({
        url : "./course_list_restore.execute.php",
        type: "post",
        data : {
            sdcourse : standardId,
            subcourse : subcourse_list
        },
        async: false,
        success: function(data){
           alert(data+" 명의 수강생을 되돌리기 하였습니다.");
           $('#course_search').submit();
        },
        error:function(e){
            console.log(e.responseText);
        }
    }); 
}
function getWindowSize() {
    var myWidth = 0;
    var myHeight = 0;

    if( typeof( window.innerWidth ) == 'number' ) {
        //Non-IE
        myWidth = window.innerWidth;
        myHeight = window.innerHeight;
    } else if( document.documentElement &&
        ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
        //IE 6+ in 'standards compliant mode'
        myWidth = document.documentElement.clientWidth;
        myHeight = document.documentElement.clientHeight;
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
        //IE 4 compatible
        myWidth = document.body.clientWidth;
        myHeight = document.body.clientHeight;
    }

    return {"width": myWidth, "height": myHeight}
}

function text_disable(checkbox, target, value) {
    target_node = $("input[name="+target+"]");
    if(checkbox.checked == value){
        target_node.attr('disabled',null);
    }else{
        target_node.attr('disabled','disabled');
    }
}

function mutiselecte_change(leave, arrive) {
    var arrive_node = $('#'+arrive+' optgroup');
    $('#'+leave+' option:selected').each(function(i, selected){
        arrive_node.append(selected);
    });
}

