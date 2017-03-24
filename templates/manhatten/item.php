<?php

if (!defined('SIMPLIFY')) exit;

$e = $simplify->myResult['e'];

$e['PARAMS'] = unserialize($e['PARAMS']);
$e['IMAGES'] = unserialize($e['IMAGES']);

$e['AGENT_PHOTO'] = unserialize($e['AGENT_PHOTO']);
$e['AGENT_PHOTO'] = $e['AGENT_PHOTO'][0];

$e['NAME']  = $e['TYPE_NAME'] == 'квартира' ? $e['ROOMS'] . ' к. ' : '';
$e['NAME'] .= $e['TYPE_NAME'];
$e['NAME'] .= $e['TYPE_NAME'] != 'участок' ? ', ' . $e['FLOOR'] . '/' . $e['HOUSE_FLOORS'] . ' эт.' : ', ' . $e['SQUERE'] . ' сот.';

$e['NAME'] = $simplify->catalog->ucfirst_utf8($e['NAME']);

$chb_params = $simplify->catalog->categoryArray('съемная квартира');

$simplify->title = $e['NAME'];

$simplify->tpl->addScript('script.js', 'flatpickr');
$simplify->tpl->addStyle('script.css', 'flatpickr');

?>
<div class="page">
  <h1><?php echo $e['NAME'] . '<br />' . $e['ADDRESS']; ?></h1>
  <div id="photos">
    <div id="current" style="background-image: url('<?php echo $e['IMAGES'][0]; ?>');"></div>
    <div id="thumbs">
      <?php if (sizeof($e['IMAGES'])) foreach ($e['IMAGES'] as $key => $v) { ?>
      <div id="thumb_<?php echo $key; ?>" class="thumb<?php echo $key == 0 ? ' active' : ''; ?>" style="background-image: url('<?php echo $v; ?>');"></div>
      <?php } ?>
    </div>
  </div>
  <div id="money">
    <div id="price">
      <?php
      if ($e['ACTION'] == 1) echo number_format($e['PRICE'] + $e['COMISSION'], 0, ',', ' ') . ' <span class="rouble">o</span>';
      else echo number_format($e['PRICE'], 0, ',', ' ') . ' <span class="rouble">o</span>/мес.<br /><div class="comission">Комиссия: ' . number_format($e['COMISSION'], 0, ',', ' ') . ' <span class="rouble">o</span></div>';
      ?>
    </div>
    <div id="agent">
      <div id="photo" style="background-image: url('<?php echo $e['AGENT_PHOTO']; ?>');"></div>
      <div id="phone">
        <?php echo $e['AGENT_PHONE']; ?><br />
        <span><?php echo $e['AGENT']; ?></span>
      </div>
    </div>
    <div class="clear"></div>
  </div>
  <h2><?php echo $e['CITY'] . ', ' . $e['AREA']; ?> район</h2>
  <div id="info">
    <div class="f">
    <?php
    
    echo $simplify->yamap->yamapWidget(array (
      'json_data_url' => '/latlongs/',
      'highlight' => $e['ID']
    ));
    
    ?>
    </div>
    <?php if ($e['ACTION'] == 2 && $e['LEASE'] == 2) { ?>
    <h2>График бронирования</h2>
    <div id="reserved">
      <?php echo $simplify->flatpickr->widgetFlatpickr(array ('name' => 'reserved', 'value' => $e['RESERVED'])); ?>
      <p><small>Здесь вы можете выбрать даты проживания.<br />Более подробную информацию о бронировании можно узнать позвонив по номеру телефона выше.</small></p>
    </div>
    <?php } ?>
    <div class="clear"></div>
  </div>
  <div id="about">
    <?php if ($e['SUBCATEGORY_NAME'] != '') echo '<div class="subcategory">' . $e['SUBCATEGORY_NAME'] . '</div>'; ?>
    <div class="square">Площадь: <?php echo $e['SQUERE'] . ($e['TYPE_NAME'] != 'участок' ? ' м<sup>2</sup>' : ' сот.'); ?></div>
    <?php echo $e['HOUSE_TYPE_NAME'] != '' ? '<div class="house_type">Тип дома: ' . $e['HOUSE_TYPE_NAME'] . '</div>' : ''; ?>
    <?php if (sizeof($e['PARAMS'])) { 
      foreach ($chb_params as $k => $v) {
        if ($v == 'separator' || $v == 'end') {
          continue;
        }
        
        if (!isset($e['PARAMS'][$k])) continue;
        
        echo '<span class="checkbox">' . $v . '</span>';
      }
    ?>
    <div class="clear"></div>
    <?php } ?>
  </div>
  
  <div id="description">
    <?php echo $e['DESCRIPTION']; ?>
  </div>
</div>