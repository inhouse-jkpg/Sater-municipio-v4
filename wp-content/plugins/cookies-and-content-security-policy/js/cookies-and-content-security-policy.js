jQuery(document).ready(function() {
	googleConsentMode();
});

jQuery(window).on("load", function() { // was $(window).load(function () {
	cookiesAndContentPolicyModal();
	cookiesAndContentPolicyToggleSwitches();
	openCookiesAndContentPolicySettingsLink();
	openCookiesAndContentPolicySettingsHash();
	gaSetReferrer();
});

var CACSP_COOKIE_NAME = 'cookies_and_content_security_policy';

if (cacspMessages.cacspWpEngineCompatibilityMode === '1') {
	CACSP_COOKIE_NAME = 'wpe-us';
}

function cookiesAndContentPolicyModal() {
	if (!Cookies.get(CACSP_COOKIE_NAME) && !jQuery('body').hasClass('modal-cacsp-do-not-show-cookie-modal') && !jQuery('html').hasClass('et-fb-app-frame')) {
		// Show info modal
		timer = setTimeout(function () {
			jQuery('html, body').addClass('modal-cacsp-open');
			jQuery('.modal-cacsp-box.modal-cacsp-box-info').addClass('modal-cacsp-box-show');
		}, cacspMessages.cacspTimeout);
		// Buttons for info modal
		// Show settings
		jQuery('.modal-cacsp-box.modal-cacsp-box-info .modal-cacsp-btns a.modal-cacsp-btn.modal-cacsp-btn-settings').on('click', function() {
			openCookiesAndContentPolicySettings(false);
			return false;
		});
		// Refuse all
		jQuery('.modal-cacsp-box.modal-cacsp-box-info .modal-cacsp-btns a.modal-cacsp-btn.modal-cacsp-btn-refuse').on('click', function() {
			jQuery('.modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-btns a.modal-cacsp-btn.modal-cacsp-btn-save').trigger('click');
			return false;
		});
		// Accept all
		jQuery('.modal-cacsp-box.modal-cacsp-box-info .modal-cacsp-btns a.modal-cacsp-btn.modal-cacsp-btn-accept').on('click', function() {
			jQuery('.modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-btns a.modal-cacsp-btn.modal-cacsp-btn-accept-all').trigger('click');
			return false;
		});
		// Allow scroll on html if set in admin
		if ( jQuery('body.modal-cacsp-open-no-backdrop').length ) {
			jQuery('html').addClass('modal-cacsp-open-no-backdrop');
		}
		// Save settings
		saveCookiesAndContentPolicySettings();
	}
	// Close by X
	jQuery('.modal-cacsp-box-close').on('click', function() {
		saveByClose = false;
		// if close on info or if cookie doesn't exists
		if (jQuery('.modal-cacsp-box.modal-cacsp-box-info').hasClass('modal-cacsp-box-show') || !Cookies.get(CACSP_COOKIE_NAME)) {
			jQuery('.modal-cacsp-box .modal-cacsp-box-settings-list ul li a').removeClass('modal-cacsp-toggle-switch-active');
			saveByClose = true;
		// if close on settings with cookie
		} else if (Cookies.get(CACSP_COOKIE_NAME)) {
			jQuery('.modal-cacsp-box.modal-cacsp-box-settings').removeClass('modal-cacsp-box-show');
			jQuery('html, body').removeClass('modal-cacsp-open');
		// fallback
		} else {
			saveByClose = true;
		}
		if ( saveByClose == true ) {
			jQuery('.modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-btns a.modal-cacsp-btn.modal-cacsp-btn-save').trigger('click');
		}
		return false;
	});
}

/* Open the settings modal */
function openCookiesAndContentPolicySettings(link) {
	if (link) {
		jQuery('html, body').addClass('modal-cacsp-open');
		jQuery('.modal-cacsp-backdrop').addClass('modal-cacsp-backdrop-show');
	}
	jQuery('body.modal-cacsp-open-no-backdrop').removeClass('modal-cacsp-open-no-backdrop');
	jQuery('.modal-cacsp-box.modal-cacsp-box-info').removeClass('modal-cacsp-box-show');
	jQuery('.modal-cacsp-box.modal-cacsp-box-settings').addClass('modal-cacsp-box-show');
	jQuery('html').removeClass('modal-cacsp-open-no-backdrop');
	// Set the toggles according to the users settings
	if (Cookies.get(CACSP_COOKIE_NAME)) {
		cookie_filter = JSON.parse(Cookies.get(CACSP_COOKIE_NAME));
		if (cookie_filter) {
			jQuery.each(cookie_filter, function( index, value ) {
				jQuery('.modal-cacsp-box .modal-cacsp-box-settings-list ul li a[data-accepted-cookie=' + value + ']').addClass('modal-cacsp-toggle-switch-active');
			});
		}
	}
	return false;
}

