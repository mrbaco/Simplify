<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('paging', array (
  'name' => 'Постраничная навигация',
  'description' => 'Обеспечивает создание постраничной навигации при выводе элементов из базы данных',
  'menu' => array (
    'main' => array (
      'title' => 'Постраничная навигация',
      'submenu' => array (
				'settings' => array (
					'icon' => '/modules/paging/images/pagination.png',
					'title' => 'Управление'
				)
			)
    )
  ),
	'sort' => 400
));

class paging {
	public $elements_number;
  public $links_number;
	
	public $strings_number;
	public $number;
	public $start;
	public $page;
	public $sql;
	
	public $query;
  
  public function cp ($g, $p) {
    global $simplify;
    
    $simplify->htmlForms->preset($simplify->mdl->modules['paging']['params']['numbers']);
    
    if (isset($_POST['elements_number'])) {
      $_POST['elements_number'] = (int)$_POST['elements_number'];
      $_POST['links_number'] = (int)$_POST['links_number'];
      
      if ($_POST['elements_number'] == 0 || $_POST['links_number'] == 0) $simplify->htmlForms->error('Поля должны быть заполнены.');
      elseif ($_POST['elements_number'] < 1 || $_POST['links_number'] < 1) $simplify->htmlForms->error('Задано неверное значение.');
      else {
        $simplify->mdl->setParams('paging', array (
          'numbers' => array (
            'elements_number' => $_POST['elements_number'],
            'links_number' => $_POST['links_number']
          )
        ));
        
        $simplify->htmlForms->success('Данные сохранены.');
      }
      
      $simplify->htmlForms->preset($_POST);
    }
    
		$simplify->title = 'Настройки постраничной навигации';
		
		return $simplify->htmlForms->createForm(array (
			'Записей на странице' => array (
				'type' => 'text',
				'name' => 'elements_number',
				'inline' => true,
				'size' => 6
			),
			'Ссылок в обе стороны' => array (
				'type' => 'text',
				'name' => 'links_number',
				'inline' => false,
				'size' => 6
			),
			'Сохранить' => array (
				'type' => 'submit'
			)
		));
  }
	
	function __construct() {
    global $simplify;
    
		$this->elements_number = $simplify->mdl->modules['paging']['params']['numbers']['elements_number'];
		$this->links_number = $simplify->mdl->modules['paging']['params']['numbers']['links_number'];
    
    $this->elements_number = $this->elements_number < 1 ? 16 : $this->elements_number;
    $this->links_number = $this->links_number < 1 ? 4 : $this->links_number;
	}
	
	/*
   * Продвинутая инициализация навигации
   *
   * sql_query - сложный запрос на выборку из базы данных
   * sql_count - сложный запрос COUNT для подсчета общего количества выводимой информации (либо число выводимых элементов)
   * page - запрошенная страница
   *
   */
	public function init($sql_query, $sql_count, $page = false) {
		global $simplify;
		
    $simplify->tpl->addStyle('style.css', 'paging');
    
    if (!is_numeric($sql_count)) {
      $this->sql = $simplify->db->query($sql_count);
			
			if (!$this->sql) return false;
			
      $this->sql = $this->sql->fetch_row();
      $this->strings_number = $this->sql[0];
    } else $this->strings_number = $sql_count;
    
    if (substr($sql_query, strlen($sql_query) - 1) == ';') $sql_query = substr($sql_query, 0, strlen($sql_query) - 1);
		
		$this->number = $this->elements_number != 0 ? ceil($this->strings_number / $this->elements_number) : 0;
		
		if ($page < 1) $this->page = 1;
		elseif ($page > $this->number) $this->page = $this->number;
    else $this->page = (int)$page;
		
		$this->start = ($this->page - 1) * $this->elements_number;
		
		if ($this->start < 0) $this->start = 0;
    
		$this->sql = $sql_query . ($this->elements_number != 0 ? ' LIMIT ' . $this->start . ', ' . $this->elements_number : '');
		
		return $this->number;
	}
	
	// Простая инициализация навигации
	function sInit($arr = array ()) {
    
    $defaults = array (
      'table' => '',
      'count_by' => '',
      'page' => false,
      'fields' => array (),
      'params' => array (),
      'order_key' => '',
      'sort_type' => 'DESC',
      'limit' => false
    );
    
    $arr = array_merge($defaults, $arr);
    
    global $simplify;
    
    $query = '';
    
    if (sizeof($arr['params'])) {
      foreach ($arr['params'] as $key => $value) {
        $symbol = '=';
        
        $symbol_check = substr($key, strlen($key) - 1, 1);
        
        if ($symbol_check == '>' || $symbol_check == '<' || $symbol_check == '?') {
          $key = substr($key, 0, strlen($key) - 1);
          $symbol = $symbol_check;
          
          if ($symbol == '?') $symbol = 'LIKE';
        }
        
        $query .= '`' . $key . '` ' . $symbol . " '" . $value . "' AND ";
      }
      
      $query = ' WHERE ' . substr($query, 0, strlen($query) - 5);
    }
    
    $columns = '';
    
    if (sizeof($arr['fields'])) {
      foreach ($arr['fields'] as $value) $columns .= '`' . $value . '`, ';
      $columns = substr($columns, 0, strlen($columns) - 2);
    } else $columns = '*';
    
    if ($arr['order_key'] != '') $arr['order_key'] = ' ORDER BY `' . $arr['order_key'] . '` ' . ((strtolower($arr['sort_type']) == 'desc') ? 'DESC' : 'ASC');
    else $arr['order_key'] = '';
		
		$sql_count = $arr['limit'] !== false ? $arr['limit'] : 'SELECT COUNT(' . ($arr['count_by'] == '' ? $columns : '`' . $arr['count_by'] . '`') . ') FROM `' . $arr['table'] . '`' . $query . ';';
    $sql_query = 'SELECT ' . $columns . ' FROM `' . $arr['table'] . '`' . $query . $arr['order_key'];
    
    return $this->init($sql_query, $sql_count, $arr['page']);
	}
	
	function get() {
		global $simplify;
		
		$this->query = $simplify->db->query($this->sql);
	}
	
	// Создание ссылок
	function links() {
		$pagination['previous'] = ($this->page > 1) ? $this->page - 1 : false;
		
		$ln = $this->page - $this->links_number;
		if ($ln < 1) $ln = 1;
		
		$rn = $this->page + $this->links_number;
		if ($rn > $this->number) $rn = $this->number;
		
    for($i = $ln; $i <= $rn; $i++) $pagination['pages'][] = array ($i, ($i != $this->page) ? true : false);
		
		$pagination['next'] = ($this->page < $this->number) ? $this->page + 1 : false;
		
		$pagination['end'] = $this->number;
		
		return $pagination;
	}
}