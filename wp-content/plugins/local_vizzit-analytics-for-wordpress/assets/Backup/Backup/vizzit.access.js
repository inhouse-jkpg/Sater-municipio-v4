/*!
 * Joseph Myer's md5() algorithm wrapped in a self-invoked function to prevent
 * global namespace polution, modified to hash unicode characters as UTF-8.
 *  
 * Copyright 1999-2010, Joseph Myers, Paul Johnston, Greg Holt, Will Bond <will@wbond.net>
 * http://www.myersdaily.org/joseph/javascript/md5-text.html
 * http://pajhome.org.uk/crypt/md5
 * 
 * Released under the BSD license
 * http://www.opensource.org/licenses/bsd-license
 */
(function(){function c(n,p){var o=n[0],m=n[1],r=n[2],q=n[3];o=a(o,m,r,q,p[0],7,-680876936);q=a(q,o,m,r,p[1],12,-389564586);r=a(r,q,o,m,p[2],17,606105819);m=a(m,r,q,o,p[3],22,-1044525330);o=a(o,m,r,q,p[4],7,-176418897);q=a(q,o,m,r,p[5],12,1200080426);r=a(r,q,o,m,p[6],17,-1473231341);m=a(m,r,q,o,p[7],22,-45705983);o=a(o,m,r,q,p[8],7,1770035416);q=a(q,o,m,r,p[9],12,-1958414417);r=a(r,q,o,m,p[10],17,-42063);m=a(m,r,q,o,p[11],22,-1990404162);o=a(o,m,r,q,p[12],7,1804603682);q=a(q,o,m,r,p[13],12,-40341101);r=a(r,q,o,m,p[14],17,-1502002290);m=a(m,r,q,o,p[15],22,1236535329);o=h(o,m,r,q,p[1],5,-165796510);q=h(q,o,m,r,p[6],9,-1069501632);r=h(r,q,o,m,p[11],14,643717713);m=h(m,r,q,o,p[0],20,-373897302);o=h(o,m,r,q,p[5],5,-701558691);q=h(q,o,m,r,p[10],9,38016083);r=h(r,q,o,m,p[15],14,-660478335);m=h(m,r,q,o,p[4],20,-405537848);o=h(o,m,r,q,p[9],5,568446438);q=h(q,o,m,r,p[14],9,-1019803690);r=h(r,q,o,m,p[3],14,-187363961);m=h(m,r,q,o,p[8],20,1163531501);o=h(o,m,r,q,p[13],5,-1444681467);q=h(q,o,m,r,p[2],9,-51403784);r=h(r,q,o,m,p[7],14,1735328473);m=h(m,r,q,o,p[12],20,-1926607734);o=e(o,m,r,q,p[5],4,-378558);q=e(q,o,m,r,p[8],11,-2022574463);r=e(r,q,o,m,p[11],16,1839030562);m=e(m,r,q,o,p[14],23,-35309556);o=e(o,m,r,q,p[1],4,-1530992060);q=e(q,o,m,r,p[4],11,1272893353);r=e(r,q,o,m,p[7],16,-155497632);m=e(m,r,q,o,p[10],23,-1094730640);o=e(o,m,r,q,p[13],4,681279174);q=e(q,o,m,r,p[0],11,-358537222);r=e(r,q,o,m,p[3],16,-722521979);m=e(m,r,q,o,p[6],23,76029189);o=e(o,m,r,q,p[9],4,-640364487);q=e(q,o,m,r,p[12],11,-421815835);r=e(r,q,o,m,p[15],16,530742520);m=e(m,r,q,o,p[2],23,-995338651);o=k(o,m,r,q,p[0],6,-198630844);q=k(q,o,m,r,p[7],10,1126891415);r=k(r,q,o,m,p[14],15,-1416354905);m=k(m,r,q,o,p[5],21,-57434055);o=k(o,m,r,q,p[12],6,1700485571);q=k(q,o,m,r,p[3],10,-1894986606);r=k(r,q,o,m,p[10],15,-1051523);m=k(m,r,q,o,p[1],21,-2054922799);o=k(o,m,r,q,p[8],6,1873313359);q=k(q,o,m,r,p[15],10,-30611744);r=k(r,q,o,m,p[6],15,-1560198380);m=k(m,r,q,o,p[13],21,1309151649);o=k(o,m,r,q,p[4],6,-145523070);q=k(q,o,m,r,p[11],10,-1120210379);r=k(r,q,o,m,p[2],15,718787259);m=k(m,r,q,o,p[9],21,-343485551);n[0]=d(o,n[0]);n[1]=d(m,n[1]);n[2]=d(r,n[2]);n[3]=d(q,n[3])}function j(u,o,n,m,r,p){o=d(d(o,u),d(m,p));return d((o<<r)|(o>>>(32-r)),n)}function a(o,n,u,r,m,q,p){return j((n&u)|((~n)&r),o,n,m,q,p)}function h(o,n,u,r,m,q,p){return j((n&r)|(u&(~r)),o,n,m,q,p)}function e(o,n,u,r,m,q,p){return j(n^u^r,o,n,m,q,p)}function k(o,n,u,r,m,q,p){return j(u^(n|(~r)),o,n,m,q,p)}function i(p){if(/[\x80-\xFF]/.test(p)){p=unescape(encodeURI(p))}txt="";var r=p.length,q=[1732584193,-271733879,-1732584194,271733878],o;for(o=64;o<=p.length;o+=64){c(q,l(p.substring(o-64,o)))}p=p.substring(o-64);var m=[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];for(o=0;o<p.length;o++){m[o>>2]|=p.charCodeAt(o)<<((o%4)<<3)}m[o>>2]|=128<<((o%4)<<3);if(o>55){c(q,m);for(o=0;o<16;o++){m[o]=0}}m[14]=r*8;c(q,m);return q}function l(n){var o=[],m;for(m=0;m<64;m+=4){o[m>>2]=n.charCodeAt(m)+(n.charCodeAt(m+1)<<8)+(n.charCodeAt(m+2)<<16)+(n.charCodeAt(m+3)<<24)}return o}var g="0123456789abcdef".split("");function f(p){var o="",m=0;for(;m<4;m++){o+=g[(p>>(m*8+4))&15]+g[(p>>(m*8))&15]}return o}function b(m){for(var n=0;n<m.length;n++){m[n]=f(m[n])}return m.join("")}md5=function(m){return b(i(m))};function d(n,m){return(n+m)&4294967295}if(md5("hello")!="5d41402abc4b2a76b9719d911017c592"){function d(m,p){var o=(m&65535)+(p&65535),n=(m>>16)+(p>>16)+(o>>16);return(n<<16)|(o&65535)}}})();/**
 * [VizzitWordPressOverlay description]
 * Modified version of the EPiOverlay
 */
