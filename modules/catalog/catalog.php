<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('catalog', array (
  'name' => 'Управление каталогом',
  'menu' => array (
    'main' => array (
      'title' => 'Управление каталогом',
      'submenu' => array (
        'categories' => array (
          'icon' => '/modules/catalog/images/categories.png',
          'title' => 'Категории'
        ),
        'pages' => array (
          'icon' => '/modules/catalog/images/pages.png',
          'title' => 'Записи'
        ),
        'blocks' => array (
          'icon' => '/modules/catalog/images/blocks.png',
          'title' => 'Блоки'
        )
      )
    )
  ),
  'widgets' => array (
    'categorySelect' => 'categorySelectWidget',
    'pageSelect' => 'pageSelectWidget',
    'block' => 'blockWidget'
  ),
  'sort' => 200
));

class catalog {
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
      'pages' => array ('title' => true, 'keywords' => false, 'description' => false, 'category' => false, 'content' => false),
      'categories' => array ('title' => true, 'content' => false, 'parent' => false),
      'blocks' => array ('title' => true, 'content' => false)
    );
    
    $success_text = array (
      'added' => array (
        'categories' => 'Категория создана.',
        'pages' => 'Страница создана.',
        'blocks' => 'Блок создан.'
      ),
      'updated' => array (
        'categories' => 'Категория обновлена.',
        'pages' => 'Страница обновлена.',
        'blocks' => 'Блок обновлен.'
      )
    );
    
    $errors_text = array (
      'empty' => 'Это поле необходимо заполнить'
    );
    
    if (!in_array($p, array ('categories', 'pages', 'blocks'))) $p = 'categories';
    
    if ($p == 'blocks') {
      $request['BLOCK'] = true;
      $db = 'pages';
    } else $db = $p;
    
    // Добавление новой (изменение существующей) категории (страницы или блока)
    if (isset($_POST['title'])) {
      foreach ($fields[$p] as $field => $r) if ($r && empty($_POST[$field])) $errors[$field] = $errors_text['empty'];
      
      if (!sizeof($errors)) {
        foreach ($fields[$p] as $field => $r) $request[strtoupper($field)] = $simplify->htmlForms->array_htmlspecialchars($_POST[$field]);
        
        $request['CONTENT'] = str_replace('"', '\"', $_POST['content']);
        $request['LINK'] = $this->createURL($request['TITLE']);
        $request['DATE'] = time();
        
        if (isset($_GET['edit'])) {
          $simplify->db->update($simplify->params['database']['prefix'] . $db, $request, array ('ID' => (int)$_GET['edit']));
          $simplify->htmlForms->success($success_text['updated'][$p]);
        } else {
          $simplify->db->insert($simplify->params['database']['prefix'] . $db, $request);
          $simplify->htmlForms->success($success_text['added'][$p]);
        }
        
        $simplify->cache->clear(false, $p);
        $simplify->cache->clear(false, 'structure');
        $simplify->cache->clear(false, 'categories');
      }
    }
    
    // Удаление категории (страницы или блока)
    if (isset($_GET['remove'])) {
      if ($p == 'categories') $this->removeCategory((int)$_GET['remove']);
      
      $simplify->db->delete($simplify->params['database']['prefix'] . $db, array ('ID' => (int)$_GET['remove']));
      
      $simplify->cache->clear(false, $p);
      $simplify->cache->clear(false, 'structure');
      $simplify->cache->clear(false, 'categories');
    }
    
    if (isset($_POST)) $simplify->htmlForms->preset($simplify->htmlForms->array_htmlspecialchars($_POST));
    
    $simplify->htmlForms->errors($errors);
    
    // Генерация страницы
    if ($g == 'main') {
      $simplify->title = (!isset($_GET['edit']) ? 'Создание' : 'Редактирование') . ' ';
      
      if ($p == 'blocks') {
        $content = $simplify->tpl->load('blocks.page', 'catalog');
        $simplify->title .= 'блока';
      } elseif ($p == 'pages') {
        $content = $simplify->tpl->load('pages.page', 'catalog');
        $simplify->title .= 'страницы';
      } else {
        $content = $simplify->tpl->load('categories.page', 'catalog');
        $simplify->title .= 'категории';
      }
    }
    
    return $content;
  }
  
  /*
   * Рекурсивное удаление категории с подкатегориями
   *
   */
  private function removeCategory($id) {
    global $simplify;
    
    if (!$id) return false;
    
    $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'categories', array ('ID'), array ('PARENT' => $id));
    
    if(!$sql || $sql->num_rows == 0) return false;
    
    while ($row = $sql->fetch_assoc()) {
      $this->removeCategory($row['ID']);
      $simplify->db->delete($simplify->params['database']['prefix'] . 'categories', array ('ID' => $row['ID']));
    }
  }
  
  /*
   * Проверка существования категории, страницы или блока
   *
   */
  public function isCategory($id, $link) {
    $this->isAnything($id, $link, 'categories');
  }
  
  public function isPage($id, $link) {
    $this->isAnything($id, $link, 'pages');
  }
  
  public function isBlock($id, $link) {
    $this->isAnything($id, $link, 'blocks');
  }
  
  public function isAnything($id, $link, $what) {
    global $simplify;
    
    if ($id == '' && $link == '') return true;
    
    $sql = $simplify->db->select($simplify->params['database']['prefix'] . $what, array ('ID'), array ('ID' => $id, 'LINK' => $link));
    if(!$sql) return false;
    
    return $sql->num_rows == 1 ? true : false;
  }
  
  /*
   * Получение числа подкатегорий выбранной категории (исключая вложенные)
   *
   */
  public function isParents($id) {
    global $simplify;
    
    if ($id == 0) return false;
    
    $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'categories', array ('ID'), array ('PARENT' => $id));
    if(!$sql) return false;
    
    return $sql->num_rows != 0 ? $sql->num_rows : false;
  }
  
  /*
   * Создание url страницы путем транслитерации строки
   *
   */
  public function createURL($string) {
    $string = htmlspecialchars_decode($string);
    $string = $this->strtolower_ru($string);
    
    $table = array ( 
      'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 
      'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k',
      'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 
      'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 
      'ч' => 'ch', 'ш' => 'sh', 'щ' => 'csh', 'ь' => '', 'ы' => 'y', 'ъ' => '', 
      'э' => 'e', 'ю' => 'yu', 'я' => 'ya', ' ' => '_'
    ); 
 
    $string = str_replace(array_keys($table), array_values($table), $string);
    $string = preg_replace('/[^a-z0-9_]/i', '', $string);
    
    return strtolower($string);
  }
  
  /*
   * Сделать первую букву заглавной ucfirst utf8
   *
   */
  function ucfirst_utf8($stri){ 
    $line = iconv("UTF-8", "Windows-1251", $stri);
    $line = ucfirst($line);
    $line = iconv("Windows-1251", "UTF-8", $line);
    return $line;
  }
  
  /*
   * StrToLower для русских букв
   *
   */
  public function strtolower_ru($string) {
    return str_replace(preg_split('~~u', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯ', null, PREG_SPLIT_NO_EMPTY),
                       preg_split('~~u', 'абвгдеёжзийклмнопрстуфхцчшщьыъэюя', null, PREG_SPLIT_NO_EMPTY),
                       $string);
  }
  
  /*
   * Загрузка категории со всеми параметрами
   *
   */
  public function getCategory($id, $link) {
    global $simplify;
    
    $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'categories',
                                 array ('*'),
                                 array ('ID' => (int)$id, 'LINK' => htmlspecialchars($link)));
    
    if ($sql) {
      $row = $sql->fetch_assoc();
      
      $simplify->myResult['category'] = array (
        'id' => $row['ID'],
        'parent' => $row['PARENT'],
        'title' => $row['TITLE'],
        'link' => $row['LINK'],
        'date' => $row['DATE'],
        'content' => $row['CONTENT']
      );
      
      return $row;
    }
  }
  
  /*
   * Загрузка страницы со всеми параметрами
   *
   */
  public function getPage($id, $link) {
    global $simplify;
    
    $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'pages',
                                 array ('*'),
                                 array ('ID' => (int)$id, 'LINK' => htmlspecialchars($link), 'BLOCK' => '!= 1'));
    
    if ($sql) {
      $row = $sql->fetch_assoc();
      
      $simplify->tpl->addMetaTag('description', $row['DESCRIPTION']);
      $simplify->tpl->addMetaTag('keywords', $row['KEYWORDS']);
      $simplify->title = $row['TITLE'];
      
      $simplify->myResult['page'] = array (
        'id' => $row['ID'],
        'title' => $row['TITLE'],
        'link' => $row['LINK'],
        'date' => $row['DATE'],
        'content' => $row['CONTENT']
      );
      
      return $simplify->tpl->load('page');
    }
  }
  
  /*
   * Виджет загрузки блока со всеми параметрами
   *
   */
  public function blockWidget($id = '') {
    global $simplify;
    
    $param = is_numeric($id) ? $param = array ('ID' => (int)$id) : array ('TITLE' => 'LIKE %' . $id . '%');
    $param['BLOCK'] = true;
    
    $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'pages',
                                 array ('*'),
                                 $param);
    
    if ($sql) {
      $row = $sql->fetch_assoc();
      
      $simplify->myResult['page'] = array (
        'id' => $row['ID'],
        'title' => $row['TITLE'],
        'link' => $row['LINK'],
        'date' => $row['DATE'],
        'content' => $row['CONTENT']
      );
      
      return $simplify->tpl->load('block');
    }
  }
  
  /*
   * Кэширование списка категорий
   *
   */
  public function createCategoriesCache($full) {
    global $simplify;
    
    $tree = new cache(array (
      'id' => 'categories_widget' . ($full ? 'f' : ''),
      'group' => 'categories',
      'output' => false,
      'array' => true
    ));
    
    if (!$tree->result) {
      $arr = array ();
      
      $params = array ('ID', 'TITLE', 'PARENT');
      if ($full) {
        $params[] = 'LINK';
        $params[] = 'CONTENT';
      }
      
      $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'categories', $params, array ());
      
      if ($sql) while ($e = $sql->fetch_assoc()) $arr[$e['PARENT']][$e['ID']] = $full === false ? $e['TITLE'] : $e;
      $tree->create($arr);
    }
    
    return $tree->result;
  }
  
  /*
   * Кэширование списка страниц
   *
   */
  public function createPagesCache($block = false) {
    global $simplify;
    
    $tree = new cache(array (
      'id' => 'pages_widget',
      'group' => 'pages',
      'output' => false,
      'array' => true
    ));
    
    if (!$tree->result) {
      $param = array ('BLOCK' => ($block ? '1' : '!= 1'));
      $arr = array ();
      
      $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'pages', array ('ID', 'TITLE', 'CATEGORY'), $param);
      
      if ($sql) while ($e = $sql->fetch_assoc()) $arr[$e['ID']] = $e['TITLE'];
      $tree->create($arr);
    }
    
    return $tree->result;
  }
  
  /*
   * Создание представления всей структуры сайта
   *
   */
  public function createStructureCache() {
    global $simplify;
    
    $tree = new cache(array (
      'id' => 'structure',
      'group' => 'structure',
      'output' => false,
      'array' => true
    ));
    
    if (!$tree->result) {
      $sql1 = $simplify->db->select($simplify->params['database']['prefix'] . 'categories', array ('ID', 'PARENT', 'TITLE'), array ());
      $sql2 = $simplify->db->select($simplify->params['database']['prefix'] . 'pages', array ('ID', 'TITLE', 'CATEGORY'), array ());
      
      if (!$sql1 || !$sql2) return false;
      
      $pages = array ();
      $arr = array ();
      
      while ($e = $sql2->fetch_assoc()) {
        $pages[$e['CATEGORY']] = $e;
        unset($pages[$e['CATEGORY']]['CATEGORY']);
      }
      
      while ($e = $sql1->fetch_assoc()) {
        if (sizeof($pages[$e['ID']])) {
          $e['PAGES'][$pages[$e['ID']]['ID']] = $pages[$e['ID']];
          unset($e['PAGES'][$pages[$e['ID']]['ID']]['ID']);
        }
        $arr[$e['PARENT']][$e['ID']] = $e;
        
        unset($arr[$e['PARENT']][$e['ID']]['ID']);
        unset($arr[$e['PARENT']][$e['ID']]['PARENT']);
      }
      
      unset($pages);
      
      $tree->create($arr);
    }
    
    return $tree->result;
  }
  
  /*
   * Виджет создает select со страницами
   *
   */
  public function pageSelectWidget($title = '', $name = 'page') {
    global $simplify;
    
    $simplify->myResult['selectWidget']['array'] = $this->createPagesCache();
    
    return $this->selectWidget($title, $name);
  }
  
  /*
   * Возвращает массив с категориями
   *
   */
  public function categoryArray($parent, $full = false) {
    $tree = $this->createCategoriesCache($full);
    
    if (!is_array($tree)) return;
    
    $parent_name = $this->strtolower_ru($parent);
    
    if (!is_integer($parent)) {
      $parent = 0;
      
      foreach ($tree as $p => $arr) foreach ($arr as $id => $e) {
        $e = $full === false ? $e : $e['TITLE'];
        if ($this->strtolower_ru($e) == $parent_name ) {
          $parent = $id;
          break 2;
        }
      }
    }
    
    if ($parent != 0) $tree = $tree[$parent];
    
    return array (
      'tree' => $tree,
      'parent' => $parent
    );
  }
  
  /*
   * Виджет создает select с категориям
   *
   */
  public function categorySelectWidget($title = '', $name = 'category', $parent = 0) {
    global $simplify;
    
    $arr = $this->categoryArray($parent);
    
    $tree = $arr['tree'];
    $parent = $arr['parent'];
    
    $simplify->myResult['selectWidget']['array'] = array ();
    
    if (is_array($tree) && $parent == 0) foreach ($tree as $parent => $e) foreach ($e as $id => $t) $simplify->myResult['selectWidget']['array'][$id] = $t;
    elseif (is_array($tree[$parent])) foreach ($tree[$parent] as $id => $t) $simplify->myResult['selectWidget']['array'][$id] = $t;
    
    return $this->selectWidget($title, $name);
  }
  
  /*
   * Создание select виджета
   *
   */
  private function selectWidget($title, $name) {
    global $simplify;
    
    $simplify->myResult['selectWidget']['array'] = is_array($simplify->myResult['selectWidget']['array']) ? array (0 => 'Не выбрано') + $simplify->myResult['selectWidget']['array'] : $simplify->myResult['selectWidget']['array'];
    $simplify->myResult['selectWidget']['array'] = $simplify->myResult['selectWidget']['array'];
    
    $simplify->myResult['selectWidget']['title'] = $title;
    $simplify->myResult['selectWidget']['name'] = $name;
    
    return $simplify->tpl->load('select.widget', 'catalog');
  }
  
  /*
   * Создание дерева категорий
   *
   */
  public function treePrint($tree, $parent = 0, $p = 'categories') {
    global $simplify;
    
    if (!is_numeric($parent)) $simplify->myResult['tree']['elements'][$parent] = $tree;
    else {
      if (empty($tree[$parent])) return;
      $simplify->myResult['tree']['elements'] = $tree;
    }
    
    $simplify->myResult['tree']['parent'] = $parent;
    $simplify->myResult['tree']['p'] = $p;
    
    echo $simplify->tpl->load('tree.element', 'catalog');
  }
  
  /*
   * Удаление элемента из дерева с потомками
   *
   */
  public function removeTreeElement($tree, $id) {
    if (sizeof($tree)) foreach ($tree as $parent => $e) {
      if ($e['PARENT'] == $id) {
        unset($tree[$parent]);
        $tree = $this->removeTreeElement($tree, $parent);
      }
    }
    
    return $tree;
  }
}

?>