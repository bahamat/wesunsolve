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
    $error = "You must not be already signed in to register...";
    $content = new Template("./tpl/error.tpl");
    $content->set("error", $error);
    goto screen;
  }

  if (!isset($_POST['save'])) {
    $content = new Template("./tpl/forgetpass.tpl");
    goto screen;
  }

  if (!isset($_POST["email"]) || empty($_POST["email"])) {
    $content = new Template("./tpl/forgetpass.tpl");
    $content->set("error", "Missing field");
    goto screen;
  } else {
    if (!HTTP::checkEmail($_POST["email"])) {
      $content = new Template("./tpl/forgetpass.tpl");
      $content->set("error", "Incorrect e-mail address");
      goto screen;
    }
    $email = $_POST['email'];
    $l = new Login();
    $l->email = $email;
    if ($l->fetchFromField("email")) {
      $content = new Template("./tpl/forgetpass.tpl");
      $content->set("error", "We couldn't find this email in our database..");
      goto screen;
    }
    if ($l->alreadyReset()) {
      $content = new Template("./tpl/forgetpass.tpl");
      $content->set("error", "A reset code has already been sent, you couldn't have two. If this is an error, just contact site administrator.");
      goto screen;
    }
    $l->sendResetcode();
    IrcMsg::add("[WWW] Reset code request: ".$l->username);
    $content = new Template("./tpl/message.tpl");
    $content->set("msg", "Reset code has been sent to your email address, please check it!");
  }


screen:
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", "Forget your password ?");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);
  $page->set("content", $content);
  echo $page->fetch();
  $m->disconnect();
?>
