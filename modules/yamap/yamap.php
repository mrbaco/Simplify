<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('yamap', array (
  'name' => 'Карта и объекты',
  'widgets' => array ('yamap' => 'yamapWidget')
));

class yamap {
  public function yamapWidget($arr = array ()) {
    global $simplify;
    
    foreach ($arr as $key => $value) $simplify->myResult['yamap'][$key] = $value;
    
    $simplify->tpl->addScript('https://api-maps.yandex.ru/2.1/?lang=ru_RU', false);
    $simplify->tpl->addStyle('style.css', 'yamap');
    
    return $simplify->tpl->load('yamap.widget', 'yamap');
  }
}

?>