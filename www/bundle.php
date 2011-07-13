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
	   die("Cannot be called as-is");
	 }

	 $id = mysql_escape_string($_GET['id']);
	 if (!preg_match("/[0-9]*/", $id)) {
	   die("Malformed bundle ID");
	 }
	 
	 $bundle = new Bundle($id);
         $error = 0;
	 if ($bundle->fetchFromId()) {
            $error = 1;
            $what = "bundle";
            $number = $id;
	 } else {
           $bundle->fetchAll(2);
	 }

	$archive = null;
	$is_dl = false;
	if ($lm->o_login) {
	  if ($lm->o_login->is_dl) {
	    $is_dl = true;
 	    $archive = $bundle->findArchive();
	  }
	}

         $title = "Bundle details: ".$bundle->filename;

         $index = new Template("./tpl/index.tpl");
         $head = new Template("./tpl/head.tpl");
	 $head->set("title", $title);
         $menu = new Template("./tpl/menu.tpl");
         $foot = new Template("./tpl/foot.tpl");
	 $foot->set("start_time", $start_time);
         $content = new Template("./tpl/bundle.tpl");
	 $content->set("archive", $archive);
	 $content->set("is_dl", $is_dl);

         $index->set("head", $head); 
         $index->set("menu", $menu);
         $index->set("foot", $foot);

         if($error) {
           $content = new Template("./tpl/notify.tpl");
           $content->set("what", $what);
	 } else {
           $content->set("bundle", $bundle);
         }
   $index->set("content", $content);
   echo $index->fetch();  
?>
