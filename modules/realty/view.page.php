<?php

if (!defined('SIMPLIFY')) exit;

if (sizeof($simplify->myResult['realty']['sql_params'])) {
  $conditions = ' WHERE ';
  foreach ($simplify->myResult['realty']['sql_params'] as $key => $value) {
    $conditions .= '`t1`.`' . $key . '` = "' . $value . '" AND ';
  }
  $conditions = substr($conditions, 0, -5);
}

$sql = $conditions . ' ORDER BY `t1`.`DATE` DESC;';

if ($simplify->myResult['realty']['sql'] == 1) {
  $sql = 'SELECT `t1`.`ID`, `t1`.`LINK`, `t1`.`TITLE`, `t1`.`CONTENT`
          FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t1`' . $sql;
  $csql = 'SELECT COUNT(`t1`.`ID`) FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t1`' . $conditions;
} else {
  $sql =  'SELECT `t1`.`ID`, `t1`.`LINK`, `t1`.`ACTION`, `t1`.`TYPE`, `t1`.`ADDRESS`,
                 `t1`.`IMAGES`, `t1`.`ROOMS`, `t1`.`FLOOR`, `t1`.`HOUSE_FLOORS`,
                 `t1`.`SQUERE`, `t1`.`PRICE`, `t1`.`COMISSION`,
                 (
                   SELECT `t2`.`TITLE`
                   FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                   WHERE `t2`.`ID` = `t1`.`SUBCATEGORY`
                 ) AS `SUBCATEGORY`,
                 (
                   SELECT `t3`.`NAME`
                   FROM `' . $simplify->params['database']['prefix'] . 'agents` AS `t3`
                   WHERE `t3`.`ID` = `t1`.`AGENT`
                 ) AS `AGENT`,
                 (
                   SELECT `t2`.`TITLE`
                   FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                   WHERE `t2`.`ID` = `t1`.`AREA`
                 ) AS `AREA`,
                 (
                   SELECT `t2`.`TITLE`
                   FROM `' . $simplify->params['database']['prefix'] . 'categories` AS `t2`
                   WHERE `t2`.`ID` = `t1`.`TYPE`
                 ) AS `TYPE_NAME`
           FROM `' . $simplify->params['database']['prefix'] . 'objects` AS `t1`' . $sql;
  $csql = 'SELECT COUNT(`t1`.`ID`) FROM `' . $simplify->params['database']['prefix'] . 'objects` AS `t1`' . $conditions;
}

$simplify->myResult['realty']['cache_prefix'] .= $simplify->myResult['cp'] === true ? 'cp' : '';

$simplify->paging->init($sql, $csql, $_GET['page']);

$cache = new cache(array (
  'id' => 'view' . $simplify->paging->number . '_' . $simplify->paging->page . '_' . $simplify->myResult['realty']['cache_prefix'],
  'group' => ($simplify->myResult['realty']['sql'] == 1 ? 'categories' : 'objects')
));

