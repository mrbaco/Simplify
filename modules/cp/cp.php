<?php

/*
 * Simplify 4.5 Evo
 *
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Модуль панели управления сайтом
 *
 * Отвечает за организацию работы других модулей движка
 *
 */

// Защита от прямого включения
if (!defined('SIMPLIFY')) exit;

// Определение модуля
$simplify->init('cp', array (
  'routing' => array (
    '/install.html/i' => 'install',
    '/cp/i' => 'controlPanel'
  ),
  'dashboard' => true,
  'widgets' => array ('egg' => 'easterEgg', 'knight' => 'knight')
));

// Главный управляющий клас
class cp {
  // Имя группы авторизированного в системе пользователя
  public $userGroup;
  
  // Модуль, отвечающий за формирование рабочего стола
  public $dashboard;
  
  /*
   * Конструктор класса
   *
   * При загрузке определяет существование файла с параметрами.
   * Если файл отсутствует - идет перенаправление по маршруту на установку системы
   *
   */
  public function __construct() {
    global $simplify;
    
    if (!file_exists(ROOT . '/core/params/system.php') && !$simplify->mdl->isDefaultRouting('cp')) {
      header('Location: /install.html');
      exit;
    }
  }
  
  /*
   * Зарезервированный метод загрузки страниц по заданном маршруту
   * 
   * (Внимание!) В методе route класса cp идет переопределение зарезервированной
   * переменной $simplify->index, которая отвечает за формирование запрошенной
   * страницы. Поэтому страница отображается не так как остальные страницы сайта.
   *
   * route - переменная, определяющая запрошенный маршрут
   *
   * В обычном состоянии функция возвращает значение в зарезервированную переменную
   * $simplify->content, однако в данном случае функция ничего не возвращает, но осу-
   * ществляет подмену этой переменной.
   * 
   */
  public function routing($route) {
    global $simplify;
    
    // Отключение вывода отладочной информации на страницах
    $simplify->params['debug'] = false;
    
    // Установка системы (страница /install.html)
    if ($route == 'install') {
      $simplify->title = 'Установка системы';
      $simplify->myResult['blockName'] = 'install';
      
      if ($simplify->mdl->isModule('htmlForms')) {
        $simplify->content = 'Настройка параметров уже произведена.';
        
        // Сохранение параметров базы данных
        if (isset($_POST['host'])) {
          $host = htmlspecialchars($_POST['host']);
          $username = htmlspecialchars($_POST['username']);
          $password = htmlspecialchars($_POST['password']);
          $database = htmlspecialchars($_POST['database']);
          $prefix = htmlspecialchars($_POST['prefix']);
          
          $simplify->db = new database($simplify->logging, array (
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'database' => $database
          ));
          
          if (!$simplify->db->connect_errno && !empty($host) && !empty($username) && !empty($database)) {
            $simplify->params['database']['host'] = $host;
            $simplify->params['database']['username'] = $username;
            $simplify->params['database']['password'] = $password;
            $simplify->params['database']['database'] = $database;
            $simplify->params['database']['prefix'] = $prefix;
            
          } else $error = 'Не удалось подключиться к базе данных.';
        }
        
        // Сохранение параметров кэширования
        if (isset($_POST['timeout'])) {
          $simplify->params['cache']['timeout'] = (int)$_POST['timeout'];
          $simplify->params['debug'] = isset($_POST['debug']) ? true : false;
        }
        
        // Сохранение называния шаблона
        if (isset($_POST['template'])) {
          if ($simplify->tpl->isTemplate($_POST['template'])) {
            $simplify->params['template'] = htmlspecialchars($_POST['template']);
          } else $error = 'Шаблон не найден.';
        }
        
        // Сохранение данных входа Главного Администратора
        if (isset($_POST['login'])) {
          if ($_POST['login'] == '') $error = 'Необходимо создать администратора.';
          elseif (strlen($_POST['password']) < 6) $error = 'Пароль не должен быть короче 6 символов.';
          else {
            $simplify->params['admin']['login'] = htmlspecialchars($_POST['login']);
            $simplify->params['admin']['password'] = md5(md5(md5($_POST['password'])));
            
            header('Location: /cp/');
          }
        }
        
        // Запись параметров в файл
        if (sizeof($_POST)) $simplify->mdl->setParams('system', $simplify->params);
        
        // Создание формы на странице
        if (!isset($simplify->params['database'])) {
          $simplify->content = array (
            'Подключение к базе данных',
            
            'Сервер' => array (
              'type' => 'text',
              'name' => 'host'
            ),
            'Пользователь' => array (
              'type' => 'text',
              'name' => 'username'
            ),
            'Пароль' => array (
              'type' => 'password',
              'name' => 'password'
            ),
            'База данных' => array (
              'type' => 'text',
              'name' => 'database'
            ),
            'Префикс таблиц' => array (
              'type' => 'text',
              'name' => 'prefix'
            ),
            'далее &rarr;' => array (
              'type' => 'submit'
            )
          );
        } elseif (!isset($simplify->params['cache'])) {
          $simplify->content = array (
            'Кэширование и отладка',
            
            'Таймаут кэширования' => array (
              'type' => 'text',
              'hint' => 'в секундах',
              'name' => 'timeout'
            ),
            'использовать отладку' => array (
              'type' => 'checkbox',
              'name' => 'debug'
            ),
            'далее &rarr;' => array (
              'type' => 'submit'
            )
          );
        } elseif (!isset($simplify->params['template'])) {
          $templates = array ();
          
          $dir = opendir(ROOT . '/templates/');
          while (false !== ($tpl = readdir($dir))) {
            if ($tpl == "." || $tpl == "..") continue;
            if (!$simplify->tpl->isTemplate($tpl)) continue;
            
            $templates[$tpl] = $tpl;
          }
          closedir($dir);
          
          $simplify->content = array (
            'Оформление',
            
            'Шаблон оформления' => array (
              'type' => 'select',
              'name' => 'template',
              'options' => $templates
            ),
            'далее &rarr;' => array (
              'type' => 'submit'
            )
          );
        } elseif (!isset($simplify->params['admin'])) {
          $simplify->content = array (
            'Администратор',
            
            'Логин' => array (
              'type' => 'text',
              'name' => 'login'
            ),
            'Пароль' => array (
              'type' => 'password',
              'name' => 'password'
            ),
            'сохранить' => array (
              'type' => 'submit'
            )
          );
        }
        
        $simplify->htmlForms->preset($_POST);
        $simplify->htmlForms->error($error);
        
        $simplify->content = $simplify->htmlForms->createForm($simplify->content);
      } else {
        $simplify->content  = 'Модуль <b>htmlForms</b> отсутствует.<br />' . PHP_EOL;
        $simplify->content .= 'Проведите установку вручную.';
      }
    // Панель управления (страница /cp)
    } elseif ($route == 'controlPanel') {
      $simplify->title = 'Панель управления';
      
      // Авторизация пользователя в панели управления
      if (isset($_POST['login'])) {
        if (is_object($simplify->db)) {
          $_POST['login'] = $simplify->db->escape_string($_POST['login']);
          $_POST['password'] = $simplify->db->escape_string($_POST['password']);
        }
        
        if (!$this->authUser($_POST['login'], $_POST['password'])) {
          $error = 'Неправильно. Попробуйте снова.';
        }
      }
      
      $this->userGroup = $this->isAuth();
      
      // Создание формы авторизации
      if (!$this->userGroup) {
        $simplify->myResult['blockName'] = 'auth';
        
        $simplify->content = array (
          'Авторизация',
          
          'Логин' => array (
            'type' => 'text',
            'name' => 'login',
            'autofocus' => true
          ),
          'Пароль' => array (
            'type' => 'password',
            'name' => 'password'
          ),
          'войти &rarr;' => array (
            'type' => 'submit'
          )
        );
        
        $simplify->htmlForms->preset($_POST);
        $simplify->htmlForms->error($error);
        
        $simplify->content = $simplify->htmlForms->createForm($simplify->content);
      } else {
        $simplify->myResult['blockName'] = 'cp';
        
        // Обработка запросов
        if (isset($_GET['logout'])) {
          $this->logout();
          header('Location: /cp/');
        }
        
        if ($this->userGroup == 'admin') {
          if (isset($_GET['clearCache'])) {
            $simplify->cache->clear();
            header('Location: /cp/');
          } elseif (isset($_GET['clearModulesList'])) {
            $simplify->mdl->examine();
            header('Location: /cp/');
          }
        }
        
        // Загрузка страницы модуля и выполнение обработки запросов к нему
        if (isset($_GET['m'])) {
          $_GET['m'] = htmlspecialchars($_GET['m']);
          
          // Проверка существования модуля и прав доступа к нему у текущего пользователя
          if ($simplify->mdl->isModule($_GET['m']) && $this->isRights($_GET['m'])) {
            // Обработка Ajax-запросов модулей
            if (isset($_GET['ajax']) && method_exists($_GET['m'], 'ajax')) {
              $simplify->index = $simplify->{$_GET['m']}->ajax();
              return false;
            // Вызов метода-обработчика запрошенного модуля
            } elseif (method_exists($_GET['m'], 'cp')) {
              $simplify->content = $simplify->{$_GET['m']}->cp($_GET['g'], $_GET['p']);
              
              $simplify->tpl->addScript('script.js', $_GET['m']);
              $simplify->tpl->addStyle('style.css', $_GET['m']);
            }
          } else {
            // Вывод сообщения об ограничении доступа
            $simplify->content = 'Доступ ограничен.';
          }
        } else {
          // Загрузка страницы модуля, которая отвечает за формирование рабочего стола
          if (!method_exists($this->dashboard, 'dashboard')) $this->dashboard = 'cp';
          $simplify->content = $simplify->{$this->dashboard}->dashboard();
          $simplify->title = 'Рабочий стол';
        }
        
        // Создание и сортировка списка модулей
        uasort($simplify->mdl->modules, array ($this, 'sortArray'));
        $simplify->myResult['menu'] = '';
        $availableWidgets = array ();
        
        foreach ($simplify->mdl->modules as $moduleClassName => $p) {
          if (!$this->isRights($moduleClassName) || !$p['params']['state']) continue;
          
          // Инициализация виджетов на рабочем столе
          if (is_array($p['source']['widgets'])) {
            foreach ($p['source']['widgets'] as $widget => $function) {
              if ($widget != 'egg' && $widget != 'knight') $availableWidgets[] = $widget;
              if (strpos($simplify->content, '{' . $widget . '}') === false ||
                  !method_exists($moduleClassName, $function)) continue;
              
              $simplify->content = str_replace(
                '{' . $widget . '}',
                call_user_func(array ($simplify->{$moduleClassName}, $function)),
                $simplify->content
              );
            }
          }
          
          // Создание списка меню
          if (!sizeof($p['source']['menu'])) continue;
          
          foreach ($p['source']['menu'] as $key => $group) {
            $simplify->myResult['module'] = $moduleClassName;
            $simplify->myResult['group'] = $group;
            $simplify->myResult['key'] = $key;
            
            $simplify->myResult['menu'] .= $simplify->tpl->load('menu.element', 'cp');
          }
        }
        
        // Вывод списка доступных для использования виджетов
        $simplify->content = str_replace(
          '{availableWidgets}',
          implode(', ', $availableWidgets),
          $simplify->content
        );
      }
    }
    
    // Добавление тегов на странцу
    $simplify->tpl->addMetaTag('viewport', 'width=device-width, initial-scale=1.0');
    $simplify->tpl->addStyle('style.css', 'cp');
    $simplify->tpl->addScript('cp.js', 'cp');
    
    // Загрузка шаблона
    $simplify->index = $simplify->tpl->load('cp.page', 'cp');
  }
  
