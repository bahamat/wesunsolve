<?php
/**
 * HTTP class
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2007-2008, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 */


class HTTP
{
  private static $_instance;    /* instance of the class */

  public $argc;
  public $argv;
  public $css;

  public function fetchCSS() {
    global $_GET;

    if (isset($_GET['r']) && !empty($_GET['r'])) {
      $where = "WHERE `id`=".mysqlCM::getInstance()->quote($_GET['r'])." LIMIT 0,1";
    } else {
      $where = "WHERE `is_default`=1 LIMIT 0,1";
    }

    $index = "`id`";
    $table = "`css`";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (!count($idx)) {
        return null;
      }
      $this->css = new CSS($idx[0]['id']);
      $this->css->fetchFromId();
    }
    return 0;
  }

  public static function errMysql() {
    global $start_time;
    $index = new Template("./tpl/index.tpl");
    $head = new Template("./tpl/head.tpl");
    $menu = new Template("./tpl/menu.tpl");
    $foot = new Template("./tpl/foot.tpl");
    $foot->set("start_time", $start_time);
    $content = new Template("./tpl/sorrypage.tpl");

    $index->set("head", $head);
    $index->set("menu", $menu);
    $index->set("content", $content);
    $index->set("foot", $foot);
    echo $index->fetch();
    die();
  }

  public static function errWWW($e) {
    global $start_time;
    $index = new Template("./tpl/index.tpl");
    $head = new Template("./tpl/head.tpl");
    $menu = new Template("./tpl/menu.tpl");
    $foot = new Template("./tpl/foot.tpl");
    $foot->set("start_time", $start_time);
    $content = new Template("./tpl/error.tpl");
    $content->set("error", $e);

    $index->set("head", $head);
    $index->set("menu", $menu);
    $index->set("content", $content);
    $index->set("foot", $foot);
    echo $index->fetch();
    die();
  }


  public static function Piwik($title) {
    global $config;
    @require_once('../../../libs/PiwikTracker.php');
     /* Log visit on piwik */
    $piwikTracker = new PiwikTracker( $config['piwikId'], $config['piwikUri']);
    $piwikTracker->setUrl( $_SERVER['REQUEST_URI'] );
    $piwikTracker->setTokenAuth( $config['piwikToken'] );
    $piwikTracker->doTrackPageView("Site News RSS Feed");
    return true;
  }

  public function parseUrl() {
    global $_SERVER;
    global $_GET;
    if (count($_GET)) {
	return;
    }
    if(!isset($_SERVER['PATH_INFO'])) {
      return;
    }
    $url = explode('/',$_SERVER['PATH_INFO']);
    $g = array();
    $idx = "";
    $val = "";
    for ($i=1,$s=0; $i<count($url); $i++) {
      if ($s == 0) {
	$idx = $url[$i];
	$g[$idx] = "";
	$s++;
      } else {
        $val = $url[$i];
	$g[$idx] = $val;
        $idx = "";
	$val = "";
	$s=0;
      }
    }
    $_GET = $g;
    return;
  }

 /**
  * return the instance of HTTP object
  */
  public static function getInstance()
  { 
    if (!isset(self::$_instance)) {
     $c = __CLASS__;
     self::$_instance = new $c;
    }
    return self::$_instance;
  }

 /**
  * Avoid the __clone method to be called
  */
  public function __clone()
  { 
    trigger_error("Cannot clone a singlton object, use ::instance()", E_USER_ERROR);
  }

 /**
  * Get the http post/get variable
  * @arg Name of the variable to get
  * @return the variable, with POST->GET priority
  */
  public function getHTTPVar($name) {
    global $_GET, $_POST;
   
    /* first check POST, then fallback on GET */
    if (isset($_POST[$name])) return $_POST[$name];
    if (isset($_GET[$name])) return $_GET[$name];
    return NULL;
  }

 /**
  * Sanitize an array by escaping the strings inside.
  * @arg Name of the variable to sanitize
  */
  public function sanitizeArray(&$var) {

    foreach($var as $name => $value) {

      if (is_array($value)) { 
        $this->sanitizeArray($value); 
        continue; 
      }

      $var[$name] = mysql_escape_string($value);

    }
  }

  public static function checkEmail($email) {
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    return false;
  }
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
      return false;
    }
  }
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|↪([A-Za-z0-9]+))$",$domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
  }

  public static function getDateFormat() {
    global $config;
    return $config['dateFormat'];
  } 

  public static function pagine($current_page, $nb_pages, $link='/page/%d', $around=3, $firstlast=1) {
	$pagination = '';
	$link = preg_replace('`%([^d])`', '%%$1', $link);
	if ( !preg_match('`(?<!%)%d`', $link) ) $link .= '%d';
	if ( $nb_pages > 1 ) {

		// Lien précédent
		if ( $current_page > 1 )
			$pagination .= '<a class="prevnext" href="'.sprintf($link, $current_page-1).'" title="Previous">&lt;&lt; Previous</a>';
		else
			$pagination .= '<span class="prevnext disabled">&lt;&lt; Previous</span>';

		// Lien(s) début
		for ( $i=1 ; $i<=$firstlast ; $i++ ) {
			$pagination .= ' ';
			$pagination .= ($current_page==$i) ? '<span class="current">'.$i.'</span>' : '<a href="'.sprintf($link, $i).'">'.$i.'</a>';
		}

		// ... après pages début ?
		if ( ($current_page-$around) > $firstlast+1 )
			$pagination .= ' &hellip;';

		// On boucle autour de la page courante
		$start = ($current_page-$around)>$firstlast ? $current_page-$around : $firstlast+1;
		$end = ($current_page+$around)<=($nb_pages-$firstlast) ? $current_page+$around : $nb_pages-$firstlast;
		for ( $i=$start ; $i<=$end ; $i++ ) {
			$pagination .= ' ';
			if ( $i==$current_page )
				$pagination .= '<span class="current">'.$i.'</span>';
			else
				$pagination .= '<a href="'.sprintf($link, $i).'">'.$i.'</a>';
		}

		// ... avant page nb_pages ?
		if ( ($current_page+$around) < $nb_pages-$firstlast )
			$pagination .= ' &hellip;';

		// Lien(s) fin
		$start = $nb_pages-$firstlast+1;
		if( $start <= $firstlast ) $start = $firstlast+1;
		for ( $i=$start ; $i<=$nb_pages ; $i++ ) {
			$pagination .= ' ';
			$pagination .= ($current_page==$i) ? '<span class="current">'.$i.'</span>' : '<a href="'.sprintf($link, $i).'">'.$i.'</a>';
		}

		// Lien suivant
		if ( $current_page < $nb_pages )
			$pagination .= ' <a class="prevnext" href="'.sprintf($link, ($current_page+1)).'" title="Next">Next &gt;&gt;</a>';
		else
			$pagination .= ' <span class="prevnext disabled">Next &gt;&gt;</span>';
	}
	return $pagination;
  }

}

?>