/* Open the settings modal from link */
function openCookiesAndContentPolicySettingsLink() {
	jQuery('a[href$="#cookiesAndContentPolicySettings"]').on('click', function() {
		openCookiesAndContentPolicySettings(true);
		saveCookiesAndContentPolicySettings();
		return false;
	});
}

/* Open the settings modal from hash */
function openCookiesAndContentPolicySettingsHash() {
	if (window.location.hash == '#cookiesAndContentPolicySettings') {
		openCookiesAndContentPolicySettings(true);
		saveCookiesAndContentPolicySettings();
		location.hash = "";
	}
}

/* Toggle switches */
function cookiesAndContentPolicyToggleSwitches() {
	jQuery('.modal-cacsp-toggle-switch').on('click', function() {
		if (!jQuery(this).hasClass('disabled')) {
			jQuery(this).toggleClass('modal-cacsp-toggle-switch-active');
		}
        return false;
    });
};

/* Save the settings buttons */
function saveCookiesAndContentPolicySettings() {
	if (!jQuery('.modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-btns a.modal-cacsp-btn').hasClass('js-modal-cacsp-btn-click')) {
		jQuery('.modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-btns a.modal-cacsp-btn').on('click', function() {
			jQuery('.modal-cacsp-box.modal-cacsp-box-settings .modal-cacsp-btns a.modal-cacsp-btn').addClass('js-modal-cacsp-btn-click');
			if (jQuery(this).hasClass('modal-cacsp-btn-accept-all')) {
				jQuery('.modal-cacsp-box .modal-cacsp-box-settings-list ul li a').addClass('modal-cacsp-toggle-switch-active');
			}
			if (jQuery(this).hasClass('modal-cacsp-btn-refuse-all')) {
				jQuery('.modal-cacsp-box .modal-cacsp-box-settings-list ul li a').removeClass('modal-cacsp-toggle-switch-active');
			}
			var acceptedCookies = [];
			jQuery('.modal-cacsp-box .modal-cacsp-box-settings-list ul li a.modal-cacsp-toggle-switch-active').each(function( index ) {
				acceptedCookies.push(jQuery(this).data('accepted-cookie'));
			});
			if (location.protocol !== 'https:') {
				secure = false;
			} else {
				secure = true;
			}
			expires = parseInt(cacspMessages.cacspExpires);
			Cookies.set(CACSP_COOKIE_NAME, JSON.stringify(acceptedCookies), { expires: expires, sameSite: 'Lax', secure: secure });
			if (cacspMessages.cacspOptionSaveConsent == '1') {
				jQuery.ajax({
					type: "POST",
					url: cacsp_ajax_object.ajax_url,
					data: {
						action: 'cacsp_insert_consent_data',
						accepted_cookies: acceptedCookies.toString(),
						expires: expires
					},
					complete: function () {
						location.reload(true);
					},
				});
			} else {
				location.reload(true);
			}
	        return false;
		});
	}
}

/* Check blocked iframes */
function cookiesAndContentPolicyCheckBlockedIframe(iframe, adminEmail) {
	if ( cacspMessages.cacspOptionDisableContentNotAllowedMessage == "0" ) {
		if (Cookies.get(CACSP_COOKIE_NAME)) {
			cookie_filter = JSON.parse(Cookies.get(CACSP_COOKIE_NAME));
			if (cookie_filter) {
				cookie_filter_length = cookie_filter.length;
			}
		} else {
			cookie_filter_length = 0;
		}
		iframe.addClass('warning-cacsp-iframe');
		if (iframe.is(":visible")) {
			warningCacspIframeHeight = iframe.height();
			warningCacspIframeContentMargin = warningCacspIframeHeight - (warningCacspIframeHeight * 2);
			warningCacspIframeWidth = iframe.width();
			warningHtml = '<div class="warning-cacsp-iframe-content" style="height: ' + warningCacspIframeHeight + 'px; width: ' + warningCacspIframeWidth + 'px; margin-top: ' + warningCacspIframeContentMargin + 'px;">';
			if ( cookie_filter_length == 3 ) {
				warningHtml += '<div class="warning-cacsp-iframe-content-text">' + cacspMessages.cacspNotAllowedDescription + '</div>';
				if ( adminEmail !== '' ) {
					warningHtml += '<div class="warning-cacsp-iframe-content-button"><a href="mailto:' + adminEmail + '?subject=' + encodeURI(cacspMessages.cacspNotAllowedDescription) + '&amp;body=' + location.href + '">' + cacspMessages.cacspNotAllowedButton + '</a></div>';
				}
			} else {
				warningHtml += '<div class="warning-cacsp-iframe-content-text">' + cacspMessages.cacspReviewSettingsDescription + '</div>';
				warningHtml += '<div class="warning-cacsp-iframe-content-button"><a href="#cookiesAndContentPolicySettings">' + cacspMessages.cacspReviewSettingsButton + '</a></div>';
			}
			warningHtml += '</div>';
			jQuery(warningHtml).insertAfter(iframe);
			openCookiesAndContentPolicySettingsLink();
		}
	}
}

