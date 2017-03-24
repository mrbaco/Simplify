var selected;
var overed;

var clearPreview = function () {
  e('preview').innerHTML = '<small>Предпросмотр изображений.</small>';
};

var createPreview = function (href) {
  e('preview').innerHTML = '<img src="' + href + '" /><p><input class="url" type="text" value="' + href + '" /></p>';
};

document.addEventListener('DOMContentLoaded', function () {
  if (e('preview')) clearPreview();
  
  var eventCatcher = function (event, func) {
    var target = event.target;
    var result;
    
    while (target != this) {
      if (target === null) break;
      
      if (target.tagName == 'A') {
        result = func.call(this, target);
        break;
      }
      
      target = target.parentNode;
    }
    
    return result;
  };
  
  e('storage').onclick = function (event) {
    return eventCatcher(event, function (target) {
      if (event.target.classList.contains('remove')) return false;
      
      if (!target.childNodes[1].classList.contains('directory')) {
        if (e('preview')) clearPreview();
        
        if (e(selected)) e(selected).classList.remove('selected');
        
        selected = target.id;
        e(selected).classList.add('selected');
        
        if (!e('selected')) {
          var el = document.createElement('INPUT');
          el.type = 'hidden';
          el.name = 'selected';
          el.id = 'selected';
          e('storage').appendChild(el);
        }
        
        e('selected').value = target.href;
        
        if (e('preview') && target.getElementsByClassName('background')[0].classList.contains('picture')) createPreview(target.href);
        if (event.ctrlKey) window.location.href = e(selected).href;
        
        return false;
      }
    });
  };
  
  e('storage').onmouseover = function (event) {
    eventCatcher(event, function (target) {
      target.getElementsByClassName('remove')[0].style.display = 'block';
      target.getElementsByClassName('remove')[0].onclick = function () {
        if (confirm('Удалить выбранный элемент?')) {
          ajax({
            url: '/cp/?m=storage&ajax&remove=' + encodeURIComponent(target.href) + (target.getElementsByClassName('background')[0].classList.contains('directory') ? '&directory' : ''),
            success: function (r) {
              r = JSON.parse(r);
              if (r.response == 1) {
                if (e('selected') && target.id == selected)  e('selected').value = '';
                e('storage').removeChild(target);
              }
            }
          });
        }
        
        return false;
      };
      
      overed = target;
    });
  };
  
  e('storage').onmouseout = function (event) {
    eventCatcher(event, function (target) {
      overed.getElementsByClassName('remove')[0].style.display = 'none';
      overed = target;
    });
  };
  
  if (e('preview')) {
    e('upload').onchange = function () {
      e('files_upload').submit();
    };
  }
});