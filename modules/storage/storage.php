<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('storage', array (
  'name' => 'Файловое хранилище',
  'description' => 'Управление файлами в хранилище',
  'menu' => array (
    'main' => array (
      'title' => 'Файловое хранилище',
      'submenu' => array (
        'upload' => array (
          'icon' => '/modules/storage/images/up.png',
          'title' => 'Загрузка файлов'
        )
      )
    )
  ),
  'widgets' => array (
    'storage' => 'storageWidget'
  ),
  'sort' => 300
));

class storage {
  // хранит список элементов (папки и файлы)
  public $page = array ();
  // массив текущего маршрута
  public $routing = array ();
  // текущий маршрут
  public $route;
  
  // максимальный размер загружаемого файла
  public $maxSize = 3145728; // 3 Mb
  
  // допустимые расширения -> классы загружаемых файлов
  public $extensions = array (
    'png' => 'picture',
    'jpg' => 'picture',
    'jpeg' => 'picture',
    'gif' => 'picture',
    'bmp' => 'picture'
  );
  
  // папка хранилища
  public $directory = 'storage';
  
  /*
   * Вывод страницы в панели управления
   *
   */
  public function cp($group, $param) {
    global $simplify;
    
    $simplify->title = 'Файловое хранилище';
    
    // проверка запрошенного пути
    $path = $this->getPath($_GET['r']);
    
    // обработка запроса на создание новой папки
    if (isset($_POST['newdir'])) {
      $_POST['newdir'] = htmlspecialchars($_POST['newdir']);
      @mkdir($path[0] . $path[1] . $_POST['newdir']);
    }
    
    // загрузка файла напрямую
    if (isset($_FILES['files'])) {
      $this->upload('files', $path[0] . $path[1], isset($_POST['original']));
    }
    
    // генерация списка папок и файлов по определенному пути
    $this->loadPage($path[0] . $path[1]);
    // создание маршрута
    $this->loadRouting($path[1]);
    
    return $simplify->tpl->load('storage.page', 'storage');
  }
  
  /*
   * Загрузка файлов
   *
   * field - имя обрабатываемого поля
   * path - путь загрузки
   * original - сохраниение оригинального имени или замена его на хэш
   *
   */
  public function upload($field, $path, $original) {
    $files = array ();
    
    if (!is_array ($_FILES[$field])) $_FILES[$field][0] = $_FILES[$field];
    
    foreach ($_FILES[$field]['error'] as $key => $error) {
      if ($error != UPLOAD_ERR_OK) continue;
      
      $tmp_name = $_FILES[$field]['tmp_name'][$key];
      
      $name = $_FILES[$field]['name'][$key];
      $name_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $name = $original ? $name : md5($name . time()) . '.' . $name_ext;
      
      $size = $_FILES[$field]['size'][$key];
      
      $file = array ();
      
      if (!is_uploaded_file($tmp_name)) {
        $file['error'] = -1; // Файл к загрузке не задан
        continue;
      }
      
      if ($size > $this->maxSize) {
        $file['error'] = -2; // Размер файла превышает допустимый
        continue;
      }
      
      $access = false;
      
      foreach ($this->extensions as $ext => $class) {
        if ($name_ext == strtolower($ext)) {
          if (@move_uploaded_file($tmp_name, $path . $name)) {
            $file['name'] = htmlspecialchars($name);
            $access = true;
          } else $file['error'] = -3; // Невозможно переместить файл из временной папки
          
          break;
        }
      }
      
      $file['error'] = !$access ? -4 : 0; // Расширение файла недопустимо
      
      $files[] = $file;
    }
    
    return $files;
  }
  
  /*
   * Обработка Ajax-запросов браузера
   *
   */
  public function ajax() {
    global $simplify;
    
    error_reporting(0);
    
    $simplify->myResult['ajax'] = true;
    
    // запрос на удаление папки или файла
    if (isset($_GET['remove'])) {
      $result = array ('response' => 0);
      
      $_GET['remove'] = urldecode($_GET['remove']);
      if (isset($_GET['directory'])) $_GET['remove'] = substr($_GET['remove'], strpos($_GET['remove'], '&r=') + 3);
      else  $_GET['remove'] = substr($_GET['remove'], strpos($_GET['remove'], $this->directory) + strlen($this->directory));
      $path = $this->getPath($_GET['remove']);
      
      if ($path !== false && $path[1] != '/') {
        $path[0] = substr($path[0], 0, strlen($path[0]) - 1);
        $this->removeElement($path[0], $path[1]);
        $result['response'] = 1;
      }
      
      return json_encode($result);
    }
    
    // загрузка файла средствами виджета без перезагрузки страницы
    if (isset($_FILES['files'])) {
      $path = $this->getPath(urldecode($_POST['relativePath']));
      $path = !$path ? '/' : $path;
      
      if ($_POST['maxNumber'] > 0) foreach ($_FILES['files'] as $key => $value) $_FILES['files'][$key] = array_slice($_FILES['files'][$key], 0, (int)$_POST['maxNumber']);
      
      $result = $this->upload('files', $path[0] . $path[1], (bool)$_POST['original']);
      
      foreach ($result as $file) if ($file['error'] == 0) $this->page[] = array ('name' => $file['name'], 'type' => 'f');
      
      $this->loadRouting($path[1]);
      
      if ($_POST['full']) $simplify->myResult['storageWidget']['full'] = true;
      
      return $simplify->tpl->load('storage.element', 'storage');
    }
    
    return '<p id="ajax_error">Произошла ошибка при обработке данных.</p>';
  }
  
