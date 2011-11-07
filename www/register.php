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
    $content = new Template("./tpl/register.tpl");
    goto screen;
  }

  if (!isset($_POST["username"]) || !isset($_POST["password"]) ||
      !isset($_POST["password2"]) || !isset($_POST["fullname"]) ||
      !isset($_POST["email"]) || empty($_POST["username"]) ||
      empty($_POST["password"]) || empty($_POST["password2"]) ||
      empty($_POST["email"]) || empty($_POST["fullname"])) {
    $content = new Template("./tpl/register.tpl");
    $content->set("error", "Missing field");
    goto screen;
  } else {
    if (strcmp($_POST["password2"], $_POST["password"])) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "Please check password and its confirmation");
      goto screen;
    }
    if (strlen($_POST['password']) < 6) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "Password must be at least 6 characters long");
      goto screen;
    }
    if (!HTTP::checkEmail($_POST["email"])) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "Incorrect e-mail address");
      goto screen;
    }
    $username = $_POST["username"];
    $l = new Login();
    $l->username = $username;
    if (!$l->fetchFromField("username")) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "This username already exists");
      goto screen;
    }
    $l->email = $_POST["email"];
    if (!$l->fetchFromField("email")) {
      $content = new Template("./tpl/register.tpl");
      $content->set("error", "This email already exists");
      goto screen;
    }
    $l = new Login();
    $l->username = $_POST["username"];
    $l->password = md5($_POST["password"]);
    $l->fullname = $_POST["fullname"];
    $l->email = $_POST["email"];
    $l->is_enabled = 0;
    $l->insert();
    $l->sendConfirm();
    IrcMsg::add("[WWW] New user registered: ".$l->username);
    $msg = "New user has been added: ".$l->username." / ".$l->fullname." / ".$l->email;
    Mail::sendAdmin("New user has registered", $msg);
    $content = new Template("./tpl/welcome.tpl");
  }


screen:
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", "Register yourself at We Sun Solve!");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  if (isset($_POST["fullname"])) $content->set("fullname", $_POST["fullname"]);
  if (isset($_POST["email"])) $content->set("email", $_POST["email"]);
  if (isset($_POST["username"])) $content->set("username", $_POST["username"]);
  if (isset($_POST["password"])) $content->set("password", $_POST["password"]);
  if (isset($_POST["password2"])) $content->set("password2", $_POST["password2"]);

  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);
  $page->set("content", $content);
  echo $page->fetch();
  $m->disconnect();
?>
