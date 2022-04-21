<?php
function redirect_to($url) {
//    @header($_SERVER['SERVER_PROTOCOL'] . ' 303 See Other');
//    @header('Location: '.$url);
    echo '<script type="text/javascript">';
    echo 'location.replace("'.$url.'");';
    echo '</script>';
   
    exit;
}

function print_paging_navbar($totalcount, $page, $perpage, $baseurl, $params = null, $maxdisplay = 18) {
    global $CFG;
    $pagelinks = array();
   
    $lastpage = 1;
    if($totalcount > 0) {
        $lastpage = ceil($totalcount / $perpage);
    }
   
    if($page > $lastpage) {
        $page = $lastpage;
    }
           
    if ($page > round(($maxdisplay/3)*2)) {
        $currpage = $page - round($maxdisplay/2);
        if($currpage > ($lastpage - $maxdisplay)) {
            if(($lastpage - $maxdisplay) > 0){
                $currpage = $lastpage - $maxdisplay;
            }
        }
    } else {
        $currpage = 1;
    }
   
   
   
    if($params == null) {
        $params = array();
    }
   
    $prevlink = '';
    if ($page > 1) {
        $params['page'] = $page - 1;
        $prevlink = html_writer::link(new moodle_url($baseurl, $params), '<img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/>', array('class'=>'next'));
    } else {
        $prevlink = '<a href="#" class="next"><img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
    }
   
    $nextlink = '';
     if ($page < $lastpage) {
        $params['page'] = $page + 1;
        $nextlink = html_writer::link(new moodle_url($baseurl, $params), '<img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/>', array('class'=>'prev'));
    } else {
        $nextlink = '<a href="#" class="prev"><img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
    }
   
   
    echo '<div class="pagination">';
   
    $pagelinks[] = $prevlink;
   
    if ($currpage > 1) {
        $params['page'] = 1;
        $firstlink = html_writer::link(new moodle_url($baseurl, $params), 1);
       
        $pagelinks[] = $firstlink;
        if($currpage > 2) {
            $pagelinks[] = '...';
        }
    }
   
    $displaycount = 0;
    while ($displaycount <= $maxdisplay and $currpage <= $lastpage) {
        if ($page == $currpage) {
            $pagelinks[] = '<strong>'.$currpage.'</strong>';
        } else {
            $params['page'] = $currpage;
            $pagelink = html_writer::link(new moodle_url($baseurl, $params), $currpage);
            $pagelinks[] = $pagelink;
        }
       
        $displaycount++;
        $currpage++;
    }
   
    if ($currpage - 1 < $lastpage) {
        $params['page'] = $lastpage;
        $lastlink = html_writer::link(new moodle_url($baseurl, $params), $lastpage);
       
        if($currpage != $lastpage) {
            $pagelinks[] = '...';
        }
        $pagelinks[] = $lastlink;
    }
   
    $pagelinks[] = $nextlink;
   
   
    echo implode('&nbsp;', $pagelinks);
   
    echo '</div>';
}
function siteadmin_get_total_pages($rows, $limit = 10) {
	if ($rows == 0) {
		return 1;
	}

	$total_pages = (int) ($rows / $limit);

	if (($rows % $limit) > 0) {
		$total_pages += 1;
	}

	return $total_pages;
}

function print_paging_navbar_script($totalcount, $page, $perpage, $baseurl, $maxdisplay = 18) {
    global $CFG;
    
    $pagelinks = array();
   
    $lastpage = 1;
    if($totalcount > 0) {
        $lastpage = ceil($totalcount / $perpage);
    }
   
    if($page > $lastpage) {
        $page = $lastpage;
    }
           
    if ($page > round(($maxdisplay/3)*2)) {
        $currpage = $page - round($maxdisplay/2);
        if($currpage > ($lastpage - $maxdisplay)) {
            $currpage = $lastpage - $maxdisplay;
        }
    } else {
        $currpage = 1;
    }
   
    $prevlink = '';
    if ($page > 1) {
        $prevlink = html_writer::link(str_replace(':page', $page - 1, $baseurl), '<img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/>', array('class'=>'next'));
    } else {
        $prevlink = '<a href="#" class="next"><img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
    }
   
    $nextlink = '';
     if ($page < $lastpage) {
        $nextlink = html_writer::link(str_replace(':page', $page + 1, $baseurl), '<img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/>', array('class'=>'prev'));
    } else {
        $nextlink = '<a href="#" class="prev"><img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
    }
   
   
    echo '<div class="pagination">';
   
    $pagelinks[] = $prevlink;
   
    if ($currpage > 1) {
        $params['page'] = 1;
        $firstlink = html_writer::link(str_replace(':page', 1, $baseurl), 1);
       
        $pagelinks[] = $firstlink;
        if($currpage > 2) {
            $pagelinks[] = '...';
        }
    }
   
    $displaycount = 0;
    while ($displaycount <= $maxdisplay and $currpage <= $lastpage) {
        if ($page == $currpage) {
            $pagelinks[] = '<strong>'.$currpage.'</strong>';
        } else {
            $params['page'] = $currpage;
            $pagelink = html_writer::link(str_replace(':page', $currpage, $baseurl), $currpage);
            $pagelinks[] = $pagelink;
        }
       
        $displaycount++;
        $currpage++;
    }
   
    if ($currpage - 1 < $lastpage) {
        $params['page'] = $lastpage;
        $lastlink = html_writer::link(str_replace(':page', $lastpage, $baseurl), $lastpage);
       
        if($currpage != $lastpage) {
            $pagelinks[] = '...';
        }
        $pagelinks[] = $lastlink;
    }
   
    $pagelinks[] = $nextlink;
   
   
    echo implode('&nbsp;', $pagelinks);
   
    echo '</div>';
}

function print_paging_navbar_notice($url, $params, $total_pages, $current_page, $max_nav = 10) {
    global $CFG;
    
	$total_nav_pages = siteadmin_get_total_pages($total_pages, $max_nav);
	$current_nav_page = (int) ($current_page / $max_nav);
	if (($current_page % $max_nav) > 0) {
		$current_nav_page += 1;
	}
	$page_start = ($current_nav_page - 1) * $max_nav + 1;
	$page_end = $current_nav_page * $max_nav;
	if ($page_end > $total_pages) {
		$page_end = $total_pages;
	}

	if (!empty($params)) {
		$tmp = array();
		foreach ($params as $key => $value) {
			$tmp[] = $key . '=' . $value;
		}
		$tmp[] = "page=";
		$url = $url . "?" . implode('&', $tmp);
	} else {
		$url = $url . "?page=";
	}
	echo '<div class="pagination" >';
        
	if ($current_page > 1) {
		echo '<a class="prev" href="' . $url . ($current_page - 1) . '"><img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
	} else {
		echo '<a class="prev" href="#"><img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
	}
	for ($i = $page_start; $i <= $page_end; $i++) {
		if ($i == $current_page) {
			echo '<strong>' . $i . '</strong>';
		} else {
			echo '<a href="' . $url . '' . $i . '">' . $i . '</a>';
		}
	}
	if ($current_page < $total_pages) {
		echo '<a class="next" href="' . $url . ($current_page + 1) . '"><img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
	} else {
		echo '<a class="next" href="#"><img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
	}
	
	echo '</div>';
}