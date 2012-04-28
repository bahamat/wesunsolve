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

 $rpp = $config['patchPerPage'];
 if ($lm->isLogged) {
   $lo = $lm->o_login;
   if ($lo) {
     $val = $lo->data('patchPerPage');
     if ($val) $rpp = $val;
   }
 }
 
  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);

  $index->set("menu", $menu);
  $index->set("foot", $foot);

  $str = "/psearch/form/2";

  $patches = array();
  $table = "`patches`";
  $idx = "`patches`.`patch`, `patches`.`revision`";
  $where = "";
  $w = 0;

  $h = HTTP::getInstance();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

  $title = "Patch search results";
  $fts = false;

  $my = mysqlCM::getInstance();

  if (isset($_POST['page']) && !empty($_POST['page'])) {
    $page = $_POST['page'];
  } else if (isset($_GET['page']) && !empty($_GET['page'])) {
    $page = $_GET['page'];
  } else {
    $page = 1;
  }
  $nb_page = 0;

  if (isset($_POST['pid']) && !empty($_POST['pid'])) {
    $pid = $_POST['pid'];
  } else if (isset($_GET['pid']) && !empty($_GET['pid'])) {
    $pid = $_GET['pid'];
  }
  if (isset($_POST['rev']) && !empty($_POST['rev'])) {
    $rev = $_POST['rev'];
  } else if (isset($_GET['rev']) && !empty($_GET['rev'])) {
    $rev = $_GET['rev'];
  }
  if (isset($_POST['synopsis']) && !empty($_POST['synopsis'])) {
    $synopsis = $_POST['synopsis'];
  } else if (isset($_GET['synopsis']) && !empty($_GET['synopsis'])) {
    $synopsis = $_GET['synopsis'];
  }
  if (isset($_POST['status']) && !empty($_POST['status'])) {
    $status = $_POST['status'];
  } else if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
  }
  if (isset($_POST['files']) && !empty($_POST['files'])) {
    $files = $_POST['files'];
  } else if (isset($_GET['files']) && !empty($_GET['files'])) {
    $files = $_GET['files'];
  }
  if (isset($_POST['pkg']) && !empty($_POST['pkg'])) {
    $pkg = $_POST['pkg'];
  } else if (isset($_GET['pkg']) && !empty($_GET['pkg'])) {
    $pkg = $_GET['pkg'];
  }
  if (isset($_GET['form']) && ($_GET['form'] == 1 || $_GET['form'] == 2)) {

    if (isset($pid) && !empty($pid)) {
      $pid = trim($pid);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`patch` LIKE '$pid'";
      $str .= "/pid/".urlencode($pid);
    }
    if (isset($rev) && !empty($rev)) {
      $rev = trim($rev);
      $rev = sprintf("%d", $rev);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`patches`.`revision` LIKE '$rev'";
      $str .= "/rev/".urlencode($rev);
    }
    if (isset($synopsis) && !empty($synopsis)) {
      $synopsis = trim($synopsis);
      $str .= "/synopsis/".urlencode($synopsis);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $idx .= ", MATCH(`patches`.`synopsis`) AGAINST(".$my->quote($synopsis).") as score";
      $where .= " MATCH(`patches`.`synopsis`) AGAINST(".$my->quote($synopsis).") ";
      $fts = true;
    }

    if (isset($status) && !empty($status)) {
      $str .= "/status/".urlencode($status);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`status` LIKE '$status'";
    }

    if (isset($pkg) && !empty($pkg)) {
      $str .= "/pkg/".urlencode($pkg);
      $table .= ",`jt_patches_srv4pkg`, `srv4pkg`";
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= " `srv4pkg`.`name` LIKE '$pkg' AND `jt_patches_srv4pkg`.`id_srv4pkg`=`srv4pkg`.`id` ";
      $where .= " AND `patches`.`patch`=`jt_patches_srv4pkg`.`patchid` AND `patches`.`revision`=`jt_patches_srv4pkg`.`revision`";
    }

    if (isset($files) && !empty($files)) {
      $str .= "/files/".urlencode($files);
      $table .= ",`jt_patches_files`, `files`";
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= " `files`.`name` LIKE '$files' AND `jt_patches_files`.`fileid`=`files`.`id` ";
      $where .= " AND `patches`.`patch`=`jt_patches_files`.`patchid` AND `patches`.`revision`=`jt_patches_files`.`revision`";
    }
    if ($_GET['form'] == 1) {
      HTTP::redirect($str);
      exit();
    }
  } else if (isset($pid) && !empty($pid)) {
    $pid = trim($pid);
    if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
    $str .= "/pid/".urlencode($pid);
    if (strpos($pid, "-")) {
      $p = explode("-", $pid);
      $pid = $p[0];
      if (count($p) > 1) {
        $rev = sprintf("%d", $p[1]);
      }
    }
    $where .= "`patch` LIKE '".$pid."'";
    if (isset($rev) && !empty($rev)) {
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`revision` LIKE '".$rev."'";
    }
  }


    if (!isset($idxcount)) $idxcount = "count(`patches`.`patch`) as c";

  if (!$fts) $where .= " ORDER BY `patches`.`releasedate` DESC,`patches`.`revision` DESC";

  /* first count max results */

  $nb = 0;
  if (($pp = $my->fetchIndex($idxcount, $table, $where)))
  { 
    if (isset($pp[0]) && isset($pp[0]['c'])) {
      $nb = $pp[0]['c'];
    }
  }

  if($nb) {
    $nb_page = $nb / $rpp;
    $nb_page = round($nb_page,0);
  }

  /* check if url is saying where to start... */
  if(isset($page) && !empty($page)) {

    if (preg_match("/[0-9]*/", $page)) {
      $start = ($page - 1) * $rpp;
      if ($start >= $nb) { /* could not start after the number of results... */
        $start = 0;
      }
    } else {
      $start = 0;
    }
  } else { /* otherwise start from scratch */
    $start = 0;
  }
  if ($start < 0) $start = 0;
  $where .= " LIMIT $start,$rpp";
  $title .= " from $start to ".($start + $rpp);
  $head->set("title", $title);
  $index->set("head", $head);

  if ($nb && ($idx = $my->fetchIndex($idx, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Patch($t['patch'], $t['revision']);
      $g->fetchFromId();
      if (isset($t['score'])) $g->score = round($t['score']);
      array_push($patches, $g);
    }
  }

  $content = new Template("./tpl/psearch.tpl");
  $content->set("patches", $patches);
  $content->set("start", $start);
  $content->set("nb", $nb);
  $content->set("rpp", $rpp);
  $content->set("score", $fts);
  $content->set("str", $str);
  $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));
  $index->set("content", $content);
  echo $index->fetch();
?>
