<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('realty', array (
  'name' => 'Каталог объектов недвижимости',
  'routing' => array (
    '' => 'main',
    '/([0-9]+)-(.*)?\.html/i' => 'item',
    '/([0-9]+)-(.*)(\/([0-9]+)-(.*))?/i' => 'category',
    '/latlongs/i' => 'latlong'
  ),
  'menu' => array (
    'main' => array (
      'title' => 'Недвижимость',
      'submenu' => array (
        'objects' => array (
          'icon' => '/modules/realty/images/add.png',
          'title' => 'Добавить объект'
        ),
        'view' => array (
          'icon' => '/modules/realty/images/apartments.png',
          'title' => 'Все объекты'
        ),
        'agents' => array (
          'icon' => '/modules/realty/images/agent.png',
          'title' => 'Агенты'
        )
      )
    )
  ),
  'widgets' => array (
    'realty' => 'widgetCatalog'
  ),
  'sort' => 100
));

class realty {
  /*
   * Создание страницы в панели управления
   *
   */
  public function cp($g, $p) {
    global $simplify;
    
    $content = 'Параметры запроса заданы неправильно.';
    
    // Обработка входящих запросов
    $request = array ();
    $errors = array ();
    
    $fields = array (
      'agents' => array ('name' => true, 'phone' => true, 'images' => array (), 'info' => false),
      'objects' => array (
        'action' => true, 'type' => true, 'category' => false, 'subcategory' => false,
        'lease' => true, 'reserved' => false, 'city' => true, 'area' => true, 'address' => true,
        'latlong' => false, 'rooms' => false, 'house_type' => false, 'floor' => false, 'house_floors' => false,
        'squere' => true, 'description' => true, 'agent' => true, 'price' => true, 'comission' => true
      )
    );
    
    $success_text = array (
      'added' => array (
        'agents' => 'Агент добавлен.',
        'objects' => 'Объявление создано.',
      ),
      'updated' => array (
        'agents' => 'Информация об агенте обновлена.',
        'objects' => 'Объявление обновлено.',
      )
    );
    
    $errors_text = array (
      'empty' => 'Это поле необходимо заполнить',
      'squere' => 'Площадь должна быть больше 0',
      'floors' => 'Неточность в количестве этажей'
    );
    
    if (!in_array($p, array ('agents', 'objects', 'view'))) $p = 'view';
    
    // Добавление нового (изменение существующего) объекта (агента)
    if (isset($_POST['action']) || isset($_POST['name'])) {
      foreach ($fields[$p] as $field => $r) if ($r && empty($_POST[$field])) $errors[$field] = $errors_text['empty'];
      
      if (!sizeof($errors)) {
        foreach ($fields[$p] as $field => $r) $request[strtoupper($field)] = $simplify->htmlForms->array_htmlspecialchars($_POST[$field]);
        
        if ($p == 'objects') {
          if ($request['SQUERE'] < 0) $errors['squere'] = $errors_text['squere'];
          if ($request['FLOOR'] > $request['HOUSE_FLOORS']) $errors['HOUSE_FLOORS'] = $errors_text['floors'];
          
          $request['PRICE'] = preg_replace('/\s/', '', $request['PRICE']);
          $request['COMISSION'] = preg_replace('/\s/', '', $request['COMISSION']);
          
          $request['PARAMS'] = $simplify->db->real_escape_string(serialize($_POST['chb']));
          $request['DATE'] = time();
        }
        
        if (!sizeof($errors)) {
          if (isset($_POST['images']) && is_array($_POST['images'])) {
            foreach ($_POST['images'] as $k => $v) {
              $_POST['images'][$k] = $simplify->storage->getPath($v);
              $_POST['images'][$k] = '/' . $_POST['images'][$k][1];
              
              if (isset($_POST['selected']) && $v == $_POST['selected']) {
                unset($_POST['images'][$k]);
                array_unshift($_POST['images'], $v);
              }
            }
            
            $request['IMAGES'] = $simplify->db->real_escape_string(serialize($_POST['images']));
          }
          
          if (isset($_GET['edit'])) {
            $simplify->db->update($simplify->params['database']['prefix'] . $p, $request, array ('ID' => (int)$_GET['edit']));
            $simplify->htmlForms->success($success_text['updated'][$p]);
          } else {
            $simplify->db->insert($simplify->params['database']['prefix'] . $p, $request);
            $simplify->htmlForms->success($success_text['added'][$p]);
          }
          
          $simplify->cache->clear(false, $p);
        }
      }
    }
    
    // Удаление объекта или агента
    if (isset($_GET['remove'])) {
      $simplify->db->delete($simplify->params['database']['prefix'] . $p, array ('ID' => (int)$_GET['remove']));
      $simplify->cache->clear(false, $p);
    }
    
    if (isset($_POST)) $simplify->htmlForms->preset($simplify->htmlForms->array_htmlspecialchars($_POST));
    $simplify->htmlForms->errors($errors);
    
    $simplify->myResult['cp'] = true;
    
    $simplify->tpl->addStyle('elements.css', 'realty');
    
    if ($g == 'main') {
      if ($p == 'objects') {
        $content = $simplify->tpl->load('objects.page', 'realty');
        $simplify->tpl->addScript('https://api-maps.yandex.ru/2.1/?lang=ru_RU', false);
        $simplify->title = isset($_GET['edit']) ? 'Изменить объект' : 'Добавить объект';
      } elseif ($p == 'agents') {
        $content  = $simplify->tpl->load('agents.page', 'realty');
        $simplify->tpl->addStyle('style.css', 'paging');
        $simplify->title = 'Агенты';
      } else {
        $content = $simplify->tpl->load('view.page', 'realty');
        $simplify->title = 'Все объекты';
      }
      
      return $content;
    }
  }
  
