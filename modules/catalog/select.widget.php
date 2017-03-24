<?php

if (!defined('SIMPLIFY')) exit;

echo $simplify->htmlForms->createForm(array (
  $simplify->myResult['selectWidget']['title'] => array (
    'type' => 'select',
    'name' => $simplify->myResult['selectWidget']['name'],
    'options' => $simplify->myResult['selectWidget']['array']
  )
));

?>