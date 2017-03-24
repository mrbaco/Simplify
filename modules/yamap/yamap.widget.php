<?php if (!defined('SIMPLIFY')) exit; ?>
<div id="map"></div>
<script type="text/javascript">
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
  
  ymaps.ready(function () {
    var map = new ymaps.Map('map', {
      controls: ['zoomControl', 'fullscreenControl', 'rulerControl'],
      center: [59.12, 37.92],
      zoom: 12
    }, {suppressMapOpenBlock: true});
    
    map.behaviors.disable(['scrollZoom']);
    
    <?php if (isset($simplify->myResult['yamap']['json_data_url'])) { ?>
    var objectManager = new ymaps.ObjectManager({
      clusterize: true,
      gridSize: 32
    });
    
    objectManager.objects.options.set('preset', 'islands#darkBlueDotIcon');
    objectManager.clusters.options.set('preset', 'islands#darkBlueClusterIcons');
    map.geoObjects.add(objectManager);
    
    ajax({
      url: '<?php echo $simplify->myResult['yamap']['json_data_url']; ?>',
      success: function (data) {
        data = JSON.parse(data);
        objectManager.add(data);
        
        <?php if (isset($simplify->myResult['yamap']['highlight'])) { ?>
        objectManager.objects.setObjectOptions('<?php echo $simplify->myResult['yamap']['highlight']; ?>', {
          preset: 'islands#geolocationIcon'
        });
        <?php } ?>
      }
    });
    <?php } ?>
  });
</script>