// Flash Activating Script
// 2006-02-01
// minu_at_ dynamicmedia
// Don't Edit Below! Never!

// s: source url
// d: flash id
// w: source width
// h: source height
// t: wmode ("" for none, transparent, opaque ...)
function mf(s,d,w,h,t){
		var q = unescape(s)
		while(q.indexOf(" ")!=-1)
			var q=q.replace(" ","");
        return "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0\" width="+w+" height="+h+" id="+d+"><param name=allowFullScreen value=true /><param name=allowScriptAccess value=sameDomain / ><param name=wmode value="+t+" /><param name=movie value="+q+" /><param name=quality value=high /><embed src="+q+" quality=high wmode="+t+" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?p1_prod_version=shockwaveflash\" width="+w+" height="+h+" allowFullScreen=\"true\"></embed></object>";
}

// write document contents
function documentwrite(src){
        document.write(src);
}

// assign code innerHTML
function setcode(target, code){
        target.innerHTML = code;
}