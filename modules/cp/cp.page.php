<?php if (!defined('SIMPLIFY')) exit; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Simplify / <?php echo $simplify->title; ?></title>
    <link rel="shortcut icon" href="/modules/cp/images/favicon.ico" />
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
    <div id="<?php echo $simplify->myResult['blockName']; ?>">
      <div id="header">
        <?php if ($simplify->cp->userGroup) { ?>
        <div id="menu_button" class="m"></div>
        <?php } ?>
        <a href="/cp" id="logotype">
          Simplify<br />
          <span>Панель&nbsp;управления</span>
        </a>
        <?php if ($simplify->cp->userGroup) { ?>
        <div id="top_menu">
          <span id="user">
            <?php echo htmlspecialchars($_COOKIE['login']); ?>
          </span>
          <a href="/" target="blank" class="icon home" title="На сайт"></a>
          <?php
            if ($simplify->cp->userGroup == 'admin') {
              if ($simplify->mdl->isModule('settings')) {
          ?>
          <a href="?m=settings" class="icon settings" title="Настройки системы"></a>
            <?php } ?>
          <a href="?clearCache" class="icon refresh" title="Очистить кэш"></a>
          <?php } ?>
          <a href="http://mrbaco.ru/simplify/userhelp" class="icon help" title="Поддержка"></a>
          <a href="?logout" class="icon logout" title="Выход"></a>
        </div>
        <?php } ?>
        <div class="clear"></div>
      </div>
      <div id="wrapper">
        <?php if ($simplify->cp->userGroup) { ?>
        <div id="menu">
          <?php echo $simplify->myResult['menu'] . PHP_EOL; ?>
        </div>
        <?php } ?>
        <div id="content"<?php echo $simplify->myResult['menu'] == '' ? ' style="margin-left: 0;"' : ''; ?>>
          <?php if ($simplify->cp->userGroup) echo '<h1>' . $simplify->title . '</h1>'; ?>
          <div class="clear"></div>
          <?php echo $simplify->content . PHP_EOL; ?>
        </div>
      </div>
      <div id="copyright">
        <a href="http://mrbaco.ru"><img src="/modules/cp/images/copyright.png" alt="BaCo LAB" /></a>
      </div>
    </div>
    <div id="overlay"></div>
  </body>
</html>