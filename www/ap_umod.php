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
 if (!$lm->isLogged || !$lo->is_admin || !isset($_GET['i']) || empty($_GET['i'])) {
   $content = new Template("./tpl/denied.tpl");
   goto screen;
 } else {
   $lid = $_GET['i'];
   $content = new Template("./tpl/ap_umod.tpl");
   $l = new Login($lid);
   $content->set('l', &$l);
   if ($l->fetchFromId()) {
     $content = new Template("./tpl/denied.tpl");
     goto screen;
   }
   if (isset($_GET['form']) && $_GET['form'] == 1) {
     $f = array('u_password', 'u_email', 'u_fullname', 'u_isadmin', 'u_enabled', 'u_dl', 'u_log');
     foreach($f as $fi) {
       if (isset($_POST[$fi])) {
	 ${$fi} = $_POST[$fi];
       } else {
	 ${$fi} = false;
       }
     }
     if ($u_dl == "on") {
       $l->is_dl = 1;
     } else {
       $l->is_dl = 0;
     }
     if ($u_isadmin == "on") {
       $l->is_admin = 1;
     } else {
       $l->is_admin = 0;
     }
     if ($u_log == "on") {
       $l->is_log = 1;
     } else {
       $l->is_log = 0;
     }
     if ($u_enabled == "on") {
       $l->is_enabled = 1;
     } else {
       $l->is_enabled = 0;
     }
     if (!empty($u_password)) {
       $l->password = md5($u_password);
     }
     if (!empty($u_email)) {
       $l->email = $u_email;
     }
     if (!empty($u_fullname)) {
       $l->fullname = $u_fullname;
     }
     $l->update();
     $msg = 'User has been updated accordingly..<br/>';
   }
 }

screen:
 
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 
 $index->set("head", $head);
 $index->set("menu", $menu);
 $head->set("title", "User edition");
 if (isset($error) && !empty($error)) {
   $content->set('error', $error);
 }
 if (isset($msg) && !empty($msg)) {
   $content->set('msg', $msg);
 }
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
