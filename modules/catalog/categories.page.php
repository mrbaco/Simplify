<?php

if (!defined('SIMPLIFY')) exit;

$tree = $simplify->catalog->createStructureCache();

if (isset($_GET['edit'])) {
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'categories', array ('*'), array ('ID' => (int)$_GET['edit']));
  
  if ($sql) {
    $e = $sql->fetch_assoc();
    $simplify->htmlForms->preset($e);
  }
}

?>
<div class="block2">
  <?php
  
  echo $simplify->htmlForms->createAdvancedForm();
  
  echo $simplify->htmlForms->createForm(array (
    'Название' => array (
      'type' => 'text',
      'name' => 'title'
    )
  ));
  
  echo $simplify->catalog->categorySelectWidget('Родительская категория', 'parent');
  
  ?>
  <p>
    <label>
      Краткое описание:<br />
      <?php
      echo $simplify->tinymce->tinymceWidget(array (
        'name' => 'content',
        'convert_urls' => false,
        'content' => $_POST['content'] ? $_POST['content'] : $e['CONTENT']
      ));
      ?>
    </label>
  </p>
  <?php
  
  echo $simplify->htmlForms->createForm(array (
    (isset($_GET['edit']) ? 'Изменить' : 'Создать') => array (
      'type' => 'submit'
    ),
    'Удалить категорию' => array (
      'type' => 'button',
      'inline' => true,
      'display' => isset($_GET['edit']),
      'onclick' => 'deleteEl(\'categories\', ' . (int)$_GET['edit'] .');'
    )
  ));
  
  echo $simplify->htmlForms->closeAdvancedForm();
  
  ?>
</div>
<div class="block2">
  <div id="tree">
    <?php $simplify->catalog->treePrint($tree); ?>
  </div>
</div>
<div class="clear"></div>
<div class="block3">
  <h2>Страница категории</h2>
  <?php if (!isset($_GET['edit'])) { ?>
  <small>Выберите категорию.</small>
  <?php } else echo $e['CONTENT']; ?>
</div>