function VizzitWordPressOverlay()
{
	var self = this;
	this.version = 0;
	this.login = '//www.vizzit.se/portal/';
	this.html = '<a class="ab-item" aria-haspopup="true" target="_blank" href=""><img id="vizzit-image" style="vertical-align: sub;" src="//www.vizzit.se/overlay/wordpress/img/wp.vizzit.overlay.logo.png" alt="Vizzit" width="48" height="18"></a><div class="vizzit-dropdown ab-sub-wrapper"><ul id="wp-admin-bar-vizzit-dropdown" class="ab-submenu"><li id="wp-admin-bar-vizzit-portal"><a class="ab-item">Portal</a></li><li id="wp-admin-bar-vizzit-thispage"><a class="ab-item">This Page</a></li></ul></div>';

	this.init = function()
	{
		// Attach event
		self.window();
	};

	this.run = function()
	{
		self.render();
	};
	this.render = function()
	{

		var holder = document.getElementById('wp-admin-bar-vizzit-button');
		holder.innerHTML = self.html;

		// Set up click method
		var clickMethod = function(link)
		{
			window.open(self.makeLoginLink(link));
			return false;
		};
		var portal = self.mainDocument.getElementById('wp-admin-bar-vizzit-portal');
		var thispage = self.mainDocument.getElementById('wp-admin-bar-vizzit-thispage');
		if(window.addEventListener) // W3C DOM
		{
			portal.addEventListener('click', function(){ clickMethod('portal'); }, false);
			thispage.addEventListener('click', function(){ clickMethod('thispage'); }, false);
		}
		else if(window.attachEvent) // IE DOM
		{
			portal.attachEvent('onclick', function(){ clickMethod('portal'); });
			thispage.attachEvent('onclick', function(){ clickMethod('thispage'); });
		}
	};

	/**
	 * Window onload event
	 * @return void
	 */
	this.window = function()
	{
		if(document.readyState === 'complete') // DOM is already complete
			self.run();
		else if(window.addEventListener) // W3C DOM
			window.addEventListener('load', self.run, false);
		else if(window.attachEvent) // IE DOM
			window.attachEvent("onload", self.run);
	};

	/**
	 * Make login link
	 * @param string tool
	 * @return string
	 */
	this.makeLoginLink = function(tool)
	{
		var href = this.login;
		href += '?user=' + $vizzit_username;
		href += '&data1=' + $vizzit_customer;
		href += '&p=' + $vizzit_pageId;
		href += '&t=' + tool;
		href += '&wp';
		return href;
	};
};

var VizzitWordPressOverlayObject = new VizzitWordPressOverlay();
VizzitWordPressOverlayObject.init();