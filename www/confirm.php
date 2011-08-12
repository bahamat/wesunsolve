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

  if (isset($_GET['c']) && !empty($_GET['c'])) {
    $c = $_GET['c'];
    $co = new UConfirm();
    $co->code = $c;
    if ($co->fetchFromField("code")) {
      $content = new Template("./tpl/error.tpl");
      $content->set("msg", "The confirm code you provided is either inexistant or already used.");
      goto screen;
    }
    $l = new Login($co->id_login);
    if ($l->fetchFromId()) {
      $content = new Template("./tpl/error.tpl");
      $content->set("msg", "The corresponding login hasn't been found.");
      goto screen;
    }
    $l->o_code = $co;
    if ($l->checkConfirm()) {
      $content = new Template("./tpl/message.tpl");
      $content->set("msg", "Your account has been succesfully activated. Please loging...");
      IrcMsg::add("[WWW] User confirmed his account: ".$l->username);
      goto screen;
    } else {
      $content = new Template("./tpl/error.tpl");
      $content->set("msg", "Unable to confirm your account");
      goto screen;
    }
  } else {
    $content = new Template("./tpl/error.tpl");
    $content->set("msg", "No confirm code in url, please check your email!");
    goto screen;
  }

screen:
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", "Confirm your account at We Sun Solve!");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");

  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);
  $page->set("content", $content);
  echo $page->fetch();
  $m->disconnect();
?>
