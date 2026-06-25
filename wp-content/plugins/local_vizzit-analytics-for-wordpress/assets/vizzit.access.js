var vizzit_init = function()
{
	vizzit_window();
};

var vizzit_run = function()
{
	var vizzitButton = document.getElementById('wp-admin-bar-vizzit-button');
	var vizzitButtonContent = '<a class="ab-item" aria-haspopup="true" target="_blank" href="">' +
								'<img id="vizzit-image" style="vertical-align: sub;" src="//www.vizzit.se/overlay/wordpress/img/wp.vizzit.overlay.logo.png" alt="Vizzit" width="48" height="18">' +
						  '</a>' +
						  '<div class="vizzit-dropdown ab-sub-wrapper">' +
								'<ul id="wp-admin-bar-vizzit-dropdown" class="ab-submenu">' +
									  '<li id="wp-admin-bar-vizzit-portal" style="cursor: pointer;">' +
											'<a class="ab-item">Portal</a>' +
									  '</li>' +
									  '<li id="wp-admin-bar-vizzit-thispage" style="cursor: pointer;">' +
											'<a class="ab-item">This Page</a>' +
									  '</li>' +
							    '</ul>' +
						  '</div>';

	vizzitButton.innerHTML = vizzitButtonContent;
	vizzitButton.addEventListener("click", function(e) {
		e.preventDefault();
	});

	vizzit_button_click = function(type)
	{
		window.open(vizzit_button_link(type));
		return false;
	};

	var vizzitPortal = document.getElementById('wp-admin-bar-vizzit-portal');
	var vizzitThispage = document.getElementById('wp-admin-bar-vizzit-thispage');
	if(window.addEventListener) // W3C DOM
	{
		vizzitPortal.addEventListener('click', function(){ vizzit_button_click('portal'); }, false);
		vizzitThispage.addEventListener('click', function(){ vizzit_button_click('thispage'); }, false);
	}
	else if(window.attachEvent) // IE DOM
	{
		vizzitPortal.attachEvent('onclick', function(){ vizzit_button_link('portal'); });
		vizzitThispage.attachEvent('onclick', function(){ vizzit_button_link('thispage'); });
	}
};

var vizzit_window = function()
{
	if(document.readyState === 'complete')
		vizzit_run();
	else if(window.addEventListener)
		window.addEventListener('load', vizzit_run, false);
	else if(window.attachEvent)
		window.attachEvent("onload", vizzit_run);
};

var vizzit_button_link = function(tool)
{
	var href = '//www.vizzit.se/portal/';
	href += '?i=' + vizzit_hex($vizzit_private_key);

	if($vizzit_username != '' && $vizzit_username != null)
		href += '&user=' + $vizzit_username;

	if(tool === 'portal')
		href += '&t=' + tool;
	else {
		if($vizzit_page_id != '' && $vizzit_page_id != null)
			href += '&p=' + $vizzit_page_id;
		else
			href += '&force=1';

		href += '&t=' + tool;
	}
	vizzit_debug_href = href;
	return href;
};

var vizzit_hex = function(a)
{
	var string = '';
	for (i = 0; i < a.length; i++)
	{
		string += a.charCodeAt(i).toString(16);
	}
	return string.toString();
}

vizzit_init();