if (!$cache->result) {
  $simplify->paging->get();
  
  if ($simplify->paging->query->num_rows) {
?>
<div class="elements">
<?php
  while ($e = $simplify->paging->query->fetch_assoc()) {
    if ($simplify->myResult['realty']['sql'] == 1) {
      if (strpos($e['CONTENT'], '<!-- pagebreak -->') !== false) {
        $e['CONTENT'] = explode('<!-- pagebreak -->', $e['CONTENT']);
        $e['CONTENT'] = $e['CONTENT'][0];
      }
      ?>
      <div class="category">
        <h1><?php echo $e['TITLE']; ?></h1>
        <div class="description"><?php echo $e['CONTENT']; ?></div>
        <a href="<?php echo $simplify->cp->getCurrentURL() . $e['ID'] . '-' . $e['LINK'] . '/'; ?>">Подробнее</a>
      </div>
      <?php
      continue;
    }
    $e['IMAGES'] = unserialize($e['IMAGES']);
    $e['IMAGES'] = $e['IMAGES'][0];
    
    $e['NAME']  = $e['TYPE_NAME'] == 'квартира' ? $e['ROOMS'] . ' к. ' : '';
    $e['NAME'] .= $e['TYPE_NAME'];
    $e['NAME'] .= $e['TYPE_NAME'] != 'участок' ? ', ' . $e['FLOOR'] . '/' . $e['HOUSE_FLOORS'] . ' эт.' : ', ' . $e['SQUERE'] . ' сот.';
    
    $e['NAME'] = $simplify->catalog->ucfirst_utf8($e['NAME']);
    
    ?>
        <div class="object">
          <div class="photo" style="background-image: url('<?php echo is_file(ROOT . $e['IMAGES']) ? $e['IMAGES'] : '/modules/realty/images/nophoto.png'; ?>');">
            <?php if ($e['SUBCATEGORY'] != '') { ?><div class="subcategory"><?php echo $e['SUBCATEGORY']; ?></div><?php } ?>
            <div class="action <?php echo $e['ACTION'] == 1 ? 'sell' : 'rent'; ?>"><?php echo $e['ACTION'] == 1 ? 'продажа' : 'аренда'; ?></div>
            <?php if ($simplify->myResult['cp'] === true) { ?>
            <div class="agent_name"><?php echo $e['AGENT']; ?></div>
            <div class="control">
              <a class="edit" href="<?php echo $simplify->cp->getCurrentURL(array ('p' => 'objects', 'edit' => $e['ID'])); ?>">правка</a>
              <a class="remove" href="<?php echo $simplify->cp->getCurrentURL(array ('remove' => $e['ID'])); ?>">x</a>
            </div>
            <?php } ?>
          </div>
          <div class="info">
            <div class="line">
              <div class="title">
                <?php echo $e['NAME']; ?><br />
                <?php echo $e['ADDRESS']; ?>
              </div>
              <div class="price"><?php echo $e['ACTION'] == 1 ? number_format($e['PRICE'] + $e['COMISSION'], 0, ',', ' ') : number_format($e['PRICE'], 0, ',', ' ');  ?> <span class="rouble">o</span><?php echo $e['ACTION'] == 2 ? '/мес.' : ''; ?></div>
              <div class="clear"></div>
            </div>
            <div class="icons">
              <div class="square"><span><?php echo number_format($e['SQUERE'], 1, ',', ' '); ?> м<sup>2</sup></span></div>
              <div class="district"><span><?php echo $e['AREA']; ?></span></div>
            </div>
            <a href="/<?php echo $e['ID'] . '-' . $simplify->catalog->createURL($e['NAME']) . '.html'; ?>" class="full">Подробнее</a>
            <div class="clear"></div>
          </div>
          <div class="clear"></div>
        </div>
<?php
    }
?>
  <div class="clear"></div>
</div>
<div class="clear"></div>
<?php
    $links = $simplify->paging->links();
    if ($simplify->paging->page > 1) {
?>
<div class="navigation">
  <?php
  
  
    
    if ($simplify->paging->page > 1) echo '<a href="' . $simplify->cp->getCurrentURL(array ('page' => 1)) . '">в начало</a>';
    else echo '<span>в начало</span>';
    
    foreach ($links['pages'] as $value) {
      if ($value[1] == false) echo '<span>' . $value[0] . '</span>';
      else echo '<a href="' . $simplify->cp->getCurrentURL(array ('page' => $value[0])) . '">' . $value[0] . '</a>';
    }
    
    if ($simplify->paging->page != $simplify->paging->number) echo '<a href="' . $simplify->cp->getCurrentURL(array ('page' => $simplify->paging->number)) . '">в конец</a>';
    else echo '<span>в конец</span>';
  
  ?>
</div>
<?php
    }
  } else echo '<div id="page"><div id="page_wrapper">Ничего не найдено.</div></div>';
  
  $cache->create();
}

?>