  /*
   * Виджет загрузки файлов
   *
   */
  public function storageWidget($arr = array ()) {
    global $simplify;
    
    $defaults = array (
      'relativePath' => '/',
      'original' => false,
      'maxNumber' => 0,
      'multiple' => true,
      'preset' => array (),
      'selected' => '',
      'full' => false
    );
    
    $arr = array_merge($defaults, $arr);
    
    $simplify->tpl->addScript('script.js', 'storage');
    $simplify->tpl->addStyle('style.css', 'storage');
    
    $arr['selected'] = $this->getPath($arr['selected']);
    $arr['selected'] = '/' . $arr['selected'][1];
    
    $simplify->myResult['storageWidget'] = array (
      'selected' => $arr['selected'],
      'relativePath' => $arr['relativePath'],
      'original' => $arr['original'],
      'maxNumber' => $arr['maxNumber'],
      'multiple' => $arr['multiple'],
      'full' => $arr['full']
    );
    
    $this->loadRouting($arr['relativePath']);
    
    if (is_array($arr['preset'])) foreach ($arr['preset'] as $e) {
      $e = $this->getPath($e);
      if (is_file($e[1])) $this->page[] = array ('name' => basename($e[1]), 'type' => 'f');
    }
    
    return $simplify->tpl->load('storage.widget', 'storage');
  }
  
  /*
   * Удаление папки или файла по заданному маршруту
   *
   */
  private function removeElement($root, $path) {
    if (is_dir($root . $path)) {
      if (!$h = opendir($root . $path)) return false;
      
      while (false !== ($file = readdir($h))) {
        if ($file == '.' || $file == '..') continue;
        if (filetype($root . $path . $file) == 'dir') {
          $this->rrmdir($root . $path . $file);
          continue;
        }
        
        @unlink($root . $path . $file);
      }
      
      closedir($h);
      
      @rmdir($root . $path);
    }
    
    if (is_file($root . $path)) {
      @unlink($root . $path);
    }
  }
  
  /*
   * Рекурсивное удаление папки
   *
   */
  private function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != '.' && $object != '..') {
          if (filetype($dir . '/' . $object) == 'dir') {
            $this->rrmdir($dir . '/' . $object);
            continue;
          }
          
          @unlink($dir . '/' . $object);
        }
      }
      
      reset($objects);
      rmdir($dir);
    }
  }
  
  /*
   * Аналог realpath
   *
   */
  private function truepath($path) {
    $unipath = strlen($path) == 0 || $path{0} != '/';
    
    if(strpos($path, ':') === false && $unipath) $path = getcwd() . DIRECTORY_SEPARATOR . $path;
    
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.'  == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
          $absolutes[] = $part;
      }
    }
    
    $path = implode(DIRECTORY_SEPARATOR, $absolutes);
    
    if(file_exists($path) && linkinfo($path) > 0) $path = readlink($path);
    
    $path =! $unipath ? '/' . $path : $path;
    
    return $path;
  }
  
  /*
   * Проверка запрошенного пути на безопасность
   *
   */
  public function getPath($request) {
    $root = ROOT . '/' . $this->directory . '/';
    
    if (strpos($this->truepath($root . $request), $this->truepath($root)) === 0) {
      $path = strstr($request, $this->directory);
      if (!$path) $path = (substr($request, 0, 1) != '/' ? '/' : '') . $request;
      
      return array ($root, $path);
    }
    
    return false;
  }
  
  /*
   * Загрузка элементов в массив по определенному маршруту
   *
   */
  private function loadPage($route = '/') {
    if (!is_dir($route)) return;
    
    // распределение файлов и папок
    $storage = opendir($route);
    while (false !== ($name = readdir($storage))) {
      if ($name == "." || $name == "..") continue;
      
      if (is_file($route . $name)) $this->page['files'][] = array ('name' => $name, 'type' => 'f');
      else $this->page['directories'][] = array ('name' => $name, 'type' => 'd');
    }
    
    // в массиве сначала папки, потом файлы
    $this->page = array_merge((array)$this->page['directories'], (array)$this->page['files']);
    
    closedir($storage);
  }
  
  /*
   * Создание маршрута до каждого элемента
   *
   */
  private function loadRouting($route = '/') {
    $this->routing = explode('/', $route);
    $this->routing = array_diff($this->routing, array (''));
    
    $path = '';
    foreach ($this->routing as $key => $value) {
      $path .= $value . '/';
      $this->routing[$key] = $path;
    }
    
    $this->route = $route;
  }
}

?>