<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Создание файлов с параметрами
 *
 */
class paramsCreation {
  // (скрыто) Содержимое сохраняемого файла
  private $content;
  
  // (скрыто) Путь к файлу
  private $path;
  
  /*
   * Создание объекта paramsCreation
   *
   * path - относительный путь к файлу (относительно константы ROOT)
   *
   */
  public function __construct($path) {
    if (strpos($path, '/core/params/') === false && strpos($path, '/core/cache/') === false) return false;
    if (file_exists(ROOT . $path)) unlink(ROOT . $path);
    
    $this->path = $path;
    $this->content = '<?php' . PHP_EOL . PHP_EOL . 'if (!defined(\'SIMPLIFY\')) exit;' . PHP_EOL . PHP_EOL;
  }
  
  /*
   * Включение файла (по относительному пути)
   *
   * includePath - относительный путь к включаемому файлу (относительно константы ROOT)
   *
   */
  public function addInclude($includePath) {
    $this->content .= 'include ROOT . \'' . $includePath . '\';' . PHP_EOL;
  }
  
  /*
   * Рекурсивный метод добавления параметров к файлу настроек
   *
   * params - параметры (массив данных) (ключ:значение, ключ:[ключ:значение,ключ:значение])
   * indent - отступ от левого края (пробелы) для записи в файл параметров
   *
   */
  public function addParams($params, $indent = '  ') {
    if ($indent == '  ') {
      $this->content .= '$params = array (' . PHP_EOL;
    }
    
    if (is_array($params)) {
      foreach ($params as $param => $value) {
        $type = gettype($value);
        
        if ($type == 'boolean') $value = ($value === true ? 'true' : 'false');
        elseif ($type == 'string') $value = '\'' . str_replace('\'', '\\\'', $value) . '\'';
        elseif ($type == 'double') $value = floatval($value);
        elseif ($type == 'array') {
          $this->content .= $indent . '\'' . $param . '\' => array (' . PHP_EOL;
          $this->content .= $this->addParams($value, $indent . '  ');
          $this->content .= $indent . '),' . PHP_EOL;
          continue;
        } else $value = intval($value);
        
        $this->content .= $indent . '\'' . $param . '\' => ' . $value . ',' . PHP_EOL;
      }
      
      $this->content = substr($this->content, 0, strlen($this->content) - strlen(PHP_EOL)) . PHP_EOL;
    }
    
    if ($indent == '  ') {
      $this->content .= ');' . PHP_EOL;
    }
  }
  
  /*
   * Создание файла настроек
   *
   */
  public function write() {
    $this->content .= PHP_EOL . '?>';
    return file_put_contents(ROOT . $this->path, $this->content);
  }
}

?>