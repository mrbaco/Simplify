<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 *
 */

if (!defined('SIMPLIFY')) exit;

if (!sizeof($simplify->params) || $simplify->params['debug']) {
?>
<p>
  <h3>Simplify <?php echo SIMPLIFY_VERSION . ' ' . SIMPLIFY_CODENAME; ?></h3>
  Created in <?php echo round(microtime(true) - DEBUG, 2) * 100 ?> ms.<br />
  Database requests: <?php echo $simplify->db->requestsNumber ? $simplify->db->requestsNumber : 0; ?>.<br />
  Database errors: <?php echo $simplify->db->error != '' ? $simplify->db->error : 'not found.'; ?><br />
  Memory usage: <?php echo round(memory_get_usage(true) / 1024, 2); ?> Kb.
  <p>
    <code>
      <?php
        $i = 1;
        foreach ($simplify->logging->log as $value) {
          if (is_array($value)) {
            echo $i . ') <b>' . $value[0] . '</b>: <i>' . $value[1] . '</i> - ' . $value[2] . '<br />' . PHP_EOL;
            $i++;
          } else {
            echo '<br />' . PHP_EOL;
          }
        }
      ?>
    </code>
  </p>
</p>
<?php } ?>