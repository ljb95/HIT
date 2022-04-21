(function(jwplayer){

  var template = function(player, config, div) {
    
    function setup(evt) {
        div.style.color = 'gray';
		div.style.width = "100%";
		div.style.textAlign = 'left';
		div.style.paddingTop = '10px';
                div.style.paddingLeft = '10px';
		div.style.fontFamily = 'Malgun Gothic';
        div.innerHTML = config.text;
    };
    player.onReady(setup);
    this.resize = function(width, height) {};
  };

  jwplayer().registerPlugin('textoutput', template);

})(jwplayer);
