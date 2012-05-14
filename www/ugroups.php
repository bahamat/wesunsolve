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
 } else {
   $lo->fetchUGroups();
   $lo->fetchMGroups();
   $str = '/panel';

   $content = new Template("./tpl/ugroups.tpl");
   $content->set("ugroups", $lo->a_ugroups);
   $content->set("mgroups", $lo->a_mgroups);
   $content->set("str", $str);
 }

 $error = '';
 if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
   $error .= 'The connection is not secured, consider using <a href="https://wesunsolve.net/panel">HTTPS</a> to avoid possible eavesdropping.<br/>';
 }
 
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 
 $index->set("head", $head);
 $index->set("menu", $menu);
 $head->set("title", "Panel for registered users of We Sun Solve!");
 if (isset($error) && !empty($error)) {
   $content->set('error', $error);
 }
 $index->set("content", $content);
 $index->set("foot", $foot);
 echo $index->fetch();
?>
