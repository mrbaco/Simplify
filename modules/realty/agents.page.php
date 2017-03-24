<?php

if (!defined('SIMPLIFY')) exit;

if (isset($_GET['edit'])) {
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'agents', array ('*'), array ('ID' => (int)$_GET['edit']));
  
  if ($sql) {
    $e = $sql->fetch_assoc();
    $e['IMAGES'] = unserialize($e['IMAGES']);
    $simplify->htmlForms->preset($e);
  }
}

?>
<div class="block2">
  <?php
  
  echo $simplify->htmlForms->createAdvancedForm();

  echo $simplify->htmlForms->createForm(array (
    'Новый агент' => array (
      'type' => 'text',
      'name' => 'name'
    ),
    'Телефон' => array (
      'type' => 'text',
      'name' => 'phone',
      'id' => 'agent_phone'
    )
  ));
  
  ?>
  Фотография:<br />
  <?php
  
  echo $simplify->storage->storageWidget(array (
    'relativePath' => '/agents/',
    'original' => false,
    'maxNumber' => 1,
    'multiple' => false,
    'preset' => $simplify->htmlForms->preset['images'],
    'full' => true
  ));
  
  echo $simplify->htmlForms->createForm(array (
    'Краткая информация' => array (
      'type' => 'textarea',
      'name' => 'info'
    ),
    (isset($_GET['edit']) ? 'Изменить' : 'Добавить') . ' агента' => array (
      'type' => 'submit'
    )
  ));
  
  echo $simplify->htmlForms->closeAdvancedForm();
  
  ?>
</div>
<div class="block2">
  <?php echo $simplify->tpl->load('all_agents.page', 'realty'); ?>
</div>