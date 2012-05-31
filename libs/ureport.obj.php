<?php
/**
 * UReport object
 *
 * @author Gouverneur Thomas <tgo@ians.be>
 * @copyright Copyright (c) 2011, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class UReport extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $id_owner = -1;
  public $id_plevel = "";
  public $pdiag_delay = 0;
  public $lastrun = 0;
  public $frequency = -1;
  public $added = -1;
  public $updated = -1;

  /* Objects */
  public $o_owner = null;
  public $o_pdiag = null;
  public $o_plevel = null;
  public $o_server = null;

  private $_cansend = false;

  public function __toString() {
    if ($this->o_pdiag && $this->o_plevel && $this->o_server) {
      return 'Report for '.$this->o_server.' / '.$this->o_plevel.' against '.$this->o_pdiag;
    }
    if ($this->o_plevel && $this->o_server) {
      return 'Report for '.$this->o_server.' / '.$this->o_plevel;
    }
    return 'Report ID '.$this->id;
  }

  public function run() {

    $this->_cansend = false;

    if (!$this->id_owner || !$this->id_plevel) {
      return -1;
    }

    if (!$this->o_owner) {
      $this->o_owner = new Login($this->id_owner);
      if ($this->o_owner->fetchFromId()) {
        return -2;
      }
    }
    
    if (!$this->o_plevel || !$this->o_server) {
      if ($this->fetchServer()) {
        return -3;
      }
    }

    /* Find proper patchdiag.xref */
    if (!$this->o_pdiag) {
      $this->o_pdiag = Patchdiag::fetchFirst($this->pdiag_delay);
      if (!$this->o_pdiag) {
        return -4;
      }
    }

    $this->o_plevel->fetchPatches(1);
    $this->o_plevel->fetchSRV4Pkgs(1);

    $this->o_plevel->parsePCA(null, $this->o_pdiag);

    $this->_cansend = true;

    return 0;
  }

  public function sendMail() {

    global $config;

    if (!$this->_cansend) {
      return -1;
    }

    $txt = $config['mlist']['header']."\n";
    $txt .= "<p>Notice: This report is BETA and no information contained here could be trusted as-is. The WeSunSolve! <a href=\"http://wiki.wesunsolve.net/Disclaimer\">disclaimer</a> apply.</p>\n";
    $txt .= '<p>Report generated for '.$this->o_server.' / '.$this->o_plevel.' based on '.$this->o_pdiag."</p>\n";
    $txt .= <<< EOF
<h2><a id="toc"></a>Table of contents</h2>
<ul class="toclist">
    <li><a href="#summary">Summary</a></li>
    <li><a href="#list_patches">List of patches to be installed</a></li>
    <li><a href="#cve_fix">Security issues addressed with list of patches</a></li>
    <li><a href="#cve_acc">Security issues addressed with accumulated list of patches</a></li>
  </ul>
  <h3><a id="summary"></a>Summary</h3>
  <ul class="listinfo">
EOF;

    $txt .= '<li>Patches to be installed: '.$this->o_plevel->cnt_pcap."</li>\n";
    $txt .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;Security patches: '.$this->o_plevel->cnt_pcas."</li>\n";
    $txt .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;Recommended patches: '.$this->o_plevel->cnt_pcar."</li>\n";
    $txt .= '<li>Accumulated patches: '.$this->o_plevel->cnt_pcaa."</li>\n";
    $txt .= '<li>Patches fixing CVE: '.$this->o_plevel->cnt_cvep."</li>\n";
    $txt .= '</ul>';
    $txt .= '<h3><a id="list_patches"></a>List of patches to be installed as of '.$this->o_pdiag."</h3>\n";
    $txt .= <<< EOF
  <div class="ctable">
  <table class="ctable"><tr><td class="greentd">RECOMMENDED</td><td class="orangetd">SECURITY</td><td class="redtd">WITHDRAWN</td></tr></table>
  <table class="ctable">
    <tr>
      <th>Patch</th>
      <th>Released</th>
      <th>Installed</th>
      <th>Synopsis</th>
    </tr>
EOF;

    foreach($this->o_plevel->a_ppatches as $p) {
      $txt .= "<tr>\n";
      $txt .= '<td '.$p->color().'>'.$p->link(1)."</a></td>\n";
      $txt .= '<td>'.date(HTTP::getDateFormat(), $p->releasedate)."</td>\n";
      if ($p->o_current) {
        $txt .= '<td>'.$p->o_current->link(1)."</td>\n";
      } else {
        $txt .= "<td>None</td>\n";
      }
      $txt .= '<td style="text-align: left;">'.$p->synopsis."</td>\n</tr>\n";
    }
    $txt .= <<< EOF
  </table>
  </div>
  <p><br/><a href="#top"><img alt="back to top" src="http://wesunsolve.net/img/arrow_up.png">back to top</a></p>
  <h3><a id="cve_fix"></a>CVE Fixed by list of patches</h3>
  <div class="ctable">
  <table id="tbl_cves" class="ctable">
   <tr>
    <th>Patch</th>
    <th>Affected</th>
    <th>CVE</th>
    <th>Score</th>
    <th>Patch release date</th>
   </tr>
EOF;

    $i = 0;
    foreach($this->o_plevel->a_pcvep as $p) {
      $txt .= "<tr>\n";
      $txt .= '<td '.$p->color().'>';
      if ($p->isNew()) { 
        $txt .= '<img class="newimg" src="http://wesunsolve.net/img/new.png" alt="New"/>'."\n";
      } 
      $txt .= $p->link(1);
      $txt .= "</td>\n";
      $txt .= '<td style="text-align: left">'.$p->o_cve->affect."</td>\n";
      $txt .= '<td '.$p->o_cve->color().'>'.$p->o_cve->link(1)."</td>\n";
      $txt .= '<td>'.$p->o_cve->score."</td>\n";
      $txt .= '<td>'.date(HTTP::getDateFormat(), $p->releasedate)."</td>\n";
      $txt .= "</tr>\n";
      $i++; 
    }

    $txt .= <<< EOF
</table>
  </div>
  <p><br/><a href="#top"><img alt="back to top" src="http://wesunsolve.net/img/arrow_up.png">back to top</a></p>
  <h3><a id="cve_acc"></a>CVE fixed by accumulated patches</h3>
  <div class="ctable">
  <table id="tbl_cves" class="ctable">
   <tr>
    <th>Patch</th>
    <th>Affected</th>
    <th>CVE</th>
    <th>Score</th>
    <th>Patch release date</th>
   </tr>
EOF;

    $i = 0;
    foreach($this->o_plevel->a_apcvep as $p) {
      $txt .= "<tr>\n";
      $txt .= '<td '.$p->color().'>';
      if ($p->isNew()) { 
        $txt .= '<img class="newimg" src="http://wesunsolve.net/img/new.png" alt="New"/>'."\n";
      } 
      $txt .= $p->link(1);
      $txt .= "</td>\n";
      $txt .= '<td style="text-align: left">'.$p->o_cve->affect."</td>\n";
      $txt .= '<td '.$p->o_cve->color().'>'.$p->o_cve->link(1)."</td>\n";
      $txt .= '<td>'.$p->o_cve->score."</td>\n";
      $txt .= '<td>'.date(HTTP::getDateFormat(), $p->releasedate)."</td>\n";
      $txt .= "</tr>\n";
      $i++; 
    }

    $txt .= <<< EOF
</table>
  </div>
  <p><br/><a href="#top"><img alt="back to top" src="http://wesunsolve.net/img/arrow_up.png">back to top</a></p>
EOF;

    $txt .= "\n".$config['mlist']['footer']."\n";

    return MList::_sendTo($this->o_owner, $txt, $this);
  }
 
  public function nextrun() {
    if (!$this->lastrun) {
      /* We remove 23h to this to ensure it will be sent at the next run */
      $this->lastrun = time() - (3600*23) - $this->frequency;
    }
    return $this->lastrun + $this->frequency;
  }

  public function fetchServer() {
    if (!$this->o_plevel) {
      $this->o_plevel = new PLevel($this->id_plevel);
      if ($this->o_plevel->fetchFromId()) {
        return -1;
      }
    }
    if ($this->o_plevel->fetchServer())
      return -1;
    $this->o_server = $this->o_plevel->o_server;
    return 0;
  }

  public function insert() {
    $this->added = time();
    parent::insert();
  }

  public function update() {
    $this->updated = time();
    parent::update();
  }

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "u_report";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_plevel" => SQL_PROPE,
                        "id_owner" => SQL_PROPE,
                        "pdiag_delay" => SQL_PROPE,
                        "lastrun" => SQL_PROPE,
                        "frequency" => SQL_PROPE,
                        "added" => SQL_PROPE,
                        "updated" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_plevel" => "id_plevel",
                        "id_owner" => "id_owner",
                        "pdiag_delay" => "pdiag_delay",
                        "lastrun" => "lastrun",
                        "frequency" => "frequency",
                        "added" => "added",
                        "updated" => "updated"
                 );
  }

}
?>
