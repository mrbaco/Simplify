<?php

if (!defined('SIMPLIFY')) exit;

echo $simplify->htmlForms->createAdvancedForm();

?>
<p>
  <label>
    Диапазон рассчета:<br />
    <?php
    
    echo $simplify->flatpickr->widgetFlatpickr(array (
      'value' => $simplify->htmlForms->preset['range'] ? $simplify->htmlForms->preset['range'] : (date('01.m.Y') . '-' . date('t.m.Y')),
      'mode' => 'range',
      'inline' => false,
      'name' => 'range'
    ));
    
    ?>
  </label>
</p>
<?php

echo $simplify->htmlForms->createForm(array (
  'Выбор' => array (
    'type' => 'submit'
  )
));

echo $simplify->htmlForms->closeAdvancedForm();

if (!$simplify->myResult['sql']) return;
?>
<div class="block2">
  <table>
    <tr>
      <th>Дата</th>
      <th>Сумма</th>
      <th>Категория</th>
      <th>Описание</th>
      <th></th>
    </tr>
    <?php
    
    $by_categories = array ();
    $by_types = array ();
    $summ = 0;
    
    while ($row = $simplify->myResult['sql']->fetch_assoc()) {
      $amount = (!$row['TYPE'] ? (-1) * $row['AMOUNT'] : $row['AMOUNT']);
      
      $by_categories[$row['TYPE']][$row['CATEGORY']] += $amount;
      $by_types[$row['TYPE']] += $amount;
      $summ += $amount;
    ?>
    <tr>
      <td><?php echo date('d.m.Y', $row['DATE']); ?></td>
      <td><?php echo number_format($amount, 2, ',', '&nbsp;'); ?>&nbsp;<span class="rouble">o</span></td>
      <td><?php echo $simplify->finance->categories[$row['TYPE']][$row['CATEGORY']]; ?></td>
      <td><?php echo $row['DESCRIPTION']; ?></td>
      <td><a href="<?php echo $simplify->cp->getCurrentURL(array ('remove' => $row['ID'], 'range' => $simplify->htmlForms->preset['range'])); ?>">x</a></td>
    </tr>
    <?php } ?>
  </table>
</div>
<div class="clear"></div>
<div class="block2">
  <table>
    <?php foreach ($simplify->finance->types as $type_id => $type_name) { ?>
    <tr>
      <th><?php echo $type_name; ?></th>
      <td><?php echo number_format($by_types[$type_id], 2, ',', '&nbsp;'); ?>&nbsp;<span class="rouble">o</span></td>
    </tr>
    <?php foreach ($simplify->finance->categories[$type_id] as $category_id => $category_name) { ?>
    <tr>
      <td><?php echo $category_name; ?></th>
      <td><?php echo number_format($by_categories[$type_id][$category_id], 2, ',', '&nbsp;'); ?>&nbsp;<span class="rouble">o</span></td>
    </tr>
    <?php } } ?>
    <tr>
      <th>Баланс:</th>
      <td><?php echo number_format($summ, 2, ',', '&nbsp;'); ?>&nbsp;<span class="rouble">o</span></td>
    </tr>
  </table>
</div>