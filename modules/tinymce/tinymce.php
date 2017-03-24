<?php

if (!defined('SIMPLIFY')) exit;

$simplify->init('tinymce', array (
  'name' => 'Website tinyMCE editor',
  'widgets' => array ('tinymce' => 'tinymceWidget')
));

class tinymce {
  public function tinymceWidget($arr = array ()) {
    global $simplify;
    
    $defaults = array (
      'id' => 'tinymce',
      'name' => 'tinymce',
      'plugins' => 'image table pagebreak wordcount code legacyoutput link paste textpattern autolink hr lists',
      'toolbar1' => 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist',
      'toolbar2' => 'outdent indent blockquote | link unlink image | forecolor backcolor | pagebreak code',
      'pagebreak_separator' => '<!-- pagebreak -->',
      'forced_root_block' => '',
      'language' => 'ru',
      'menubar' => false
    );
    
    $simplify->myResult['tinymce'] = array_merge($defaults, $arr);
    
    $simplify->tpl->addScript('tinymce.min.js', 'tinymce');
    
    return $simplify->tpl->load('tinymce.widget', 'tinymce');
  }
}