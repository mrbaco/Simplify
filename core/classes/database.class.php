<?php

/*
 * Simplify 4.5 Evo
 * 
 * (c) Baricov Ltd. 2017
 * http://mrbaco.ru/simplify/
 * 
 */

/*
 * Осуществление работы с базой данных
 * Расширение стандартного класса mysqli
 *
 */
class database extends mysqli {
  // Количество запросов (query) к базе данных
  public $requestsNumber = 0;
  
  /*
   * Создание подключения к базе данных
   *
   * db - параметры подключения (массив данных)
   * logging - ссылка на объект для работы с журналом
   *
   */
  public function __construct($db = array ()) {
    @parent::__construct($db['host'], $db['username'], $db['password'], $db['database']);
  }
  
  /*
   * Расширение стандартной функции выполнения запроса к базе данных
   *
   */
  public function query($query, $resultmode = MYSQLI_STORE_RESULT) {
    if (empty($query)) return false;
    
    global $simplify;
    $simplify->logging->add('db', 'query', $query);
    
    $this->requestsNumber += 1;
    $query = parent::query($query, $resultmode);
    
    if ($this->error) {
      $simplify->logging->add('db', 'sql error', $this->error);
    }
    
    return $query;
  }
  
  /*
   * Выполнение стандартной операции INSERT
   *
   */
  public function insert($table, $values, $output = false) {
    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($values)) . '`) VALUES ("' . implode('", "', $values) . '");';
    
    if ($output) return $sql;
    return $this->query($sql);
  }
  
  /*
   * Выполнение стандартной операции UPDATE
   *
   */
  public function update($table, $values, $where, $output = false) {
    foreach ($where as $k => $v) {
      if (strtolower(substr($v, 0, 4)) == 'like') $v = 'LIKE "' . trim(substr($v, 4)) . '"';
      elseif (substr($v, 0, 2) == '!=') $v = '!= "' . trim(substr($v, 2)) . '"';
      else $v = '= "' . $v . '"';
      $where[$k] = '`' . $k . '` ' . $v;
    }
    foreach ($values as $k => $v) $values[$k] = '`' . $k . '` = "' . $v . '"';
    
    $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $values) . (sizeof($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ';';
    
    if ($output) return $sql;
    return $this->query($sql);
  }
  
  /*
   * Выполнение стандартной операции DELETE
   *
   */
  public function delete($table, $where, $output = false) {
    foreach ($where as $k => $v) {
      if (strtolower(substr($v, 0, 4)) == 'like') $v = 'LIKE "' . trim(substr($v, 4)) . '"';
      elseif (substr($v, 0, 2) == '!=') $v = '!= "' . trim(substr($v, 2)) . '"';
      else $v = '= "' . $v . '"';
      $where[$k] = '`' . $k . '` ' . $v;
    }
    
    $sql = 'DELETE FROM `' . $table . '`' . (sizeof($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ';';
    
    if ($output) return $sql;
    return $this->query($sql);
  }
  
  /*
   * Выполнение стандартной операции SELECT
   *
   */
  public function select($table, $values, $where, $output = false) {
    foreach ($where as $k => $v) {
      if (strtolower(substr($v, 0, 4)) == 'like') $v = 'LIKE "' . trim(substr($v, 4)) . '"';
      elseif (substr($v, 0, 2) == '!=') $v = '!= "' . trim(substr($v, 2)) . '"';
      else $v = '= "' . $v . '"';
      $where[$k] = '`' . $k . '` ' . $v;
    }
    
    $sql = 'SELECT ' . ($values[0] == '*' ? '*' : '`' . implode('`, `', $values) . '`') . ' FROM `' . $table . '`' . (sizeof($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ';';
    
    if ($output) return $sql;
    return $this->query($sql);
  }
}

?>