  /*
   * Функция выполняет создание рабочего стола в панели управления
   *
   */
  public function dashboard() {
    global $simplify;
    
    if (isset($_POST['dashboard'])) {
      $_POST['dashboard'] = str_replace('{', '', $_POST['dashboard']);
      $_POST['dashboard'] = str_replace('}', '', $_POST['dashboard']);
      
      $simplify->mdl->setParams('cp', array (
        'dashboard' => htmlspecialchars($_POST['dashboard'])
      ));
    }
    
    return $simplify->tpl->load('dashboard.page', 'cp');
  }
  
  /*
   * Функция выполняет проверку авторизации пользователя
   *
   * Возвращает группу авторизированного пользователя в случае успеха
   * false - если пользователь не авторизирован
   *
   */
  public function isAuth() {
    global $simplify;
    
    if ($_COOKIE['login'] == $simplify->params['admin']['login'] &&
        md5($_COOKIE['hash']) == $simplify->params['admin']['password']) {
      return 'admin';
    }
    
    return false;
  }
  
  /*
   * Проверяет наличие прав у авторизированного пользователя для доступа к модулю
   *
   */
  public function isRights($moduleClassName) {
    global $simplify;
    
    if (!is_array($simplify->mdl->modules[$moduleClassName]['source']['rights']) ||
        in_array($this->userGroup, $simplify->mdl->modules[$moduleClassName]['source']['rights'])) return true;
    
    return false;
  }
  
