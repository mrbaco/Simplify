<?php

if (!defined('SIMPLIFY')) exit;

$cache = new cache(array (
  'id' => 'agents_list',
  'group' => 'realty',
  'output' => false,
  'array' => true
));

if (!$cache->result) {
  $agentsList = array ();
  
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'agents', array ('ID', 'NAME', 'PHONE'), array ());
  if ($sql) while ($e = $sql->fetch_assoc()) $agentsList[$e['ID']] = $e['NAME'] . ' (' . $e['PHONE'] . ')';
  if (!$sql->num_rows) $agentsList[0] = 'Агентов в списке нет';
  
  $cache->create($agentsList);
}

$agentsList = $cache->result;

if (isset($_GET['edit'])) {
  $sql = $simplify->db->select($simplify->params['database']['prefix'] . 'objects', array ('*'), array ('ID' => (int)$_GET['edit']));
  if ($sql) {
    $row = $sql->fetch_assoc();
    
    $row['PRICE'] = number_format($row['PRICE'], 0, ',', ' ');
    $row['COMISSION'] = number_format($row['COMISSION'], 0, ',', ' ');
    
    $row['PARAMS'] = unserialize($row['PARAMS']);
    $row['IMAGES'] = unserialize($row['IMAGES']);
    
    $row['SELECTED'] = $row['IMAGES'][0];
    
    $simplify->htmlForms->preset(array_change_key_case($row));
  }
}

echo $simplify->htmlForms->createAdvancedForm();

$object = $simplify->catalog->categoryArray('объекты');
$categories = $simplify->catalog->categoryArray('меню сайта');
$subcategory = $simplify->catalog->categoryArray('новостройки');
$districts = $simplify->catalog->categoryArray('районы города');
$chb_params = $simplify->catalog->categoryArray('съемная квартира');
$house_types = $simplify->catalog->categoryArray('типы дома');

$object['tree'] = is_array($object['tree']) ? array (0 => 'Не выбрано') + $object['tree'] : $object['tree'];
$categories['tree'] = is_array($categories['tree']) ? array (0 => 'Не выбрано') + $categories['tree'] : $categories['tree'];
$subcategory['tree'] = is_array($subcategory['tree']) ? array (0 => 'Не выбрано') + $subcategory['tree'] : $subcategory['tree'];
$districts['tree'] = is_array($districts['tree']) ? array (0 => 'Не выбрано') + $districts['tree'] : $districts['tree'];
$house_types['tree'] = is_array($house_types['tree']) ? array (0 => 'Не выбрано') + $house_types['tree'] : $house_types['tree'];

?>
<div class="block3">
  <?php
  
  echo $simplify->htmlForms->createForm(array (
    'Тип элемента' => array (
      'type' => 'select',
      'name' => 'action',
      'id' => 'action_select',
      'options' => array (
        1 => 'продам',
        2 => 'сдам'
      )
    ),
    'Объект' => array (
      'type' => 'select',
      'name' => 'type',
      'id' => 'type_select',
      'options' => $object['tree']
    ),
    'Категория размещения' => array (
      'type' => 'select',
      'name' => 'category',
      'id' => 'property_select',
      'options' => $categories['tree']
    )
  ));
  
  ?>
  <div id="subcat"<?php echo $categories['tree'][$simplify->htmlForms->preset['category']] != 'Новостройки' ? ' style="display: none;"' : ''; ?>>
    <?php
    
    echo $simplify->htmlForms->createForm(array (
      'Жилой комплекс' => array (
        'type' => 'select',
        'name' => 'subcategory',
        'options' => $subcategory['tree']
      )
    ));
    
    ?>
  </div>
  <div id="lease"<?php echo $simplify->htmlForms->preset['action'] != 2 ? ' style="display: none;"' : ''; ?>>
    <?php
    
    echo $simplify->htmlForms->createForm(array (
      'Срок аренды' => array (
        'type' => 'select',
        'name' => 'lease',
        'id' => 'lease_select',
        'options' => array (
          1 =>'на длительный срок',
          2 => 'посуточно'
        )
      )
    ));
    
    ?>
  </div>
</div>
<div id="reserve"<?php echo $simplify->htmlForms->preset['lease'] != 2 ? ' style="display: none;"' : ''; ?>>
  <h4>График бронирования</h4>
  <div class="block3">
    <?php echo $simplify->flatpickr->widgetFlatpickr(array ('name' => 'reserved', 'value' => $simplify->htmlForms->preset['reserved'])); ?>
    <p>
      <small>
        Необходимо выбрать даты, когда квартира забронирована.<br />
        В дальнейшем менять даты можно будет при редактировании объекта.
      </small>
    </p>
  </div>
