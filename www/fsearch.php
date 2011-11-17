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

 } else {
   $str = "/fsearch/form/1";
   $files = array();
   $f_hash = $f_md5 = $f_sha1 = $f_fpa = false;

  if (isset($_POST['page']) && !empty($_POST['page'])) {
    $page = $_POST['page'];
  } else if (isset($_GET['page']) && !empty($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    $page = 1;
  }
  $nb_page = 0;

   if (isset($_POST['what']) && !empty($_POST['what'])) {
     $s_what = $_POST['what'];
     $str .= "/what/".urlencode($s_what);
   } else {
     $content = new Template("./tpl/fsearch.tpl");
     $content->set("error", "Nothing in what...");
     goto screen;
   }
   if (isset($_POST['md5']) && !empty($_POST['md5'])) {
     $f_md5 = true;
     $f_hash = true;
     $s_md5 = $_POST['md5'];
     $str .= "/md5/".urlencode($s_md5);
   }
   if (isset($_POST['sha1']) && !empty($_POST['sha1'])) {
     $f_hash = true;
     $f_sha1 = true;
     $s_sha1 = $_POST['sha1'];
     $str .= "/sha1/".urlencode($s_sha1);
   }
   if (isset($_POST['fpa']) && !empty($_POST['fpa'])) {
     $f_fpa = true;
     $s_fpa = $_POST['fpa'];
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
   switch($s_what) {
     case "patches":
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
               $mfile->pkg = $t['pkg'];
	     }
             array_push($patches, $g);
           }
         }
         $content = new Template("./tpl/fs_patch.tpl");
         $content->set("patches", $patches);
         $content->set("mfile", $mfile);

       } else { // filename search
         $mfile = new File();
         $mfile->name = $s_fpa;
	 if ($mfile->fetchFromField("name")) {
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
	       $mfile->size = $t['size'];
	       $mfile->sha1 = $t['sha1'];
	       $mfile->md5 = $t['md5'];
	     }
             array_push($patches, $g);
           }
         }
	 $content = new Template("./tpl/fs_patch.tpl");
         $content->set("patches", $patches);
	 $content->set("mfile", $mfile);

       }

     break;
     case "release":
       if ($f_hash) { // Digest search
         $idx = "`fileid`, `id_release`, `md5`, `sha1`, `size`";
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
	     }
             array_push($osrs, $g);
           }
         }
         $content = new Template("./tpl/fs_release.tpl");
         $content->set("osrs", $osrs);
         $content->set("mfile", $mfile);

       } else { // filename search
         $mfile = new File();
         $mfile->name = $s_fpa;
	 if ($mfile->fetchFromField("name")) {
           $content = new Template("./tpl/fsearch.tpl");
           $content->set("error", "File not found in database");
           goto screen;
	 }
         $idx = "`id_release`, `md5`, `sha1`, `size`";
	 $table = "jt_osrelease_files";
	 $where = "WHERE `fileid`='".$mfile->id."'";
         $osrs = array();
         if (($i = $my->fetchIndex($idx, $table, $where)))
         {
           foreach($i as $t) {
	     $g = new OSRelease($t['id_release']);
	     $g->fetchFromId();
	     $mfile->size = $t['size'];
	     $mfile->sha1 = $t['sha1'];
	     $mfile->md5 = $t['md5'];
             array_push($osrs, $g);
           }
         }
	 $content = new Template("./tpl/fs_release.tpl");
         $content->set("osrs", $osrs);
	 $content->set("mfile", $mfile);

       }
     break;
     default:
       $content = new Template("./tpl/fsearch.tpl");
       $content->set("error", "You little sneaky one...");
       goto screen;
     break;
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