  /*
   * Авторизирует пользователя с заданной парой логин пароль
   *
   * login - логин
   * password - пароль
   * 
   */
  public function authUser($login, $password) {
    global $simplify;
    
    $password = md5(md5($password));
    
    if ($simplify->params['admin']['login'] == $login && $simplify->params['admin']['password'] == md5($password)) {
      setcookie('hash', $password, time() + 7200, '/');
      setcookie('login', $login, time() + 7200, '/');
      
      $_COOKIE['hash'] = $password;
      $_COOKIE['login'] = $login;
      
      return true;
    }
    
    return false;
  }
  
  /*
   * Выход пользователя из системы
   *
   */
  public function logout() {
    setcookie('login', '', 1, '/');
    setcookie('hash', '', 1, '/');
  }
  
  /*
   * Возвращает текущий URL-страницы включая запрошенные GET-параметры
   *
   * arr - ключ-значениче GET-параметров, которые необходимо изменить
   *
   */
  public function getCurrentURL($arr = array ()) {
    if (!sizeof($arr)) return $_SERVER['REQUEST_URI'];
    
    $newget = array ();
    $query = '';
    
    foreach ($arr as $k => $v) $arr[$k] = $v;
    
    $arr = array_change_key_case($arr);
    $newget = array_merge($_GET, $arr);
    
    foreach ($newget as $k => $v) {
      if ($k == 'do' || $v === null) continue;
      $query .= $k . '=' . urlencode($v) . '&';
    }
    
    return substr(strtok($_SERVER['REQUEST_URI'], '?') . '?' . $query, 0, -1);
  }
  
  /*
   * Функция сортировки массива модулей
   *
   */
  private function sortArray($array1, $array2) {
    if ($array1['source']['sort'] == $array2['source']['sort']) return 0;
    elseif ($array1['source']['sort'] < $array2['source']['sort']) return -1;
    else return 1;
  }
  
  /*
   * Тестовые виджеты
   *
   */
  public function easterEgg() {
    global $simplify;
    return $simplify->tpl->load('egg.widget', 'cp');
  }
  
  public function knight() {
    global $simplify;
    return $simplify->tpl->load('knight.widget', 'cp');
  }
}

?>