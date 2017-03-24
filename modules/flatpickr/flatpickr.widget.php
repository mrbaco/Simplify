<?php if (!defined('SIMPLIFY')) exit; ?>
  <input type="text" name="<?php echo $simplify->myResult['flatpickr']['name']; ?>"<?php echo $simplify->myResult['flatpickr']['value'] != '' ? ' value="' . htmlspecialchars($simplify->myResult['flatpickr']['value']) . '"' :''; ?> id="datepicker" />
  <div id="container"></div>
  <script type="text/javascript">
    document.getElementById('datepicker').flatpickr({
      onDayCreate: function (dObj, dStr, fp, dayElem) {
        if (dayElem.dateObj.getDay() % 6 === 0 &&
            (dayElem.className.indexOf('nextMonthDay') == -1) &&
            (dayElem.className.indexOf('prevMonthDay') == -1) &&
            (dayElem.className.indexOf('disabled') == -1)) dayElem.style.color = '#cc0000';
      },
      <?php
      if (sizeof($simplify->myResult['flatpickr']['disabled'])) {
        echo 'disable: [';
        foreach ($simplify->myResult['flatpickr']['disabled'] as $value) {
          echo '"' . trim($value) . '",';
        }
        echo '],';
      }
      
      foreach ($simplify->myResult['flatpickr'] as $param => $value) {
        if ($param == 'name' || $param == 'value' || $param == 'disabled') continue;
        echo $param . ': ' . (is_numeric($value) ? $value : (is_string($value) ? '\'' . $value . '\'' : (is_bool($value) ? ($value ? 'true' : 'false') : 'null'))) . ',' . PHP_EOL;
      }
      
      ?>
    });
  </script>