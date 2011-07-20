<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $h = HTTP::getInstance();
 $h->parseUrl();
 $h->sanitizeArray($_GET);


 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die("Error");
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 if (!isset($lm->o_login) || !$lm->o_login) {
   die("Error");
 }

 $l = $lm->o_login;
 $l->fetchUCLists();
 if (!isset($_GET['i']) || empty($_GET['i'])) {
   die("Failed");
 }
 $i = $_GET['i'];
 $ul = null;
 foreach($l->a_uclists as $li) {
   if ($li->id == $i) {
     $ul = $li;
     break;
   }
 }
 if (!$ul) { die("Failed"); }
 $ul->fetchPatches();

 if (!isset($_GET['p']) || empty($_GET['p'])) {
   die("Failed");
 }
 $p = $_GET['p'];
 $p = explode('-', $p);
 if (count($p) != 2) {
   die("Failed");
 }
 $p = new Patch($p[0], $p[1]);
 if ($p->fetchFromId()) {
   die("Failed");
 }
 if (!$ul->isPatch($p)) {
   die("Failed");
 } else {
   $ul->delPatch($p);
   die("Removed!");
 }
?>
