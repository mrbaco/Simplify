<?php

/*
 * Simplify 4.4 Falcon
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

if (!defined('SIMPLIFY')) exit;

$simplify->init('htmlForms');

class htmlForms {
  public $params = array ();
  public $preset = array ();
  
  private $errors = array ();
  private $error;
  
  private $success;
  
  private $advanced = false;
  
  /*
   * HTMLSpecialChars для массивов
   *
   */
  public function array_htmlspecialchars($arr) {
    if (!is_array($arr)) return htmlspecialchars($arr);
    
    foreach ($arr as $k => $v) {
      if (is_array($v)) {
        $arr[$k] = $this->array_htmlspecialchars($v);
        continue;
      }
      $arr[$k] = htmlspecialchars($v);
    }
    
    return $arr;
  }
  
  /*
   * Начало создания формы
   *
   */
  public function createAdvancedForm() {
    $this->advanced = true;
    return '<form action="' . $this->params['action'] . '" method="' . ($this->params['method'] ? $this->params['method'] : 'post') . '"' . ($this->params['enctype'] != '' ? ' enctype="' . $this->params['enctype'] . '"' : '') . ($this->params['id'] != '' ? ' id="' . $this->params['id'] . '"' : '') . '>' . PHP_EOL;
  }
  
  /*
   * Закрытие формы
   *
   */
  public function closeAdvancedForm() {
    $this->advanced = false;
    return '</form>';
  }
  
  /*
   * Загрузка значений элементов формы
   *
   */
  public function preset($preset) {
    if (!sizeof($preset)) return;
    $this->preset = array_change_key_case($preset);
  }
  
  /*
   * Загрузка ошибок элементов формы либо всей формы
   *
   */
  public function error($error) {
    $this->error = $error;
  }
  
  public function errors($errors) {
    $this->errors = $errors;
  }
  
  /*
   * Загрузка сообщения успешной обрабоки формы
   *
   */
  public function success($success) {
    $this->success = $success;
  }
  
  /*
   * Создание формы
   *
   */
  public function createForm($formArray) {
    if (!is_array($formArray)) return $formArray;
    /*
     * Зарезервированные слова, которые не могут стать атрибутами элемента
     *
     */
    $reserved = array ('hint', 'options', 'display', 'inline');
    
    /*
     * Элементы-исключения, которые формируются не так как обычные input
     *
     */
    $unusual['withoutvalue'] = array ('textarea', 'select', 'checkbox', 'radio');
    $unusual['notinputs'] = array ('select', 'textarea');
    $unusual['buttons'] = array ('button', 'submit', 'reset');
    $unusual['flags'] = array ('checkbox', 'radio');
    
    /*
     * Создание формы
     *
     */
    $form = '';
    
    $inline = false;
    $inline_error = false;
    
    if ($this->advanced !== true) {
      $form .= $this->createAdvancedForm();  
      $this->advanced = false;
    }
    
    foreach ($formArray as $title => $e) {
      /*
       * Если элемент не является массивом, он становится заголовком формы
       *
       */
      if (!is_array($e)) {
        $form .= '  <h2>' . $e . '</h2>' . PHP_EOL;
        continue;
      }
      
      /*
       * Если есть общая ошибка для формы или сообщение об успешной обработке - они показываются
       *
       */
      if ($this->error != '') {
        $form .= '<div class="error">' . $this->error . '</div>';
        $this->error = '';
      }
      
      if ($this->success != '') {
        $form .= '<div class="success">' . $this->success . '</div>';
        $this->success = '';
      }
      
      /*
       * Если у элемента задано свойство display = false, он не отображается
       *
       */
      if ($e['display'] === false) continue;
      
      /*
       * Загрузка сохраненных значений из preset
       *
       */
      if (isset($this->preset[$e['name']])) {
        $e['value'] = $this->preset[$e['name']];
      }
      
      /*
       * Создание элемента
       *
       */
      if ($inline !== true) $form .= '  <p' . ($e['id'] ? ' id="' . $e['id'] . '_p"' : '') . ($e['inline'] ? ' class="inline"' : '') . '>' . PHP_EOL;
      if (isset($e['inline'])) $inline = (bool)$e['inline'];
      
      $form .= '    <label' . ($e['id'] ? ' id="' . $e['id'] . '_l"' : '') . (isset($this->errors[$e['name']]) && $e['name'] ? ' class="errors"' : '') . '>' . PHP_EOL;
      
      /*
       * Если элемент - кнопка - не отображается заголовок
       *
       */
      if (in_array($e['type'], $unusual['buttons'])) {
        $e['value'] = $title;
        $title = '';
      }
      
      /*
       * Если элемент - флажок - не отображается заголовок
       *
       */
      if (!in_array($e['type'], $unusual['flags'])) {
        $form .= $title != '' ? '      ' . $title . ':<br />' . PHP_EOL : '';
      }
      
      /*
       * Если элемент не является стандартным input, он рисуется иначе
       *
       */
      if (!in_array($e['type'], $unusual['notinputs'])) {
        $form .= '      <input ';
      } else {
        $form .= '      <' . $e['type'] . ' '; 
      }
      
      /*
       * Перебор атрибутов элемента
       *
       */
      foreach ($e as $key => $value) {
        if (is_numeric($key)) {
          $form .= $value . ' ';
          continue;
        }
        
        if (in_array($key, $reserved)) continue;
        
        if ($key == 'value') {
          if (in_array($e['type'], $unusual['flags'])) {
            $key = 'checked';
            $value = '';
          } elseif (in_array($e['type'], $unusual['withoutvalue'])) continue;
        }
        
        if ($key == 'checked' && !$value) continue;
        
        $form .= $key . ($value != '' ? '="' . $value . '"' : '') . ' ';
      }
      
      /*
       * Если элемент не является стандартным input, он рисуется иначе
       *
       */
      if (!in_array($e['type'], $unusual['notinputs'])) {
        $form .= '/>';
      } else {
        /*
         * Обработка элементов select и textarea
         *
         */
        $form = substr($form, 0, strlen($form) - 1) . '>';
        
        if ($e['type'] == 'select') {
          $form .= PHP_EOL;
          
          if (sizeof($e['options'])) foreach ($e['options'] as $key => $value) {
            $form .= '        <option value="' . $key . '"';
            $form .= isset($e['value']) && $e['value'] == $key ? ' selected="selected"' : '';
            $form .= '>' . $value . '</option>' . PHP_EOL;
          }
          
          $form .= '      ';
        } else {
          $form .= $e['value'];
        }
        
        $form .= '</' . $e['type'] . '>'; 
      }
      
      /*
       * Если элемент - флажок - заголовок добавляется после него
       *
       */
      if (in_array($e['type'], $unusual['flags'])) {
        $form .= '<span></span>&nbsp;<span>' . $title . '</span>';
      } else {
        $form .= isset($e['hint']) ? '&nbsp;<small class="hint">' . $e['hint'] . '</small>' : '';
      }
      
      $form .= PHP_EOL;
      
      /*
       * Окончание создания элемента и вывод сообщения об ошибке
       *
       */
      $form .= '    </label>' . PHP_EOL;
      if ($inline !== true) {
        if ((isset($this->errors[$e['name']]) && $e['name']) || $inline_error) {
          $form .= '  <br /><small class="errors">';
          $form .= $inline_error ? $inline_error : $this->errors[$e['name']];
          $form .= '</small>';
        }
        
        $form .= '  </p>' . PHP_EOL;
        
        $inline_error = false;
      } elseif (isset($this->errors[$e['name']]) && $e['name']) {
        $inline_error = $this->errors[$e['name']];
      }
    }
    
    /*
     * Закрытие формы
     *
     */
    if ($this->advanced !== true) {
      $form .= $this->closeAdvancedForm();
    }
    
    return $form;
  }
}

?>