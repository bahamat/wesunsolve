<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
//  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

 if (!isset($lm->o_login) || !$lm->o_login) {
   $content = new Template("./tpl/denied.tpl");
//   $error = "You should be logged in to access this page...";
 //  $content->set("error", $error);
   goto screen;
 }
 
 if (!isset($_GET['s']) || empty($_GET['s'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No id of server specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new Server();
 $s->id = $_GET['s'];
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Server not found in database";
   $content->set("error", $error);
   goto screen;
 }

 if ($lm->o_login->id != $s->id_owner) {
   $content = new Template("./tpl/error.tpl");
   $error = "You have no rights to view this server!";
   $content->set("error", $error);
   goto screen;
 }

 if (isset($_GET['form']) && $_GET['form'] == 1) {
   if (!isset ($_POST['name']) || empty($_POST['name'])) {
     $content = new Template("./tpl/add_plevel.tpl");
     $content->set("error", "Check the name of the patch level provided..");
     $content->set("s", $s);
     goto screen;
   }
   if(!isset($_FILES) && isset($HTTP_POST_FILES))
     $_FILES = $HTTP_POST_FILES;

   if (isset($_FILES['showrev']) && isset($_FILES['pkginfo'])) {
     $showrev = file($_FILES['showrev']['tmp_name']);
     $pkginfo = file($_FILES['pkginfo']['tmp_name']);
     if (empty($showrev)) {
       $content = new Template("./tpl/add_plevel.tpl");
       $content->set("error", "Showrev file empty");
       $content->set("s", $s);
       goto screen;
     }
     if (empty($pkginfo)) {
       $content = new Template("./tpl/add_plevel.tpl");
       $content->set("error", "Pkginfo file empty");
       $content->set("s", $s);
       goto screen;
     }
   } else {
     $content = new Template("./tpl/add_plevel.tpl");
     $content->set("error", "You must fill the field for patch level and upload the two requested files...");
     $content->set("s", $s);
     goto screen;
   }
   $pl = new PLevel();
   $pl->id_server = $s->id;
   $pl->name = addslashes($_POST['name']);
   if (!$pl->fetchFromFields(array("id_server", "name"))) {
     $content = new Template("./tpl/add_plevel.tpl");
     $content->set("error", "This patch level name is already used for this server..");
     $content->set("s", $s);
     goto screen;
   }
   if (isset($_POST['comment']))
     $pl->comment = addslashes($_POST['comment']);
   if (isset($_POST['is_applied']) && $_POST['is_applied']) {
     $pl->is_applied = 1;
   }
   if (isset($_POST['is_current']) && $_POST['is_current']) {
     $pl->is_current = 1;
   }
   $pl->insert();
   IrcMsg::add("[WWW] User added Patch level: ".$pl->name." to his account (".$lm->o_login->username.")", MSG_ADM);

   $pl->buildFromFiles($showrev, $pkginfo);

   $content = new Template("./tpl/message.tpl");
   $content->set("msg", "Patch level added with ".count($pl->a_patches)." patches and ".count($pl->a_srv4pkgs)." packages.");
 } else {
   $content = new Template("./tpl/add_plevel.tpl");
   $content->set("s", $s);
 }

screen:
  $index->set("content", $content);
  if (isset($s)) $content->set("s", $s);
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  echo $index->fetch();
?>
