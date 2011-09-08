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

	 if (!isset($_GET['id']) || !isset($_GET['type'])) {
	   HTTP::errWWW("Cannot be called as-is");
	 }

         switch($_GET['type']) {
	  case "patch":
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
//              $patch->fetchAll(2);
//              $patch->fetchPrevious(2);
	    }
            $title = "Readme diff for patch: ".$patch->name()." - ".$patch->synopsis;
            $content = new Template("./tpl/diffrp.tpl");
            $patch->fetchReadmes(0);
            if (!count($patch->a_readmes)) {
    	      HTTP::errWWW("This object doesn't have multiple readme");
            }
            $content->set("patch", $patch);
            $content->set("config", $config);
            break;
           default:
	     HTTP::errWWW("type not recognized or not supported yet");
	   break;
        }
   

         $index = new Template("./tpl/index.tpl");
         $head = new Template("./tpl/head.tpl");
	 $head->set("title", $title);
         $menu = new Template("./tpl/menu.tpl");
         $foot = new Template("./tpl/foot.tpl");
	 $foot->set("start_time", $start_time);

         $index->set("head", $head); 
         $index->set("menu", $menu);
         $index->set("foot", $foot);

   $index->set("content", $content);
   echo $index->fetch();  
?>
