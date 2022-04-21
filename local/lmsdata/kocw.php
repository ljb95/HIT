<?php

        require('../../config.php');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.kocw.net/home/api/handler.do?key=cbfdd2be800141a5426e8570c39e8341811ba14b50663ee6&from=20160101&end_num=4");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $xml = curl_exec($ch);
        if (curl_error($ch)) {
            echo curl_error($ch);
        }
        curl_close($ch);
        $xml = new SimpleXmlElement($xml);
        
        
        for($i=0; $i<4; $i++)
        {
            $url    = $xml->list->list_item[$i]->course_url;
            $title    = $xml->list->list_item[$i]->course_title;
            $thumbnail = $xml->list->list_item[$i]->thumbnail_url;

            echo '<div class="kocw_course">';
            echo '<img src="'.$thumbnail.'" alt="thubnail" title="Course thubnail" />';
            echo '<div><a href="'.$url.'">'.$title.'</a></div>';
            echo '</div>';

        }

