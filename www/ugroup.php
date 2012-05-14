<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head_add = "<script type=\"text/javascript\" src=\"/js/ax_main.js\"></script>";
 $head_add .= "<script type=\"text/javascript\" src=\"/js/ax_patch.js\"></script>";
 $head->set("head_add", $head_add);
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 }

 if (!isset($_GET['id']) || empty($_GET['id'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No id of group specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new UGroup();
 $s->id = $_GET['id'];
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Group not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $s->id_owner) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this group!";
   $content->set("error", $error);
   goto screen;
 }

 $s->fetchFromId();
 $s->fetchUsers();
 $s->fetchSrv();
 $lm->o_login->fetchServers();
 $error = '';
 $msg = '';

 $content = new Template("./tpl/ugroup.tpl");
 $content->set("l", $lm->o_login);
 if (!isset($_GET['form']) && !isset($_GET['del']) && !isset($_GET['dels'])) {
   $content->set("ugroup", $s);
   goto screen;
 }

 if (isset($_GET['del'])) {
  $h->sanitizeArray($_POST);
  if (!empty($_GET['del'])) {
    $id = $_GET['del'];
    $l = new Login($id);
    if ($l->fetchFromId() || $s->delUser($l)) {
      $content = new Template("./tpl/ugroup.tpl");
      $error .= "Failed to remove user!";
      $content->set("ugroup", $s);
      $content->set("error", $error);
      goto screen;
    }
    $msg .= 'User has been removed from group';
    IrcMsg::add("[WWW] User $l removed from group $s by ".$lm->o_login, MSG_ADM);
    $content->set("ugroup", $s);
    $content->set("msg", $msg);
    goto screen;
  }
 }

 if (isset($_GET['dels'])) { 
  $h->sanitizeArray($_POST);
  if (!empty($_GET['dels'])) {
    $id = $_GET['dels'];
    $l = new Server($id);
    if ($l->fetchFromId() || $s->delSrv($l)) {
      $content = new Template("./tpl/ugroup.tpl");
      $error .= "Failed to remove server!";
      $content->set("ugroup", $s);
      $content->set("error", $error);
      goto screen;
    }
    $msg .= 'Server access has been removed from group';
    IrcMsg::add("[WWW] Server $l removed from group $s by ".$lm->o_login, MSG_ADM);
    $content->set("ugroup", $s);
    $content->set("msg", $msg);
    goto screen;
  }
 }

 if (isset($_GET['form']) && $_GET['form'] == '1') {
   if (isset($_POST['sname']) && !empty($_POST['sname'])) {
     $h->sanitizeArray($_POST);
     $l = $_POST['sname'];
     $u = new Server();
     $u->id = $l;
     if (isset($_POST['rw']) && $_POST['rw'] == '1') {
       $u->w = 1;
     }
     if ($u->fetchFromId()) {
       $error .= "Cannot add $l<br/>";
     } else {
       $s->addSrv($u);
       $msg .= "Server access for $u added to group<br/>";
       IrcMsg::add("[WWW] Server $u added to group $s by ".$lm->o_login, MSG_ADM);
     }
     $content->set("ugroup", $s);
     $content->set("msg", $msg);
     $content->set("error", $error);
   }
   if (isset($_POST['uname']) && !empty($_POST['uname'])) {
     $h->sanitizeArray($_POST);
     $l = $_POST['uname'];
     $u = new Login();
     $u->username = $l;
     if ($u->fetchFromField('username')) {
       $error .= "Cannot add $l<br/>";
     } else {
       $s->addUser($u);
       $msg .= "User $l added to group<br/>";
       IrcMsg::add("[WWW] User $u added to group $s by ".$lm->o_login, MSG_ADM);
     }
     $content->set("ugroup", $s);
     $content->set("msg", $msg);
     $content->set("error", $error);
   }
   if (isset($_POST['unames']) && !empty($_POST['unames'])) {
     $unames = explode(PHP_EOL, $_POST['unames']);
     foreach($unames as $l) {
       $l = trim($l);
       if (empty($l)) continue;
       $u = new Login();
       $u->username = $l;
       if ($u->fetchFromField('username')) {
        $error .= "Cannot add $l<br/>";
       } else {
         $s->addUser($u);
         $msg .= "User $l added to group<br/>";
       }
     }
     IrcMsg::add("[WWW] ".count($unames)." users added to group $s by ".$lm->o_login, MSG_ADM);
     $content->set("ugroup", $s);
     $content->set("msg", $msg);
     $content->set("error", $error);
   }
   if (isset($_POST['snames']) && !empty($_POST['snames'])) {
     $unames = explode(PHP_EOL, $_POST['snames']);
     foreach($unames as $l) {
       $l = trim($l);
       if (empty($l)) continue;
       $u = new Server();
       $u->name = $l;
       if (isset($_POST['rw']) && $_POST['rw'] == '1') {
         $u->w = 1;
       }
       if ($u->fetchFromField('name')) {
        $error .= "Cannot add $l<br/>";
       } else {
         $s->addSrv($u);
         $msg .= "Server $l added to group<br/>";
       }
     }
     IrcMsg::add("[WWW] ".count($unames)." servers added to group $s by ".$lm->o_login, MSG_ADM);
     $content->set("ugroup", $s);
     $content->set("msg", $msg);
     $content->set("error", $error);
   }

 }

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
