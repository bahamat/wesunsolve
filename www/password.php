<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $lo = $lm->o_login;
 if (!$lm->isLogged) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }
 if (isset($_POST['save'])) {
    if (!isset($_POST['password']) || !isset($_POST['password2']) || empty($_POST['password']) || empty($_POST['password2'])) {
      $content = new Template("./tpl/password.tpl");
      $content->set("login", $lm->o_login->username);
      $content->set("error", "Please check your new password and its confirmation");
      goto screen;
    }
    $pass = $_POST['password'];
    $pass2 = $_POST['password2'];
    if (strcmp($pass,$pass2)) {
      $content = new Template("./tpl/password.tpl");
      $content->set("error", "Please check your new password and its confirmation");
      $content->set("login", $lm->o_login->username);
      goto screen;
    }
    $lm->o_login->password = md5($pass);
    $lm->o_login->update();
    $content = new Template ("./tpl/message.tpl");
    $content->set("msg", "Your new password has been succesfully saved!");
    goto screen;
  }
 $content = new Template("./tpl/password.tpl");
 $content->set("login", $lo->username);

screen:
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head->set("title", "Change password for We Sun Solve!");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
