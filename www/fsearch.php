<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $my = $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $title = "We Sun Solve - File Search";

 $rpp = $config['patchPerPage'];
 if ($lm->isLogged) {
   $lo = $lm->o_login;
   if ($lo) {
     $lo->fetchData();
     $val = $lo->data('patchPerPage');
     if ($val) $rpp = $val;
   }
 }

 $h->sanitizeArray($_POST);
 $h->sanitizeArray($_GET);

 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

  if (!isset($_GET['form'])) {
   $content = new Template("./tpl/fsearch.tpl");
 } else if ($_GET['form'] == 1) {
   $str = "/fsearch?form=2&";
   $files = array();
   $f_hash = $f_md5 = $f_sha1 = $f_fpa = false;

   if (isset($_POST['md5']) && !empty($_POST['md5'])) {
     $f_md5 = true;
     $f_hash = true;
     $s_md5 = $_POST['md5'];
     $str .= "md5=".urlencode($s_md5).'&';
   }
   if (isset($_POST['sha1']) && !empty($_POST['sha1'])) {
     $f_hash = true;
     $f_sha1 = true;
     $s_sha1 = $_POST['sha1'];
     $str .= "sha1=".urlencode($s_sha1).'&';
   }
   if (isset($_POST['fpa']) && !empty($_POST['fpa'])) {
     $f_fpa = true;
     $s_fpa = $_POST['fpa'];
     $str .= "fpa=".urlencode($s_fpa);
   }
   HTTP::redirect($str);
   exit();

 } else {

   $str = "/fsearch/form/2/";
   $files = array();
   $f_hash = $f_md5 = $f_sha1 = $f_fpa = false;

   if (isset($_GET['md5']) && !empty($_GET['md5'])) {
     $f_md5 = true;
     $f_hash = true;
     $s_md5 = $_GET['md5'];
     $str .= "/md5/".urlencode($s_md5);
   }
   if (isset($_GET['sha1']) && !empty($_GET['sha1'])) {
     $f_hash = true;
     $f_sha1 = true;
     $s_sha1 = $_GET['sha1'];
     $str .= "/sha1/".urlencode($s_sha1);
   }
   if (isset($_GET['fpa']) && !empty($_GET['fpa'])) {
     $f_fpa = true;
     $s_fpa = $_GET['fpa'];
     $str .= "/fpa/".urlencode($s_fpa);
   }


   if ($f_md5 && $f_sha1) {
     $content = new Template("./tpl/fsearch.tpl");
     $content->set("error", "Can't use both MD5 and SHA1 at the same time...");
     goto screen;
   }
   if (($f_md5 && $f_fpa) || ($f_sha1 && $f_fpa)) {
     $content = new Template("./tpl/fsearch.tpl");
     $content->set("error", "Can't use both Pattern search and checksum at the same time...");
     goto screen;
   }
       if ($f_hash) { // Digest search
         $idx = "`fileid`, `patchid`, `revision`, `pkg`, `md5`, `sha1`, `size`";
	 $table = "jt_patches_files";
	 if ($f_md5) {
           $where = "WHERE `md5`='".$s_md5."'";
	 }
         if ($f_sha1) {
           $where = "WHERE `sha1`='".$s_sha1."'";
         }

	 $mfile = null;
         $patches = array();
         if (($i = $my->fetchIndex($idx, $table, $where)))
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
	       $g->o_mfile = clone $mfile;
               $g->o_mfile->pkg = $t['pkg'];
	     }
             array_push($patches, $g);
           }
         }
	// releases
         $idx = "`fileid`, `id_release`, `md5`, `sha1`, `size`, `pkg`";
	 $table = "jt_osrelease_files";
	 if ($f_md5) {
           $where = "WHERE `md5`='".$s_md5."'";
	 }
         if ($f_sha1) {
           $where = "WHERE `sha1`='".$s_sha1."'";
         }

	 $mfile = null;
         $osrs = array();
         if (($i = $my->fetchIndex($idx, $table, $where)))
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
	       $g->o_mfile = clone $mfile;
               $g->o_mfile->pkg = $t['pkg'];
	     }
             array_push($osrs, $g);
           }
         }
         $content = new Template("./tpl/fs_results.tpl");
         $content->set("osrs", $osrs);
         $content->set("patches", $patches);
         $content->set("mfile", $mfile);
         $content->set("namebased", false);

       } else { // filename search
         $mfile = new File();
         $mfile->name = '%'.$s_fpa;
	 if ($mfile->fetchFromField("name", 'LIKE')) {
           $content = new Template("./tpl/fsearch.tpl");
           $content->set("error", "File not found in database");
           goto screen;
	 }
         $idx = "`patchid`, `revision`, `pkg`, `md5`, `sha1`, `size`";
	 $table = "jt_patches_files";
	 $where = "WHERE `fileid`='".$mfile->id."'";
         $patches = array();
         if (($i = $my->fetchIndex($idx, $table, $where)))
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
               $g->o_mfile = clone $mfile;
	       $g->o_mfile->size = $t['size'];
	       $g->o_mfile->pkg = $t['pkg'];
	       $g->o_mfile->sha1 = $t['sha1'];
	       $g->o_mfile->md5 = $t['md5'];
	     }
             array_push($patches, $g);
           }
         }
	 $idx = "`id_release`, `md5`, `pkg`, `sha1`, `size`";
	 $table = "jt_osrelease_files";
	 $where = "WHERE `fileid`='".$mfile->id."'";
         $osrs = array();
         if (($i = $my->fetchIndex($idx, $table, $where)))
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
               $g->o_mfile = clone $mfile;
	       $g->o_mfile->size = $t['size'];
	       $g->o_mfile->pkg = $t['pkg'];
	       $g->o_mfile->sha1 = $t['sha1'];
	       $g->o_mfile->md5 = $t['md5'];
  	     }
             array_push($osrs, $g);
           }
         }
	 if ($mfile) {
	   $mfile->pkg = "";
	   $mfile->md5 = "";
	   $mfile->sha1 = "";
	   $mfile->size = 0;
	 }
	 $content = new Template("./tpl/fs_results.tpl");
         $content->set("osrs", $osrs);
         $content->set("patches", $patches);
         $content->set("namebased", true);
         $content->set("mfile", $mfile);

       }


   $title = "We Sun Solve - File Search Results";

}

screen:
 $index->set("menu", $menu);
 $index->set("foot", $foot);
 $head->set("title", $title);
 $index->set("head", $head);
 $index->set("content", $content);
 echo $index->fetch();
?>
