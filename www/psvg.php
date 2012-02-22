<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   die('.');
 }

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (isset($_GET['pid']) && !empty($_GET['pid'])) {
   $id = $_GET['pid'];
 } else {
   die('.');
 }

 $p = Patch::fromString($id);
 if ($p->fetchFromId()) {
   die('.');
 }
 $p->fetchObsby();
 $p->fetchObsolated();
 $p->fetchRequired();

 $ddata = "digraph \"Patch tree for $p\" {\n";
 if ($p->o_obsby) {
   $ddata .= '  "'.$p->o_obsby->name().'" -> "'.$p->name().'"'."\n";
 }
 foreach($p->a_depend as $pd) {
   $pd->fetchFromId();
   $ddata .= '  "'.$p->name().'" -> "'.$pd->name().'"'."\n";
 }
 $ddata .= '  label = "Number of required patches: '.count($p->a_depend).'"'."\n";
 $ddata .= '  labelloc = t'."\n";
 $ddata .= '  labeljust = l'."\n";

 /* Add myself */
 $ddata .= '  "'.$p->name().'" [ '.$p->svgColor().' URL="/psvg/pid/'.$p->name().'" comment="'.$p->name().'" label="'.$p->name().'"]'."\n";

 if ($p->o_obsby) {
   $ddata .= '  "'.$p->o_obsby->name().'" [ '.$p->o_obsby->svgColor().' color=red URL="/psvg/pid/'.$p->o_obsby->name().'" comment="'.$p->o_obsby->name().'" label="'.$p->o_obsby->name().'"]'."\n";
 }
 foreach($p->a_depend as $pd) {
   $ddata .= '  "'.$pd->name().'" [ '.$pd->svgColor().' URL="/psvg/pid/'.$pd->name().'" comment="'.$pd->name().'" label="'.$pd->name().'"]'."\n";
 }
 $ddata .= '  subgraph "cluster_depend" { label="Requirements"  URL="" ';
 foreach($p->a_depend as $pd) { $ddata .= '"'.$pd->name().'" '; }
 $ddata .= '}'."\n";

 $ddata .= '}'."\n";
 header('Content-type: image/svg+xml');
 passthru("echo '$ddata'|".$config['dotpath']." -Tsvg");
//echo "<pre>$ddata\n";
?>
