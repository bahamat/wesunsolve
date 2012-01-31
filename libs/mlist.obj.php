<?php
/**
 * MList object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class MList extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $name = "";
  public $sdesc = "";
  public $example = "";
  public $frequency = "";
  public $fct = "";

  public $a_logins = array();

  function liveCount() {
    $table = "`jt_login_mlist`";
    $index = "count(`id_login`) as c";
    $where = "WHERE `id_mlist`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      if (isset($idx[0]) && isset($idx[0]['c'])) {
        return $idx[0]['c'];
      }
    }
    return 0;
  }

  function fetchLogins() {
    $this->a_logins = array();
    $table = "`jt_login_mlist`";
    $index = "`id_login`";
    $where = "WHERE `id_mlist`='".$this->id."'";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Login($t['id_login']);
        $k->fetchFromId();
        array_push($this->a_logins, $k);
      }
    }
    return 0;
  }

  function addLogin($k) {

    $table = "`jt_login_mlist`";
    $names = "`id_login`, `id_mlist`";
    $values = "'$k->id', '".$this->id."'";

    if (mysqlCM::getInstance()->insert($names, $values, $table)) {
      return -1;
    }
    array_push($this->a_logins, $k);
    return 0;
  }

  function delLogin($k) {

    $table = "`jt_login_mlist`";
    $where = " WHERE `id_login`='".$k->id."' AND `id_mlist`='".$this->id."'";

    if (mysqlCM::getInstance()->delete($table, $where)) {
      return -1;
    }
    foreach ($this->a_logins as $ak => $v) {
      if ($k->id == $v->id) {
        unset($this->a_logins[$ak]);
      }
    }
    return 0;
  }

  function isLogin($p) {
    foreach($this->a_logins as $po)
      if ($p->id == $po->id)
        return TRUE;
    return FALSE;
  }

  function sendToAll() {
    if (!method_exists('Mlist', $this->fct)) {
      return false;
    }
    $fct = $this->fct;
    $mlc = Mlist::$fct();
    foreach($this->a_logins as $l) {
      $this->sendTo($l, $mlc);
      echo "[-] Sending mail to ".$l->email."\n";
    }
  }

  public function sendTo($login, $content) {
    global $config;
    $from = '"'.$config['mailName']."\" <".$config['mailFrom'].">";
    $headers = "";
    $headers = "From: $from\r\n";
    $headers .= "Reply-to: ".$config['mailFrom']."\r\n";
    $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";

    mail($login->email, "[SUNSOLVE] ".$this->name, $content, $headers);

    return true;
  }

  public function example() {
    if (!method_exists('Mlist', $this->fct)) {
      return false;
    }
    $fct = $this->fct;
    $mlc = Mlist::$fct();
    return $mlc;
  }

 /**
  * Implementation of mailling list text generation for recurrents ones
  *
  * @TODO: * Add colors and patches flags
  */
  static public function patchesWeekly() {
    global $config;
    $txt = $config['mlist']['header']."\n";
    $lwpatches = array();

    $p_stop = time();
    $p_start = $p_stop - (60*60*24*7);
    $d_stop = date(HTTP::getDateFormat(), $p_stop);
    $d_start = date(HTTP::getDateFormat(), $p_start);

    /* Gather everything we need */

    /**Readme*/
    $readmes = array();
    $table = "`p_readmes`";
    $index = "`patch`, `revision`, `when`";
    $where = "where `when`<=$p_stop and `when`>=$p_start order by `when` desc";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    {
      foreach($idx as $t) {
        $k = new Readme($t['patch'], $t['revision'], 0);
        $k->fetchFromId();
        $lines = explode(PHP_EOL, $k->diff);
	if (count($lines) == 13) continue;
        $k->fetchPatch();
        $k->o_patch->fetchData();
        $arch = $k->o_patch->data("arch");
        if (empty($arch)) {
           $arch = "all";
        } else {
          $arch = explode(' ', $arch);
          $arch = $arch[0];
          $arch = explode('.', $arch);
          $arch = $arch[0];
        }
	$d = date(HTTP::getDateFormat(), $t['when']);
	if (!isset($readmes[$d])) {
	  $readmes[$d] = array();
	}
        if (!isset($readmes[$d][$arch])) {
          $readmes[$d][$arch] = array();
        }

	$readmes[$d][$arch][] = $k;
      }
    }
    
    $patches = array();
    $table = "`patches`";
    $index = "`patch`, `revision`";
    $where = "WHERE `releasedate` > $p_start AND `releasedate` < $p_stop";
    $where .= " ORDER BY `releasedate` ASC";

    if (($idx = mysqlCM::getInstance()->fetchIndex($index, $table, $where)))
    { 
      foreach($idx as $t) {
        $k = new Patch($t['patch'], $t['revision']);
        $k->fetchFromId();
        $k->fetchBugids();
	$d = date(HTTP::getDateFormat(), $k->releasedate);
        $k->fetchData();
	$arch = $k->data("arch");
	if (empty($arch)) {
	   $arch = "all";
	} else {
	  $arch = explode(' ', $arch);
	  $arch = $arch[0];
	}
        if (!isset($patches[$d])) {
	  $patches[$d] = array();
        }
	if (!isset($patches[$d][$arch])) {
	  $patches[$d][$arch] = array();
	}
	$patches[$d][$arch][] = $k;
      }
    }
    $txt .= "<p>Notice: This mailling list is BETA and no information contained here could be trusted as-is. The WeSunSolve! <a href=\"http://wesunsolve.net/disclaimer\">disclaimer</a> apply.</p>\n"; 
    $txt .= "<h2>Table of contents</h2>\n";
    $txt .= "<ul>\n";
     $txt .= "<li><a href=\"#patches\">Patches released</a><br/>\n";
     $txt .= "    <ul style=\"list-style-type: square;\">\n";
     foreach($patches as $date => $p) {
       $cnt = arrayCount($p)-count($p);
       $txt .= "     <li><a href=\"#patches_$date\">Released on $date</a> ($cnt patches)<br/>\n";
       $txt .= "      <ul style=\"circle\">\n";
       foreach($p as $arch => $ps) {
         $cnt2 = count($ps);
         $txt .= "     <li><a href=\"#patches_${date}_$arch\">$arch</a>: ";
	 foreach($ps as $p) { $txt .= "<a ".$p->color()." href=\"#patch_".$p->name()."\">".$p->name()."</a> "; }
         $txt .= "</li>\n";
       }
       $txt .= "    </ul><br/></li>\n";
     }
     $txt .= "    </ul></li>\n";
     $txt .= "<li><a href=\"#readmes\">Readmes changes</a><br/>\n";
     $txt .= "    <ul style=\"list-style-type: square;\">\n";
     foreach($readmes as $date => $p) {
       $cnt = arrayCount($p)-count($p);
       $txt .= "     <li><a href=\"#readmes_$date\">Released on $date</a> ($cnt changes)<br/>\n";
       $txt .= "      <ul style=\"circle\">\n";
       foreach($p as $arch => $ps) {
         $cnt2 = count($ps);
         $txt .= "     <li><a href=\"#readmes_${date}_$arch\">$arch</a>: ";
	 foreach($ps as $p) { $txt .= "<a ".$p->o_patch->color()." href=\"#readme_".$p->o_patch->name()."\">".$p->o_patch->name()."</a> "; }
         $txt .= "</li>\n";
       }
       $txt .= "    </ul><br/></li>\n";

     }
     $txt .= "    </ul><br/></li>\n";

    $txt .= "</ul>\n";

    $txt .= <<< EOF
    <table id="legend" class="ctable"><tr>
                                <td class="greentd">RECOMMENDED</td>
                                <td class="orangetd">SECURITY</td>
                                <td class="redtd">WITHDRAWN</td>
                                <td class="violettd">OBSOLETE</td>
                        </tr></table>
EOF;


    $txt .= "<a id=\"patches\"></a><h2>Patches released from $d_start and $d_stop</h2>\n";

    foreach($patches as $date => $patchs) {
      $txt .= "<a id=\"patches_$date\"></a><h3>Released on $date</h3>\n";
      foreach($patchs as $arch => $patchss) {
        $txt .= "<a id=\"patches_${date}_$arch\"></a><h4>$arch</h4>\n";
        $txt .= "<ul>\n";
	foreach($patchss as $p) {
          $txt .= "<a id=\"patch_".$p->name()."\"></a><li>".$p->link(1, true)." [".$p->flags()."] - ".$p->synopsis."<br/>\n";
	  $txt .= "  <ul style=\"list-style-type: square;\">\n";
          foreach($p->a_bugids as $bug) {
            $txt .= "<li>".$bug->link(1)." - ".$bug->synopsis."</li>\n";
	  }
	  $txt .= "</ul><br/></li>\n";
	}
        $txt .= "</ul>\n";
      }
    }

    $txt .= "<a id=\"readmes\"></a><h2>Readme changes from $d_start and $d_stop</h2>\n";

    foreach($readmes as $date => $rmes) {
      $txt .= "<a id=\"readmes_$date\"></a><h3>Changed on $date</h3>\n";
      foreach($rmes as $arch => $rms2) {
        $txt .= "<a id=\"readmes_${date}_$arch\"></a><h4>$arch</h4>\n";
        $txt .= "<ul>\n";
        foreach($rms2 as $readme) {
          $txt .= "<a id=\"readme_".$readme->o_patch->name()."\"></a><h4>Changed readme for patch ".$readme->o_patch->link(1, true)." [".$p->flags()."] - ".$readme->o_patch->synopsis."</h4>\n";
  	  $txt .= "<pre>".htmlspecialchars($readme->diff)."</pre>";
        }
	$txt .= "</ul><br/></li>\n";
      }
      $txt .= "</ul>\n";
    }

    $txt .= "\n".$config['mlist']['footer'];
    return $txt;
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "mlist";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE,
                        "sdesc" => SQL_PROPE,
                        "example" => SQL_PROPE,
                        "fct" => SQL_PROPE,
                        "frequency" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "sdesc" => "sdesc",
                        "example" => "example",
                        "fct" => "fct",
                        "frequency" => "frequency"
                 );
  }

}
?>
