<?php
require_once('../../../config.php');

if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $val) {
        ${$key} = $val;
    }
}

?>
<script src="/local/repository/searchV.js" type="text/javascript"></script>
<script type="text/javascript">
                var vimeoCtrl = {
                setVimeoUrl : function(url) {
                    $('input[name=emb_code]').val(url);
                    $('body').css({'overflow': 'auto'});
                    $('#load_form').remove();

                    this.closeVimeoPop();
                },

                openVimeoPop : function() {
                    $('div._vimeo').show();
                },

                closeVimeoPop : function() {
                    $('#load_form').remove();
                }
            };
                        var loadingCtrl = {
                startLoading : function() {
                    $('.loading').show();
                    $('#v_response').hide();
                },
                endLoading : function() {
                    $('.loading').hide();
                    $('#v_response').show();
                }
            };
</script>
<style type="text/css">
                .loading {
                    width:100%;
                    height: 306px;
                    background: white;
                    text-align: center;
                    display: none;
                }
                .loading img {
                    margin-top: 80px; 
                    background: white;
                }
            </style>

<div id="dialog_wrap">
        <div id="contents" class="sub_contents">
            <div class="search_style01">
                <input type="text" id="v_query" value="<?php echo $search; ?>" placeholder="검색어를 입력해주세요" title="search"/>
                <input type="submit" value="Search" onclick="v_search()"/>
                <input type="button" id="v_id_pre" value="pre" class="button_style01 gray" style= 'display:none' onclick="v_searchPage('pre')" />
                <input type="button" id="v_id_next" value="next" class="button_style01 gray" style= 'display:none' onclick="v_searchPage('next')" />
                <input type="hidden" id="v_pageToken" value ="" />
                <input type="hidden" id="v_id_next_value" value ="" />
            </div>
                <div class="loading"><img src="/local/repository/ajax/default.gif"></div> 
                <div id="v_response"></pre>
                </div>
            <script>
                <?php 
                if($search!='' && $search!=null){
                ?>
                    v_search();
                <?php 
                }
                ?>
            </script>
        </div>
</div>