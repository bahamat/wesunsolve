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
	 if (!preg_match("/^[0-9]{6}-[0-9]{2}$/", $id)) {
	   HTTP::errWWW("Malformed patch ID");
	 }
	 
	 $p = explode("-", $id);
	 $patch = new Patch($p[0], $p[1]);
         $error = 0;
	 if ($patch->fetchFromId()) {
            $error = 1;
            $what = "patch";
            $number = $id;
	 } else {
           $patch->fetchAll(2);
           $patch->fetchPrevious(2);
           $patch->fetchObsby();
	 }
        $patch->viewed();
   
        $patch->getAllReadme();
        $patch->fetchBundles();
        $mreadme = false;
        if (count($patch->a_readmes)) {
          $mreadme = true;
        }

	$archive = null;
	$is_dl = false;
	if ($lm->o_login) {
	  if ($lm->o_login->is_dl) {
	    $is_dl = true;
 	    $archive = $patch->findArchive();
	  }
	}

         $title = "Patch details: ".$patch->name()." - ".$patch->synopsis;

         $index = new Template("./tpl/index.tpl");
         $head = new Template("./tpl/head.tpl");
	 $head->set("title", $title);
         $menu = new Template("./tpl/menu.tpl");
         $foot = new Template("./tpl/foot.tpl");
	 $foot->set("start_time", $start_time);
         $content = new Template("./tpl/patch.tpl");
	 $content->set("archive", $archive);
	 $content->set("is_dl", $is_dl);
	 $content->set("mreadme", $mreadme);

        if ($lm->o_login) {
	  $lm->o_login->fetchUCLists();
	  $content->set("l", $lm->o_login);
	  $head_add = "<script type=\"text/javascript\" src=\"/js/ax_main.js\"></script>";
	  $head_add .= "<script type=\"text/javascript\" src=\"/js/ax_patch.js\"></script>";
	  $head->set("head_add", $head_add);
	}

         $index->set("head", $head); 
         $index->set("menu", $menu);
         $index->set("foot", $foot);

         if($error) {
           $content = new Template("./tpl/notify.tpl");
           $content->set("what", $what);
	 } else {
           $content->set("patch", $patch);
	   if ($lm->o_login && $lm->o_login->is_log) {
	     $lm->o_login->logAction('patch', $patch->name());
	   }
         }
   $index->set("content", $content);
   echo $index->fetch();  
?>
