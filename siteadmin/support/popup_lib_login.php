<?php

global $DB , $CFG;
require_once (dirname(dirname(dirname(__FILE__))) . '/lib/deprecatedlib.php');
require_once (dirname(dirname(dirname(__FILE__))) . '/lib/filelib.php');
require_once (dirname(dirname(dirname(__FILE__))) . '/local/popup/lib.php');

$where = "";
$popups = $DB->get_records_select('popup', "(timeavailable < ? or timeavailable=0) and (timedue > ? or timedue=0 ) and isactive = 1 " . $where, array(time(), time()));
?>
<style>
    /*************************************
    Popup Style
*************************************/
div.find_identity {
	margin: 0 20px 0 0 !important;
}
div.find_password {
	margin: 0 !important;
}
.popup {
    display: block;
    background-color: #fff;
    position: absolute !important;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #ccc; 
    z-index: 9999;
}
h2.popup_title {
        background: linear-gradient(to bottom, #eee, #ddd, #ccc); 
	background-color: #ddd;
	font-size: 16px;
	padding: 0px 10px;
	margin: 0 !important;
}
h2.popup_title .right{
	float: right !important;
}
.main_popup p {
	font-size: 12px;
	line-height: 17px;
}
.p-footer {
    margin-top: 0 !important;
    margin: 0 -15px 0 -15px;
    padding: 15px;
    background-color: #eee;
    width: calc(100% - 30px);
    float: left;
    position: absolute;
    bottom: 0px;
}
/*************************************
    
*************************************/
@media all and (max-width: 480px) {
    .popup { 
        left:0 !important; 
    }
}
</style>
<script type="text/javascript">
    function getCookie(name) {
        var Found = false;
        var start, end;
        var i = 0;
        while (i <= document.cookie.length) {
            start = i;
            end = start + name.length;
            if (document.cookie.substring(start, end) == name) {
                Found = true;
                break;
            }
            i++;
        }

        if (Found) {
            start = end + 1;
            end = document.cookie.indexOf(";", start);
            if (end < start)
                end = document.cookie.length;
            return document.cookie.substring(start, end);
        }
        return "";
    }

    var newwin = null;

    function openPopup(url, name, param) {
        var noticeCookie = getCookie(name);
        if (noticeCookie != "no") {

            newwin = window.open(url, name, param);
            newwin.focus();
        }
    }
    function setCookie(name, value, expiredays) {
        var todayDate = new Date();
        todayDate.setDate(todayDate.getDate() + 1);
        document.cookie = name + "=" + escape(value) + "; path=/; expires=" + todayDate.toGMTString() + ";"
    }

    function closeWin(name, value, expiredays) {
        //if ( document.popupform.confirmed.checked ) 
        setCookie(name, value, expiredays);
        //self.close(); 
        document.getElementById(name).style.visibility = 'hidden';
    }

    function closepopup(name) {
        //self.close(); 
        document.getElementById(name).style.visibility = 'hidden';
    }

 jQuery(function($) {
    $(document).ready(function() {
        // a링크 #일때 이벤트 제거
        $(document).on('click', 'a[href="#"]', function(e){
            e.preventDefault();
        });
        $('.main_popup_header').each(function() {
            var $el = $(this);
            var $parent = $el.parent();

            var dragging = false;
            var startY = 0;
            var startX = 0;
            var startT = 0;
            var startL = 0;

            $el.mousedown(function(ev) {
                dragging = true;
                startY = ev.clientY;
                startX = ev.clientX;
                startT = $parent.css('top');
                startL = $parent.css('left');
            });

            $(window).mousemove(function(ev) {
                if (dragging) {
                    // calculate new top
                    var newTop = parseInt(startT) + (ev.clientY - startY);
                    var newLeft = parseInt(startL) + (ev.clientX - startX);

                    $parent.css('top', newTop);
                    $parent.css('left', newLeft);
                }
            }).mouseup(function() {
                dragging = false;
            });

        });
    });
    });
</SCRIPT> 
<?php
$pageurl=$_SERVER['SCRIPT_NAME'];
$popuppage = 1;
$context = context_system::instance();
foreach ($popups as $popup) {
    if (isset($_COOKIE['popup_' . $popup->id]) || $popuppage != $popup->type) {
    } else { 
        ?>                     
            <div class="popup" id="popup_<?php echo $popup->id; ?>" style="width:<?php echo $popup->popupwidth; ?>px; height:<?php echo $popup->popupheight; ?>px; left:<?php echo $popup->popupx; ?>px; top:<?php echo $popup->popupy; ?>px;">
                <div class="main_popup_header">
                    <h2 class="popup_title"><?php echo $popup->title; ?><a href="#" onclick="<?php echo 'closepopup(\'popup_' . $popup->id . '\')' ?>" class="right"><img src="<?php echo $CFG->httpswwwroot; ?>/siteadmin/img/close_icon_popup.png" width="12px" height="12px" alt="Close" title="Close"></a></h2>
                </div>
				<div style="<?php if($popup->availablescroll == 1){ ?>overflow: auto; <?php } ?> height:<?php echo $popup->popupheight-46; ?>px; padding: 15px 15px 0 15px;">
                    <font style = "color : black;"><?php  echo file_rewrite_pluginfile_urls($popup->description, 'pluginfile.php', $context->id, 'local_popup', 'popup', $popup->id); ?></font>
                    <div style="margin-top:5px;" class="p-footer">
                        <form class="popup_btn_area">
                            <?php  
                            if ($popup->cookieday > 0) {
                                echo '<input type="checkbox" name="confirmed" title="confirmed" id="confirmed" onclick="closeWin(\'popup_' . $popup->id . '\', \'no\', \'' . $popup->cookieday . '\')"><font style = "color : black;">' . get_string("closepopupdays", "local_popup", $popup->cookieday).'</font>';
                            }
                            ?>
                        </form>
                    </div>  
                </div>
            </div>

        <?php
    }
}
    ?>
