<?php

/*
 * Simplify 4.5 Evo
 *
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

class simplify {
  // Параметры системы
  public $params = array ();
  
  // Экземпляры системных классов
  public $cache, $db, $logging, $mdl, $tpl;
  
  // Текущая страница
  public $index = true;
  
  // Параметры текущей страницы
  public $title;
  public $content;
  
  // Массив данных для отображения данных из модулей
  public $myResult;
  
  // (скрыто) Массив для хранения экземпляров классов модулей
  private $modules_obj;
  
  /*
   *
   * Создание экземпляров системных классов
   *
   * params - параметры системы (массив данных)
   *
   */
  public function __construct($params) {
    $this->db = new database($params['database']);
    $this->cache = new cacheControl;
    $this->logging = new logging;
    $this->tpl = new templates;
    $this->mdl = new modules;
    
    if (is_object($this->db) && !$this->db->connect_errno) $this->db->set_charset('utf8');
    
    $this->params = $params;
  }
  
  /*
   * Инициализация загруженного модуля
   *
   * Каждый модуль, который необходимо использовать,
   * должен быть инициализирован с помощью этого метода
   *
   * moduleClassName - наименование модуля (класса модуля)
   * source - параметры (массив данных). Описание каждого параметра в тестовом модуле
   *
   */
  public function init($moduleClassName, $source = array ()) {
    $this->logging->delimiter();
    $this->logging->add('simplify', 'init module', $moduleClassName . ' init');
    
    $this->mdl->modules[$moduleClassName] = array (
      'source' => array (),
      'params' => array ()
    );
    
    $this->mdl->modules[$moduleClassName]['source'] = $source;
    
    if (!file_exists(ROOT . '/core/params/modules/' . $moduleClassName . '.php')) {
      $this->mdl->setParams($moduleClassName, array ('state' => true));
    }
    
    include ROOT . '/core/params/modules/' . $moduleClassName . '.php';
    $this->mdl->modules[$moduleClassName]['params'] = $params;
    
    $this->logging->add('simplify', 'init module', $moduleClassName . '\'s params loaded');
    
    if ($params['state'] === true) {
      if (!sizeof($this->mdl->moduleMethod) && sizeof($source['routing'])) {
        foreach ($source['routing'] as $pattern => $route) {
          if (empty($_GET['do']) XOR empty($pattern)) continue;
          elseif (!empty($pattern) && !preg_match($pattern, $_GET['do'])) continue;
          
          $this->mdl->moduleMethod = array (
            'className' => $moduleClassName,
            'route' => $route
          );
          
          $this->logging->add('simplify', 'init module', $moduleClassName . ' is default routing');
          
          break;
        }
      }
      
      $this->$moduleClassName = new $moduleClassName;
    }
    
    $this->logging->delimiter();
  }
  
  /*
   * Освобождение памяти при завершении работы скрипта
   *
   */
  function __destruct() {
    if (is_object($this->db) && !$this->db->connect_errno) $this->db->close();
  }
  
  /*
   * Управление экземплярами классов подключенных модулей
   * Магические методы
   *
   */
  public function __set($name, $value) {
    $this->modules_obj[$name] = $value;
  }
  
  public function __get($name) {
    if (array_key_exists($name, $this->modules_obj)) return $this->modules_obj[$name];
  }
  
  public function __isset($name) {
    return isset($this->modules_obj[$name]);
  }
}

?>