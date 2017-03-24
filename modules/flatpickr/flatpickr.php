<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('flatpickr', array (
  'name' => 'Простой и легкий календарь',
  'widgets' => array ('flatpickr' => 'widgetFlatpickr')
));

class flatpickr {
  public function widgetFlatpickr($arr = array ()) {
    global $simplify;
    
    $defaults = array (
      'name' => 'flatpickr',
      'value' => '',
      'disabled' => array (),
      'mode' => 'multiple',
      'inline' => true,
      'dateFormat' => 'd.m.Y'
    );
    
    $simplify->myResult['flatpickr'] = array_merge($defaults, $arr);
    
    if (!is_array($simplify->myResult['flatpickr']['disabled'])) $disabled = explode(';', $simplify->myResult['flatpickr']['disabled']);
    
    $simplify->myResult['flatpickr']['disabled'] = $disabled;
    
    $simplify->tpl->addScript('flatpickr.min.js', 'flatpickr');
    $simplify->tpl->addStyle('style.css', 'flatpickr');
    
    return $simplify->tpl->load('flatpickr.widget', 'flatpickr');
  }
}

?>