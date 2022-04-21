<div class="menu_area">
    <span class="left"><input id="information_btn" type="image" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu1.png";?>" alt="btnMenu" /></span>
    <span class="left"><input id="database_btn" type="image" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu2.png";?>" alt="btnMenu" /></span>
    <span class="left"><input id="endnote_btn" type="image" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu3.png";?>" alt="btnMenu" /></span>
</div>
<div class="icon_info">
    <div class="fl">
        <img src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard');?>" />
        <span><?php echo " : ".get_string('information', 'local_jinoboard');?></span>
    </div>
    <div class="fl">
        <img src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
        <span><?php echo " : ".get_string('go-site', 'local_jinoboard');?></span>
    </div>
    <div class="fl">
        <img src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
        <span><?php echo " : ".get_string('document_download', 'local_jinoboard');?></span>
    </div>
    <div class="fl">
        <img src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
        <span><?php echo " : ".get_string('document-film', 'local_jinoboard');?></span>
    </div>
    <div class="fl">
        <img src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
        <span><?php echo " : ".get_string('document-film_download', 'local_jinoboard');?></span>
    </div>
</div>
<div id="information" class="div_taps">
    <div class="online_table_style">
        <table cellspacing='0' cellpadding='0'>
            <col width='610'><col width='60'><col width='40'><col width='40'>
            <tr>
                <th><?php echo get_string('lecture', 'local_jinoboard')?></th>
                <th><?php echo get_string('view', 'local_jinoboard')?></th>
                <th colspan="2"><?php echo get_string('download', 'local_jinoboard')?></th>
            </tr>
            <tbody>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[KOR]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/int_k.html','','width=840,height=600')">학술정보원 이용교육</b>  / 나하나<br>
                        <span class="onlinecontent">학술정보원에 대한 안내 및 서비스 이용안내 입니다.</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/int_k.html','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/introduction_kor.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>

                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[KOR]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/sea_k.html','','width=840,height=600')">정보검색교육</b>  / 노정임<br>
                        <span class="onlinecontent">학술활동에 필요한 다양한 유형의 자료검색, 이용방법에 대해 보다 구체적으로 안내합니다.<br>도서┃학위논문┃학술데이터베이스┃학술논문</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/sea_k.html','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/search.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/content/search.pdf')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/int_e.html','','width=840,height=600')">Introduction to Yonsei University Library</b>  / Sonia Key<br>
                        <span class="onlinecontent">This video introduces facilities and services of Yonsei University Library, and helps how to find books and journals of interest.</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/int_e.html','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/introduction_eng.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[CHI]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/int_c.html','','width=840,height=600')">图书馆资源与服务导览</b>  / 李多美<br>
                        <span class="onlinecontent">本教育包括延世大学图书馆馆藏概况、图书检索和学术论文检索三个部分。目的在于帮助用户了解图书馆的主要设施以及更好地利用图书馆的资源和服务。</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/int_c.html','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/introduction_chi.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/content/Introduction_chi.pdf')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div id="database" class="div_taps">
    <div class="online_table_style">
        <table cellspacing='0' cellpadding='0'>
            <col width='570'><col width='60'><col width='40'><col width='40'><col width='40'>
            <tr>
                <th><?php echo get_string('lecture', 'local_jinoboard')?></th>
                <th><?php echo get_string('view', 'local_jinoboard')?></th>
                <th colspan="2"><?php echo get_string('download', 'local_jinoboard')?></th>
                <th><?php echo get_string('db_guide', 'local_jinoboard')?></th>
            </tr>
            <tbody>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[KOR]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/blo_k.jsp','','width=840,height=600')">Bloomberg Basic Course</b>  / 성지하
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/blo_k.jsp','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/bloomberg.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/content/bloomberg.pdf')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&fctN=facet_rtype&elementId=0&recIdxs=0&frbrVersion=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&fctV=databases&tab=default_tab&dstmp=1288577247262&srt=rank&mode=Basic&indx=1&tb=t&renderMode=poppedOut&vl(freeText0)=bloomberg&vid=yul&fn=search&frbg=&displayMode=full&ct=display&dum=true&recIds=metalibYON02334&doc=metalibYON02334&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[KOR]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/sci_k.html','','width=840,height=600')">SCiFinder Scholar</b>  / 신원데이터넷 김정하
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/sci_k.html','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/sci.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577270425&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON01927&renderMode=poppedOut&doc=metalibYON01927&vl(freeText0)=SCIFINDER&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://library.yonsei.ac.kr/onlineedu/wes_e.html','','width=840,height=600')">Westlaw Basic Legal Research</b>  / William P. White (Westlaw Online Trainer)
                    </td>
                    <td>
                        <img onclick="window.open('http://library.yonsei.ac.kr/onlineedu/wes_e.html','','width=840,height=600')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td>
                        <img onclick="location.href='http://library.yonsei.ac.kr/onlineedu/download/westlaw.zip'" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/document-film_download.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON01865&indx=1&recIds=metalibYON01865&recIdxs=0&elementId=0&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288253699246&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=WESTLAW&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://support.ebsco.com/training/tutorials.php')">EBSCohost</b>  /  Thomson Reuters 제공
                    </td>
                    <td>
                        <img onclick="window.open('http://support.ebsco.com/training/tutorials.php')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=3&recIdxs=3&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577311491&srt=rank&ct=display&mode=Basic&dum=true&indx=14&tb=t&recIds=metalibYON01545&renderMode=poppedOut&doc=metalibYON01545&vl(freeText0)=EBSCOHOST&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <strong>ISI Web of Knowledge</strong>  / Thomson Reuters 제공
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON00731&indx=3&recIds=metalibYON00731&recIdxs=2&elementId=2&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288310742926&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=WOs&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('https://www.brainshark.com/brainshark/vu/view.asp?pi=651277848&uid=0&sid=81725063&sky=7EA3D182D99945619F4')">- All database 검색</span>
                    </td>
                    <td>
                        <img onclick="window.open('https://www.brainshark.com/brainshark/vu/view.asp?pi=651277848&uid=0&sid=81725063&sky=7EA3D182D99945619F4')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON00731&indx=3&recIds=metalibYON00731&recIdxs=2&elementId=2&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288310742926&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=WOs&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.brainshark.com/thomsonscientific/vu?pi=742386791')">- 검색결과 관리 </span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.brainshark.com/thomsonscientific/vu?pi=742386791')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON00731&indx=3&recIds=metalibYON00731&recIdxs=2&elementId=2&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288310742926&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=WOs&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <strong>Journal Citation Report</strong>  / Thomson Reuters 제공
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON02485&indx=5&recIds=metalibYON02485&recIdxs=4&elementId=4&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288310713426&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=jcr&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.brainshark.com/thomsonscientific/vu?pi=875285718')">- Impact factor </span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.brainshark.com/thomsonscientific/vu?pi=875285718')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON02485&indx=5&recIds=metalibYON02485&recIdxs=4&elementId=4&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288310713426&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=jcr&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.brainshark.com/thomsonscientific/vu?pi=902787540')">- Immediacy Index </span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.brainshark.com/thomsonscientific/vu?pi=902787540')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON02485&indx=5&recIds=metalibYON02485&recIdxs=4&elementId=4&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288310713426&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=jcr&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://help.scopus.com/flare/Content/tutorials/sc_menu.html')">Scoupus</b>  /  Elsevier 제공
                    </td>
                    <td>
                        <img onclick="window.open('http://help.scopus.com/flare/Content/tutorials/sc_menu.html')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=metalibYON00819&indx=1&recIds=metalibYON00819&recIdxs=0&elementId=0&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&frbg=&tab=default_tab&dstmp=1288254441989&srt=rank&mode=Basic&dum=true&tb=t&vl(freeText0)=SCOPUS&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://heinonline.org/wiki/index.php/HeinOnline:Videos')">HeinOnline</b>  /  William S. Hein & Co., Inc. 제공
                    </td>
                    <td>
                        <img onclick="window.open('http://heinonline.org/wiki/index.php/HeinOnline:Videos')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=9&recIdxs=9&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577439182&srt=rank&ct=display&mode=Basic&dum=true&indx=10&tb=t&recIds=metalibYON02484&renderMode=poppedOut&doc=metalibYON02484&vl(freeText0)=HEINONLINE&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://www.nlm.nih.gov/bsd/disted/pubmed.html')">PubMed</b>  /  NIH 제공
                    </td>
                    <td>
                        <img onclick="window.open('http://www.nlm.nih.gov/bsd/disted/pubmed.html')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=1&recIdxs=1&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577465231&srt=rank&ct=display&mode=Basic&dum=true&indx=2&tb=t&recIds=metalibYON00068&renderMode=poppedOut&doc=metalibYON00068&vl(freeText0)=SCOPUS&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG/CHI]</span> <b onclick="window.open('http://oversea.cnki.net/hykf/Default_en2.htm')">CNKI 플랫폼 및 검색 기능 안내</b>  /  中国知网(CNKI) 제공
                    </td>
                    <td>
                        <img onclick="window.open('http://oversea.cnki.net/hykf/Default_en2.htm')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/search.do?dscnt=0&frbg=&tab=default_tab&dstmp=1342678639484&srt=rank&ct=search&mode=Basic&dum=true&indx=2&tb=t&vl(freeText0)=CNKI&fn=search&vid=yul')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div id="endnote" class="div_taps">
    <div class="online_table_style">
        <table cellspacing='0' cellpadding='0'>
            <col width='570'><col width='60'><col width='40'><col width='40'><col width='40'>
            <tr>
                <th><?php echo get_string('lecture', 'local_jinoboard')?></th>
                <th><?php echo get_string('view', 'local_jinoboard')?></th>
                <th colspan="2"><?php echo get_string('download', 'local_jinoboard')?></th>
                <th><?php echo get_string('db_guide', 'local_jinoboard')?></th>
            </tr>
            <tbody>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <b onclick="window.open('http://www.endnote.com/training/')">EndNote</b>  /  Thomson Reuters 제공
                    </td>
                    <td>
                        <img onclick="window.open('http://www.endnote.com/training/')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577484320&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON02408&renderMode=poppedOut&doc=metalibYON02408&vl(freeText0)=ENDNOTE&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class='redbold'>[ENG]</span> <strong>ISI Web of Knowledge</strong>  / Thomson Reuters 제공
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577501923&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON02409&renderMode=poppedOut&doc=metalibYON02409&vl(freeText0)=REFWORKS&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.refworks.com/tutorial/')">- Basic Tutorial</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.refworks.com/tutorial/')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577533047&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON02409&renderMode=poppedOut&doc=metalibYON02409&vl(freeText0)=REFWORKS&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.refworks.com/tutorial/advanced/RefWorks_Advanced_Feature_Tutorial.html')">- Advanced feature Tutorial</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.refworks.com/tutorial/advanced/RefWorks_Advanced_Feature_Tutorial.html')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577533047&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON02409&renderMode=poppedOut&doc=metalibYON02409&vl(freeText0)=REFWORKS&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.refworks.com/tutorial/RefShare%20User%20tutorial.htm')">- Sharing your RefWorks Database</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.refworks.com/tutorial/RefShare%20User%20tutorial.htm')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577533047&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON02409&renderMode=poppedOut&doc=metalibYON02409&vl(freeText0)=REFWORKS&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
                <tr>
                    <td class="onlinetitle">
                        <span class="onlinecontent content_hover" onclick="window.open('http://www.refworks.com/tutorial/Creating_An_Output_Style.html')">- Creating an Output Style</span>
                    </td>
                    <td>
                        <img onclick="window.open('http://www.refworks.com/tutorial/Creating_An_Output_Style.html')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/go-site.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <img onclick="window.open('http://yulprm1.yonsei.ac.kr/primo_library/libweb/action/display.do?dscnt=1&elementId=0&recIdxs=0&frbrVersion=&frbg=&scp.scps=scope%3A(YBR)%2Cscope%3A(oldbook)%2Cscope%3A(christserial)%2Cscope%3A(digitool)%2Cscope%3A(dissertation)%2Cscope%3A(SEOUL_CAMPUS)%2Cscope%3A(sfx_seoul)%2Cscope%3A(metalib)%2Cscope%3A(christbk)&displayMode=full&tab=default_tab&dstmp=1288577533047&srt=rank&ct=display&mode=Basic&dum=true&indx=1&tb=t&recIds=metalibYON02409&renderMode=poppedOut&doc=metalibYON02409&vl(freeText0)=REFWORKS&vid=yul&fn=search&tabs=detailsTab&fromLogin=true')" src="<?php echo $CFG->wwwroot . "/local/jinoboard/images/information.png";?>" alt="<?php echo get_string('icon:img', 'local_jinoboard')?>" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    $(function(){
        
    })
</script>