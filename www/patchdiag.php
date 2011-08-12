<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (isset($_POST['id']) && !empty($_POST['id'])) {
   $id = $_POST['id'];
 } else {
   $lm = loginCM::getInstance();
   $lm->startSession();
   $index = new Template("./tpl/index.tpl");
   $head = new Template("./tpl/head.tpl");
   $head->set("title", 'patchdiag.xref Archive download');
   $menu = new Template("./tpl/menu.tpl");
   $foot = new Template("./tpl/foot.tpl");
   $foot->set("start_time", $start_time);
 }

 if (isset($id) && $id) {
   $pd = new Patchdiag($id);
   if ($pd->fetchFromId()) {
     HTTP::errWWW("error, id not found");
   }
   if (file_exists($config['pdiagpath']."/".$pd->filename)) {
     header("Content-type: text/plain");
     header("Content-Disposition: filename=patchdiag.xref");
     echo file_get_contents($config['pdiagpath']."/".$pd->filename);
     die();
   } else {
     HTTP::errWWW("error, file not found");
   }
 } else {
   $content = new Template("./tpl/patchdiag.tpl");
   $content->set("list", Patchdiag::listFiles());
 }

 $index->set("head", $head);
 $index->set("menu", $menu);
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();

?>
