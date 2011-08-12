<?php
	 require_once("../libs/autoload.lib.php");
	 require_once("../libs/config.inc.php");

	 $m = mysqlCM::getInstance();
	 if ($m->connect()) {
           HTTP::errMysql();
	 }
        $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

	 if (!isset($_GET['id'])) {
	   HTTP::errWWW("Cannot be called as-is");
	 }

	 $id = mysql_escape_string($_GET['id']);
	 if (!preg_match("/[0-9]{4}/", $id)) {
	   HTTP::errWWW("Malformed Bug ID");
	 }
	 
	 $bug = new Bugid($id);
	 if ($bug->fetchFromId()) {
	   HTTP::errWWW("Bug not found in our database");
	 }
         $bug->viewed();
         $bug->fetchAll();
	 $bug->fetchFulltext();
         
         if ($lm->o_login && $lm->o_login->is_log) {
           $lm->o_login->logAction('bug', $bug->id);
         }

         $title = 'We Sun Solve: Bug details for '.$bug->id;
         $index = new Template("./tpl/index.tpl");
         $head = new Template("./tpl/head.tpl");
         $head->set("title", $title);
         $menu = new Template("./tpl/menu.tpl");
         $foot = new Template("./tpl/foot.tpl");
	 $foot->set("start_time", $start_time);
         $content = new Template("./tpl/bugid.tpl");
         $content->set("bug", $bug);
         $content->set("l", $lm->o_login);

         $index->set("head", $head);
         $index->set("menu", $menu);
         $index->set("foot", $foot);
         $index->set("content", $content);

         echo $index->fetch();

?>
