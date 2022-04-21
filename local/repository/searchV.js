// Your use of the YouTube API must comply with the Terms of Service:
// https://developers.google.com/youtube/terms
// Called automatically when JavaScript client library is loaded.

function v_searchPage(type) {

  var query = document.getElementById('v_query').value;
  var pageToken = document.getElementById('v_pageToken').value;
  // Use the JavaScript client library to create a search.list() API call.
  var request = gapi.client.youtube.search.list({
      part: 'snippet',
      q:query,
      maxResults:10,
      pageToken:pageToken,

  });
  // Send the request to the API server, call the onSearchVimeoResponse function when the data is returned
  request.execute(onSearchVimeoResponse);

}


// Called when the search button is clicked in the html code
function v_search() {

    loadingCtrl.startLoading();

    var responseString = "";
    var query = document.getElementById('v_query').value;
    // Use the JavaScript client library to create a search.list() API call.

    var url = ''

    $.ajax({
        url: 'https://api.vimeo.com/videos?access_token=57c404691cc0499693c93f6b8b6eb5e9&per_page=9&query='+query,
        type: 'GET',
        contentType: 'application/json',
  //.      data: JSON.stringify( postData ),
        success: function(response){

          if(response.paging.next) {
            document.getElementById('v_id_next').style.display =  "inline";
            document.getElementById('v_pageToken').value = response.nextPageToken;
          }


          if(response.paging.previous) {
            document.getElementById('v_id_pre').style.display =  "inline";
            document.getElementById('v_pageToken').value = response.nextPageToken;
          }


           var entries = response.data || [];



           for (var i = 0; i < entries.length; i++) {

             var entry = entries[i];
            // id = entry.id.videoId;
             title = entry.name;
             link = entry.link;
             if(entry.pictures){
                imgSrc = entry.pictures.sizes[0].link;
            } else {
                imgSrc = 'https://i.vimeocdn.com/video/384893738_100x75.jpg?r=pad';
            }

            returnText = '<div class="vimeo_video">'
                             + "<div class='embed_thumbnail'> "
                             + "    <img src='"+ imgSrc +"' alt='course01' title='course01' /> "
                             + "</div>"
                             + " <div class='embed_title'> " + title +"<br> "
                             + "     <span>"+ link+" </span></div>"
                             + "     <div class='embed_select'> "
                             + "         <input type='button' class='button_style02' value='선택' onclick=\"vimeoCtrl.setVimeoUrl('"+link+"');\"> "
                             + "     </div>"
                         + " </div>";

             responseString += returnText;
                               //alert(entry.name);
            }
            document.getElementById('v_response').innerHTML = responseString;

            loadingCtrl.endLoading();

        },
        error: function(){
            alert('error');

            loadingCtrl.endLoading();
        }
    });

//    var request = gapi.client.youtube.search.list({
//        part: 'snippet',
//        q:query,
        //maxResults:10
    //});
    // Send the request to the API server, call the onSearchVimeoResponse function when the data is returned
    //request.execute(onSearchVimeoResponse);
}

function selectData(data) {
  window.opener.getReturnValue(data);
  window.close();
}

// Triggered by this line: request.execute(onSearchVimeoResponse);
function onSearchVimeoResponse(response) {
    loadingCtrl.startLoading();

    var responseString ="";

    console.log(response.nextPageToken);
    console.log(response.prevPageToken);

    if(response.nextPageToken) {
      document.getElementById('v_id_next').style.display =  "inline";
      document.getElementById('v_pageToken').value = response.nextPageToken;

    }

    if(response.prevPageToken) {
      document.getElementById('v_id_pre').style.display =  "inline";
      document.getElementById('v_pageToken').value = response.prevPageToken;
    }
    // token 이 존재할 경우  버튼 생성하기

    //console.

    var entries = response.items || [];

    for (var i = 0; i < entries.length; i++) {
        var entry = entries[i];
        id = entry.id.videoId;
        title = entry.snippet.title;
        description = entry.snippet.description;
        imgSrc = entry.snippet.thumbnails.default.url;

        //returnText = "<table boder='1'>"
      //               + " <tr><td><a href='https://www.youtube.com/embed/"+id+"' target=blank><img src='"+ imgSrc +"'  width='120' height='90' /></a></td>"
      //               + "    <td>"
      //               + "<b>"+ title  +"</b><br/> -"+ description/
//
  //                   + "<br><br><input type='button' title='선택' value='선택' class='button_style01 gray' onclick=selectData('https://www.youtube.com/embed/"+id+"') /></td>"
    //                 + "</tr>"
      //               + "</table>";

       returnText = '<div style="width:480px float:left;">'
                             + "<div class='embed_thumbnail'> "
                             + "    <img src='"+ imgSrc +"' alt='course01' title='course01' /> "
                             + "</div>"
                             + " <div class='embed_title'> " + title +"<br> "
                             + "     <span>"+ link+" </span></div>"
                             + "     <div class='embed_select'> "
                             + "         <input type='button' class='button_style02' value='선택' onclick=\"vimeoCtrl.setVimeoUrl('"+link+"');\"> "
                             + "     </div>"
                         + " </div>";

        responseString += returnText;
    }
  //  alert(feed);




    //try {
  	//	JSONObject jsonObj = (JSONObject)jsonParser.parse(jsonStr);
  	//	String name = (String) jsonObj.get("nextPageToken");

      //console.log(name);
      /*
  		JSONArray jsonArr = (JSONArray) jsonObj.get("items");
  		for(int i=0;i<jsonArr.size();i++){
  			String hobby = (String) jsonArr.get(i);
  			System.out.println(hobby);
		}
	} catch (ParseException e) {
		e.printStackTrace();
	}

*/



//    JSONObject json = new JSONParser(response);

  //  JSONObject dataObject = json.getJSONObject("data"); // this is the "data": { } part
  //  JSONArray items = dataObject.getJSONArray("items"); // this is the "items: [ ] part


  //  for (int i = 0; i < items.length(); i++) {
  //    JSONObject videoObject = items.getJSONObject(i);
  //    String title = videoObject.getString("title");
  //    String videoId = videoObject.getString("id");
  //  }

    document.getElementById('v_response').innerHTML = responseString;

    loadingCtrl.endLoading();
}
