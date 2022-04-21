function update_page_progress(id) {
    var fc = $('#package').contents();
    var fw = $('#package').get(0).contentWindow;
    var totalpage = fw.totalPage;
    var url = fc.find('#myStage').get(0).contentWindow.location.href;
    var curpagestr = url.substring(url.lastIndexOf('/') + 4, url.lastIndexOf('/') + 6);
    var curpage = Number(curpagestr);

    var status = true;

    $.ajax({
        url: 'package_ajax.php',
        type: 'POST',
        dataType: 'json',
        async: false,
        data: {totalpage:totalpage, curpage:curpage, id:id},
        success: function (data, textStatus, jqXHR) {
            if (data.status == 'success') {
                status = true;
            } else {
                alert(data.message);
                status = false;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR.responseText);
            status = false;
        }
    });

    return status;

}