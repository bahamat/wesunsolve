<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

  $h = HTTP::getInstance();
  $h->parseUrl();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);
 $index->set("head", $head);
 $index->set("foot", $foot);
 $index->set("menu", $menu);

 if (!isset($_GET['i']) || empty($_GET['i'])) {
   $content = new Template("./tpl/error.tpl");
   $error = "No id of list specified.";
   $content->set("error", $error);
   goto screen;
 }
 $s = new Bundle($_GET['i']);
 if ($s->fetchFromId()) {
   $content = new Template("./tpl/error.tpl");
   $error = "Bundle not found in database";
   $content->set("error", $error);
   goto screen;
 }

 $s->fetchFromId();
 $s->fetchPatches(0);
 header("Content-type: text/plain");
 header("Content-Disposition: filename=patchdiag.xref");
 $pd_head = "## PATCHDIAG TOOL CROSS-REFERENCE FILE FOR BUNDLE ".$s->filename." ##\n";
 $pd_head .= "##\n";
 $pd_head .= "## Please note that certain patches which are listed in\n";
 $pd_head .= "## Sun's Quick Reference Section or other patch reference\n";
 $pd_head .= "## files are not publicly available, but instead are\n";
 $pd_head .= "## available only to customers of Sun Microsystems who\n";
 $pd_head .= "## have purchased an appropriate support services contract.\n";
 $pd_head .= "## For more information about Sun support services contracts\n";
 $pd_head .= "## visit www.sun.com/service\n";

// https://support.oracle.com/CSP/main/article?cmd=show&type=NOT&doctype=REFERENCE&id=1019527.1
 echo $pd_head;

 echo Patchdiag::genFromPatches($s->a_patches);
 die();

screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
