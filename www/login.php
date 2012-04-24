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
  $error = '';

  if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
    $error .= 'The connection is not secured, consider using <a href="https://wesunsolve.net/login">HTTPS</a> to avoid possible eavesdropping.<br/>';
  }

  if (!isset($_POST['save'])) {
    $content = new Template("./tpl/login.tpl");
    goto screen;
  }

  if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    $content = new Template("./tpl/login.tpl");
    $content->set("error", "Missing field");
    $error .= "Missing field.<br/>";
    goto screen;
  } else {
    if (isset($_POST['keep']) && $_POST['keep']) {
      $keep = 1;
    } else {
      $keep = 0;
    }
    if (($rc = $lm->login($_POST["username"], $_POST["password"], $keep)) == -1) {
      $content = new Template("./tpl/login.tpl");
      $error .= "Error in either login or password<br/>";
      $f = new LoginFailed();
      $f->when = time();
      $f->login = $_POST["username"];
      $f->pass = $_POST["password"];
      $f->agent = $_SERVER['HTTP_USER_AGENT'];
      $f->ip = $_SERVER['REMOTE_ADDR'];
      $f->insert();
      IrcMsg::add("[WWW] Login failed: ".$f->login."/".$f->ip, MSG_ADM);
      goto screen;
    } else if ($rc == -2) {
      $content = new Template("./tpl/error.tpl");
      $error .= "Your account hasn't been confirmed yet<br/>";
      goto screen;
    }
  }
  IrcMsg::add("[WWW] Login succeed: ".$lm->o_login->username."/".$_SERVER['REMOTE_ADDR'], MSG_ADM);
  HTTP::piwikLogin($lm->o_login->username);

  header("Location: /panel"); 
  exit();

screen:
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", 'Please login with your username and password');
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");

  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);
  if (isset($error) && !empty($error)) {
    $content->set('error', $error);
  }
  $page->set("content", $content);
  echo $page->fetch();
  $m->disconnect();
?>
