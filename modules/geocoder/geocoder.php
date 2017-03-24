<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('geocoder', array (
  'name' => 'Yandex Geocoder',
  'routing' => array (
    '/geocoder/i' => 'coder'
  )
));

class geocoder {
  public function routing($route) {
    global $simplify;
    
    if ($route != 'coder') return false;
    
    $simplify->params['debug'] = false;
    
    $url  = '?geocode=' . urlencode(htmlspecialchars($_GET['address']));
    $hash = preg_replace('/(\.|\,| |улица|ул)/i', '', strtolower(urldecode($url)));
    
    $url  = 'https://geocode-maps.yandex.ru/1.x/' . $url . '&results=1&format=json&sco=longlat&kind=house';
    
    $point = new cache(array (
      'id' => $hash,
      'group' => 'geocoder',
      'output' => false,
      'eternal' => true
    ));
    
    if (!$point->result) {
      $json = json_decode(file_get_contents($url));
      $point->create($json->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
    }
    
    $simplify->index = $point->result;
  }
}