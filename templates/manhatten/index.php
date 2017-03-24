<?php if (!defined('SIMPLIFY')) exit; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Манхэттен / <?php echo $simplify->title != '' ? $simplify->title : 'Агенство недвижимости - Все операции с недвижимостью'; ?></title>
    <link rel="apple-touch-icon" sizes="57x57" href="/templates/manhatten/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/templates/manhatten/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/templates/manhatten/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/templates/manhatten/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/templates/manhatten/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/templates/manhatten/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/templates/manhatten/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/templates/manhatten/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/templates/manhatten/images/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/templates/manhatten/images/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/templates/manhatten/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/templates/manhatten/images/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/templates/manhatten/images/favicon/favicon-16x16.png">
    <link rel="shortcut icon" href="/templates/manhatten/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/templates/manhatten/images/favicon/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/templates/manhatten/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/templates/manhatten/images/favicon/ms-icon-144x144.png">
    <link rel="stylesheet" type="text/css" href="/templates/manhatten/style.css">
    <link rel="stylesheet" type="text/css" href="/modules/paging/style.css">
    <meta name="theme-color" content="#ffffff">
    <script type="text/javascript" src="/templates/manhatten/script.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <?php
      if (sizeof($simplify->tpl->metaTags)) foreach ($simplify->tpl->metaTags as $name => $content) {
        echo '    <meta name="' . $name . '" content="' . $content . '" />' . PHP_EOL;
      }
      
      if (sizeof($simplify->tpl->scripts)) foreach ($simplify->tpl->scripts as $path) {
        echo '    <script type="text/javascript" src="' . $path . '"></script>' . PHP_EOL;
      }
      
      if (sizeof($simplify->tpl->styles)) foreach ($simplify->tpl->styles as $path) {
        echo '    <link rel="stylesheet" type="text/css" href="' . $path . '" />' . PHP_EOL;
      }
    ?>
  </head>
  <body>
    <div id="header">
      <div id="top">
        <div class="logotype" href="">
          <div>
            <a href="/">
              агенство недвижимости<br />
              <span>манхэттен</span>
            </a>
          </div>
        </div>
        <div class="menu">
          <?php
          
          $menu = $simplify->catalog->categoryArray('меню сайта', true);
          if (sizeof($menu)) foreach ($menu['tree'] as $id => $e) {
            echo '<a class="element" href="/' . $id . '-' . $e['LINK'] . '/">' . $e['TITLE'] . '</a>';
          }
          
          ?>
        </div>
        <div class="clear"></div>
      </div>
      <?php if ($simplify->title == '') { ?>
      <div id="ads" class="f">
        <div id="news">
          <ul id="slides">
          <?php
          
          $s = true;
          $slides = $simplify->catalog->categoryArray('слайдшоу', true);
          if (sizeof($slides)) foreach ($slides['tree'] as $id => $e) {
            echo '<li class="slide' . ($s ? ' showing' : '') . '">' . $e['CONTENT'] . '</li>';
            $s = false;
          }
          
          ?>
          </ul>
        </div>
        <div class="f">
          <?php
          
          echo $simplify->yamap->yamapWidget(array (
            'json_data_url' => '/latlongs/'
          ));
          
          ?>
        </div>
        <div class="clear"></div>
      </div>
      <?php } ?>
    </div>
    <div id="content">
      <div id="sidebar">
        <?php
        
        $banners = $simplify->catalog->categoryArray('баннеры сайта', true);
        if (sizeof($banners)) foreach ($banners['tree'] as $id => $e) {
          echo '<div>' . $e['CONTENT'] . '</div>' . PHP_EOL;
        }
        
        ?>
      </div>
      <?php echo $simplify->content; ?>
      <div class="clear"></div>
    </div>
    <div id="footer">
      <div id="wrapper">
        <div class="cols">
          <div class="col">
            <h1>Меню</h1>
            <?php
            
            if (sizeof($menu)) foreach ($menu['tree'] as $id => $e) {
              echo '<a class="element" href="/' . $id . '-' . $e['LINK'] . '/">' . $e['TITLE'] . '</a><br />';
            }
            
            ?>
          </div>
          <div class="col right copyright">
            <a href="http://mrbaco.ru/" target="blank">
              <img src="/modules/cp/images/copyright2.png" /><br />
              создание и поддержка сайта
            </a>
          </div>
          <div class="clear"></div>
        </div>
      </div>
    </div>
  </body>
</html>