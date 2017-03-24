<?php

if (!defined('SIMPLIFY')) exit;

$widgets = $simplify->mdl->modules['cp']['params']['dashboard'];

if ($widgets == '') echo 'На рабочем столе нет ни одного виджета.<br /><small>Создайте свой список виджетов с помощью поля снизу.</small>';
else {
  $widgets = explode(',', $widgets);
  
  foreach ($widgets as $widget) {
    if ($widget == '') continue;
?>
<div class="block">
  {<?php echo $widget; ?>}
</div>
<?php } } ?>
<div class="clear"></div>
<hr />
<div class="block2">
<?php

$simplify->htmlForms->preset(array (
  'dashboard' => $simplify->mdl->modules['cp']['params']['dashboard']
));

echo $simplify->htmlForms->createForm(array (
  '' => array (
    'type' => 'text',
    'name' => 'dashboard',
    'style' => 'width: 70%;'
  ),
  'Сохранить' => array (
    'type' => 'submit'
  )
));

?>
<small>Доступные виджеты: {availableWidgets}.</small>
</div>
<div class="clear"></div>