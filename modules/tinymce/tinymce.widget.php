<?php if (!defined('SIMPLIFY')) exit; ?>
<textarea name="<?php echo $simplify->myResult['tinymce']['name']; ?>" id="<?php echo $simplify->myResult['tinymce']['id']; ?>"><?php echo $simplify->myResult['tinymce']['content']; ?></textarea>
<script type="text/javascript">
  tinymce.init({
    selector: '#<?php echo $simplify->myResult['tinymce']['id']; ?>',
    <?php
      foreach ($simplify->myResult['tinymce'] as $param => $value) {
        if ($param == 'id' || $param == 'name' || $param == 'content') continue;
        echo $param . ': ' . (is_numeric($value) ? $value : (is_string($value) ? '\'' . $value . '\'' : (is_bool($value) ? ($value ? 'true' : 'false') : 'null'))) . ',' . PHP_EOL;
      }
    ?>
  });
</script>