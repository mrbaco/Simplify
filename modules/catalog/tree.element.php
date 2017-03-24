<?php if (!defined('SIMPLIFY')) exit; ?>
<ul>
  <?php
  $mainCount = sizeof($simplify->myResult['tree']['elements'][$simplify->myResult['tree']['parent']]);
  $counter = 0;
  
  foreach ($simplify->myResult['tree']['elements'][$simplify->myResult['tree']['parent']] as $parent => $e) {
    $counter++;
    
    $marker = is_numeric($simplify->myResult['tree']['parent']) ? 'folders' : 'pages';
    
    $a_class = $_GET['edit'] == $parent ? ' class="current"' : '';
    
    if ($simplify->myResult['tree']['p'] == 'blocks') {
      $a_href = $simplify->cp->getCurrentURL(array (
        'edit' => $parent,
        'p' => 'blocks',
      ));
      
      $marker = 'blocks';
      $title = $e;
    } else {
      $a_href = $simplify->cp->getCurrentURL(array (
        'edit' => $parent,
        'p' => !is_numeric($simplify->myResult['tree']['parent']) ? $simplify->myResult['tree']['parent'] : 'categories',
      ));
      
      $title = $e['TITLE'];
    }
  ?>
  <li class="<?php echo (is_array($simplify->myResult['tree']['elements'][$parent]) || sizeof($e['PAGES']) ? 'subtree' : '') . ($simplify->myResult['tree']['last'] === true ? ' last' : ''); ?>">
    <span>
      <a<?php echo $a_class; ?> href="<?php echo $a_href; ?>">
        <em class="marker <?php echo $marker; ?>"></em>
        <?php echo $title; ?>
      </a>
    </span>
    <?php
    if ($mainCount == $counter) $simplify->myResult['tree']['last'] = true;
    if (is_array($e) && sizeof($e['PAGES'])) {
      $old_parent = $simplify->myResult['tree']['parent'];
      $simplify->catalog->treePrint($e['PAGES'], 'pages', $simplify->myResult['tree']['p']);
      $simplify->myResult['tree']['parent'] = $old_parent;
    }
    $simplify->catalog->treePrint($simplify->myResult['tree']['elements'], $parent, $simplify->myResult['tree']['p']);
    ?>
  </li>
  <?php
  }
  
  $simplify->myResult['tree']['last'] = false;
  ?>
</ul>