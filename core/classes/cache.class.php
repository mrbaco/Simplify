<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Класс кэширования данных
 *
 */
class cacheControl {
  /*
   * Удаление кэша
   *
   * id - уникальное название кэша
   * group - папка кэша
   *
   * Если $id = false, $group = false, то папка /cache/ очищается полностью
   * Если $id = false, $group = 'имя_папки', то очищаются кэш-файлы только в папке заданной папки
   * Если $id = 'имя_кэша', $group = 'имя_папки', то удаляется кэш файл с заданным именем в заданной папке
   * 
   */
  public function clear($id = false, $group = false) {
    global $simplify;
    
    $simplify->logging->add('cache', 'clear', $group . '/' . $id . ' clear');
    
    if ($id) {
      $file = $this->fullpath($id, $group);
      if (file_exists($file)) @unlink($file);
    } else {
      $dir = $this->fullpath('', $group);
      
      if (!file_exists($dir) || !$h = opendir($dir)) return false;
      
      while (false !== ($file = readdir($h))) {
        if ($file == '.' || $file == '..') continue;
        if (!$group && filetype($dir . $file) == 'dir') {
          array_map('unlink', glob($dir . $file . '/*'));
          rmdir($dir . $file);
          continue;
        }
        
        @unlink($dir . $file);
      }
      
      closedir($h);
    }
  }
  
  /*
   * Определение абсолютного пути до файла кэша
   * 
   * id - уникальное название кэша
   * group - папка кэша
   * 
   */
  public function fullpath($id = false, $group = false) {
    return ROOT . '/core/cache/' . ($group ? $group . '/' : '') . ($id != '' ? md5($id) : '');
  }
}

class cache {
  public $result = false;
  
  private $fullpath;
  private $timeout;
  private $params;
  
  /*
   * Инициализация кэша
   *
   * arr - массив параметров (id, group, output, array, eternal)
   *
   */
  public function __construct($arr = array ()) {
    $defaults = array (
      'group' => false,
      'output' => true,
      'array' => false,
      'eternal' => false
    );
    
    $this->params = array_merge($defaults, $arr);
    
    global $simplify;
    
    $this->timeout = $simplify->params['cache']['timeout'];
    $this->fullpath  = $simplify->cache->fullpath($this->params['id'], $this->params['group']);
    $this->fullpath .= $this->params['array'] ? '.php' : '';
    
    $simplify->logging->add('cache', 'init', $this->params['group'] . '/' . $this->params['id'] . ' init');
    
    if ($this->params['output'] && !$this->params['array']) ob_start();
    
    if (!$this->timeout && !$this->params['eternal'] || !$this->params['id']) return false;
    if (!file_exists($this->fullpath)) return false;
    if ((time() - filemtime($this->fullpath) > $this->timeout) && !$this->params['eternal']) return false;
    
    if (!$this->params['array']) {
      $this->result = file_get_contents($this->fullpath);
      if ($this->params['output']) {
        echo $this->result;
        $this->result = ob_get_contents();
        ob_end_flush();
      }
    } else {
      include $this->fullpath;
      $this->result = $params;
    }
  }
  
  /*
   * Создание файла кэша
   *
   * content - содержимое файла
   *
   */
  public function create($content = false) {
    $this->result = $content;
    
    if ($this->params['output'] && !$this->params['array']) {
      $this->result = ob_get_clean();
      echo $this->result;
      
      if ($this->result == '') return false;
    }
    
    global $simplify;
    
    if (!$this->timeout && !$this->params[$id]['eternal']) return false;
    
    $simplify->logging->add('cache', 'create', $this->fullpath . ' created');
    
    if (!is_dir(dirname($this->fullpath))) mkdir(dirname($this->fullpath));
    
    if ($this->params['array']) {
      $params = new paramsCreation(substr($this->fullpath, strlen(ROOT)));
      $params->addParams($this->result);
      $params->write();
    } else file_put_contents($this->fullpath, $this->result);
  }
  
  /*
   * Изменение параметров текущего кэша на лету
   *
   */
  public function change($arr = array ()) {
    $this->params = array_merge($this->params, $arr);
  }
}

?>