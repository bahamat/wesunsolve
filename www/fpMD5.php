<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   die("SQL Error, please try again later or contact site admins");
 }

 $h = HTTP::getInstance();
 $h->parseUrl();

 if (!isset($_POST['md5list']) || empty($_POST['md5list'])) {
   die("No md5list specified");
 }
 header("Content-type: text/xml");
 HTTP::Piwik("Fingerprint Database API");

 $lines = explode(PHP_EOL, $_POST['md5list']);
 $j=0;
 foreach ($lines as $md5) {
   $md5 = trim($md5);
   if (empty($md5)) continue;
   if (strlen($md5) != 32) {
     echo " -> $md5 (invalid sum)\n";
     continue;
   }
   $j++;
   if ($j > 255) die("You can't submit more than 255 digest in one request...\n");
   $idx = "`fileid`, `patchid`, `revision`, `pkg`, `md5`, `sha1`, `size`";
   $table = "jt_patches_files";
   $where = "WHERE `md5`=".$m->quote($md5);

   $mfile = null;
   $patches = array();
   if (($i = $m->fetchIndex($idx, $table, $where)))
   {
     foreach($i as $t) {
       $g = new Patch($t['patchid'], $t['revision']);
       $g->fetchFromId();
       if (!$mfile) {
          $mfile = new File($t['fileid']);
          if ($mfile->fetchFromId()) {
            $mfile = null;
          }
       }
       if ($mfile) {
         $mfile->size = $t['size'];
         $mfile->sha1 = $t['sha1'];
         $mfile->md5 = $t['md5'];
         $mfile->pkg = $t['pkg'];
         $g->o_mfile = clone $mfile;
         $g->o_mfile->pkg = $t['pkg'];
       }
       array_push($patches, $g);
     }
   }

   // releases
   $idx = "`fileid`, `id_release`, `md5`, `sha1`, `size`, `pkg`";
   $table = "jt_osrelease_files";
   $where = "WHERE `md5`=".$m->quote($md5);
   $osrs = array();
   if (($i = $m->fetchIndex($idx, $table, $where)))
   {
     foreach($i as $t) {
       $g = new OSRelease($t['id_release']);
       $g->fetchFromId();
       if (!$mfile) {
          $mfile = new File($t['fileid']);
          if ($mfile->fetchFromId()) {
            $mfile = null;
          }
       }
       if ($mfile) {
         $mfile->size = $t['size'];
         $mfile->sha1 = $t['sha1'];
         $mfile->md5 = $t['md5'];
         $mfile->pkg = $t['pkg'];
         $g->o_mfile = clone $mfile;
         $g->o_mfile->pkg = $t['pkg'];
       }
       array_push($osrs, $g);
     }
   }
   echo "-> $md5 found in ".count($osrs)." OS Releases and ".count($patches)." patches.\n";
   if ($mfile) {
     echo "  path: ".$mfile->name."\n";
     echo "  md5: ".$mfile->md5."\n";
     echo "  sha1: ".$mfile->sha1."\n";
     echo "  size: ".$mfile->size."\n";
     echo "  associated package: ".$mfile->pkg."\n";
   }

   if (count($osrs)) {
     $osr_txt = "";
     foreach($osrs as $osr) {
       $osr_txt .= "   > ".$osr->__toString()."\n";
     }
     echo "  associated solaris releases:\n";
     echo $osr_txt;
   }
   if (count($patches)) {
     $pat_txt = "";
     foreach($patches as $patch) {
       $pat_txt .= "   > ".$patch->name()."\n";
     }
     echo "  associated solaris patches:\n";
     echo $pat_txt;
   }
   if ($mfile) echo "\n";
 }


?>
