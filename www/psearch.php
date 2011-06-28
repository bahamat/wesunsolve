<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
    die($argv[0]." Error with SQL db: ".$m->getError()."\n");
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();
 
  $page = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);

  $page->set("head", $head);
  $page->set("menu", $menu);
  $page->set("foot", $foot);

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

  if (isset($_GET['form']) && $_GET['form'] == 1) {

    if (isset($_POST['pid']) && !empty($_POST['pid'])) {
      $pid = $_POST['pid'];
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`patch` LIKE '$pid'";
    }
    if (isset($_POST['rev']) && !empty($_POST['rev'])) {
      $rev = $_POST['rev'];
      $rev = sprintf("%d", $rev);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`revision` LIKE '$rev'";
    }
    if (isset($_POST['synopsis']) && !empty($_POST['synopsis'])) {
      $synopsis = $_POST['synopsis'];
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`synopsis` LIKE '$synopsis'";
    }

    if (isset($_POST['status']) && !empty($_POST['status'])) {
      $pid = $_POST['status'];
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`status` LIKE '$status'";
    }

    if (isset($_POST['files']) && !empty($_POST['files'])) {
      $files = $_POST['files'];
      $table .= ",`jt_patches_files`, `files`";
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= " `files`.`name` LIKE '$files' AND `jt_patches_files`.`fileid`=`files`.`id` ";
      $where .= " AND `patches`.`patch`=`jt_patches_files`.`patchid` AND `patches`.`revision`=`jt_patches_files`.`revision`";
      //echo "SELECT $index FROM $table $where<br/>\n";
    }
  } else if (isset($_POST['pid']) && !empty($_POST['pid'])) {
    if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
    $pid = $_POST['pid'];
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


  $where .= " ORDER BY `releasedate` DESC,`revision` DESC LIMIT 0,50";
  if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Patch($t['patch'], $t['revision']);
      $g->fetchFromId();
      array_push($patches, $g);
    }
  }

  $content = new Template("./tpl/psearch.tpl");
  $content->set("patches", $patches);
  $page->set("content", $content);
  echo $page->fetch();
?>