</div>
<h4>Местоположение объекта</h4>
<div class="block3">
  <?php
  
  echo $simplify->htmlForms->createForm(array (
    'Город' => array (
      'type' => 'text',
      'name' => 'city',
      'value' => 'Череповец',
      'id' => 'city'
    ),
    'Район города' => array (
      'type' => 'select',
      'name' => 'area',
      'options' => $districts['tree']
    ),
    'Адрес' => array (
      'type' => 'text',
      'name' => 'address',
      'id' => 'address',
      'inline' => true
    ),
    '&rarr;' => array (
      'type' => 'button',
      'inline' => false,
      'class' => 'inline',
      'id' => 'search'
    ),
    '' => array (
      'type' => 'hidden',
      'name' => 'latlong',
      'id' => 'latlong'
    )
  ));
  
  ?>
  <p>Переместите метку на объект:</p>
  <div id="map"></div>
  <script type="text/javascript">
    ymaps.ready(function () {
      createCPMap('map');
    });
  </script>
</div>
<div id="checkbox_params"<?php echo $simplify->htmlForms->preset['action'] != 2 ? ' style="display: none;"' : ''; ?>>
  <h4>Параметры</h4>
  <div class="block2">
    <?php
    
    unset($chb_params['tree'][0]);
    
    foreach ($chb_params['tree'] as $key => $value) {
      if ($value == 'separator') {
    ?>
      </div>
      <div class="block2">
    <?php
        continue;
      }
      
      if ($value == 'end') {
    ?>
      </div>
      <div class="clear"></div>
      <hr />
      <div class="block2">
    <?php
      continue;
      }
      
      echo $simplify->htmlForms->createForm(array (
        $value => array (
          'type' => 'checkbox',
          'name' => 'chb[' . $key . ']',
          'checked' => isset($simplify->htmlForms->preset['params'][$key])
        )
      ));
    }
    
    ?>
  </div>
</div>
<div class="clear"></div>
<h4>Фотографии</h4>
<div class="block3">
  <?php
  
  echo $simplify->storage->storageWidget(array (
    'selected' => $simplify->htmlForms->preset['selected'],
    'relativePath' => '/realty/',
    'preset' => $simplify->htmlForms->preset['images'],
    'original' => false,
    'maxNumber' => 12,
    'full' => true
  ));
  
  ?>
</div>
<div class="clear"></div>
<h4>Объявление</h4>
<div class="block3">
  <div id="rooms_number"<?php echo $object['tree'][$simplify->htmlForms->preset['type']] != 'квартира' ? ' style="display: none;"' : ''; ?>>
  <?php
  
  echo $simplify->htmlForms->createForm(array (
    'Количество комнат' => array (
      'type' => 'text',
      'name' => 'rooms',
      'size' => 2
    )
  ));
  
  ?>
  </div>
  <?php
  
  echo $simplify->htmlForms->createForm(array (
    'Тип дома' => array (
      'type' => 'select',
      'name' => 'house_type',
      'options' => $house_types['tree']
    ),
    'Этаж' => array (
      'type' => 'text',
      'name' => 'floor',
      'size' => 3,
      'inline' => true
    ),
    'Всего этажей' => array (
      'type' => 'text',
      'name' => 'house_floors',
      'size' => 3,
      'inline' => false
    ),
    'Площадь' => array (
      'type' => 'text',
      'name' => 'squere',
      'id' => 'squere',
      'size' => 2,
      'hint' => '<span id="s_unit"></span>'
    ),
    'Описание' => array (
      'type' => 'textarea',
      'name' => 'description'
    ),
    'Агент' => array (
      'type' => 'select',
      'name' => 'agent',
      'options' => $agentsList
    ),
    'Стоимость' => array (
      'type' => 'text',
      'name' => 'price',
      'hint' => 'в рублях (без агентской комиссии)',
      'id' => 'price_text',
      'size' => 6
    ),
    'Комиссия' => array (
      'type' => 'text',
      'name' => 'comission',
      'hint' => 'в рублях',
      'id' => 'comission_text',
      'size' => 6
    ),
    (isset($_GET['edit']) ? 'Изменить' : 'Создать') . ' объявление' => array (
      'type' => 'submit'
    )
  ));
  
  ?>
  <div class="clear"></div>
</div>
<?php echo $simplify->htmlForms->closeAdvancedForm(); ?>