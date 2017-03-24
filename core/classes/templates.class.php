<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Осуществление работы с шаблонами
 *
 */
class templates {
  // Параметры текущей страницы
  public $metaTags = array ();
  public $scripts = array ();
  public $styles = array ();
  
  // (скрыто) Используемый шаблон
  private $template;
  
  /*
   * Задание используемого шаблон
   *
   * template - название шаблона в папке templates
   * 
   */
  public function setTemplate($template) {
    if (!$this->isTemplate($template)) return false;
    $this->template = $template;
  }
  
  /*
   * Загрузка файла шаблона из папки с шаблонами либо из папки соответствующего модуля
   *
   * name - имя файла без расширения
   * moduleClassName - наименование модуля (класса модуля)
   *
   */
  public function load($name, $moduleClassName = '') {
    global $simplify;
    
    if (!$this->template) $this->template = !$this->isTemplate($simplify->params['template']) ? 'blank' : $simplify->params['template'];
    
    if ($moduleClassName == '') {
      $filepath = ROOT . '/templates/' . $this->template . '/' . $name . '.php';
    } else {
      $filepath = ROOT . '/modules/' . $moduleClassName . '/' . $name . '.php';
    }
    
    if (!file_exists($filepath)) return false;
    
    global $simplify;
    
    $simplify->logging->add('tpl', 'load template file', $name . ' in proccess');
    
    unset ($name, $moduleClassName);
    
    ob_start();
    
    include $filepath;
    
    $content = ob_get_clean();
    
    return $content;
  }
  
  /*
   * Проверка существования шаблона
   *
   * template - название шаблона в папке templates
   *
   */
  public function isTemplate($template) {
    if (!$template) return false;
    return is_file(ROOT . '/templates/' . $template . '/index.php');
  }
  
  /*
   * Проверка существования скриптов, стилей и их последующая загрузка
   *
   * name - имя файла
   * moduleClassName - имя модуля
   *
   */
  private function addStyleOrScript($arrName, $name, $moduleClassName) {
    $path = '';
    
    if ($moduleClassName === false) $this->{$arrName}[] = $name;
    elseif ($moduleClassName != '') $path = '/modules/' . $moduleClassName . '/';
    else $path = '/templates/' . $this->template . '/';
    
    if (file_exists(ROOT . $path . $name) || $path == '') $this->{$arrName}[] = $path . $name;
  }
  
  public function addScript($name = 'script.js', $moduleClassName = '') {
    $this->addStyleOrScript('scripts', $name, $moduleClassName);
  }
  
  public function addStyle($name = 'style.css', $moduleClassName = '') {
    $this->addStyleOrScript('styles', $name, $moduleClassName);
  }
  
  /*
   * Добавление мета-данных на страницу
   *
   * name - имя (например, keywords, description)
   * content
   *
   */
  public function addMetaTag($name, $content) {
    $this->metaTags[$name] = $content;
  }
}

?>