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
   $lo->fetchUCLists();
   if ($lo) {
     $val = $lo->data('patchPerPage');
     if ($val) $rpp = $val;
   }
 }

 if (isset($_POST['page']) && !empty($_POST['page'])) {
   $page = $_POST['page'];
 } else if (isset($_GET['page']) && !empty($_GET['page'])) {
   $page = $_GET['page'];
 } else {
   $page = 1;
 }
 $nb_page = 0;

  $ru = array();
  $table = "`p_readmes`";
  $index = "`patch`, `revision`, `when`";
  $icount = "count(`patch`) as c";
  $where = "where `when`!=0 order by `when` desc";

  if (($idx = mysqlCM::getInstance()->fetchIndex($icount, $table, $where)))
  {
    $nb = 0;
    if (isset($idx[0]) && isset($idx[0]['c'])) {
      $nb = $idx[0]['c'];
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

  if ($nb && ($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
  {
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
        $k->fetchFromId();
        $k->lmod = $t['when'];
        $ru[$k->lmod] = $k;
      }
  }

 $head_add = '';

 $title = 'Last modified Solaris patches READMEs - results from '.$start.' to '.($start+$rpp).' (of '.$nb.')';

  $index = new Template("./tpl/index.tpl");
  $head = new Template("./tpl/head.tpl");
  $head->set("title", $title);
  $head->set("head_add", $head_add);
  $menu = new Template("./tpl/menu.tpl");
  $foot = new Template("./tpl/foot.tpl");
  $foot->set("start_time", $start_time);
  $content = new Template("./tpl/lastreadme.tpl");
  $str = "/lastreadme";
  $content->set("ru", $ru);
  $content->set("start", $start);
  $content->set("nb", $nb);
  $content->set("rpp", $rpp);
  $content->set("title", $title);
  $content->set("str", $str);
  $content->set("pagination", HTTP::pagine($page, $nb_page, $str."/page/%d"));
  if (isset($lo) && $lo) {
    $content->set("l", $lo);
  }

  $index->set("head", $head);
  $index->set("menu", $menu);
  $index->set("foot", $foot);
  $index->set("content", $content);
  echo $index->fetch();
?>