/* Check blocked objects */
function cookiesAndContentPolicyCheckBlockedObject(object, adminEmail) {
	if (Cookies.get(CACSP_COOKIE_NAME)) {
		cookie_filter = JSON.parse(Cookies.get(CACSP_COOKIE_NAME));
		if (cookie_filter) {
			cookie_filter_length = cookie_filter.length;
		}
	} else {
		cookie_filter_length = 0;
	}
	object.addClass('warning-cacsp-object');
	if (object.is(":visible")) {
		warningCacspIframeHeight = jQuery('.warning-cacsp-object').height();
		warningCacspIframeContentMargin = warningCacspIframeHeight - (warningCacspIframeHeight * 2);
		warningCacspIframeWidth = jQuery('.warning-cacsp-object').width();
		warningHtml = '<div class="warning-cacsp-object-content" style="height: ' + warningCacspIframeHeight + 'px; width: ' + warningCacspIframeWidth + 'px; margin-top: ' + warningCacspIframeContentMargin + 'px;">';
		if ( cookie_filter_length == 3 ) {
			warningHtml += '<div class="warning-cacsp-object-content-text">' + cacspMessages.cacspNotAllowedDescription + '</div>';
			warningHtml += '<div class="warning-cacsp-object-content-button"><a href="mailto:' + adminEmail + '?subject=' + encodeURI(cacspMessages.cacspNotAllowedDescription) + '&amp;body=' + location.href + '">' + cacspMessages.cacspNotAllowedButton + '</a></div>';
		} else {
			warningHtml += '<div class="warning-cacsp-object-content-text">' + cacspMessages.cacspReviewSettingsDescription + '</div>';
			warningHtml += '<div class="warning-cacsp-object-content-button"><a href="#cookiesAndContentPolicySettings">' + cacspMessages.cacspReviewSettingsButton + '</a></div>';
		}
		warningHtml += '</div>';
		jQuery(warningHtml).insertAfter('.warning-cacsp-object');
		openCookiesAndContentPolicySettingsLink();
	}
}

/* Check for blocked images */
jQuery('img').on('error', function() {
	jQuery(this).addClass('warning-cacsp-img');
});

function cookiesAndContentPolicyErrorMessage(domains, siteUrl) {
	//console.log('cookiesAndContentPolicyErrorMessage run');
	domains = domains + ' ' + cookiesAndContentPolicyTrailingSlash(siteUrl);
	domainsArr = domains.split(" ");
	jQuery('iframe').each(function() {
		isAllowed = false;
		iframeHostname = cookiesAndContentPolicyGetHostname(jQuery(this).attr('src'));
		if (iframeHostname) {
			iframeHostname = cookiesAndContentPolicyTrailingSlash(iframeHostname);
			jQuery.each(domainsArr, function( index, domainRule ) {
				match = false;
				domainRule = cookiesAndContentPolicyTrailingSlash(domainRule);
				if ( iframeHostname.startsWith('//') ) {
					domainRule = domainRule.split(':')[1];
				}
				match = cookiesAndContentPolicyMatchHostname(iframeHostname, domainRule);
				//console.log( index + ": " + iframeHostname + " matches " + domainRule + " is " + match);
				if (match) {
					isAllowed = true;
					return false;
				}
			});
			if (isAllowed == false) {
				jQuery.ajax({
					url: siteUrl + '/wp-json/cacsp/v1/texts',
					dataType: 'json',
					async: false,
					type: 'GET',  
					success: function(data) {
						var theContent = data;
						adminEmail = theContent.warning_texts.admin_email; 
					}
				});
				cookiesAndContentPolicyCheckBlockedIframe(jQuery(this), adminEmail);
			}
		}
		//console.log('isAllowed = ' + isAllowed);
	});
	jQuery('object').each(function() {
		isAllowed = false;
		objectHostname = cookiesAndContentPolicyGetHostname(jQuery(this).attr('data'));
		if (objectHostname) {
			objectHostname = cookiesAndContentPolicyTrailingSlash(objectHostname);
			jQuery.each(domainsArr, function( index, domainRule ) {
				match = false;
				domainRule = cookiesAndContentPolicyTrailingSlash(domainRule);
				if ( objectHostname.startsWith('//') ) {
					domainRule = domainRule.split(':')[1];
				}
				match = cookiesAndContentPolicyMatchHostname(objectHostname, domainRule);
				//console.log( index + ": " + objectHostname + " matches " + domainRule + " is " + match);
				if (match) {
					isAllowed = true;
					return false;
				}
			});
			if (isAllowed == false) {
				jQuery.ajax({
					url: siteUrl + '/wp-json/cacsp/v1/texts',
					dataType: 'json',
					async: false,
					type: 'GET',  
					success: function(data) {
						var theContent = data;
						adminEmail = theContent.warning_texts.admin_email; 
					}
				});
				cookiesAndContentPolicyCheckBlockedObject(jQuery(this), adminEmail);
			}
		}
		//console.log('isAllowed = ' + isAllowed);
	});
	
}