  /*
   * Вывод координат расположения всех объектов на карте в формате json
   *
   */
  public function ajaxLatlong() {
    global $simplify;
    
    $simplify->params['debug'] = false;
    $simplify->index = '';
    
    header('Content-Type: application/json');
    
    $cache = new cache(array (
      'id' => 'json',
      'group' => 'objects'
    ));
    
    if (!$cache->result) {
      $sql = $simplify->db->query('SELECT `t1`.`ID`, `t1`.`ACTION`, `t1`.`TYPE`, `t1`.`ADDRESS`,
                                   `t1`.`LATLONG`, `t1`.`IMAGES`, `t1`.`ROOMS`, `t1`.`FLOOR`, `t1`.`HOUSE_FLOORS`,
                                   `t1`.`PRICE`, `t1`.`COMISSION`, `t1`.`SQUERE`,
                                   (
                                     SELECT `t2`.`TITLE`
                                     FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                     WHERE `t2`.`ID` = `t1`.`SUBCATEGORY`
                                   ) AS `SUBCATEGORY`,
                                   (
                                     SELECT `t2`.`TITLE`
                                     FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                     WHERE `t2`.`ID` = `t1`.`TYPE`
                                   ) AS `TYPE_NAME`
                                   FROM `' . $simplify->params['database']['prefix'] . 'objects` AS `t1`');
      
      $arr = array (
        'type' => 'FeatureCollection',
        'features' => array ()
      );
      
      if ($sql) {
        while ($e = $sql->fetch_assoc()) {
          if (!$e['LATLONG']) continue;
          
          $e['IMAGES'] = unserialize($e['IMAGES']);
          $e['IMAGES'] = $e['IMAGES'][0];
          
          $e['NAME']  = $e['TYPE_NAME'] == 'квартира' ? $e['ROOMS'] . ' к. ' : '';
          $e['NAME'] .= $e['TYPE_NAME'];
          $e['NAME'] .= $e['TYPE_NAME'] != 'участок' ? ', ' . $e['FLOOR'] . '/' . $e['HOUSE_FLOORS'] . ' эт.' : ', ' . $e['SQUERE'] . ' сот.';
          
          $e['NAME'] = $simplify->catalog->ucfirst_utf8($e['NAME']);
          
          $e['LATLONG'] = explode(',', $e['LATLONG']);
          
          $content  = '<h4>' . $e['NAME'] . '<br />' . $e['ADDRESS'] . '</h4>';
          $content .= '<div class="baloon_photo" style="background-image: url(\'' . (is_file(ROOT . $e['IMAGES']) ? $e['IMAGES'] : '/modules/realty/images/nophoto.png') . '\');">';
          $content .= '<div>' . ($e['ACTION'] == 1 ? number_format($e['PRICE'] + $e['COMISSION'], 0, ',', ' ') : number_format($e['PRICE'], 0, ',', ' ')) . ' <span class="rouble">o</span>' . ($e['ACTION'] == 2 ? '/мес.' : '') . '</div>';
          $content .= '</div>';
          $content .= '<p><a href="' . ($e['ID'] . '-' . $simplify->catalog->createURL($e['NAME']) . '.html') . '">Подробнее</a></p>';
          
          $arr['features'][] = array (
            'type' => 'Feature',
            'id' => $e['ID'],
            'geometry' => array (
              'type' => 'Point',
              'coordinates' => $e['LATLONG']
            ),
            'properties' => array (
              'balloonContent' => $content,
              'hintContent' => $e['ACTION'] == 2 ? 'Аренда' : 'Продажа'
            ),
            'options' => array ('preset' => $e['ACTION'] == 2 ? 'islands#greenDotIcon' : 'islands#darkBlueDotIcon')
          );
        }
      }
      
      echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
      
      $cache->create();
    }
  }
  
  /*
   * Определение запрошенного маршрута
   *
   */
  public function routing($route) {
    global $simplify;
    
    // Определение маршрута
    $request = explode('/', trim($_GET['do'], '/'));
    $arr[0] = explode('-', $request[0]);
    $arr[1] = explode('-', end($request));
    
    $id1 = (int)$arr[0][0];
    $id2 = (int)$arr[1][0];
    
    $link1 = htmlspecialchars($arr[0][1]);
    $link2 = htmlspecialchars($arr[1][1]);
    
    $simplify->tpl->addStyle('elements.css', 'realty');
    $simplify->tpl->addScript('https://api-maps.yandex.ru/2.1/?lang=ru_RU', false);
    
    $content = false;
    
    // Обработка данных по маршруту
    if ($route == 'latlong') return $this->ajaxLatlong();
    else {
      // Обработка и кэширование каталога
      $content_cache = new cache(array (
        'id' => 'objects' . $id2 . $link2,
        'group' => 'realty',
        'output' => false
      ));
      
      $title_cache = new cache(array (
        'id' => 'objects' . $id2 . $link2 . 'title',
        'group' => 'realty',
        'output' => false
      ));
      
      if (!$content_cache->result) {
        if ($route == 'item') {
          // Загрузка объекта недвижимости
          $sql = $simplify->db->query('SELECT `t1`.*,
                                      (
                                        SELECT `t2`.`TITLE`
                                        FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                        WHERE `t2`.`ID` = `t1`.`CATEGORY`
                                      ) AS `CATEGORY_NAME`,
                                      (
                                        SELECT `t2`.`TITLE`
                                        FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                        WHERE `t2`.`ID` = `t1`.`SUBCATEGORY`
                                      ) AS `SUBCATEGORY_NAME`,
                                      (
                                        SELECT `t3`.`NAME`
                                        FROM `' . $simplify->params['database']['prefix'] . 'agents` AS `t3`
                                        WHERE `t3`.`ID` = `t1`.`AGENT`
                                      ) AS `AGENT`,
                                      (
                                        SELECT `t3`.`PHONE`
                                        FROM `' . $simplify->params['database']['prefix'] . 'agents` AS `t3`
                                        WHERE `t3`.`ID` = `t1`.`AGENT`
                                      ) AS `AGENT_PHONE`,
                                      (
                                        SELECT `t3`.`IMAGES`
                                        FROM `' . $simplify->params['database']['prefix'] . 'agents` AS `t3`
                                        WHERE `t3`.`ID` = `t1`.`AGENT`
                                      ) AS `AGENT_PHOTO`,
                                      (
                                        SELECT `t2`.`TITLE`
                                        FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                        WHERE `t2`.`ID` = `t1`.`AREA`
                                      ) AS `AREA`,
                                      (
                                        SELECT `t2`.`TITLE`
                                        FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                        WHERE `t2`.`ID` = `t1`.`TYPE`
                                      ) AS `TYPE_NAME`,
                                      (
                                        SELECT `t2`.`TITLE`
                                        FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                                        WHERE `t2`.`ID` = `t1`.`HOUSE_TYPE`
                                      ) AS `HOUSE_TYPE_NAME`
                                       FROM `' . $simplify->params['database']['prefix'] . 'objects` AS `t1`
                                      WHERE `ID` = "' . $id2 . '";');
          if ($sql) {
            $simplify->myResult['e'] = $sql->fetch_assoc();
            $content = $simplify->tpl->load('item');
          }
        } else {
          $category = $simplify->catalog->getCategory($id2, $link2);
          $simplify->title = $category['TITLE'];
          
          if (empty($id2) || $category) {
            $simplify->myResult['sql'] = 2;
            $simplify->myResult['realty']['cache_prefix'] = $link2;
            
            // Делаем вывод подкатегорий загруженной категории
            if ($id1 == $id2 && $simplify->catalog->isParents($id2)) {
              $simplify->myResult['realty']['sql'] = 1;
              $simplify->myResult['realty']['sql_params'] = array ('PARENT' => $id2);
            } else {
              // Делаем вывод элементов категории
              if (!empty($id1)) $simplify->myResult['realty']['sql_params'] = array ('CATEGORY' => $id1);
              if ($id1 != $id2) $simplify->myResult['realty']['sql_params']['SUBCATEGORY'] = $id2;
            }
            
            $content = $simplify->tpl->load('page');
            
            // Делаем вывод статичной страницы
            if ($category['TITLE'] == 'Контакты') $content .= $this->getAdditionalToPage($category['TITLE']);
            else $content .= $simplify->tpl->load('view.page', 'realty');
          }
        }
        
        if ($content === false) $content = $simplify->tpl->load('404');
        
        $title = $simplify->title;
        $title_cache->change(array ('id' => 'objects' . $id2 . $link2 . $simplify->paging->page . 'title'));
        $title_cache->create($title);
        
        $content_cache->change(array ('id' => 'objects' . $id2 . $link2 . $simplify->paging->page));
        $content_cache->create($content);
      }
    }
    
    $simplify->title = $title_cache->result;
    
    return $content_cache->result;
  }
  
  /*
   * Определяет, существует ли объект в базе данных
   *
   */
  public function isObject($id, $link) {
    global $simplify;
    return $simplify->catalog->isAnything($id, $link, 'objects');
  }
  
  /*
   * Получение данных, прекрепленных к определенной странице
   *
   */
  public function getAdditionalToPage($title) {
    global $simplify;
    
    $additional = '';
    
    if ($title == 'Контакты') {
      $additional = $simplify->tpl->load('all_agents.page', 'realty');
    }
    
    return $additional;
  }
}

?>