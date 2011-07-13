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
     $lo->fetchData();
     $val = $lo->data('patchPerPage');
     if ($val) $rpp = $val;
   }
 }
 
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);

  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);

  $str = "/psearch/form/1";

  $patches = array();
  $table = "`patches`";
  $index = "`patches`.`patch`, `patches`.`revision`";
  $where = "";
  $w = 0;

  $h = HTTP::getInstance();
  $h->sanitizeArray($_POST);
  $h->sanitizeArray($_GET);

  $title = "Patch search";
  $head->set("title", $title); 
  $fts = false;

  $my = mysqlCM::getInstance();

  if (isset($_POST['start']) && !empty($_POST['start'])) {
    $start = $_POST['start'];
  } else if (isset($_GET['start']) && !empty($_GET['start'])) {
    $start = $_GET['start'];
  }
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
  if (isset($_GET['form']) && $_GET['form'] == 1) {

    if (isset($pid) && !empty($pid)) {
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`patch` LIKE '$pid'";
      $str .= "/pid/".urlencode($pid);
    }
    if (isset($rev) && !empty($rev)) {
      $rev = sprintf("%d", $rev);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`patches`.`revision` LIKE '$rev'";
      $str .= "/rev/".urlencode($rev);
    }
    if (isset($synopsis) && !empty($synopsis)) {
      $str .= "/synopsis/".urlencode($synopsis);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $index .= ", MATCH(`patches`.`synopsis`) AGAINST(".$my->quote($synopsis).") as score";
      $where .= " MATCH(`patches`.`synopsis`) AGAINST(".$my->quote($synopsis).") ";
      $fts = true;
    }

    if (isset($status) && !empty($status)) {
      $str .= "/status/".urlencode($status);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`status` LIKE '$status'";
    }

    if (isset($files) && !empty($files)) {
      $str .= "/files/".urlencode($files);
      $table .= ",`jt_patches_files`, `files`";
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= " `files`.`name` LIKE '$files' AND `jt_patches_files`.`fileid`=`files`.`id` ";
      $where .= " AND `patches`.`patch`=`jt_patches_files`.`patchid` AND `patches`.`revision`=`jt_patches_files`.`revision`";
      //echo "SELECT $index FROM $table $where<br/>\n";
    }
  } else if (isset($pid) && !empty($pid)) {
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
  if (($idx = $my->fetchIndex($idxcount, $table, $where)))
  { 
    if (isset($idx[0]) && isset($idx[0]['c'])) {
      $nb = $idx[0]['c'];
    }
  }

  /* check if url is saying where to start... */
  if(isset($start) && !empty($start)) {

    if (preg_match("/[0-9]*/", $start)) {
      if ($start >= $nb) { /* could not start after the number of results... */
        $start = 0;
      }
    } else {
      $start = 0;
    }
  } else { /* otherwise start from scratch */
    $start = 0;
  }

  $where .= " LIMIT $start,$rpp";

  if ($nb && ($idx = $my->fetchIndex($index, $table, $where)))
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
  $page->set("content", $content);
  echo $page->fetch();
?>
