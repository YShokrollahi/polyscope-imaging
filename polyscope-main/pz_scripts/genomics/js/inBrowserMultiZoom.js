/*
*	Description: In Browser Multizoom
*   Author: Sebastian Schmittner (stp.schmittner@gmail.com)
*   Date: 2015.11.08
*   LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
*   LastDate: 2015.11.23
*   Version: 0.0.2
*/

Base64 = {_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

var header64 = 'PCEtLQ0KKiAgIEF1dGhvcjogU2ViYXN0aWFuIFNjaG1pdHRuZXINCiogICBEYXRlOg0KKiAgIExhc3RBdXRob3I6IFNlYmFzdGlhbiBTY2htaXR0bmVyDQoqICAgTGFzdERhdGU6IDIwMTUuMTEuMDkgDQoqICAgVmVyc2lvbjogMC4xLjINCiogICBWZXJzaW9uIEtleTogTk9ORQ0KLS0+DQo8IURPQ1RZUEUgaHRtbD4NCjxodG1sPg0KPGhlYWQ+DQo8bGluayByZWw9Imljb24iIA0KICAgICAgdHlwZT0iaW1hZ2UvcG5nIiANCiAgICAgIGhyZWY9Imh0dHA6Ly95dWFubGFiLm9yZy9sb2dvcy9Mb2dvUG9seXpvb21lcjEuMGZhdi5wbmciPg0KDQogICAgPG1ldGEgY2hhcnNldD0ndXRmLTgnPg0KDQogICAgPHRpdGxlPkltYWdlT21pY3MgUG9seXpvb21lcjwvdGl0bGU+DQoNCiAgICA8bGluayByZWw9J3N0eWxlc2hlZXQnIA0KICAgICAgICAgIHR5cGU9J3RleHQvY3NzJw0KICAgICAgICAgIG1lZGlhPSdzY3JlZW4nDQogICAgICAgICAgaHJlZj0naHR0cDovL3BvbHl6b29tZXIuaWNyLmFjLnVrL3B6X3NjcmlwdHMvZ2Vub21pY3MvbXVsdGl6b29tL2Nzcy9zdHlsZS5jc3MnLz4NCg0KCTxsaW5rIHJlbD0nc3R5bGVzaGVldCcgdHlwZT0ndGV4dC9jc3MnIG1lZGlhPSdzY3JlZW4nIGhyZWY9J2h0dHA6Ly9wb2x5em9vbWVyLmljci5hYy51ay9wel9zY3JpcHRzL3VzZXJwYWdlL3RlbXBsYXRlcy9jc3MvanF1ZXJ5LXVpLm1pbi5jc3MnLz4NCgk8bGluayByZWw9J3N0eWxlc2hlZXQnIHR5cGU9J3RleHQvY3NzJyBtZWRpYT0nc2NyZWVuJyBocmVmPSdodHRwOi8vcG9seXpvb21lci5pY3IuYWMudWsvcHpfc2NyaXB0cy91c2VycGFnZS90ZW1wbGF0ZXMvY3NzL2pxdWVyeS11aS5zdHJ1Y3R1cmUubWluLmNzcycvPg0KCTxsaW5rIHJlbD0nc3R5bGVzaGVldCcgdHlwZT0ndGV4dC9jc3MnIG1lZGlhPSdzY3JlZW4nIGhyZWY9J2h0dHA6Ly9wb2x5em9vbWVyLmljci5hYy51ay9wel9zY3JpcHRzL3VzZXJwYWdlL3RlbXBsYXRlcy9jc3MvanF1ZXJ5LXVpLnRoZW1lLm1pbi5jc3MnLz4NCgkNCiAgICA8c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCIgc3JjPSJodHRwOi8vcG9seXpvb21lci5pY3IuYWMudWsvcHpfc2NyaXB0cy91c2VycGFnZS90ZW1wbGF0ZXMvanF1ZXJ5LmpzIj48L3NjcmlwdD4NCiAgICA8c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCIgc3JjPSJodHRwOi8vcG9seXpvb21lci5pY3IuYWMudWsvcHpfc2NyaXB0cy91c2VycGFnZS90ZW1wbGF0ZXMvanF1ZXJ5LXVpLm1pbi5qcyI+PC9zY3JpcHQ+DQoJPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiIHNyYz0iaHR0cDovL3BvbHl6b29tZXIuaWNyLmFjLnVrL3B6X3NjcmlwdHMvdXNlcnBhZ2UvdGVtcGxhdGVzL3NnYmVhbC1jb2xvcnBpY2tlci0yMDA3MDcxMi5qcXVlcnkuanMiPjwvc2NyaXB0Pg0KICAgIDxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0IiBzcmM9Imh0dHA6Ly9wb2x5em9vbWVyLmljci5hYy51ay9wel9zY3JpcHRzL3VzZXJwYWdlL3RlbXBsYXRlcy9yYXBoYWVsLmpzIiA+PC9zY3JpcHQ+DQogICAgPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiIHNyYz0iaHR0cDovL3BvbHl6b29tZXIuaWNyLmFjLnVrL3B6X3NjcmlwdHMvdXNlcnBhZ2UvdGVtcGxhdGVzL09wZW5TZWFkcmFnb24uanMiPjwvc2NyaXB0Pg0KCTxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0IiBzcmM9Imh0dHA6Ly9wb2x5em9vbWVyLmljci5hYy51ay9wel9zY3JpcHRzL3VzZXJwYWdlL3RlbXBsYXRlcy9zY3JvbGxXaWR0aC5qcyI+PC9zY3JpcHQ+DQoJDQogICAgPCEtLSBhbmRyZWFzLmhlaW5kbEBpY3IuYWMudWsgLS0+DQoNCjxzY3JpcHQ+DQogICAgdmFyIHZpZXdlciA9IG51bGw7DQogICAgdmFyIFNlYWRyYWdvbjsNCiAgICBTZWFkcmFnb24gPSBPcGVuU2VhZHJhZ29uOw0KICAgIE9wZW5TZWFkcmFnb24uVXRpbHMgPSBPcGVuU2VhZHJhZ29uOw0KCQ0KZnVuY3Rpb24gU3luY0ltYWdlKHZpZXdlciwgdmlld2VyVG9TeW5jV2l0aCkgew0KCWNvbnNvbGUubG9nKCdTeW5jaW5nJyk7DQoJdmlld2VyLnZpZXdwb3J0LnBhblRvKHZpZXdlclRvU3luY1dpdGgudmlld3BvcnQuZ2V0Q2VudGVyKCkpOw0KCXZpZXdlci52aWV3cG9ydC56b29tVG8odmlld2VyVG9TeW5jV2l0aC52aWV3cG9ydC5nZXRab29tKCkpOw0KfQ0KDQp2YXIgaEZ1bmNIYW5kbGVyPWZ1bmN0aW9uIG15SGFuZGxlcihTb3VyY2VWaWV3ZXIpIHsNCiAgICANCgl2YXIgdmlld2Vyc1RvU3luYyA9IFtdOw0KCQ0KCWZvciAodmFyIGtleSBpbiBWaWV3ZXJIYXNoKSB7DQoJCWlmIChWaWV3ZXJIYXNoLmhhc093blByb3BlcnR5KGtleSkpIHsNCgkJCWlmKGtleSAhPSBTb3VyY2VWaWV3ZXIuaWQpIHsNCgkJCQlpZihWaWV3ZXJIYXNoW2tleV0uaWQgPT0gU291cmNlVmlld2VyLmlkKSB7DQoJCQkJCXZpZXdlcnNUb1N5bmMucHVzaChrZXkpOw0KCQkJCX0NCgkJCX0NCgkJfQ0KCX0NCgkNCgljb25zb2xlLmxvZygnSGFzaGVzIGZvciBTb3VyY2VWaWV3ZXI6ICcsIHZpZXdlcnNUb1N5bmMpOw0KICAgIA0KCWZvcih2YXIgdmlld2VyID0gMDsgdmlld2VyIDwgdmlld2Vyc1RvU3luYy5sZW5ndGg7ICsrdmlld2VyKSB7DQoJCVRhcmdldFZpZXdlciA9IHdpbmRvd1t2aWV3ZXJzVG9TeW5jW3ZpZXdlcl1dOyAgIA0KCQkNCgkJaWYgKCFUYXJnZXRWaWV3ZXIuaXNPcGVuKCkpIHsNCgkJCWNvbnNvbGUubG9nKCdUYXJnZXRWaWV3ZXIgaXMgbm90IG9wZW4nKTsNCgkJfQ0KDQoJCWNvbnNvbGUubG9nKCdTdGFydGluZyBsaXZlIHN5bmMuLi4nLCBTb3VyY2VWaWV3ZXIuaWQsICcgd2l0aCAnLCBUYXJnZXRWaWV3ZXIuaWQpOw0KCQlTeW5jSW1hZ2UoVGFyZ2V0Vmlld2VyLFNvdXJjZVZpZXdlcikgICAgICANCgkJY29uc29sZS5sb2coJ2RvbmUnKTsNCgl9DQp9DQogIA0KZnVuY3Rpb24gTGl2ZVN5bmMoU291cmNlVmlld2VyKSB7DQogIGNvbnNvbGUubG9nKCdBdHRhY2hpbmcgbGl2ZSBzeW5jIGhhbmRsZXIgdG8gJyxTb3VyY2VWaWV3ZXIuaWQpOw0KICBTb3VyY2VWaWV3ZXIuYWRkSGFuZGxlcigiYW5pbWF0aW9uIixoRnVuY0hhbmRsZXIpOw0KfQ0KDQpmdW5jdGlvbiBVbkxpdmVTeW5jKFNvdXJjZVZpZXdlcikgew0KICBjb25zb2xlLmxvZygnUmVtb3ZpbmcgaGFuZGxlciBmcm9tICcsU291cmNlVmlld2VyLmlkKTsNCiAgU291cmNlVmlld2VyLnJlbW92ZUhhbmRsZXIoImFuaW1hdGlvbiIsaEZ1bmNIYW5kbGVyKQ0KfQ0KDQpmdW5jdGlvbiBTeW5jVGhlbUFsbCgpDQp7DQo=';
var body64 = 'fQ0KDQp2YXIgY3VycmVudE1vdXNlUG9zID0geyB4OiAtMSwgeTogLTEgfTsNCiQoZG9jdW1lbnQpLm1vdXNlbW92ZShmdW5jdGlvbihldmVudCkgew0KCWN1cnJlbnRNb3VzZVBvcy54ID0gZXZlbnQucGFnZVg7DQogICAgY3VycmVudE1vdXNlUG9zLnkgPSBldmVudC5wYWdlWTsNCn0pOw0KDQo8L3NjcmlwdD4NCjwvaGVhZD4NCg0KPGJvZHkgb25sb2FkPSJTeW5jVGhlbUFsbCgpIj4NCjx0YWJsZSBib3JkZXI9IjAiPg0KPHRyPg0KPHRkICBjb2xzcGFuID0gIjIiPg0KPGNlbnRlcj4NCjxicj4NCiA8ZGl2IHN0eWxlPSJib3JkZXItYm90dG9tOiAxcHggc29saWQ7Ij4NCgk8ZGl2IHN0eWxlPSJkaXNwbGF5OmlubGluZS1ibG9jazsiPjxoMT5Qb2x5c2NvcGU8L2gxPjwvZGl2Pg0KIDwvZGl2Pg0KPC9kaXY+DQo8L2NlbnRlcj4NCjwvdGQ+DQo8L3RyPg0KIDx0cj4NCg0K';
var zoom64 = 'PHRkPgpfVklFV0VSTkFNRV8KICAgIDxkaXYgaWQ9Il9DT05URU5USURfIiBjbGFzcz0ib3BlbnNlYWRyYWdvbiI+PC9kaXY+CjxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0Ij4KICAgIC8vIEV4YW1wbGUKICAgICB2YXIgX1ZJRVdFUl9WQVJOQU1FXyA9IE9wZW5TZWFkcmFnb24oewogICAgICAgICBpZDogICAgICAgICAgICAgICJfQ09OVEVOVElEXyIsCiAgcHJlZml4VXJsOiAgICAgICAiaHR0cDovL3BvbHl6b29tZXIuaWNyLmFjLnVrL3B6X3NjcmlwdHMvdXNlcnBhZ2UvdGVtcGxhdGVzL2ltYWdlcy8iLAp0aWxlU291cmNlczogICAgICJfUkVMX1BBVEhfVE9fRFpJXyIKCSAgICB9KTsKPC9zY3JpcHQ+CjwvdGQ+Cg==';
var bottom64 = 'PC90YWJsZT4KPC9ib2R5Pgo=';

function createInBrowserMultiZoom( layout, _titles ) {
	
	var inBrowserMultiZoomHtml = '';
	
	inBrowserMultiZoomHtml = _createHeader();
	inBrowserMultiZoomHtml = inBrowserMultiZoomHtml + _createLayoutTable(layout, _titles);
	inBrowserMultiZoomHtml = inBrowserMultiZoomHtml + _createBottom();
	
	return inBrowserMultiZoomHtml;
}

function _createHeader() {
	var header = Base64.decode(header64);
	var body = Base64.decode(body64);
	
	return header + '\n\r' + body + '\n\r';
}

function _createLayoutTable(layout, titles) {
	
	var zoomHtml = '';
	var index = 0;
	
	for(var y = 0; y < layout.rows; ++y) {
		zoomHtml = zoomHtml + '<tr>';
		for(var x = 0; x < layout.cols; ++x) {
			
			var contentId = 'contentDiv' + index;
			
			if(layout.zooms[x][y] !== undefined && 
			   !isEmpty(layout.zooms[x][y])) {
				var currentTarget = layout.zooms[x][y];

				if(isEmpty(currentTarget)) {
					zoomHtml = zoomHtml + '<td></td>';
				}
				else {
					zoomHtml = zoomHtml + createZoomEntry(currentTarget, contentId, titles[index]);
					index = index + 1;
				}
			}
			else {
				zoomHtml = zoomHtml + '<td></td>';
			}
		}
		zoomHtml = zoomHtml + '</tr>';
	}
	
	return zoomHtml;
}

function createZoomEntry(dzi, contentId, title) {
	var zoom = Base64.decode(zoom64);
	
	var viewerName = basename(dzi);
	viewerName = viewerName.split('.')[0];
	viewerName = viewerName.replace('UNKNOWNPAT0001_UNKNOWNCHANNEL0001_','_');
	
	zoom = zoom.replace('_CONTENTID_', contentId);
	zoom = zoom.replace('_CONTENTID_', contentId);
	zoom = zoom.replace('_REL_PATH_TO_DZI_', dzi);
	zoom = zoom.replace('_VIEWER_VARNAME_', viewerName);
	zoom = zoom.replace('_VIEWERNAME_', title + '(' + viewerName + ')');
	
	return zoom;
}

function _createBottom() {
	return Base64.decode(bottom64);
}

function isEmpty(str) {
    return (!str || 0 === str.length);
}

function basename(str) {
	return str.split(/[\\/]/).pop();
}
