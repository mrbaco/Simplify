<?php

if (!defined('SIMPLIFY')) exit;

echo '<a href="?m=storage&r=/">' . $simplify->storage->directory . '</a>/';
if (sizeof($simplify->storage->routing)) foreach ($simplify->storage->routing as $route) echo '<a href="?m=storage&r=' . $route . '">' . basename($route) . '</a>' . '/';

?>
<hr />
<div id="storage" class="block2">
<?php echo $simplify->tpl->load('storage.element', 'storage'); ?>
</div>
<div id="preview" class="block2"></div>
<div class="clear"></div>
<hr />
<div class="block2">
<?php

echo $simplify->htmlForms->createForm(array (
  'Новая папка' => array (
    'type' => 'text',
    'name' => 'newdir'
  ),
  'Создать' => array (
    'type' => 'submit'
  )
));

?>
</div>
<div class="block2">
<?php

$simplify->htmlForms->params = array (
  'id' => 'files_upload',
  'enctype' => 'multipart/form-data'
);

echo $simplify->htmlForms->createForm(array (
  '' => array (
    'type' => 'file',
    'multiple' => 'multiple',
    'name' => 'files[]',
    'id' => 'upload'
  ),
  'сохранять оригинальные имена' => array (
    'type' => 'checkbox',
    'name' => 'original',
    'checked' => true
  )
));

$simplify->htmlForms->params = array ();

?>
  <small>Загрузка файлов начнется автоматически после их выбора.</small>
</div>