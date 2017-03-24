<?php

if (!defined('SIMPLIFY')) exit;

header('HTTP/1.0 404 Not Found');

$simplify->title = 'Ошибка 404';
$simplify->myResult['category']['content'] = 'Страница не найдена.<br />Перейдите в <a href="/">каталог</a>, здесь ничего нет :(';

echo $simplify->tpl->load('page');

?>