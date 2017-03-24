<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Управление модулями системы, формирование страниц
 *
 */
class modules {
  // Имя класса и параметр функции, отвечающие за формирование запрошенной страницы (согласно маршруту)
  public $moduleMethod = array ();
  
  // Список модулей (массив данных)
  public $modules = array ();
  
  /*
   * Формирование списка модулей и его кэширование
   *
   */
  public function examine() {
    $params = new paramsCreation('/core/cache/modules.php');
    
    $dir = opendir(ROOT . '/modules/');
    while (false !== ($mdl = readdir($dir))) {
      if ($mdl == "." || $mdl == "..") continue;
      if (!is_dir(ROOT . '/modules/' . $mdl)) continue;
      
      $params->addInclude('/modules/' . $mdl . '/' . $mdl . '.php');
    }
    closedir($dir);
    
    global $simplify;
    $simplify->logging->add('mdl', 'examine', 'cache updated');
    
    return $params->write();
  }
  
  /*
   * Проверка существования модуля
   *
   * moduleClassName - наименование модуля (класса модуля)
   *
   */
  public function isModule($moduleClassName) {
    return isset($this->modules[$moduleClassName]) && sizeof($this->modules[$moduleClassName]['params']);
  }
  
  /*
   * Сохранение параметров модуля
   *
   * moduleClassName - наименование модуля (класса модуля)
   * modulesParams - параметры модуля (массив данных) (ключ:значение, ключ:[ключ:значение,ключ:значение])
   *
   */
  public function setParams($moduleClassName, $moduleParams) {
    if ($moduleClassName != 'system') {
      $moduleParams = array_merge((array)$this->modules[$moduleClassName]['params'], (array)$moduleParams);
      $this->modules[$moduleClassName]['params'] = $moduleParams;
    }
    
    $params = new paramsCreation('/core/params' . ($moduleClassName != 'system' ? '/modules' : '') . '/' . $moduleClassName . '.php');
    $params->addParams($moduleParams);
    
    global $simplify;
    $simplify->logging->add('mdl', 'save params', $moduleClassName . '\'s params saved');
    
    return $params->write();
  }
  
  /*
   * Получение значения параметра модуля
   *
   * moduleClassName - наименование модуля (класса модуля)
   * moduleParam - запрашиваемый параметр
   * 
   */
  public function getParam($moduleClassName, $moduleParam) {
    return $this->modules[$moduleClassName]['params'][$moduleParam];
  }
  
  /*
   * Проверка, отвечает ли модуль за формирование текущей страницы
   * 
   * moduleClassName - наименование модуля (класса модуля)
   *
   */
  public function isDefaultRouting($moduleClassName) {
    global $simplify;
    
    if (is_object($simplify->mdl->moduleMethod['className'])) return get_class($simplify->mdl->moduleMethod['className']) == $moduleClassName ? true : false;
    else return $simplify->mdl->moduleMethod['className'] == $moduleClassName ? true : false;
  }
}

?>