M.local_jinoanalytics = {};

M.local_jinoanalytics.index = function(Y) {
    // 레이어 토글
    $('.switch-layer').bind('click', function(e) {
        e.preventDefault();
        var layer_id = $(this).attr('href');
        $('.switch-layer').removeClass('active');
        if (!$(this).hasClass('active')) $(this).addClass('active');
        $('.dashboard-content').addClass('hide');
        $(layer_id).removeClass('hide');
    });
}


M.local_jinoanalytics.view = function(Y) {

}

