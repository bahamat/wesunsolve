<?php
	 require_once("../libs/autoload.lib.php");
	 require_once("../libs/config.inc.php");

	 $m = mysqlCM::getInstance();
	 if ($m->connect()) {
           HTTP::getInstance()->errMysql();
	 }
//	 $lm = loginCM::getInstance();
//	 $lm->startSession();

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

         $title = 'Bundle details for file '.$bundle->filename;

         $index = new Template("./tpl/index.tpl");
         $head = new Template("./tpl/head.tpl");
	 $head->set("title", $title);
         $head->set("paget", "Bundle details");
         $foot = new Template("./tpl/foot.tpl");
         $content = new Template("./tpl/bundle.tpl");

         $index->set("head", $head); 
         $index->set("foot", $foot);

         if($error) {
           $content = new Template("./tpl/error.tpl");
           $content->set("what", $what);
	 } else {
           $content->set("bundle", $bundle);
         }
   $index->set("content", $content);
   echo $index->fetch();  
?>
