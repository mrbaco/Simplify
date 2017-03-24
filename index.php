<?php

/*
 * Simplify 4.5 Evo
 *
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

/*
 * Объявление необходимых констант
 *
 */
define ('SIMPLIFY', true);
define ('SIMPLIFY_VERSION', 4.5);
define ('SIMPLIFY_CODENAME', 'Evo');

define ('DEBUG', microtime(true));
define ('ROOT', dirname(__file__));

/*
 * Проверка версии PHP на сервере
 *
 */
if (phpversion() < '5.4') die ('<b>Simplify:</b> php version is not up to date.');

/*
 * Изменение режима отображения ошибок на сайте
 *
 */
error_reporting(E_ALL & ~E_NOTICE);

/*
 * Подключение ядра системы
 *
 */
include ROOT . '/core/core.php';

/*
 * Вывод страницы
 *
 */
echo $simplify->index;

/*
 * Вывод отладочной информации
 *
 */
include ROOT . '/core/debug.php';

/*
 * Удаление глобального объекта
 *
 */
unset($simplify);

?>