<?php

if (!defined('SIMPLIFY')) exit;

echo $simplify->htmlForms->createAdvancedForm();

echo $simplify->htmlForms->createForm(array (
  'Ордер' => array (
    'type' => 'select',
    'name' => 'type',
    'id' => 'type',
    'options' => $simplify->finance->types
  )
));

foreach ($simplify->finance->types as $id => $type) {
?>
<div id="type_<?php echo $id; ?>" style="display: <?php echo (int)$simplify->htmlForms->preset['type'] === $id || (!isset($_POST) && $id === 0) ? 'block' : 'none'; ?>;">
  <?php
  
  echo $simplify->htmlForms->createForm(array (
    'Категория' => array (
      'type' => 'select',
      'name' => 'category_' . $id,
      'options' => $simplify->finance->categories[$id]
    )
  ));
  
  ?>
</div>
<?php } ?>
<script type="text/javascript">
  var previousFinanceBlock = <?php echo (int)$simplify->htmlForms->preset['type']; ?>;
</script>
<p>
  <label>
    Дата:<br />
    <?php
    
    echo $simplify->flatpickr->widgetFlatpickr(array (
      'value' => $simplify->htmlForms->preset['date'] ? $simplify->htmlForms->preset['date'] : date('d.m.Y'),
      'mode' => 'single',
      'inline' => false,
      'name' => 'date'
    ));
    
    ?>
  </label>
</p>
<?php

echo $simplify->htmlForms->createForm(array (
  'Сумма' => array (
    'type' => 'text',
    'name' => 'amount'
  ),
  'Описание' => array (
    'type' => 'text',
    'name' => 'description'
  ),
  'Создать' => array (
    'type' => 'submit'
  )
));

echo $simplify->htmlForms->closeAdvancedForm();

?>