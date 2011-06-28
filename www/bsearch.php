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

 $search = false;

 $title = "Bug search page...";
 $index = new Template("./tpl/index.tpl");
 $head = new Template("./tpl/head.tpl");
 $head->set("title", $title);
 $menu = new Template("./tpl/menu.tpl");
 $foot = new Template("./tpl/foot.tpl");
 $foot->set("start_time", $start_time);

 $rpp = $config['bugsPerPage'];
 if ($lm->isLogged) {
   $lo = $lm->o_login;
   if ($lo) {
     $lo->fetchData();
     $val = $lo->data('bugsPerPage');
     if ($val) $rpp = $val;
   }
 }

 
 $bugids = array();
 $table = "`bugids`";
 $indx = "`id`";
 $where = "";
 $w = 0;

 $my = mysqlCM::getInstance();
   if (isset($_POST['synopsis']) && !empty($_POST['synopsis'])) {
   $ftext = $_POST['synopsis'];
 } else if (isset($_GET['synopsis']) && !empty($_GET['synopsis'])) {
   $ftext = $_GET['synopsis'];
 }

 if (isset($_POST['bid']) && !empty($_POST['bid'])) {
   $bid = $_POST['bid'];
 } else if (isset($_GET['bid']) && !empty($_GET['bid'])) {
   $bid = $_GET['bid'];
 }

  if (isset($_POST['start']) && !empty($_POST['start'])) {
    $start = $_POST['start'];
  } else if (isset($_GET['start']) && !empty($_GET['start'])) {
    $start = $_GET['start'];
  }

  if (isset($bid) && !empty($bid)) {
    if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
    $where .= "`id` LIKE ".$my->quote($bid);
    $search = true;
  }

  if (isset($ftext) && !empty($ftext)) {
    if (!$w) { $where = "WHERE "; $w++; } else { $where .= " AND "; }
    $where .= " `synopsis` LIKE ".$my->quote($ftext);
    $search = true;
  }

  $where .= " ORDER BY `d_updated` DESC";
  $idxcount = "count($indx) as c";

  if (!$search) {
    $content = new Template("./tpl/fbsearch.tpl");
    goto screen;
  }

  /* first count max results */

  if (($idx = mysqlCM::getInstance()->fetchIndex($idxcount, $table, $where)))
  {
    $nb = 0;
    if (isset($idx[0]) && isset($idx[0]['c'])) {
      $nb = $idx[0]['c'];
    }
  }
  
  /* check if url is saying where to start... */
  if(isset($start) && !empty($start)) {

    if (preg_match("/[0-9]*/", $start)) {
      if ($start > $nb) { /* could not start after the number of results... */
	$start = 0;
      }
    } else {
      $start = 0;
    }
  } else { /* otherwise start from scratch */
    $start = 0;
  }
 
  $where .= " LIMIT $start,$rpp";

  if ($nb && ($idx = mysqlCM::getInstance()->fetchIndex($indx, $table, $where)))
  {
    foreach($idx as $t) {
      $g = new Bugid($t['id']);
      $g->fetchFromId();
      array_push($bugids, $g);
    }
  }
  $content = new Template("./tpl/bsearch.tpl");
  $content->set("bugids", $bugids);
  $content->set("start", $start);
  $content->set("nb", $nb);
  $content->set("rpp", $rpp);
  $str = "/bsearch/form/1";
  if (isset($ftext)) {
    $str .= "/synopsis/".urlencode($ftext);
    $content->set("ftext", $ftext);
  }
  if (isset($bid)) {
    $str .= "/bid/".urlencode($bid);
    $content->set("bid", $bid);
  }
  $content->set("str", $str);

screen:
  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("content", $content);

 echo $index->fetch();
?>
