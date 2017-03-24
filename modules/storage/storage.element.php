<?php

if (!defined('SIMPLIFY')) exit;

if (sizeof($simplify->storage->page)) {
  foreach ($simplify->storage->page as $element) {
    if ($element['type'] == 'd') {
?>
      <a href="?m=storage&r=<?php echo $simplify->storage->route . $element['name']; ?>/" class="el">
        <div class="remove">x</div>
        <div class="background directory"></div>
        <?php echo $element['name']; ?>
      </a>
<?php
      continue;
    }
    
    $ext = strtolower(pathinfo($element['name'] , PATHINFO_EXTENSION));
    $class = array_key_exists($ext, $simplify->storage->extensions) ? $simplify->storage->extensions[$ext] : 'file';
    $path = '/' . $simplify->storage->directory . $simplify->storage->route . $element['name'];
    
?>
    <a id="el_<?php echo md5($path); ?>" target="blank" href="<?php echo $path; ?>" class="el<?php echo ($simplify->myResult['storageWidget']['full'] ? ' full' : '') . ($simplify->myResult['storageWidget']['selected'] == $path ? ' selected' : ''); ?>">
      <div class="remove">x</div>
      <div class="background <?php echo $class == 'picture' ? 'picture" style="background-image: url(\'' . $path . '\');' : $class; ?>"></div>
      <?php echo !$simplify->myResult['storageWidget']['full'] ? $element['name'] : ''; ?>
      <input checked="checked" name="images[]" type="checkbox" style="display: none;" value="<?php echo $path; ?>" />
    </a>
<?php
  }
  
  if ($simplify->myResult['storageWidget']['selected']) {
?>
<input type="hidden" name="selected" id="selected" value="<?php echo $simplify->myResult['storageWidget']['selected']; ?>" />
<script type="text/javascript">
  var selected = 'el_<?php echo md5($simplify->myResult['storageWidget']['selected']); ?>';
</script>
<?php
  }
} elseif (!$simplify->myResult['ajax']) echo '<div class="not_found">Файлы не найдены.</div>';

?>