function cookiesAndContentPolicyMatchHostname(domain, domainRule) {
	if (domainRule) {
		var escapeRegex = function(domain) {
			return domain.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
		}
		domainRule = domainRule.split("*").map(escapeRegex).join(".*");
		domainRule = "^" + domainRule + "$"
		var regex = new RegExp(domainRule);
		return regex.test(domain);
	}
}

function cookiesAndContentPolicyGetHostname(url) {
	if (url) {
		locationProtocol = false;
		if (url.startsWith("//")) {
			url = location.protocol + url;
			locationProtocol = true;
		}
		var a = new URL(url);
		if (locationProtocol) {
			domain = location.protocol + '//' + a.hostname;
		} else {
			domain = a.protocol + '//' + a.hostname;
		}
		return a.hostname ? domain : null;
    }
}

function cookiesAndContentPolicyTrailingSlash(url) {
	var lastChar = url.substr(-1);
	if (lastChar != '/') { 
	   url = url + '/';
	}
	return url;
}

function gaSetReferrer() {
	if ( document.referrer.indexOf(location.protocol + "//" + location.host) !== 0 && !Cookies.get(CACSP_COOKIE_NAME) && document.referrer ) {
		Cookies.set('ga_page_referrer', document.referrer);
	}
	if (Cookies.get('ga_page_referrer') && Cookies.get(CACSP_COOKIE_NAME)) {
		if (typeof gtag != 'function') {
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
		}
		gtag('set', 'page_referrer', Cookies.get(CACSP_COOKIE_NAME));
		Cookies.remove('ga_page_referrer');
	}
}

// https://developers.google.com/tag-platform/security/guides/consent?consentmode=advanced#gtag.js_3
function googleConsentMode() {
	if (cacspMessages.cacspOptionGoogleConsentMode == '1') {
		hasMarketing = false;
		hasStatistics = false;
		hasExperience = false;
		waitForUpdate = 500;
		if (Cookies.get(CACSP_COOKIE_NAME)) {
			hasMarketing = Cookies.get(CACSP_COOKIE_NAME).includes("markerting");
		}
		if (Cookies.get(CACSP_COOKIE_NAME)) {
			hasStatistics = Cookies.get(CACSP_COOKIE_NAME).includes("statistics");
		}
		if (Cookies.get(CACSP_COOKIE_NAME)) {
			hasExperience = Cookies.get(CACSP_COOKIE_NAME).includes("experience");
		}
	}
	if (cacspMessages.cacspOptionGoogleConsentMode == '1' && Cookies.get(CACSP_COOKIE_NAME)) {
		gtag('consent', 'update', {
			'ad_storage': (hasMarketing ? 'granted' : 'denied'), 
			'ad_user_data': (hasMarketing ? 'granted' : 'denied'), 
			'ad_personalization': (hasMarketing ? 'granted' : 'denied'), 
			'analytics_storage': (hasStatistics ? 'granted' : 'denied'), 
			'functionality_storage': (hasExperience ? 'granted' : 'denied'), 
			'personalization_storage': (hasExperience ? 'granted' : 'denied'), 
			'security_storage': (hasExperience ? 'granted' : 'denied'), 
		});
	}
}
