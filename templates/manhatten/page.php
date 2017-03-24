<?php

if (!defined('SIMPLIFY')) exit;

if ($simplify->title == '') return;

if (strpos($simplify->myResult['category']['content'], '<!-- pagebreak -->') !== false) {
  $simplify->myResult['category']['content'] = explode('<!-- pagebreak -->', $simplify->myResult['category']['content']);
  $simplify->myResult['category']['content'] = $simplify->myResult['category']['content'][1];
}

?>
<div class="page">
  <h1><?php echo $simplify->title; ?></h1>
  <?php if ($simplify->myResult['category']['content'] != '') { ?>
  <div class="page_wrapper">
    <?php echo $simplify->myResult['category']['content']; ?>
  </div>
  <?php } ?>
</div>