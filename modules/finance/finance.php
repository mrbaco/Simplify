<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('finance', array (
  'menu' => array (
    'main' => array (
      'title' => 'Личные финансы',
      'submenu' => array (
        'new' => array (
          'title' => 'Создать ордер',
          'icon' => '/modules/finance/images/plus.png'
        ),
        'statistics' => array (
          'title' => 'Просмотр статистики',
          'icon' => '/modules/finance/images/statistics.png'
        )
      )
    )
  ),
  'rights' => array ('admin'),
  'sort' => 300
));

class finance {
  public $types = array ('расходный', 'приходный');
  public $categories = array (
    array ('еда', 'одежда', 'платежи и налоги', 'хоз. товары', 'благотворительность', 'подарки', 'развлечения', 'учеба', 'транспорт', 'другое'),
    array ('работа', 'бизнес', 'хобби', 'подработка', 'другое')
  );
  
  public function cp($g, $p) {
    global $simplify;
    
    // Обработка входящих запросов
    $request = array ();
    $errors = array ();
    
    $fields = array ('date' => true, 'amount' => true, 'type' => true, 'description' => false);
    
    $success_text = 'Ордер создан.';
    $errors_text = array (
      'empty' => 'Это поле необходимо заполнить'
    );
    
    if (isset($_POST['date'])) {
      foreach ($fields as $field => $r) if ($r && $_POST[$field] === '') $errors[$field] = $errors_text['empty'];
      
      if (!sizeof($errors)) {
        foreach ($fields as $field => $r) $request[strtoupper($field)] = $simplify->htmlForms->array_htmlspecialchars($_POST[$field]);
        
        $request['DATE'] = strtotime($request['DATE']);
        
        $request['CATEGORY'] = (int)$_POST['category_' . $request['TYPE']];
        $request['TYPE'] = (int)$request['TYPE'];
        
        $request['AMOUNT'] = str_replace(',', '.', $request['AMOUNT']);
        
        $simplify->db->insert($simplify->params['database']['prefix'] . 'finance', $request);
        $simplify->htmlForms->success($success_text);
      }
    }
    
    // Удаление ордера
    if (isset($_GET['remove'])) {
      $simplify->db->delete($simplify->params['database']['prefix'] . 'finance', array ('ID' => (int)$_GET['remove']));
      $simplify->cache->clear(false, 'finance');
    }
    
    // Выгрузка из базы
    $simplify->myResult['sql'] = false;
    
    if (isset($_GET['range'])) $_POST['range'] = $_GET['range'];
    if (isset($_POST['range'])) {
      $range = explode('-', $_POST['range']);
      
      $start = strtotime($range[0]);
      $end = strtotime($range[1]);
      
      $simplify->myResult['sql'] = $simplify->db->query('SELECT *
                                                         FROM `' . $simplify->params['database']['prefix'] . 'finance`
                                                         WHERE `DATE` > ' . $start . ' AND `DATE` < ' . $end . ';');
    }
    
    if (isset($_POST)) $simplify->htmlForms->preset($simplify->htmlForms->array_htmlspecialchars($_POST));
    $simplify->htmlForms->errors($errors);
    
    // Вывод страницы
    $content = 'Параметры запроса заданы неправильно.';
    
    if ($g == 'main') {
      if ($p == 'statistics') {
        $content = $simplify->tpl->load('statistics.page', 'finance');
        $simplify->title = 'Финансовая статистика';
      } else {
        $content = $simplify->tpl->load('order.page', 'finance');
        $simplify->title = 'Создание ордера';
      }
    }
    
    return $content;
  }
}

?>