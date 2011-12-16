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
 
  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);

  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("head", $head);

  $str = "/pkgsearch/form/1";

  $pkgs = array();
  $table = "`pkg`";
  $idx = "`pkg`.`id`";
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

  if (isset($_POST['name']) && !empty($_POST['name'])) {
    $name = $_POST['name'];
  } else if (isset($_GET['name']) && !empty($_GET['name'])) {
    $name = $_GET['name'];
  }
  if (isset($_POST['desc']) && !empty($_POST['desc'])) {
    $desc = $_POST['desc'];
  } else if (isset($_GET['desc']) && !empty($_GET['desc'])) {
    $desc = $_GET['desc'];
  }
  if (isset($_POST['files']) && !empty($_POST['files'])) {
    $files = $_POST['files'];
  } else if (isset($_GET['files']) && !empty($_GET['files'])) {
    $files = $_GET['files'];
  }
  if (isset($_GET['form']) && $_GET['form'] == 1) {

    if (isset($name) && !empty($name)) {
      $name = trim($name);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "`name` LIKE '$name'";
      $str .= "/name/".urlencode($name);
    }
    if (isset($desc) && !empty($desc)) {
      $desc = trim($desc);
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= "(`summary` LIKE '$desc' OR `desc` LIKE '$desc')";
      $str .= "/desc/".urlencode($desc);
    }

    if (isset($files) && !empty($files)) {
      $str .= "/files/".urlencode($files);
      $table .= ",`jt_pkg_files`, `files`";
      if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
      $where .= " `files`.`name` LIKE '$files' AND `jt_pkg_files`.`fileid`=`files`.`id` ";
      $where .= " AND `pkg`.`id`=`jt_pkg_files`.`id_pkg`";
    }
  } else {
    $content = new Template("./tpl/pkgsearch.tpl");
    goto screen;
  }

/* else if (isset($pid) && !empty($pid)) {
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
  }*/

    if (!isset($idxcount)) $idxcount = "count(`pkg`.`id`) as c";

  if (!$fts) $where .= " ORDER BY `pkg`.`pstamp` DESC";

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
      $g = new Pkg($t['id']);
      $g->fetchFromId();
      if (isset($t['score'])) $g->score = round($t['score']);
      array_push($pkgs, $g);
    }
  }

  $content = new Template("./tpl/pkgsearch_r.tpl");
  $content->set("pkgs", $pkgs);
  $content->set("start", $start);
  $content->set("nb", $nb);
  $content->set("rpp", $rpp);
  $content->set("score", $fts);
  $content->set("str", $str);
  $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));
screen:
  $index->set("content", $content);
  echo $index->fetch();
?>
