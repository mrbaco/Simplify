<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Ведение журнала событий
 *
 */
class logging {
  // Журнал событий (массив данных)
  public $log = array ();
  
  /*
   * Добавление записи в журнал
   *
   * mdlName - имя модуля/класса
   * action - выполняемое действие
   * message - примечание
   *
   */
  public function add($mdlName, $action, $message = '') {
    $this->log[] = array ($mdlName, $action, $message);
  }
  
  /*
   * Добавление строки-разделителя в журнал
   *
   */
  public function delimiter() {
    $this->log[] = '';
  }
}

?>