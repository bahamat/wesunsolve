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

         $h = HTTP::getInstance();
         $h->parseUrl();
         $h->sanitizeArray($_POST);
         $h->sanitizeArray($_GET);

	 if (!isset($_GET['id'])) {
	   HTTP::errWWW("Cannot be called as-is");
	 }

	 $id = mysql_escape_string($_GET['id']);
	 if (!preg_match("/^[0-9]{1,11}$/", $id)) {
	   HTTP::errWWW("Malformed cve ID");
	 }
	 
	 $cve = new CVE($id);
         $error = 0;
	 if ($cve->fetchFromId()) {
            $error = 1;
            $what = "cve";
            $number = $id;
	 } else {
           $cve->fetchAll(1);
	 }
        $cve->viewed();
   
         $title = "CVE details: ".$cve;

         $index = new Template("./tpl/index.tpl");
         $head = new Template("./tpl/head.tpl");
	 $head->set("title", $title);
         $menu = new Template("./tpl/menu.tpl");
         $foot = new Template("./tpl/foot.tpl");
	 $foot->set("start_time", $start_time);
         $content = new Template("./tpl/cve.tpl");

        if ($lm->o_login) {
	  $content->set("l", $lm->o_login);
	}

         $index->set("head", $head); 
         $index->set("menu", $menu);
         $index->set("foot", $foot);

         if($error) {
           $content = new Template("./tpl/notify.tpl");
           $content->set("what", $what);
	 } else {
           $content->set("cve", $cve);
	   if ($lm->o_login && $lm->o_login->is_log) {
	     $lm->o_login->logAction('cve', $cve->id);
	   }
         }
   $index->set("content", $content);
   echo $index->fetch();  
?>
