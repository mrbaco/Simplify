<?php

if (!defined('SIMPLIFY')) exit;

$cache = new cache(array (
  'id' => 'agents' . ($simplify->myResult['cp'] === true ? 'cp' : ''),
  'group' => 'agents'
));

if (!$cache->result) {
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'agents', array ('*'), array ());
  if ($sql) {
?>
<div class="page">
  <?php if ($simplify->myResult['cp'] !== true) echo '<h1>Наша команда</h1>'; ?>
  <div class="elements">
    <?php
    while ($o = $sql->fetch_assoc()) {
      $o['IMAGES'] = unserialize($o['IMAGES']);
    ?>
    <div class="agent">
      <div class="photo" style="background-image: url('<?php echo is_file(ROOT . $o['IMAGES'][0]) ? $o['IMAGES'][0] : '/modules/realty/images/nophoto.png'; ?>');">
        <div class="phone"><?php echo $o['PHONE']; ?></div>
        <?php if ($simplify->myResult['cp'] === true) { ?>
        <div class="control">
          <a class="edit" href="<?php echo $simplify->cp->getCurrentURL(array ('edit' => $o['ID'], 'remove' => null)); ?>">правка</a>
          <a class="remove" href="<?php echo $simplify->cp->getCurrentURL(array ('edit' => null, 'remove' => $o['ID'])); ?>">x</a>
        </div>
        <?php } ?>
      </div>
      <div class="line">
        <div class="title"><?php echo $o['NAME']; ?></div>
      </div>
    </div>
    <?php } ?>
    <div class="clear"></div>
  </div>
</div>
<?php
  }
  
  if ($sql->num_rows == 0) echo '<p>Агенты не найдены.</p>';

  $cache->create();
}
?>