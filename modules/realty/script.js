var createCPMap = function (map_id) {
  var map = new ymaps.Map(map_id, {
    controls: ['zoomControl', 'fullscreenControl', 'rulerControl'],
    center: [59.12, 37.92],
    zoom: 12
  });
  
  map.behaviors.disable(['scrollZoom']);
  
  var moveMap = function () {
    map.setCenter(pm.geometry.getCoordinates());
  };
  
  var setValue = function () {
    e('latlong').value = pm.geometry.getCoordinates();
  };
  
  var pm = new ymaps.Placemark(map.getCenter(), {}, {
    preset: 'islands#darkBlueDotIcon',
    draggable: true
  });
  
  map.geoObjects.add(pm);
  
  pm.events.add('dragend', function () {
    setValue();
  });
  
  var findButton = new ymaps.control.Button('Центр');
  
  findButton.events.add('press', function () {
    moveMap();
  });
  
  map.controls.add(findButton, {selectOnClick: false, float: 'right'});
  
  var geocoder = function () {
    var city = e('city').value;
    var address = e('address').value;
  
    if (!city || !address) return;
    
    ajax({
      url: '/geocoder/?address=' + city + ' ' + address,
      success: function (longlat) {
        longlat = longlat.split(' ');
        longlat = [longlat[1], longlat[0]];
        
        pm.geometry.setCoordinates(longlat);
        
        moveMap();
        setValue();
        
        map.setZoom(16);
      }
    });
  };
  
  e('search').onclick = function () {
    geocoder.call(this);
  };
  
  e('address').onkeyup = function (event) {
    if (event.which == 13 || event.keyCode == 13) {
      geocoder.call(this);
      return false;
    }
  };
  
  if (e('latlong').value != '') {
    longlat = e('latlong').value.split(',');
    longlat = [longlat[0], longlat[1]];
    
    pm.geometry.setCoordinates(longlat);
    
    moveMap();
    setValue();
    
    map.setZoom(16);
  }
};

document.addEventListener('DOMContentLoaded', function () {
  if (e('action_select')) {
    e('action_select').onchange = function () {
      var forRent = false;
      
      if (this.options.selectedIndex === 0) toggle(e('reserve'), false);
      else {
        if (e('lease_select').options.selectedIndex == 1) toggle(e('reserve'), true);
        forRent = true;
      }
      
      toggle(e('lease'), forRent);
      toggle(e('checkbox_params'), forRent);
    };
    
    e('property_select').onchange = function () {
      if (this.options[this.options.selectedIndex].text == 'Новостройки') toggle(e('subcat'), true);
      else toggle(e('subcat'), false);
    };
  
    e('lease_select').onchange = function () {
      if (e('lease_select').options.selectedIndex == 1) toggle(e('reserve'), true);
      else toggle(e('reserve'), false);
    };
    
    e('price_text').onkeyup = function () {
      this.value = this.value.replace(/\s/g, '').replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
    };
    
    e('type_select').onchange = function () {
      if (e('type_select').options.selectedIndex == 1) toggle(e('rooms_number'), true);
      else toggle(e('rooms_number'), false);
    };
    
    e('comission_text').onkeyup = function () {
      this.value = this.value.replace(/\s/g, '').replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
    };
    
    e('squere').onkeyup = function () {
      if (e('type_select').options[e('type_select').options.selectedIndex].text == 'участок') e('s_unit').innerHTML = 'сотки';
      else e('s_unit').innerHTML = 'м<sup>2</sup>';
    };
  }
  
  if (e('agent_phone')) {
    e('agent_phone').onkeyup = function () {
      var pattern = '+7 123 456-78-90', arr = this.value.match( /\d/g ), i = 0;
      if (arr === null) return;
      this.value = pattern.replace(/\d/g, function(a, b) {
        if (arr.length) i = b + 1;
        return arr.shift();
      }).substring(0, i);
    };
  }
});