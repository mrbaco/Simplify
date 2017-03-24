<?php if (!defined('SIMPLIFY')) exit; ?>
<h3><?php echo $simplify->myResult['group']['title']; ?></h3>
<?php if (!sizeof($simplify->myResult['group']['submenu'])) return; ?>
<ul>
<?php foreach ($simplify->myResult['group']['submenu'] as $key => $group) { ?>
  <li<?php echo $_GET['m'] == $simplify->myResult['module'] && $_GET['g'] == $simplify->myResult['key'] && $_GET['p'] == $key ? ' class="active"' : ''; ?>>
    <a<?php echo $group['icon'] ? ' style="background-image: url(\'' . $group['icon'] . '\');"' : ''; ?> href="<?php echo '?m=' . $simplify->myResult['module'] . '&g=' . $simplify->myResult['key'] . '&p=' . $key; ?>">
      <?php echo $group['title']; ?>
    </a>
  </li>
<?php } ?>
</ul>