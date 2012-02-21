<?php
/**
 * Patch Timeline object
 *
 * @author Gouverneur Thomas <thomas@espix.net>
 * @copyright Copyright (c) 2011-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class pTimeline extends mysqlObj
{
  /* Data Var */
  public $id = -1;
  public $id_patchdiag = -1;
  public $id_patch = -1;
  public $id_revision = -1;
  public $when = 0;
  public $f_added = 0;
  public $f_removed = 0;
  public $f_replace = 0;
  public $f_obs = 0;
  public $f_rec = 0;
  public $f_y2k = 0;
  public $f_sec = 0;
  public $f_bad = 0;
  public $f_other = 0;
  public $what = "";
  public $details = "";

  public $o_patch = null;

  public function tell() {
    if (!empty($this->what)) {
      $what = Patch::fromString($this->what);
      $what->fetchFromId();
      if (abs($what->revision - $this->id_revision) > 1) {
        $rjumped = "";
        if ($this->id_revision < $what->revision) {
          $low = $this->id_revision;
	  $high = $what->revision;
        } else if ($what->revision < $this->id_revision) {
          $high = $this->id_revision;
	  $low = $what->revision;
        }
        while(++$low < $high) {
	  $w = Patch::fromString($what->patch.'-'.$low);
	  $w->fetchFromId();
          $rjumped .= ' '.$w->shortLink(false, true);
        }
      }
    }

    if ($this->f_added) {
      return "Has been added to the set of patches";
    }
    if ($this->f_removed) {
      return "Has been removed from the set of patches";
    }
    if ($this->f_replace) {
      $ret = "Replace ".$what->shortLink(false, true);
      if (isset($rjumped)) $ret .= ' (Skipped releases: '.$rjumped.')';
      return $ret;
    }
    if ($this->f_obs == 1) {
      if (!empty($this->what)) {
	if ($what->patch == $this->id_patch) {
          return "obsoletes ".$what->shortLink(false, true);
	} else {
          return "obsoletes ".$what->link(false, true);
	}
      } else {
        return "is now Obsolete";
      }
    } else if ($this->f_obs == -1) {
        return "Has been reintroduced as non-Obsoleted";
    }
    if ($this->f_rec == 1) {
      return "is now Recommended Patch";
    } else if ($this->f_rec == -1) {
      return "is no longer a Recommended Patch";
    }
    if ($this->f_y2k == 1) {
      return "is now Y2K Patch";
    } else if ($this->f_y2k == -1) {
      return "is no longer a Y2K Patch";
    }
    if ($this->f_sec == 1) {
      return "is now Security Patch";
    } else if ($this->f_sec == -1) {
      return "is no longer a Security Patch";
    }
    if ($this->f_bad == 1) {
      return "is now BAD/WITHDRAWN Patch";
    } else if ($this->f_bad == -1) {
      return "is no longer a BAD/WITHDRAWN Patch";
    }
    return "Unknown operation";
  }

  public function fetchPatch() {
    $this->o_patch = new Patch($this->id_patch, $this->id_revision);
    return $this->o_patch->fetchFromId();
  }
  

 /**
  * Constructor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "p_timeline";
    $this->_nfotable = "";
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "id_patchdiag" => SQL_PROPE,
                        "id_patch" => SQL_PROPE,
                        "id_revision" => SQL_PROPE,
                        "when" => SQL_PROPE,
                        "f_added" => SQL_PROPE,
                        "f_removed" => SQL_PROPE,
                        "f_replace" => SQL_PROPE,
                        "f_obs" => SQL_PROPE,
                        "f_rec" => SQL_PROPE,
                        "f_sec" => SQL_PROPE,
                        "f_bad" => SQL_PROPE,
                        "f_y2k" => SQL_PROPE,
                        "f_other" => SQL_PROPE,
                        "what" => SQL_PROPE,
                        "details" => SQL_PROPE
                 );

    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "id_patchdiag" => "id_patchdiag",
                        "id_patch" => "id_patch",
                        "id_revision" => "id_revision",
                        "when" => "when",
                        "f_added" => "f_added",
                        "f_removed" => "f_removed",
                        "f_replace" => "f_replace",
                        "f_obs" => "f_obs",
                        "f_rec" => "f_rec",
                        "f_sec" => "f_sec",
                        "f_bad" => "f_bad",
                        "f_y2k" => "f_y2k",
                        "f_other" => "f_other",
                        "what" => "what",
                        "details" => "details"
                 );
  }

}
?>
