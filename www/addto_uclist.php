<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $h = HTTP::getInstance();
 $h->parseUrl();
 $h->sanitizeArray($_GET);


 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 if (!isset($lm->o_login) || !$lm->o_login) {
   die("no");
 }

 $l = $lm->o_login;
 $l->fetchUCLists();
 if (!isset($_GET['i']) || empty($_GET['i'])) {
   die("List ID Not specified");
 }
 $i = $_GET['i'];
 $ul = null;
 foreach($l->a_uclists as $li) {
   if ($li->id == $i) {
     $ul = $li;
     break;
   }
 }
 if (!$li) { die("This list does not belong to your user!"); }

 if (isset($_GET['pl']) && !empty($_GET['pl'])) {
   $pl = $_GET['pl'];
   $pl = explode("+", $pl);
   $error = 0;
   foreach($pl as $p) {
         $p = trim($p);
	 if (empty($p)) continue;
	 if(!preg_match("/[0-9]{6}-[0-9]{2}/", $p)) {
	  continue;
	 }

	 $p = explode('-', $p);
	 if (count($p) != 2) {
	   $error++;
	 }
	 $p = new Patch($p[0], $p[1]);
	 if ($p->fetchFromId()) {
	   $error++;
	 }
	 if (!$ul->isPatch($p)) {
	   $ul->addPatch($p);
	 } else {
	   $error++;
	 }
   }
   if ($error) die("Failed");
   die("Added to list!");
 } else if (!isset($_GET['p']) || empty($_GET['p'])) {
   die("No patch specified");
 }
 $p = $_GET['p'];
 $p = explode('-', $p);
 if (count($p) != 2) {
   die("Malformed patch id");
 }
 $p = new Patch($p[0], $p[1]);
 if ($p->fetchFromId()) {
   die("Unable to fetch this patch");
 }
 if (!$ul->isPatch($p)) {
   $ul->addPatch($p);
   die("Added patch to list!");
 } else {
   die("This patch is already on this list");
 }
?>
