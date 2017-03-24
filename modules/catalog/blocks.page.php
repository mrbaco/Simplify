<?php

if (!defined('SIMPLIFY')) exit;

$tree = $simplify->catalog->createPagesCache(true);

if (isset($_GET['edit'])) {
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'pages', array ('*'), array ('ID' => (int)$_GET['edit'], 'BLOCK' => '1'));
  
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
    'Заголовок блока' => array (
      'type' => 'text',
      'name' => 'title'
    )
  ));
  
  ?>
  <p>
    <label>
      Содержание блока:<br />
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
    'Удалить блок' => array (
      'type' => 'button',
      'inline' => true,
      'display' => isset($_GET['edit']),
      'onclick' => 'deleteEl(\'blocks\', ' . (int)$_GET['edit'] .');'
    )
  ));
  
  echo $simplify->htmlForms->closeAdvancedForm();
  
  if (isset($_GET['edit'])) {
  ?>
  <p>
    <small>
      <b>Для разработчика:</b> чтобы вывести блок на странице сайта, необходимо произвести вызов<br />
      <code>$simplify->catalog->blockWidget(<?php echo (int)$_GET['edit'] ?>);</code>
    </small>
  </p>
  <?php } ?>
</div>
<div class="block2">
  <div id="tree">
    <?php $simplify->catalog->treePrint(array (0 => $tree), 0, 'blocks'); ?>
  </div>
</div>
<div class="clear"></div>
<div class="block3">
  <h2>Содержимое блока</h2>
  <?php if (!isset($_GET['edit'])) { ?>
  <small>Выберите блок.</small>
  <?php } else echo $e['CONTENT']; ?>
</div>