<?php if (!defined('SIMPLIFY')) exit; ?>
<div class="upload">
  <label>
    <input onchange="file_uploads();" type="file"<?php echo $simplify->myResult['storageWidget']['multiple'] ? ' multiple="multiple"' : ''; ?> name="files[]" id="upload" />
    <div class="button">Прикрепить&nbsp;файл<?php echo $simplify->myResult['storageWidget']['multiple'] ? 'ы' : ''; ?></div>
  </label>
</div>
<div id="storage">
  <?php echo sizeof($simplify->storage->page) ? $simplify->tpl->load('storage.element', 'storage') : ''; ?>
</div>
<div class="clear"></div>
<script type="text/javascript">
  var file_uploads = function () {
    var files = e('upload').files;
    var form = new FormData();
    
    for(var i = 0; i < files.length; i++) {
      form.append("files[]", files[i]); 
    }
    
    form.append('relativePath', encodeURIComponent('<?php echo $simplify->myResult['storageWidget']['relativePath']; ?>'));
    form.append('original', <?php echo $simplify->myResult['storageWidget']['original'] ? 1 : 0; ?>);
    form.append('maxNumber', <?php echo (int)$simplify->myResult['storageWidget']['maxNumber']; ?>);
    form.append('full', <?php echo (int)$simplify->myResult['storageWidget']['full']; ?>);
    
    var request = new XMLHttpRequest();
    
    request.open('POST', '<?php echo $simplify->cp->getCurrentURL(array ('m' => 'storage', 'ajax' => true)); ?>', true);
    request.send(form);
    
    request.onreadystatechange = function() {
      if (request.readyState != 4) return;
      if (request.status == 200) {
        if (e('ajax_error')) e('ajax_error').style.display = 'none';
        
        e('storage').innerHTML <?php echo $simplify->myResult['storageWidget']['multiple'] ? '+=' : '='; ?> request.responseText;
        e('upload').parentNode.parentNode.innerHTML = e('upload').parentNode.parentNode.innerHTML;
      }
    };
  };
</script>