<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 if (!isset($lm->o_login) || !$lm->o_login || !$lm->o_login->is_admin) {
   die("no");
 }
  
 if (!isset($_GET['i']) || empty($_GET['i'])) {
   die("no");
 }

 if (!isset($_GET['p']) || empty($_GET['p'])) {
   die("no");
 }
 $q = "INSERT INTO `toadd`(`patch`,`rev`) VALUES('".addslashes($_GET['p'])."', '".addslashes($_GET['i'])."')";
 $m->rawQuery($q);
?>
<html>
<head></head>
<body onload="window.close()">
</body>
</html>
