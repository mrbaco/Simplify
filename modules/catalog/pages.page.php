<?php

if (!defined('SIMPLIFY')) exit;

$tree = $simplify->catalog->createStructureCache();

if (isset($_GET['edit'])) {
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'pages', array ('*'), array ('ID' => (int)$_GET['edit'], 'BLOCK' => '!= 1'));
  
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
    'Заголовок страницы' => array (
      'type' => 'text',
      'name' => 'title'
    ),
    'Ключевые слова' => array (
      'type' => 'text',
      'name' => 'keywords'
    ),
    'Описание' => array (
      'type' => 'text',
      'name' => 'description'
    )
  ));
  
  echo $simplify->catalog->categorySelectWidget('Категория', 'category');
  
  ?>
  <p>
    <label>
      Содержание страницы:<br />
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
    'Удалить страницу' => array (
      'type' => 'button',
      'inline' => true,
      'display' => isset($_GET['edit']),
      'onclick' => 'deleteEl(\'pages\', ' . (int)$_GET['edit'] .');'
    )
  ));
  
  echo $simplify->htmlForms->closeAdvancedForm();
  
  ?>
</div>
<div class="block2">
  <div id="tree">
    <?php $simplify->catalog->treePrint($tree, 0, 'pages'); ?>
  </div>
</div>
<div class="clear"></div>
<div class="block3">
  <h2>Содержимое страницы</h2>
  <?php if (!isset($_GET['edit'])) { ?>
  <small>Выберите страницу.</small>
  <?php } else echo $e['CONTENT']; ?>
</div>