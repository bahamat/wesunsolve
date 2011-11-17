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
   $f_md5 = $f_sha1 = $f_fpa = false;

  if (isset($_POST['page']) && !empty($_POST['page'])) {
    $page = $_POST['page'];
  } else if (isset($_GET['page']) && !empty($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    $page = 1;
  }
  $nb_page = 0;


   if (isset($_POST['md5']) && !empty($_POST['md5'])) {
     $f_md5 = true;
     $s_md5 = $_POST['md5'];
     $str .= "/md5/".urlencode($s_md5);
   }
   if (isset($_POST['sha1']) && !empty($_POST['sha1'])) {
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

   $title = "We Sun Solve - File Search Results";

   if ($f_md5 || $f_sha1) { // Checksum search
     $where = "";
     if ($f_md5) {
       $where = "WHERE `md5`='$s_md5'";
     } else if ($f_sha1) {
       $where = "WHERE `sha1`='$s_sha1'";
     }
     $table1 = "`jt_patches_files`";
     $table2 = "`jt_osrelease_files`";
     $index = "`fileid`";
     $cindex = "count(`fileid`) as c";
 
   } else if ($f_fpa) { // File pattern search
      
   }
}

screen:
 $index->set("menu", $menu);
 $index->set("foot", $foot);
 $head->set("title", $title);
 $index->set("head", $head);
 $index->set("content", $content);
 echo $index->fetch();
?>
