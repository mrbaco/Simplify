/*
 * Preload images
 */
var imagesList = ['modules/cp/images/menu_hover.png'];
var imagesPreloader = [];

for (var i = 0, len = imagesList.length; i < len; i++) {
	imagesPreloader[i] = new Image();
	imagesPreloader[i].src = imagesList[i];
}

/*
 * Short document.getElementById
 *
 */
var e = function(e) {
  return document.getElementById(e);
};

/*
 * Adaptive menu event handler
 * 
 */
var toggle = function (el, p = undefined) {
	var display = el.style.display;
	
	if (p !== undefined) display = (p === true) ? 'block' : 'none';
	else display = (display == 'block') ? 'none' : 'block';
	
	el.style.display = display;
};

var t = function () {
	toggle(e('menu'));
	toggle(e('overlay'));
};

document.addEventListener('DOMContentLoaded', function () {
  if (e('menu')) {
    e('menu_button').onclick = t;
    e('overlay').onclick = t;
    
    window.addEventListener('resize', function() {
      if ((window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) >= 768) {
        toggle(e('menu'), true);
      } else {
        toggle(e('menu'), false);
      }
      
      toggle(e('overlay'), false);
    }, true);
  }
});

/*
 * Ajax get-request
 *
 */
var ajax = function (o) {
	var request = new XMLHttpRequest();
	
	request.open('GET', o.url, true);
	request.send();
	
	request.onreadystatechange = function() {
		if (request.readyState != 4) return;
		if (request.status == 200 && typeof o.success == 'function') {
			o.success.call(this, request.responseText);
		}
	};
};