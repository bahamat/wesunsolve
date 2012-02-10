<?php
  require_once("../libs/config.inc.php");
  require_once("../libs/autoload.lib.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

  $m = mysqlCM::getInstance();
  if ($m->connect()) {
   HTTP::getInstance()->errMysql();
  }
  $lm = loginCM::getInstance();
  $lm->startSession();
  if (isset($lm->o_login) && $lm->o_login) {
    $error = "You must not be already signed in to reset your password...";
    $content = new Template("./tpl/error.tpl");
    $content->set("error", $error);
    goto screen;
  }

  if (!isset($_GET['c']) && !isset($_POST['c'])) {
    $content = new Template("./tpl/error.tpl");
    $content->set("error", "No reset code found.. what are you doing here ?!");
    goto screen;
  }

  $c = null;
  if (isset($_GET['c']) && !empty($_GET['c'])) $c = $_GET['c'];
  if (isset($_POST['c']) && !empty($_POST['c'])) $c = $_POST['c'];
  $co = new UForgetp();
  $co->code = $c;
  if ($co->fetchFromField("code")) {
    $content = new Template("./tpl/error.tpl");
    $content->set("error", "This reset code hasn't been found inside the database...");
    goto screen;
  }

  $l = new Login($co->id_login);
  $l->fetchFromId();

  if (!isset($_POST['save'])) {
    $content = new Template("./tpl/resetpass.tpl");
    $content->set("c", $c);
    $content->set("login", $l->username);
    goto screen;
  }

  if (!isset($_POST["password2"]) || !isset($_POST["password"]) ||
      empty($_POST["password"]) || empty($_POST["password2"])) {

    $content = new Template("./tpl/resetpass.tpl");
    $content->set("error", "Missing field");
    $content->set("login", $l->username);
    $content->set("c", $c);
    goto screen;
  } else {
    if (strcmp($_POST["password2"], $_POST["password"])) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "Please check password and its confirmation");
      $content->set("login", $l->username);
      $content->set("c", $c);
      goto screen;
    }
    if (strlen($_POST['password']) < 6) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "Password must be at least 6 characters long");
      $content->set("login", $l->username);
      $content->set("c", $c);
      goto screen;
    }
    $l->password = md5($_POST["password"]);
    $l->update();
    $l->resetPasswordcode($co);
    IrcMsg::add("[WWW] User used reset code: ".$l->username, MSG_ADM);
    $content = new Template("./tpl/message.tpl");
    $content->set("msg", "Your password has been successfully reset, you can now login.");
  }


screen:
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", "Register yourself at We Sun Solve!");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);
  $page->set("content", $content);
  echo $page->fetch();
  $m->disconnect();
?>
