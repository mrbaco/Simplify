<?php

/*
 * Simplify 4.5 Evo
 *
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

if (!defined('SIMPLIFY')) exit;

/*
 * Подключение системных параметров
 *
 */
if (file_exists(ROOT . '/core/params/system.php')) include ROOT . '/core/params/system.php';

/*
 * Подключение классов, необходимых для работы системы
 *
 */
include ROOT . '/core/classes/simplify.class.php';

include ROOT . '/core/classes/templates.class.php';
include ROOT . '/core/classes/database.class.php';
include ROOT . '/core/classes/modules.class.php';
include ROOT . '/core/classes/logging.class.php';
include ROOT . '/core/classes/params.class.php';
include ROOT . '/core/classes/cache.class.php';

/*
 * Создание экземпляра глобального класса
 *
 */
$simplify = new simplify($params);

/*
 * Формирование и загрузка списка модулей
 *
 */
if (!file_exists(ROOT . '/core/cache/modules.php')) $simplify->mdl->examine();
include ROOT . '/core/cache/modules.php';

/*
 * Формирование запрошенной страницы
 *
 */
if (sizeof($simplify->mdl->moduleMethod) &&
    method_exists($simplify->mdl->moduleMethod['className'], 'routing')) {
      $simplify->mdl->moduleMethod['className'] = $simplify->{$simplify->mdl->moduleMethod['className']};
      
      $simplify->content = call_user_func(array (
        $simplify->mdl->moduleMethod['className'],
        'routing'
      ), $simplify->mdl->moduleMethod['route']);
} else $simplify->content = $simplify->tpl->load('404');

$simplify->index = $simplify->index === true ? $simplify->tpl->load('index') : $simplify->index;

?>