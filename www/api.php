<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   die("SQL Error, please try again later or contact site admins");
 }

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (!isset($_GET['u']) || empty($_GET['u'])) {
   die("Username not provided");
 }

 if (!isset($_GET['p']) || empty($_GET['p'])) {
   die("Password not provided");
 }

 $lm = loginCM::getInstance();
 if ($lm->login($_GET['u'], $_GET['p'])) {
   die("Authentication failed");
 }

 if (!isset($lm->o_login) || !$lm->o_login->is_api) {
   die("Authorization failed");
 }

 if (!isset($_GET['action']) || empty($_GET['action'])) {
   die("No action specified");
 }

 $arg = false;
 if (isset($_GET['arg']) && !empty($_GET['arg'])) {
   $arg = $_GET['arg'];
 }

 $xml = new XMLMake();
 switch($_GET['action']) {
   case "l10p":
     $xml->push('Last10Patches');
     $table = "`patches`";
     $index = "`patch`, `revision`";
     $where = " WHERE `releasedate`!='' ORDER BY `patches`.`releasedate` DESC,`patches`.`patch` DESC,`patches`.`revision` DESC LIMIT 0,10";
     if ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where))
     {
       foreach($idx as $t) {
         $g = new Patch($t['patch'], $t['revision']);
         $g->fetchFromId();
         $g->toXML(&$xml, $arg);
       }
     }
     $xml->pop();
     break;
   default:
     die("Unknown function");
     break;
 }

 header("Content-type: text/xml");
 print $xml->getXml();

?>
