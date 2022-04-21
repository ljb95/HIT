<?php
require_once '../../config.php';
global $CFG;
require_once $CFG->libdir.'/contents.class.php';
$DB = new contents;

$seq = $_REQUEST['seq'];

$datas = $DB->list_data($seq,0);
$data_list = '';

if(sizeof($datas)<=1){
    $data_list .= '<li>등록된 파일이 없습니다.</li>';
}else{
    $path = $datas['path'];
    foreach($datas as $key=>$val){
        if($key=='dir'){
            foreach($datas['dir'] as $dir){
                $data_list .= '<li id="d0_'.$dir.'">
                    <span id="opendir"><a href="javascript:load_data(\''.$dir.'\',\''.$path.'\',0,'.$seq.');">
                    <img src="../images/icon_folder.png"/> '.$dir.'</a></span>
                    <span id="closedir" style="display:none;"><a href="javascript:close_data(\''.$dir.'\',\''.$path.'\',0);">
                    <img src="../images/icon_folder_open.png"/> '.$dir.'</a></span>    
                    </li>';
            }
        }
        
        
        if($key=='file'){
            foreach($datas['file'] as $file){
                $data_list .= '<li>'.$file.'</li>';
            }
        }
    }
} 

?>
<style>
    ul.data_list{margin: 10px;}
    ul.data_list li{margin: 5px;}
</style>
<ul class="data_list"><?php echo $data_list;?></ul>

<script type="text/javascript">
    
    //로드되는 히스토리와 파일
    function load_data(dir,path,depth,seq){
        
        if(path&&dir) var dir_path = path+'/'+dir;
        else dir_path = path;
        
        if(dir){
        //오픈했으니 클로우즈로 바꾸자.
        $('#d'+depth+'_'+dir+' #closedir').css({
            'display':'inline'
        });
        $('#d'+depth+'_'+dir+' #opendir').css({
            'display':'none'
        });
        }
        
        var form = $('#file_form');
        
        $.ajax({
            type: 'POST',
            url:'action.php', 
            dataType: 'json',
            data: {
                'amode':'dir_scan',
                'path':dir_path,
                'seq':seq
            }, 
            async: false,
            cache: false,
            error:function(xhr,status,e){  
                //alert('Error');
		
            },
            success: function(jdata){
	
                if(jdata!=''){
	
                            var depth2 = depth+1;
                            var uln = $('#d'+depth+'_'+dir);
                            uln.append('<ul></ul>');
                            uln = $('#d'+depth+'_'+dir+' ul');
                            var depth_size = 'style=margin-left:"'+depth2*5+'px;"';
	
                        //데이터 불러오기
                        //디렉토리부터
                        if(jdata.dir){
           
                            for(var i=0;i<jdata.dir.length;i++){
                                uln.append('<li id="d'+depth2+'_'+jdata.dir[i]+'" '+depth_size+'>'+
                                    '<span id="opendir"><a href="javascript:load_data(\''+jdata.dir[i]+'\',\''+jdata.path+'\','+depth2+',\''+jdata.seq+'\');">'+
                                    '<img src="../img/icon_folder.png"/> '+jdata.dir[i]+'</a></span> '+
                                    '<span id="closedir"><a href="javascript:close_data(\''+jdata.dir[i]+'\',\''+jdata.path+'\','+depth2+');">'+
                                    '<img src="../img/icon_folder_open.png"/> '+jdata.dir[i]+'</a></span> '+
                                    '</li>');
                                $('#d'+depth2+'_'+jdata.dir[i]).css({
                                    'font-weight':'bold'
                                });
                                $('#d'+depth2+'_'+jdata.dir[i]+' #closedir').css({
                                    'display':'none'
                                });
                            }
                        }

                        //파일가져오기
                        if(jdata.file){
                            for(var i=0;i<jdata.file.length;i++){
                                uln.append('<li id="filelist">'+
                                    jdata.file[i]+'</li>');
                                $('#d'+depth+'_'+dir+' ul #filelist').css({
                                    'font-weight':'normal',
                                    'padding':'5px'
                                });
                            } 
	
                        }//iffile
       
    
                }

            }
        });

    }
    
//클로우즈 클릭시
function close_data(dir,path,depth){

    //클로우즈했으니 오픈으로 바꾸자.
    $('#d'+depth+'_'+dir+' #closedir').css({
        'display':'none'
    });
    $('#d'+depth+'_'+dir+' #opendir').css({
        'display':'inline'
    });

    $('#d'+depth+'_'+dir+' ul').remove();

}
    

</script>